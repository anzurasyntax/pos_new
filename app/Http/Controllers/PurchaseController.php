<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function create()
    {
        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->with('variants')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'purchase_price']);

        return view('purchase', [
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['sometimes', 'nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $items = $data['items'];
        foreach ($items as &$item) {
            if (isset($item['variant_id']) && $item['variant_id'] === '') {
                $item['variant_id'] = null;
            }
        }
        unset($item);

        $service = app(PurchaseService::class);

        try {
            $purchase = $service->createPurchase((int) $data['supplier_id'], $items);
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('purchase.show', $purchase)
            ->with('success', 'Purchase saved successfully.');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load([
            'supplier',
            'items.product',
            'items.variant',
            'payments.account',
        ]);

        $accounts = Account::query()
            ->whereIn('type', ['cash', 'bank'])
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('purchase.show', [
            'purchase' => $purchase,
            'accounts' => $accounts,
        ]);
    }
}
