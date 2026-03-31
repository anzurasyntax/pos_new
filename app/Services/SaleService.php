<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerProductPrice;
use App\Models\CustomerVariantPrice;
use App\Models\FinancialTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private readonly StockMovementService $stockMovementService,
        private readonly FinancialTransactionService $financialTransactionService,
    ) {}

    /**
     * Create a sale, update stock + customer last prices, create customer ledger
     * and create double-entry-like accounting transactions.
     *
     * @param  array<int, array{product_id:int,variant_id:int|null,quantity:int,price:float|string}>  $items
     */
    public function createSale(int $customerId, array $items): Sale
    {
        $items = array_values($items);
        if (empty($items)) {
            throw ValidationException::withMessages(['items' => ['At least one item is required.']]);
        }

        $productIds = collect($items)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $variantIds = collect($items)
            ->pluck('variant_id')
            ->filter(fn ($id) => ! empty($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $totalAmount = 0.0;
        $normalizedItems = [];

        // Pre-resolve whether products have variants (no locks needed here).
        $variantsByProductId = ProductVariant::query()
            ->whereIn('product_id', $productIds)
            ->get(['id', 'product_id'])
            ->groupBy('product_id');

        $sale = DB::transaction(function () use (
            $customerId,
            $items,
            $productIds,
            $variantIds,
            $variantsByProductId,
            &$totalAmount,
            &$normalizedItems
        ) {
            $customer = Customer::query()->find($customerId);
            if (! $customer) {
                throw ValidationException::withMessages(['customer_id' => ['Invalid customer selected.']]);
            }

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $variants = ProductVariant::query()
                ->whereIn('id', $variantIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Accounting accounts (created if missing).
            $customerAccount = $this->getOrCreateCustomerAccount($customer->id);
            $salesRevenueAccount = $this->getOrCreateBusinessAccount('Sales Revenue', 'expense');

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $variantId = ! empty($item['variant_id']) ? (int) $item['variant_id'] : null;
                $qty = (int) $item['quantity'];
                $price = (float) $item['price'];

                $product = $products->get($productId);
                if (! $product) {
                    throw ValidationException::withMessages(['stock' => ['Invalid product selected.']]);
                }

                $hasVariants = $variantsByProductId->has($productId) && $variantsByProductId->get($productId)->count() > 0;

                if ($hasVariants) {
                    if (! $variantId) {
                        throw ValidationException::withMessages(['stock' => ['Please select a variant for '.$product->name.'.']]);
                    }

                    $variant = $variants->get($variantId);
                    if (! $variant || (int) $variant->product_id !== $productId) {
                        throw ValidationException::withMessages(['stock' => ['Invalid variant selected for '.$product->name.'.']]);
                    }
                }

                $lineTotal = $qty * $price;
                $totalAmount += $lineTotal;

                $normalizedItems[] = [
                    'product_id' => $productId,
                    'variant_id' => $hasVariants ? $variantId : null,
                    'quantity' => $qty,
                    'price' => $price,
                ];
            }

            $requiredByKey = [];
            foreach ($normalizedItems as $row) {
                $pk = $row['product_id'].'|'.($row['variant_id'] ?? '');
                $requiredByKey[$pk] = ($requiredByKey[$pk] ?? 0) + $row['quantity'];
            }

            foreach ($requiredByKey as $pk => $needQty) {
                [$pidStr, $vStr] = explode('|', $pk, 2);
                $pid = (int) $pidStr;
                $variantKey = $vStr === '' ? null : (int) $vStr;
                $product = $products->get($pid);
                $hasVariants = $variantsByProductId->has($pid) && $variantsByProductId->get($pid)->count() > 0;

                if ($hasVariants) {
                    $variant = $variants->get($variantKey);
                    if (! $variant) {
                        throw ValidationException::withMessages(['stock' => ['Invalid variant for stock check.']]);
                    }
                    $avail = (int) $variant->stock_quantity;
                    if ($needQty > $avail) {
                        throw ValidationException::withMessages([
                            'stock' => ['Insufficient stock for '.$product->name.' ('.$variant->variant_name.'). Available '.$avail.', requested '.$needQty.'.'],
                        ]);
                    }
                } else {
                    $avail = (int) $product->stock_quantity;
                    if ($needQty > $avail) {
                        throw ValidationException::withMessages([
                            'stock' => ['Insufficient stock for '.$product->name.'. Available '.$avail.', requested '.$needQty.'.'],
                        ]);
                    }
                }
            }

            $sale = Sale::create([
                'customer_id' => $customerId,
                'total_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'paid_amount' => 0,
                'due_amount' => $totalAmount,
            ]);

            $saleItems = [];

            foreach ($normalizedItems as $row) {
                $saleItems[] = [
                    'sale_id' => $sale->id,
                    'product_id' => $row['product_id'],
                    'variant_id' => $row['variant_id'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Update stock.
                if ($row['variant_id']) {
                    ProductVariant::where('id', $row['variant_id'])->decrement('stock_quantity', $row['quantity']);
                } else {
                    Product::where('id', $row['product_id'])->decrement('stock_quantity', $row['quantity']);
                }

                $this->stockMovementService->record(
                    StockMovement::TYPE_SALE,
                    -$row['quantity'],
                    'sale',
                    $sale->id,
                    $row['variant_id'] ? null : $row['product_id'],
                    $row['variant_id'],
                    Auth::id(),
                );

                // Store last price for this customer.
                if ($row['variant_id']) {
                    CustomerVariantPrice::updateOrCreate(
                        [
                            'customer_id' => $customerId,
                            'variant_id' => $row['variant_id'],
                        ],
                        [
                            'last_price' => $row['price'],
                        ]
                    );
                } else {
                    CustomerProductPrice::updateOrCreate(
                        [
                            'customer_id' => $customerId,
                            'product_id' => $row['product_id'],
                        ],
                        [
                            'last_price' => $row['price'],
                        ]
                    );
                }
            }

            SaleItem::insert($saleItems);

            // Keep base product stock in sync with sum(variants) when variants exist.
            $productsWithVariants = $variantsByProductId->keys()->map(fn ($id) => (int) $id)->all();
            if (! empty($productsWithVariants)) {
                $sums = ProductVariant::query()
                    ->selectRaw('product_id, SUM(stock_quantity) as total_stock')
                    ->whereIn('product_id', $productsWithVariants)
                    ->groupBy('product_id')
                    ->get();

                foreach ($sums as $sum) {
                    Product::where('id', (int) $sum->product_id)->update([
                        'stock_quantity' => (int) $sum->total_stock,
                    ]);
                }
            }

            // Customer ledger: debit for sale total.
            $prevBalance = (float) CustomerLedger::query()
                ->where('customer_id', $customerId)
                ->orderByDesc('id')
                ->value('balance');

            if (! is_finite($prevBalance)) {
                $prevBalance = 0.0;
            }

            CustomerLedger::create([
                'customer_id' => $customerId,
                'debit' => $totalAmount,
                'credit' => 0,
                'balance' => $prevBalance + $totalAmount,
            ]);

            // Double-entry-like accounting transactions (2 lines).
            DB::table('transactions')->insert([
                [
                    'account_id' => $customerAccount->id,
                    'type' => 'debit',
                    'amount' => $totalAmount,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'description' => 'Sale debit (Customer AR)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'account_id' => $salesRevenueAccount->id,
                    'type' => 'credit',
                    'amount' => $totalAmount,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'description' => 'Sale credit (Sales Revenue)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $this->financialTransactionService->record(
                FinancialTransaction::TYPE_SALE,
                (float) $totalAmount,
                FinancialTransaction::ACCOUNT_TYPE_CREDIT,
                'sale',
                (int) $sale->id,
                $customerId,
                null,
                Auth::id(),
            );

            return $sale;
        });

        return $sale;
    }

    private function getOrCreateCustomerAccount(int $customerId): Account
    {
        $name = 'Customer AR #'.$customerId;

        return Account::query()->firstOrCreate([
            'type' => 'customer',
            'name' => $name,
        ]);
    }

    private function getOrCreateBusinessAccount(string $name, string $type): Account
    {
        return Account::query()->firstOrCreate([
            'type' => $type,
            'name' => $name,
        ]);
    }
}
