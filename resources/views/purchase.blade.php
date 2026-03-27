<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            New Purchase
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-red-800">
                    <div class="font-medium mb-2">Please fix the following:</div>
                    <ul class="list-disc ps-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('purchase.store') }}" id="purchaseForm">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-select
                                label="Supplier"
                                name="supplier_id"
                                placeholder="Select supplier"
                                autofocus
                            >
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        @selected(old('supplier_id') == $supplier->id)>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div class="flex items-end">
                            <div class="w-full rounded-md bg-gray-50 border border-gray-200 p-3">
                                <div class="text-sm text-gray-600">Running Total</div>
                                <div class="text-2xl font-semibold text-gray-900" id="runningTotal">
                                    0.00
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Total amount for all purchase items.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h3 class="text-lg font-semibold text-gray-800">Purchase Items</h3>
                            <x-button
                                type="primary"
                                htmlType="button"
                                text=""
                                id="addRowBtn"
                                class="px-4 py-3"
                            >
                                <svg class="w-5 h-5 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                                <span>Add Row</span>
                            </x-button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>

                                <tbody id="itemsBody">
                                    @php
                                        $oldItems = old('items', []);
                                        $rowCount = is_array($oldItems) ? count($oldItems) : 1;
                                        $productsById = $products->keyBy('id');
                                    @endphp

                                    @for ($i = 0; $i < $rowCount; $i++)
                                        @php
                                            $item = $oldItems[$i] ?? [];
                                            $productId = (int) ($item['product_id'] ?? 0);
                                            $variantId = isset($item['variant_id']) && $item['variant_id'] !== '' ? (int) $item['variant_id'] : null;
                                            $product = $productsById->get($productId);
                                            $variants = $product?->variants ?? collect();
                                            $priceValue = old(
                                                "items.$i.price",
                                                $item['price'] ?? (
                                                    $variantId && $variants->firstWhere('id', $variantId) ? $variants->firstWhere('id', $variantId)->purchase_price : ($product?->purchase_price ?? '')
                                                )
                                            );
                                        @endphp

                                        <tr class="align-top" data-row="{{ $i }}">
                                            <td class="px-3 py-3">
                                                <select name="items[{{ $i }}][product_id]"
                                                    class="productSelect block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                                                    <option value="">Select</option>
                                                    @foreach ($products as $p)
                                                        <option value="{{ $p->id }}" @selected((int)($p->id) === (int)($productId))>
                                                            {{ $p->name }} ({{ $p->sku }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("items.$i.product_id")
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </td>

                                            <td class="px-3 py-3" style="min-width: 220px;">
                                                <select name="items[{{ $i }}][variant_id]"
                                                    class="variantSelect block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                                                    {{ $variants->count() === 0 ? 'disabled' : '' }}>
                                                    @if ($variants->count() > 0)
                                                        @php
                                                            $selectedVariantId = $variantId ?? $variants->first()?->id;
                                                        @endphp
                                                        @foreach ($variants as $v)
                                                            <option value="{{ $v->id }}" @selected((int) $v->id === (int) $selectedVariantId)>
                                                                {{ $v->variant_name }}{{ $v->sku ? ' ('.$v->sku.')' : '' }}
                                                            </option>
                                                        @endforeach
                                                    @else
                                                        <option value="">No variants</option>
                                                    @endif
                                                </select>
                                                @error("items.$i.variant_id")
                                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </td>

                                            <td class="px-3 py-3" style="min-width: 120px;">
                                                <x-input
                                                    name="items[{{ $i }}][quantity]"
                                                    type="number"
                                                    min="1"
                                                    step="1"
                                                    placeholder="1"
                                                    value="{{ $item['quantity'] ?? 1 }}"
                                                    class="qtyInput"
                                                />
                                            </td>

                                            <td class="px-3 py-3" style="min-width: 160px;">
                                                <x-input
                                                    name="items[{{ $i }}][price]"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    placeholder="0.00"
                                                    value="{{ $priceValue }}"
                                                    class="priceInput"
                                                />
                                            </td>

                                            <td class="px-3 py-3">
                                                <div class="text-gray-900 font-medium tabular-nums lineTotal">
                                                    0.00
                                                </div>
                                            </td>

                                            <td class="px-3 py-3">
                                                <x-button
                                                    type="danger"
                                                    htmlType="button"
                                                    text="Remove"
                                                    class="removeRowBtn px-3 py-2"
                                                />
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5">
                            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                                <div class="text-sm text-gray-600">
                                    Add products using the Product dropdown. Variant and Price will auto-fill.
                                </div>

                                <div class="rounded-md bg-gray-50 border border-gray-200 px-4 py-3">
                                    <div class="text-sm text-gray-600">Total Amount</div>
                                    <div class="text-2xl font-semibold text-gray-900 tabular-nums" id="runningTotalBottom">
                                        0.00
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex justify-end">
                                <x-button
                                    type="primary"
                                    htmlType="submit"
                                    text=""
                                    loadingText="Saving..."
                                    class="px-6 py-3"
                                >
                                    <svg class="w-5 h-5 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.172 8.707 9.879a1 1 0 00-1.414 1.414l1.414 1.414a1 1 0 001.414 0l4.001-4.001z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Save Purchase</span>
                                </x-button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const products = @json($products->map(fn($p) => [
                'id' => $p->id,
                'purchase_price' => $p->purchase_price,
                'variants' => $p->variants->map(fn($v) => [
                    'id' => $v->id,
                    'variant_name' => $v->variant_name,
                    'sku' => $v->sku,
                    'purchase_price' => $v->purchase_price,
                ]),
            ]));

            const variantsByProduct = {};
            const purchasePriceByProduct = {};
            products.forEach((p) => {
                purchasePriceByProduct[String(p.id)] = p.purchase_price;
                variantsByProduct[String(p.id)] = p.variants || [];
            });

            const itemsBody = document.getElementById('itemsBody');
            const addRowBtn = document.getElementById('addRowBtn');
            const runningTotalEl = document.getElementById('runningTotal');

            function toNumber(v) {
                const n = parseFloat(String(v).replace(',', '.'));
                return Number.isFinite(n) ? n : 0;
            }

            function updateRow(rowEl) {
                const qtyInput = rowEl.querySelector('.qtyInput');
                const priceInput = rowEl.querySelector('.priceInput');
                const lineTotalEl = rowEl.querySelector('.lineTotal');

                const qty = toNumber(qtyInput?.value);
                const price = toNumber(priceInput?.value);

                const lineTotal = qty * price;
                if (lineTotalEl) lineTotalEl.textContent = lineTotal.toFixed(2);
            }

            function updateTotals() {
                let total = 0;
                const rows = itemsBody.querySelectorAll('tr[data-row]');
                rows.forEach((rowEl) => {
                    const lineTotalEl = rowEl.querySelector('.lineTotal');
                    total += toNumber(lineTotalEl?.textContent);
                });

                if (runningTotalEl) runningTotalEl.textContent = total.toFixed(2);
                const bottomEl = document.getElementById('runningTotalBottom');
                if (bottomEl) bottomEl.textContent = total.toFixed(2);
            }

            function recalcAll() {
                const rows = itemsBody.querySelectorAll('tr[data-row]');
                rows.forEach((rowEl) => updateRow(rowEl));
                updateTotals();
            }

            function getNextIndex() {
                let maxIndex = -1;
                const qtyInputs = itemsBody.querySelectorAll('input[name^="items["][name$="[quantity]"]');
                qtyInputs.forEach((inp) => {
                    const m = inp.getAttribute('name').match(/^items\[(\d+)\]\[quantity\]$/);
                    if (m && m[1]) maxIndex = Math.max(maxIndex, parseInt(m[1], 10));
                });
                return maxIndex + 1;
            }

            function productOptionsHtml(selectedProductId) {
                const opts = [];
                itemsBody.querySelectorAll('select.productSelect').forEach(() => {});
                // Use existing first row options as the HTML source.
                const first = itemsBody.querySelector('select.productSelect');
                if (!first) return `<option value="">Select</option>`;
                return first.innerHTML;
            }

            function rebuildVariantOptions(rowEl) {
                const productSelect = rowEl.querySelector('select[name*="[product_id]"]');
                const variantSelect = rowEl.querySelector('select[name*="[variant_id]"]');
                const priceInput = rowEl.querySelector('.priceInput');

                const productId = String(productSelect?.value || '');
                const variants = variantsByProduct[productId] || [];

                if (!productId || !variants.length) {
                    variantSelect.disabled = true;
                } else {
                    variantSelect.disabled = false;
                }

                variantSelect.innerHTML = '';

                if (!productId || !variants.length) {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No variants';
                    variantSelect.appendChild(opt);
                    if (priceInput) priceInput.value = productId ? purchasePriceByProduct[productId] : '';
                    return;
                }

                // Choose first variant by default.
                let selected = null;
                const current = String(variantSelect.value || '');
                const match = variants.find((v) => String(v.id) === current);
                if (match) selected = match.id;
                else selected = variants[0].id;

                variants.forEach((v) => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = `${v.variant_name}${v.sku ? ' (' + v.sku + ')' : ''}`;
                    if (String(v.id) === String(selected)) opt.selected = true;
                    variantSelect.appendChild(opt);
                });

                if (priceInput) {
                    const chosen = variants.find((v) => String(v.id) === String(variantSelect.value));
                    priceInput.value = chosen ? chosen.purchase_price : (purchasePriceByProduct[productId] || '');
                }
            }

            // Initial calc for any old() values.
            recalcAll();

            // Update totals while typing.
            itemsBody.addEventListener('input', function (e) {
                const target = e.target;
                if (!target) return;

                if (target.classList.contains('qtyInput') || target.classList.contains('priceInput')) {
                    const rowEl = target.closest('tr[data-row]');
                    if (!rowEl) return;
                    updateRow(rowEl);
                    updateTotals();
                }
            });

            // When product changes -> rebuild variant options + auto price.
            itemsBody.addEventListener('change', function (e) {
                const target = e.target;
                if (!target) return;

                if (target.classList.contains('productSelect') || String(target.name || '').includes('[product_id]')) {
                    const rowEl = target.closest('tr[data-row]');
                    if (!rowEl) return;
                    rebuildVariantOptions(rowEl);
                    updateRow(rowEl);
                    updateTotals();
                }

                if (target.classList.contains('variantSelect') || String(target.name || '').includes('[variant_id]')) {
                    const rowEl = target.closest('tr[data-row]');
                    if (!rowEl) return;

                    if (target.disabled) return;

                    // Auto-fill price from selected variant.
                    const productSelect = rowEl.querySelector('select[name*="[product_id]"]');
                    const variantSelect = rowEl.querySelector('select[name*="[variant_id]"]');
                    const priceInput = rowEl.querySelector('.priceInput');

                    const pid = String(productSelect?.value || '');
                    const variants = variantsByProduct[pid] || [];
                    const vid = String(variantSelect?.value || '');

                    const chosen = variants.find((v) => String(v.id) === vid);
                    if (priceInput) {
                        priceInput.value = chosen ? chosen.purchase_price : (pid ? purchasePriceByProduct[pid] : '');
                    }
                    updateRow(rowEl);
                    updateTotals();
                }
            });

            // Remove row.
            itemsBody.addEventListener('click', function (e) {
                const btn = e.target.closest('.removeRowBtn');
                if (!btn) return;
                const rowEl = btn.closest('tr[data-row]');
                if (!rowEl) return;

                const rowCount = itemsBody.querySelectorAll('tr[data-row]').length;
                if (rowCount <= 1) return;

                rowEl.remove();
                recalcAll();
            });

            // Add row.
            addRowBtn.addEventListener('click', function () {
                const idx = getNextIndex();
                const first = itemsBody.querySelector('select.productSelect');
                const optionsHtml = first ? first.innerHTML : '<option value="">Select</option>';

                const tr = document.createElement('tr');
                tr.className = 'align-top';
                tr.setAttribute('data-row', String(idx));

                tr.innerHTML = `
                    <td class="px-3 py-3">
                        <select name="items[${idx}][product_id]" class="productSelect block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                            ${optionsHtml}
                        </select>
                    </td>
                    <td class="px-3 py-3" style="min-width: 220px;">
                        <select name="items[${idx}][variant_id]" class="variantSelect block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2" disabled>
                            <option value="">No variants</option>
                        </select>
                    </td>
                    <td class="px-3 py-3" style="min-width: 120px;">
                        <input type="number" min="1" step="1"
                            name="items[${idx}][quantity]" value="1"
                            class="qtyInput block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"/>
                    </td>
                    <td class="px-3 py-3" style="min-width: 160px;">
                        <input type="number" min="0" step="0.01"
                            name="items[${idx}][price]" value=""
                            class="priceInput block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"/>
                    </td>
                    <td class="px-3 py-3">
                        <div class="text-gray-900 font-medium tabular-nums lineTotal">0.00</div>
                    </td>
                    <td class="px-3 py-3">
                        <button type="button" class="removeRowBtn inline-flex items-center justify-center px-3 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 font-medium disabled:opacity-60 disabled:cursor-not-allowed">
                            Remove
                        </button>
                    </td>
                `;

                itemsBody.appendChild(tr);
                recalcAll();
            });
        })();
    </script>
</x-app-layout>

