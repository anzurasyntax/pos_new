<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q');

        $products = Product::query()
            ->with('variants')
            ->when(
                filled($q),
                fn ($query) => $query->where('name', 'like', '%'.$q.'%')
            )
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants' => ['nullable', 'array'],
            'variants.*.variant_name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.purchase_price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['required_with:variants', 'integer', 'min:0'],
        ]);

        $variants = $data['variants'] ?? [];
        $hasVariants = is_array($variants) && count($variants) > 0 && filled($variants[0]['variant_name'] ?? null);

        $baseStock = isset($data['stock_quantity']) ? (int) $data['stock_quantity'] : 0;
        $basePurchasePrice = isset($data['purchase_price']) ? (float) $data['purchase_price'] : 0.0;
        $baseSalePrice = isset($data['sale_price']) ? (float) $data['sale_price'] : 0.0;

        $sumVariantStock = 0;
        if ($hasVariants) {
            foreach ($variants as $v) {
                $sumVariantStock += (int) ($v['stock_quantity'] ?? 0);
            }
        }

        $product = Product::create([
            'name' => $data['name'],
            'sku' => $data['sku'],
            // Base fields are used only when the product has no variants.
            'stock_quantity' => $hasVariants ? $sumVariantStock : $baseStock,
            'purchase_price' => $hasVariants ? 0 : $basePurchasePrice,
            'sale_price' => $hasVariants ? 0 : $baseSalePrice,
        ]);

        if ($hasVariants) {
            $rows = array_map(function (array $v) use ($product) {
                return [
                    'product_id' => $product->id,
                    'variant_name' => $v['variant_name'],
                    'sku' => $v['sku'] ?? null,
                    'sale_price' => (float) $v['sale_price'],
                    'purchase_price' => (float) $v['purchase_price'],
                    'stock_quantity' => (int) $v['stock_quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $variants);

            ProductVariant::insert($rows);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product added successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', ['product' => $product->load('variants')]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants' => ['nullable', 'array'],
            'variants.*.variant_name' => ['required_with:variants', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:100'],
            'variants.*.purchase_price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['required_with:variants', 'integer', 'min:0'],
        ]);

        $variants = $data['variants'] ?? [];
        $hasVariants = is_array($variants) && count($variants) > 0 && filled($variants[0]['variant_name'] ?? null);

        $baseStock = isset($data['stock_quantity']) ? (int) $data['stock_quantity'] : 0;
        $basePurchasePrice = isset($data['purchase_price']) ? (float) $data['purchase_price'] : 0.0;
        $baseSalePrice = isset($data['sale_price']) ? (float) $data['sale_price'] : 0.0;

        $sumVariantStock = 0;
        if ($hasVariants) {
            foreach ($variants as $v) {
                $sumVariantStock += (int) ($v['stock_quantity'] ?? 0);
            }
        }

        $product->update([
            'name' => $data['name'],
            'sku' => $data['sku'],
            'stock_quantity' => $hasVariants ? $sumVariantStock : $baseStock,
            'purchase_price' => $hasVariants ? 0 : $basePurchasePrice,
            'sale_price' => $hasVariants ? 0 : $baseSalePrice,
        ]);

        if ($hasVariants) {
            // Simple sync approach for beginner-friendly forms:
            // delete all variants and recreate from submitted values.
            $product->variants()->delete();

            $rows = array_map(function (array $v) use ($product) {
                return [
                    'product_id' => $product->id,
                    'variant_name' => $v['variant_name'],
                    'sku' => $v['sku'] ?? null,
                    'sale_price' => (float) $v['sale_price'],
                    'purchase_price' => (float) $v['purchase_price'],
                    'stock_quantity' => (int) $v['stock_quantity'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $variants);

            ProductVariant::insert($rows);
        } else {
            $product->variants()->delete();
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
