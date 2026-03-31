<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Product
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('products.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-input
                                label="Name"
                                name="name"
                                type="text"
                                placeholder="Product name"
                                value="{{ old('name') }}"
                                autofocus
                            />
                        </div>

                        <div>
                            <x-input
                                label="SKU"
                                name="sku"
                                type="text"
                                placeholder="Unique SKU"
                                value="{{ old('sku') }}"
                            />
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-semibold text-slate-700">Category</label>
                            <p class="text-xs text-slate-500 mt-0.5 mb-2">Optional. Manage categories under <a href="{{ route('categories.index') }}" class="text-emerald-700 font-medium hover:underline">Categories</a>.</p>
                            <select id="category_id" name="category_id"
                                class="block w-full rounded-lg border-slate-200 bg-white shadow-sm px-3 py-2.5 text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="">— None —</option>
                                @foreach ($categoryOptions as $opt)
                                    <option value="{{ $opt['id'] }}" @selected(old('category_id') == $opt['id'])>
                                        {{ $opt['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-input
                                label="Stock Quantity"
                                name="stock_quantity"
                                type="number"
                                placeholder="0"
                                value="{{ old('stock_quantity') }}"
                                min="0"
                            />
                        </div>

                        <div>
                            <x-input
                                label="Purchase Price (base)"
                                name="purchase_price"
                                type="number"
                                placeholder="0.00"
                                value="{{ old('purchase_price') }}"
                                step="0.01"
                                min="0"
                            />
                        </div>

                        <div>
                            <x-input
                                label="Sale Price (base)"
                                name="sale_price"
                                type="number"
                                placeholder="0.00"
                                value="{{ old('sale_price') }}"
                                step="0.01"
                                min="0"
                            />
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">Product Variants (optional)</h3>
                                <p class="text-sm text-gray-600">If you add variants, Sales/Purchases will use variant price & stock.</p>
                            </div>
                            <x-button
                                type="primary"
                                htmlType="button"
                                text="Add Variant"
                                id="addVariantBtn"
                                class="px-4 py-2"
                            >
                                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                            </x-button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant Name</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU (optional)</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Price</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Price</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="variantsBody">
                                    @php
                                        $oldVariants = old('variants', []);
                                        $variantCount = is_array($oldVariants) ? count($oldVariants) : 0;
                                    @endphp

                                    @for ($i = 0; $i < $variantCount; $i++)
                                        @php $v = $oldVariants[$i] ?? []; @endphp
                                        <tr data-variant-row="{{ $i }}">
                                            <td class="px-3 py-3">
                                                <x-input
                                                    name="variants[{{ $i }}][variant_name]"
                                                    type="text"
                                                    placeholder="Small, Large, Red, 1KG"
                                                    value="{{ $v['variant_name'] ?? '' }}"
                                                />
                                            </td>
                                            <td class="px-3 py-3">
                                                <x-input
                                                    name="variants[{{ $i }}][sku]"
                                                    type="text"
                                                    placeholder="Optional SKU"
                                                    value="{{ $v['sku'] ?? '' }}"
                                                />
                                            </td>
                                            <td class="px-3 py-3">
                                                <x-input
                                                    name="variants[{{ $i }}][sale_price]"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    value="{{ $v['sale_price'] ?? '' }}"
                                                />
                                            </td>
                                            <td class="px-3 py-3">
                                                <x-input
                                                    name="variants[{{ $i }}][purchase_price]"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    placeholder="0.00"
                                                    value="{{ $v['purchase_price'] ?? '' }}"
                                                />
                                            </td>
                                            <td class="px-3 py-3">
                                                <x-input
                                                    name="variants[{{ $i }}][stock_quantity]"
                                                    type="number"
                                                    min="0"
                                                    placeholder="0"
                                                    value="{{ $v['stock_quantity'] ?? '' }}"
                                                />
                                            </td>
                                            <td class="px-3 py-3">
                                                <x-button
                                                    type="danger"
                                                    htmlType="button"
                                                    text="Remove"
                                                    class="removeVariantBtn px-3 py-2"
                                                />
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 mt-6">
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center justify-center px-5 py-3 rounded-md border border-gray-300 text-gray-700 font-medium hover:bg-gray-50">
                            Cancel
                        </a>
                            <x-button
                                type="primary"
                                htmlType="submit"
                                text="Save Product"
                                loadingText="Saving..."
                            />
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const variantsBody = document.getElementById('variantsBody');
            const addVariantBtn = document.getElementById('addVariantBtn');

            let nextIndex = variantsBody.querySelectorAll('tr[data-variant-row]').length;

            addVariantBtn.addEventListener('click', function () {
                const idx = nextIndex;
                nextIndex++;

                const tr = document.createElement('tr');
                tr.setAttribute('data-variant-row', String(idx));

                tr.innerHTML = `
                    <td class="px-3 py-3">
                        <input name="variants[${idx}][variant_name]" type="text"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="Small, Large, Red, 1KG"/>
                    </td>
                    <td class="px-3 py-3">
                        <input name="variants[${idx}][sku]" type="text"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="Optional SKU"/>
                    </td>
                    <td class="px-3 py-3">
                        <input name="variants[${idx}][sale_price]" type="number" step="0.01" min="0"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="0.00"/>
                    </td>
                    <td class="px-3 py-3">
                        <input name="variants[${idx}][purchase_price]" type="number" step="0.01" min="0"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="0.00"/>
                    </td>
                    <td class="px-3 py-3">
                        <input name="variants[${idx}][stock_quantity]" type="number" min="0"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            placeholder="0"/>
                    </td>
                    <td class="px-3 py-3">
                        <button type="button" class="removeVariantBtn inline-flex items-center justify-center px-3 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 font-medium disabled:opacity-60 disabled:cursor-not-allowed">
                            Remove
                        </button>
                    </td>
                `;

                variantsBody.appendChild(tr);
            });

            variantsBody.addEventListener('click', function (e) {
                const btn = e.target.closest('.removeVariantBtn');
                if (!btn) return;
                const row = btn.closest('tr[data-variant-row]');
                if (row) row.remove();
            });
        })();
    </script>
</x-app-layout>

