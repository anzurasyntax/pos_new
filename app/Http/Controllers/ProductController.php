<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->input('q');

        $products = Product::query()
            ->with(['variants', 'category.parent'])
            ->when(
                filled($q),
                fn ($query) => $query->where('products.name', 'like', '%'.$q.'%')
            )
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*')
            ->orderByRaw('CASE WHEN products.category_id IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('COALESCE(categories.sort_order, 9999) ASC')
            ->orderBy('categories.name')
            ->orderBy('products.name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'q' => $q,
            'priceWizardSteps' => $this->buildPriceWizardSteps(),
        ]);
    }

    /**
     * JSON: update base sale or purchase price (products without variants only).
     */
    public function quickUpdateProductPrice(Request $request, Product $product): JsonResponse
    {
        if ($product->variants()->exists()) {
            return response()->json([
                'message' => 'This product uses variants. Prices are updated per variant.',
            ], 422);
        }

        $data = $request->validate([
            'field' => ['required', Rule::in(['sale_price', 'purchase_price'])],
            'value' => ['required', 'numeric', 'min:0'],
        ]);

        $product->update([
            $data['field'] => $data['value'],
        ]);

        return response()->json([
            'success' => true,
            $data['field'] => (float) $product->fresh()->{$data['field']},
        ]);
    }

    /**
     * JSON: update variant sale or purchase price.
     */
    public function quickUpdateVariantPrice(Request $request, Product $product, ProductVariant $variant): JsonResponse
    {
        if ((int) $variant->product_id !== (int) $product->id) {
            abort(404);
        }

        $data = $request->validate([
            'field' => ['required', Rule::in(['sale_price', 'purchase_price'])],
            'value' => ['required', 'numeric', 'min:0'],
        ]);

        $variant->update([
            $data['field'] => $data['value'],
        ]);

        return response()->json([
            'success' => true,
            $data['field'] => (float) $variant->fresh()->{$data['field']},
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildPriceWizardSteps(): array
    {
        $products = $this->catalogOrderedProductsQuery()->get();

        $steps = [];

        foreach ($products as $product) {
            $group = '—';
            if ($product->category) {
                $parent = $product->category->parent?->name;
                $group = $parent
                    ? $parent.' › '.$product->category->name
                    : $product->category->name;
            }

            if ($product->variants->isEmpty()) {
                $steps[] = [
                    'kind' => 'product',
                    'productId' => $product->id,
                    'title' => $product->name,
                    'subtitle' => $group,
                    'sku' => $product->sku,
                    'sale_price' => number_format((float) $product->sale_price, 2, '.', ''),
                    'purchase_price' => number_format((float) $product->purchase_price, 2, '.', ''),
                ];

                continue;
            }

            foreach ($product->variants->sortBy('variant_name') as $variant) {
                $steps[] = [
                    'kind' => 'variant',
                    'productId' => $product->id,
                    'variantId' => $variant->id,
                    'title' => $product->name.' — '.$variant->variant_name,
                    'subtitle' => $group,
                    'sku' => $variant->sku ?: $product->sku,
                    'sale_price' => number_format((float) $variant->sale_price, 2, '.', ''),
                    'purchase_price' => number_format((float) $variant->purchase_price, 2, '.', ''),
                ];
            }
        }

        return $steps;
    }

    private function catalogOrderedProductsQuery()
    {
        return Product::query()
            ->with(['variants', 'category.parent'])
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*')
            ->orderByRaw('CASE WHEN products.category_id IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('COALESCE(categories.sort_order, 9999) ASC')
            ->orderBy('categories.name')
            ->orderBy('products.name');
    }

    public function create(): View
    {
        return view('products.create', [
            'categoryOptions' => $this->categorySelectOptions(),
        ]);
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
            'category_id' => ['nullable', 'exists:categories,id'],
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
            'category_id' => $data['category_id'] ?? null,
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
        return view('products.edit', [
            'product' => $product->load('variants'),
            'categoryOptions' => $this->categorySelectOptions(),
        ]);
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
            'category_id' => ['nullable', 'exists:categories,id'],
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
            'category_id' => $data['category_id'] ?? null,
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

    /**
     * @return list<array{id: int, label: string}>
     */
    private function categorySelectOptions(): array
    {
        $options = [];
        $walk = function ($categories, int $depth = 0) use (&$walk, &$options): void {
            foreach ($categories as $cat) {
                $options[] = [
                    'id' => $cat->id,
                    'label' => str_repeat('— ', $depth).$cat->name,
                ];
                $children = Category::query()
                    ->where('parent_id', $cat->id)
                    ->orderByRaw('sort_order IS NULL, sort_order ASC')
                    ->orderBy('name')
                    ->get();
                $walk($children, $depth + 1);
            }
        };

        $roots = Category::query()
            ->whereNull('parent_id')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('name')
            ->get();

        $walk($roots);

        return $options;
    }
}
