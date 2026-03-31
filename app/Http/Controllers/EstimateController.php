<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConvertEstimateRequest;
use App\Http\Requests\StoreEstimateRequest;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\PaymentService;
use App\Services\SalePaymentAccountResolver;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EstimateController extends Controller
{
    public function store(StoreEstimateRequest $request): JsonResponse
    {
        $customerId = $request->input('customer_id');
        $newName = trim((string) $request->input('new_customer_name', ''));

        if (empty($customerId) && $newName !== '') {
            $customer = Customer::create([
                'name' => $newName,
                'phone' => null,
                'address' => null,
            ]);
            $customerId = $customer->id;
        }

        $customerId = (int) $customerId;
        $items = $request->input('items', []);

        foreach ($items as &$item) {
            if (isset($item['variant_id']) && ($item['variant_id'] === '' || $item['variant_id'] === null)) {
                $item['variant_id'] = null;
            }
        }
        unset($item);

        $total = 0.0;
        $rows = [];

        $variantsByProductId = ProductVariant::query()
            ->whereIn('product_id', collect($items)->pluck('product_id')->map(fn ($id) => (int) $id)->unique()->all())
            ->get(['id', 'product_id'])
            ->groupBy('product_id');

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $variantId = ! empty($item['variant_id']) ? (int) $item['variant_id'] : null;
            $qty = (int) $item['quantity'];
            $price = (float) $item['price'];

            $hasVariants = $variantsByProductId->has($productId) && $variantsByProductId->get($productId)->count() > 0;
            if ($hasVariants && ! $variantId) {
                throw ValidationException::withMessages([
                    'items' => ['Select a variant for each product that has variants.'],
                ]);
            }

            $line = $qty * $price;
            $total += $line;

            $rows[] = [
                'product_id' => $productId,
                'variant_id' => $hasVariants ? $variantId : null,
                'quantity' => $qty,
                'price' => $price,
            ];
        }

        $aggregates = [];
        foreach ($rows as $row) {
            $pk = $row['product_id'].'|'.($row['variant_id'] ?? '');
            $aggregates[$pk] = ($aggregates[$pk] ?? 0) + $row['quantity'];
        }

        $productIds = collect($rows)->pluck('product_id')->unique()->map(fn ($id) => (int) $id)->all();
        $products = Product::query()->whereIn('id', $productIds)->with('variants')->get()->keyBy('id');

        foreach ($aggregates as $pk => $needQty) {
            [$pidStr, $vStr] = explode('|', $pk, 2);
            $pid = (int) $pidStr;
            $variantId = $vStr === '' ? null : (int) $vStr;
            $product = $products->get($pid);
            if (! $product) {
                throw ValidationException::withMessages(['items' => ['Invalid product in estimate.']]);
            }

            $hasVariants = $product->variants->count() > 0;
            if ($hasVariants) {
                $variant = $product->variants->firstWhere('id', $variantId);
                if (! $variant) {
                    throw ValidationException::withMessages(['items' => ['Invalid variant for '.$product->name.'.']]);
                }
                $avail = (int) $variant->stock_quantity;
                if ($needQty > $avail) {
                    throw ValidationException::withMessages([
                        'items' => ['Insufficient stock for '.$product->name.' ('.$variant->variant_name.'). Available '.$avail.', requested '.$needQty.'.'],
                    ]);
                }
            } else {
                $avail = (int) $product->stock_quantity;
                if ($needQty > $avail) {
                    throw ValidationException::withMessages([
                        'items' => ['Insufficient stock for '.$product->name.'. Available '.$avail.', requested '.$needQty.'.'],
                    ]);
                }
            }
        }

        $estimate = DB::transaction(function () use ($customerId, $total, $rows) {
            $e = Estimate::create([
                'customer_id' => $customerId,
                'total_amount' => round($total, 2),
                'status' => Estimate::STATUS_OPEN,
            ]);

            $now = now();
            $insert = [];
            foreach ($rows as $row) {
                $insert[] = [
                    'estimate_id' => $e->id,
                    'product_id' => $row['product_id'],
                    'variant_id' => $row['variant_id'],
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            EstimateItem::insert($insert);

            return $e;
        });

        $estimate->load(['customer', 'items.product', 'items.variant']);

        return response()->json([
            'success' => true,
            'estimate' => [
                'id' => $estimate->id,
                'total_amount' => (float) $estimate->total_amount,
                'customer_name' => $estimate->customer?->name,
                'items' => $estimate->items->map(function (EstimateItem $it) {
                    return [
                        'product_name' => $it->product?->name,
                        'variant_name' => $it->variant?->variant_name,
                        'quantity' => (int) $it->quantity,
                        'price' => (float) $it->price,
                        'line_total' => round((int) $it->quantity * (float) $it->price, 2),
                    ];
                }),
            ],
            'redirect_url' => route('estimates.show', $estimate),
        ], 201);
    }

    public function show(Estimate $estimate): View|RedirectResponse
    {
        if ($estimate->status === Estimate::STATUS_CONVERTED && $estimate->sale_id) {
            return redirect()->route('sales.show', $estimate->sale_id);
        }

        $estimate->load(['customer', 'items.product', 'items.variant']);

        return view('estimates.show', [
            'estimate' => $estimate,
        ]);
    }

    public function convert(
        ConvertEstimateRequest $request,
        Estimate $estimate,
        SaleService $saleService,
        PaymentService $paymentService,
        SalePaymentAccountResolver $accountResolver,
    ): JsonResponse {
        if ($estimate->status !== Estimate::STATUS_OPEN) {
            return response()->json(['success' => false, 'message' => 'This estimate is already converted.'], 422);
        }

        $estimate->load('items');

        $amount = round((float) $request->input('amount'), 2);
        $methodKey = (string) $request->input('payment_method', '');

        $itemsPayload = $estimate->items->map(fn (EstimateItem $it) => [
            'product_id' => (int) $it->product_id,
            'variant_id' => $it->variant_id ? (int) $it->variant_id : null,
            'quantity' => (int) $it->quantity,
            'price' => (float) $it->price,
        ])->all();

        $total = (float) $estimate->total_amount;
        if ($amount > $total) {
            throw ValidationException::withMessages([
                'amount' => ['Amount paid cannot exceed the estimate total.'],
            ]);
        }

        if ($amount > 0 && $methodKey === '') {
            throw ValidationException::withMessages([
                'payment_method' => ['Select how the customer paid.'],
            ]);
        }

        try {
            $sale = DB::transaction(function () use (
                $estimate,
                $itemsPayload,
                $saleService,
                $paymentService,
                $accountResolver,
                $amount,
                $methodKey
            ) {
                $sale = $saleService->createSale((int) $estimate->customer_id, $itemsPayload);

                if ($amount > 0) {
                    $resolved = $accountResolver->resolve($methodKey);
                    $methodLabel = match ($methodKey) {
                        'cash' => 'Cash',
                        'jazzcash' => 'JazzCash',
                        'easypaisa' => 'Easypaisa',
                        'bank_mezzan' => 'Bank Mezzan',
                        default => $resolved['label'],
                    };

                    $paymentService->addSalePayment(
                        $sale,
                        $amount,
                        $methodLabel,
                        $resolved['id'],
                        'Converted from estimate #'.$estimate->id,
                    );
                }

                $estimate->update([
                    'status' => Estimate::STATUS_CONVERTED,
                    'sale_id' => $sale->id,
                ]);

                return $sale->fresh();
            });
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create sale.',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'sale_id' => $sale->id,
            'redirect_url' => route('sales.show', $sale),
        ]);
    }
}
