<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerProductPrice;
use App\Models\CustomerVariantPrice;
use App\Models\Account;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function create()
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->with('variants')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'sale_price']);

        $customerProductPrices = CustomerProductPrice::query()
            ->get(['customer_id', 'product_id', 'last_price']);

        $customerVariantPrices = CustomerVariantPrice::query()
            ->get(['customer_id', 'variant_id', 'last_price']);

        $pricesByCustomerProducts = [];
        foreach ($customerProductPrices as $row) {
            $pricesByCustomerProducts[(string) $row->customer_id][(string) $row->product_id] = $row->last_price;
        }

        $pricesByCustomerVariants = [];
        foreach ($customerVariantPrices as $row) {
            $pricesByCustomerVariants[(string) $row->customer_id][(string) $row->variant_id] = $row->last_price;
        }

        return view('sales', [
            'customers' => $customers,
            'products' => $products,
            'pricesByCustomerProducts' => $pricesByCustomerProducts,
            'pricesByCustomerVariants' => $pricesByCustomerVariants,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $customerId = (int) $request->input('customer_id');
        $items = $request->input('items', []);

        // Normalize variant_id to null instead of empty string.
        foreach ($items as &$item) {
            if (isset($item['variant_id']) && $item['variant_id'] === '') {
                $item['variant_id'] = null;
            }
        }
        unset($item);

        $service = app(SaleService::class);

        try {
            $sale = $service->createSale($customerId, $items);
        } catch (ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Sale saved successfully.');
    }

    public function show(Sale $sale): View
    {
        $sale->load([
            'customer',
            'items.product',
            'items.variant',
            'payments.account',
        ]);

        $accounts = Account::query()
            ->whereIn('type', ['cash', 'bank'])
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return view('sales.show', [
            'sale' => $sale,
            'accounts' => $accounts,
        ]);
    }
}
