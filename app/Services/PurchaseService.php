<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FinancialTransaction;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(
        private readonly StockMovementService $stockMovementService,
        private readonly FinancialTransactionService $financialTransactionService,
    ) {}

    /**
     * Create a purchase, update stock, and create double-entry-like accounting transactions.
     *
     * @param  array<int, array{product_id:int,variant_id:int|null,quantity:int,price:float|string}>  $items
     */
    public function createPurchase(int $supplierId, array $items): Purchase
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

        $variantsByProductId = ProductVariant::query()
            ->whereIn('product_id', $productIds)
            ->get(['id', 'product_id'])
            ->groupBy('product_id');

        $purchase = DB::transaction(function () use (
            $supplierId,
            $items,
            $productIds,
            $variantIds,
            $variantsByProductId,
            &$totalAmount,
            &$normalizedItems
        ) {
            $supplier = Supplier::query()->find($supplierId);
            if (! $supplier) {
                throw ValidationException::withMessages(['supplier_id' => ['Invalid supplier selected.']]);
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

            $supplierAccount = $this->getOrCreateSupplierAccount($supplier->id);
            $inventoryExpenseAccount = $this->getOrCreateBusinessAccount('Inventory / Purchases', 'expense');

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

                $resolvedVariantId = null;
                if ($hasVariants) {
                    if (! $variantId) {
                        throw ValidationException::withMessages(['stock' => ['Variant is required for '.$product->name.'.']]);
                    }

                    $variant = $variants->get($variantId);
                    if (! $variant || (int) $variant->product_id !== $productId) {
                        throw ValidationException::withMessages(['stock' => ['Invalid variant selected for '.$product->name.'.']]);
                    }

                    $resolvedVariantId = $variantId;
                }

                $lineTotal = $qty * $price;
                $totalAmount += $lineTotal;

                $normalizedItems[] = [
                    'product_id' => $productId,
                    'variant_id' => $resolvedVariantId,
                    'quantity' => $qty,
                    'price' => $price,
                ];
            }

            $purchase = Purchase::create([
                'supplier_id' => $supplierId,
                'total_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'paid_amount' => 0,
                'due_amount' => $totalAmount,
            ]);

            $purchaseItems = [];

            foreach ($normalizedItems as $row) {
                $purchaseItems[] = [
                    'purchase_id' => $purchase->id,
                    'product_id' => $row['product_id'],
                    'variant_id' => $row['variant_id'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Update stock by purchased quantities.
                if ($row['variant_id']) {
                    ProductVariant::where('id', $row['variant_id'])->increment('stock_quantity', $row['quantity']);
                } else {
                    Product::where('id', $row['product_id'])->increment('stock_quantity', $row['quantity']);
                }

                $this->stockMovementService->record(
                    StockMovement::TYPE_PURCHASE,
                    $row['quantity'],
                    'purchase',
                    $purchase->id,
                    $row['variant_id'] ? null : $row['product_id'],
                    $row['variant_id'],
                    Auth::id(),
                );
            }

            PurchaseItem::insert($purchaseItems);

            // Keep base product stock in sync (sum of variants).
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

            // Double-entry-like accounting transactions (2 lines).
            DB::table('transactions')->insert([
                [
                    'account_id' => $supplierAccount->id,
                    'type' => 'credit',
                    'amount' => $totalAmount,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'description' => 'Purchase credit (Supplier AP)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'account_id' => $inventoryExpenseAccount->id,
                    'type' => 'debit',
                    'amount' => $totalAmount,
                    'reference_type' => 'purchase',
                    'reference_id' => $purchase->id,
                    'description' => 'Purchase debit (Inventory / Purchases)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $this->financialTransactionService->record(
                FinancialTransaction::TYPE_PURCHASE,
                (float) $totalAmount,
                FinancialTransaction::ACCOUNT_TYPE_CREDIT,
                'purchase',
                (int) $purchase->id,
                null,
                $supplierId,
                Auth::id(),
            );

            return $purchase;
        });

        return $purchase;
    }

    private function getOrCreateSupplierAccount(int $supplierId): Account
    {
        $name = 'Supplier AP #'.$supplierId;

        return Account::query()->firstOrCreate([
            'type' => 'supplier',
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
