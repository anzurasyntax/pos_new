<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            POS - New Sale
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                <form method="POST" action="{{ route('sales.store') }}" id="salesForm">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-1">
                            <div class="flex items-end gap-3">
                                <div class="flex-1">
                                    <x-select
                                        label="Customer"
                                        name="customer_id"
                                        placeholder="Select customer"
                                    >
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}"
                                                @selected(old('customer_id', $customers->first()?->id) == $customer->id)>
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </x-select>
                                </div>

                                <div class="pb-0.5">
                                    <x-button
                                        type="secondary"
                                        htmlType="button"
                                        text="Add New Customer"
                                        id="openCustomerModalBtn"
                                        class="px-4 py-2 whitespace-nowrap"
                                    />
                                </div>
                            </div>

                            <div class="mt-3 text-sm text-gray-600">
                                Choose customer, then scan/type products and press Enter.
                            </div>
                        </div>

                        <div class="lg:col-span-2">
                            <label for="productSearch" class="block text-sm font-medium text-gray-700">
                                Search Product (name or SKU)
                            </label>

                            <div class="relative">
                                <x-input
                                    name="productSearch"
                                    type="text"
                                    placeholder="Type and press Enter..."
                                    class="mt-2 pr-28"
                                    autocomplete="off"
                                    spellcheck="false"
                                    autofocus
                                />

                                <x-button
                                    type="primary"
                                    htmlType="button"
                                    text=""
                                    id="addProductBtn"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-2"
                                >
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                    </svg>
                                </x-button>

                                <div id="suggestions" class="absolute left-0 right-0 mt-2 bg-white border border-gray-200 rounded-md shadow-sm overflow-hidden hidden z-10">
                                    <div id="suggestionsInner" class="max-h-60 overflow-auto"></div>
                                </div>
                            </div>

                            <div id="searchHint" class="mt-2 text-sm text-red-600" style="min-height: 18px;"></div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h3 class="text-lg font-semibold text-gray-800">Sale Items</h3>
                            <x-button
                                type="secondary"
                                htmlType="button"
                                text="Clear"
                                id="clearBtn"
                                class="px-4 py-2 border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variant</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="itemsBody">
                                    @php
                                        $oldItems = old('items', []);
                                        $rowCount = is_array($oldItems) ? count($oldItems) : 0;
                                        $productsById = $products->keyBy('id');
                                    @endphp

                                    @for ($i = 0; $i < $rowCount; $i++)
                                        @php
                                            $item = $oldItems[$i] ?? [];
                                            $productId = (int) ($item['product_id'] ?? 0);
                                            $variantId = isset($item['variant_id']) && $item['variant_id'] !== '' ? (int) $item['variant_id'] : null;
                                            $product = $productsById->get($productId);
                                            $hasVariants = $product?->variants?->count() > 0;
                                            $variant = $hasVariants ? $product->variants->firstWhere('id', $variantId) : null;
                                        @endphp

                                        <tr class="align-top" data-row="{{ $i }}"
                                            data-product-id="{{ $productId }}"
                                            data-variant-id="{{ $variantId }}">
                                            <td class="px-3 py-3">
                                                <div class="font-medium text-gray-800">
                                                    {{ $product?->name ?? 'Unknown' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    SKU: {{ $product?->sku ?? '-' }}
                                                </div>
                                                <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $productId }}"/>
                                            </td>

                                            <td class="px-3 py-3" style="min-width: 180px;">
                                                @if ($hasVariants)
                                                    <select name="items[{{ $i }}][variant_id]"
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 variantSelect"
                                                        data-variant-select>
                                                        @php
                                                            $selectedVariantId = $variantId ?? $product->variants->first()?->id;
                                                        @endphp
                                                        @foreach ($product->variants as $v)
                                                            <option value="{{ $v->id }}" @selected((int) $v->id === (int) $selectedVariantId)>
                                                                {{ $v->variant_name }}{{ $v->sku ? ' ('.$v->sku.')' : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <span class="text-sm text-gray-700">-</span>
                                                @endif
                                            </td>

                                            <td class="px-3 py-3" style="min-width: 110px;">
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
                                                    value="{{ $item['price'] ?? '' }}"
                                                    class="priceInput"
                                                />
                                            </td>

                                            <td class="px-3 py-3">
                                                <div class="text-gray-900 font-medium tabular-nums lineTotal">0.00</div>
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

                                    @if ($rowCount === 0)
                                        <tr>
                                            <td colspan="6" class="px-3 py-6 text-gray-600">
                                                No items yet. Add products above.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                            <div class="text-sm text-gray-600">Grand Total</div>
                            <div class="text-3xl font-semibold text-gray-900 tabular-nums" id="grandTotal">0.00</div>
                        </div>
                        @error('stock')
                            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5 flex justify-end">
                        <x-button
                            type="primary"
                            htmlType="submit"
                            text=""
                            loadingText="Saving..."
                            class="text-base px-8 py-3"
                        >
                            <svg class="w-5 h-5 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.172 8.707 9.879a1 1 0 00-1.414 1.414l1.414 1.414a1 1 0 001.414 0l3.586-3.586z" clip-rule="evenodd"/>
                            </svg>
                            <span>Save Sale</span>
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add New Customer Modal -->
    <div
        id="customerModal"
        class="fixed inset-0 z-50 hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="customerModalTitle"
    >
        <div id="customerModalBackdrop" class="absolute inset-0 bg-gray-900/40"></div>

        <div class="relative max-w-lg mx-auto mt-20">
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 id="customerModalTitle" class="text-lg font-semibold text-gray-800">
                            Add New Customer
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Fill in the details and save. The customer will be added to the dropdown.
                        </p>
                    </div>

                    <x-button
                        type="secondary"
                        htmlType="button"
                        text=""
                        id="closeCustomerModalBtn"
                        class="px-2 py-2"
                        aria-label="Close modal"
                    >
                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </x-button>
                </div>

                <form id="customerModalForm">
                    <div class="mt-4 grid grid-cols-1 gap-4">
                        <x-input
                            label="Name"
                            name="name"
                            type="text"
                            placeholder="Customer name"
                        />

                        <x-input
                            label="Phone"
                            name="phone"
                            type="text"
                            placeholder="Optional phone"
                        />

                        <x-input
                            label="Address"
                            name="address"
                            type="text"
                            placeholder="Optional address"
                        />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <x-button
                            type="secondary"
                            htmlType="button"
                            text="Cancel"
                            id="cancelCustomerModalBtn"
                        />

                        <x-button
                            type="primary"
                            htmlType="button"
                            text="Save Customer"
                            id="saveCustomerBtn"
                            loadingText="Saving..."
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            @php
                // Build a plain payload array to keep Blade/PHP syntax out of JS parsing.
                $productsPayload = $products->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'sale_price' => $p->sale_price,
                    'variants' => $p->variants->map(fn($v) => [
                        'id' => $v->id,
                        'variant_name' => $v->variant_name,
                        'sku' => $v->sku,
                        'sale_price' => $v->sale_price,
                    ])->values(),
                ])->values();
            @endphp

            const products = @json($productsPayload);

            const pricesByCustomerProducts = @json($pricesByCustomerProducts);
            const pricesByCustomerVariants = @json($pricesByCustomerVariants);

            const customerSelect = document.getElementById('customer_id');
            const searchInput = document.getElementById('productSearch');
            const addProductBtn = document.getElementById('addProductBtn');
            const clearBtn = document.getElementById('clearBtn');
            const itemsBody = document.getElementById('itemsBody');
            const grandTotalEl = document.getElementById('grandTotal');
            const searchHint = document.getElementById('searchHint');

            const suggestionsBox = document.getElementById('suggestions');
            const suggestionsInner = document.getElementById('suggestionsInner');

            const moneyFmt = (n) => {
                const x = Number.isFinite(n) ? n : 0;
                return x.toFixed(2);
            };

            function toNumber(v) {
                const n = parseFloat(String(v ?? '').replace(',', '.'));
                return Number.isFinite(n) ? n : 0;
            }

            function currentCustomerId() {
                return String(customerSelect?.value ?? '');
            }

            function productById(id) {
                return products.find((p) => String(p.id) === String(id));
            }

            function unitPriceForCustomer(productId, variantId) {
                const cid = currentCustomerId();
                if (!cid) return 0;

                // Variant price override.
                if (variantId) {
                    const last = pricesByCustomerVariants?.[cid]?.[String(variantId)];
                    if (last !== undefined && last !== null && last !== '') {
                        const n = parseFloat(String(last).replace(',', '.'));
                        if (Number.isFinite(n)) return n;
                    }

                    const p = productById(productId);
                    const v = p?.variants?.find((x) => String(x.id) === String(variantId));
                    return v ? toNumber(v.sale_price) : 0;
                }

                // Product price override.
                const last = pricesByCustomerProducts?.[cid]?.[String(productId)];
                if (last !== undefined && last !== null && last !== '') {
                    const n = parseFloat(String(last).replace(',', '.'));
                    if (Number.isFinite(n)) return n;
                }

                const p = productById(productId);
                return p ? toNumber(p.sale_price) : 0;
            }

            function updateRowTotal(rowEl) {
                const qtyInput = rowEl.querySelector('.qtyInput');
                const priceInput = rowEl.querySelector('.priceInput');
                const totalEl = rowEl.querySelector('.lineTotal');

                const qty = toNumber(qtyInput?.value);
                const price = toNumber(priceInput?.value);
                const lineTotal = qty * price;

                if (totalEl) totalEl.textContent = moneyFmt(lineTotal);
            }

            function updateGrandTotal() {
                let total = 0;
                const rows = itemsBody.querySelectorAll('tr[data-row]');
                rows.forEach((rowEl) => {
                    const lineEl = rowEl.querySelector('.lineTotal');
                    total += toNumber(lineEl?.textContent);
                });
                grandTotalEl.textContent = moneyFmt(total);
            }

            function recalcAll() {
                const rows = itemsBody.querySelectorAll('tr[data-row]');
                rows.forEach((rowEl) => updateRowTotal(rowEl));
                updateGrandTotal();
            }

            function getNextIndex() {
                let maxIndex = -1;
                const productInputs = itemsBody.querySelectorAll('input[name^="items["][name$="[product_id]"]');
                productInputs.forEach((inp) => {
                    const m = inp.getAttribute('name').match(/^items\[(\d+)\]\[product_id\]$/);
                    if (m && m[1]) maxIndex = Math.max(maxIndex, parseInt(m[1], 10));
                });
                return maxIndex + 1;
            }

            function variantOptionsHtml(productId, selectedVariantId) {
                const p = productById(productId);
                if (!p || !p.variants || !p.variants.length) return '';

                return p.variants.map((v) => {
                    const sel = String(v.id) === String(selectedVariantId) ? 'selected' : '';
                    const sku = v.sku ? ` (${v.sku})` : '';
                    return `<option value="${v.id}" ${sel}>${v.variant_name}${sku}</option>`;
                }).join('');
            }

            function addProduct(productId) {
                const cid = currentCustomerId();
                if (!cid) {
                    searchHint.textContent = 'Select customer first.';
                    customerSelect?.focus();
                    return;
                }

                const product = productById(productId);
                if (!product) {
                    searchHint.textContent = 'Product not found.';
                    return;
                }

                searchHint.textContent = '';

                const hasVariants = product.variants && product.variants.length > 0;
                const variantId = hasVariants ? product.variants[0].id : '';

                // If the product is already present once, increase that row (better for fast billing).
                let existing = null;
                const sameProductRows = itemsBody.querySelectorAll(`tr[data-product-id="${productId}"]`);
                if (sameProductRows.length === 1) {
                    existing = sameProductRows[0];
                } else {
                    existing = itemsBody.querySelector(
                        `tr[data-product-id="${productId}"][data-variant-id="${variantId}"]`
                    );
                }

                if (existing) {
                    const qtyInput = existing.querySelector('.qtyInput');
                    qtyInput.value = Math.max(1, parseInt(qtyInput.value || '1', 10) + 1);
                    updateRowTotal(existing);
                    updateGrandTotal();
                    searchInput.value = '';
                    suggestionsBox.classList.add('hidden');
                    return;
                }

                // Remove placeholder row.
                const placeholder = itemsBody.querySelector('tr:not([data-row])');
                if (placeholder) placeholder.remove();

                const idx = getNextIndex();
                const price = unitPriceForCustomer(productId, variantId || null);
                const qty = 1;
                const lineTotal = qty * price;

                const tr = document.createElement('tr');
                tr.className = 'align-top';
                tr.setAttribute('data-row', String(idx));
                tr.setAttribute('data-product-id', String(productId));
                tr.setAttribute('data-variant-id', String(variantId));

                const variantCell = hasVariants
                    ? `
                        <select name="items[${idx}][variant_id]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 variantSelect">
                            ${variantOptionsHtml(productId, variantId)}
                        </select>
                      `
                    : `<span class="text-sm text-gray-700">-</span>`;

                tr.innerHTML = `
                    <td class="px-3 py-3">
                        <div class="font-medium text-gray-800">${product.name}</div>
                        <div class="text-xs text-gray-500">SKU: ${product.sku}</div>
                        <input type="hidden" name="items[${idx}][product_id]" value="${productId}"/>
                    </td>
                    <td class="px-3 py-3" style="min-width: 180px;">
                        ${variantCell}
                    </td>
                    <td class="px-3 py-3" style="min-width: 110px;">
                        <input type="number" min="1" step="1"
                            name="items[${idx}][quantity]"
                            value="${qty}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 qtyInput" />
                    </td>
                    <td class="px-3 py-3" style="min-width: 160px;">
                        <input type="number" min="0" step="0.01"
                            name="items[${idx}][price]"
                            value="${price}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 priceInput" />
                    </td>
                    <td class="px-3 py-3">
                        <div class="text-gray-900 font-medium tabular-nums lineTotal">${moneyFmt(lineTotal)}</div>
                    </td>
                    <td class="px-3 py-3">
                        <button type="button" class="removeRowBtn inline-flex items-center justify-center px-3 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 font-medium disabled:opacity-60 disabled:cursor-not-allowed">
                            Remove
                        </button>
                    </td>
                `;

                itemsBody.appendChild(tr);
                recalcAll();
                searchInput.value = '';
                suggestionsBox.classList.add('hidden');
            }

            // Suggestion logic
            let activeSuggestionIndex = -1;
            let filtered = [];

            function renderSuggestions(list) {
                suggestionsInner.innerHTML = '';
                if (!list.length) {
                    suggestionsBox.classList.add('hidden');
                    return;
                }

                suggestionsBox.classList.remove('hidden');
                list.forEach((p, i) => {
                    const div = document.createElement('div');
                    div.className = 'px-3 py-2 cursor-pointer hover:bg-indigo-50';
                    if (i === activeSuggestionIndex) div.className = 'px-3 py-2 cursor-pointer bg-indigo-50';
                    div.dataset.productId = p.id;
                    div.textContent = `${p.name} (${p.sku})`;
                    div.addEventListener('click', () => addProduct(p.id));
                    suggestionsInner.appendChild(div);
                });
            }

            function searchMatches(q) {
                const query = String(q).trim().toLowerCase();
                if (!query) return [];

                return products
                    .filter((p) => String(p.name).toLowerCase().includes(query) || String(p.sku).toLowerCase().includes(query))
                    .slice(0, 7);
            }

            searchInput.addEventListener('input', function () {
                const q = searchInput.value;
                filtered = searchMatches(q);
                activeSuggestionIndex = filtered.length ? 0 : -1;
                renderSuggestions(filtered);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!filtered.length) return;
                    activeSuggestionIndex = Math.min(filtered.length - 1, activeSuggestionIndex + 1);
                    renderSuggestions(filtered);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!filtered.length) return;
                    activeSuggestionIndex = Math.max(0, activeSuggestionIndex - 1);
                    renderSuggestions(filtered);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const product = filtered[activeSuggestionIndex] || filtered[0];
                    if (product) return addProduct(product.id);

                    // Exact match fallback.
                    const q = String(searchInput.value).trim().toLowerCase();
                    const exact = products.find((p) => String(p.sku).toLowerCase() === q || String(p.name).toLowerCase() === q);
                    if (exact) addProduct(exact.id);
                    else searchHint.textContent = 'No matching product.';
                }
            });

            addProductBtn.addEventListener('click', function () {
                const product = filtered[activeSuggestionIndex] || filtered[0];
                if (product) addProduct(product.id);
                else {
                    searchHint.textContent = 'Type a product name/SKU first.';
                    searchInput.focus();
                }
            });

            itemsBody.addEventListener('input', function (e) {
                const target = e.target;
                if (!target) return;
                if (target.classList.contains('qtyInput') || target.classList.contains('priceInput')) {
                    const rowEl = target.closest('tr[data-row]');
                    if (!rowEl) return;
                    updateRowTotal(rowEl);
                    updateGrandTotal();
                }
            });

            itemsBody.addEventListener('change', function (e) {
                const target = e.target;
                if (!target) return;
                if (!target.classList.contains('variantSelect')) return;

                const rowEl = target.closest('tr[data-row]');
                if (!rowEl) return;

                const productId = rowEl.getAttribute('data-product-id');
                const variantId = target.value;
                rowEl.setAttribute('data-variant-id', variantId);

                const priceInput = rowEl.querySelector('.priceInput');
                if (priceInput) {
                    priceInput.value = unitPriceForCustomer(productId, variantId || null);
                }
                updateRowTotal(rowEl);
                updateGrandTotal();
            });

            itemsBody.addEventListener('click', function (e) {
                const btn = e.target.closest('.removeRowBtn');
                if (!btn) return;
                const rowEl = btn.closest('tr[data-row]');
                if (!rowEl) return;
                rowEl.remove();
                recalcAll();

                if (!itemsBody.querySelector('tr[data-row]')) {
                    itemsBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-gray-600">
                                No items yet. Add products above.
                            </td>
                        </tr>
                    `;
                    grandTotalEl.textContent = moneyFmt(0);
                }
            });

            clearBtn.addEventListener('click', function () {
                itemsBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-gray-600">
                            No items yet. Add products above.
                        </td>
                    </tr>
                `;
                grandTotalEl.textContent = moneyFmt(0);
                searchInput.focus();
            });

            // When customer changes, auto-fill last prices for all items.
            customerSelect.addEventListener('change', function () {
                const rows = itemsBody.querySelectorAll('tr[data-row]');
                rows.forEach((rowEl) => {
                    const pid = rowEl.getAttribute('data-product-id');
                    const vid = rowEl.getAttribute('data-variant-id');

                    const priceInput = rowEl.querySelector('.priceInput');
                    if (pid && priceInput) {
                        priceInput.value = unitPriceForCustomer(pid, vid || null);
                        updateRowTotal(rowEl);
                    }
                });
                updateGrandTotal();
            });

            // Init totals on load (for old() values).
            recalcAll();
        })();
    </script>

    <script>
        (function () {
            const openBtn = document.getElementById('openCustomerModalBtn');
            const modal = document.getElementById('customerModal');
            const backdrop = document.getElementById('customerModalBackdrop');
            const closeBtn = document.getElementById('closeCustomerModalBtn');
            const cancelBtn = document.getElementById('cancelCustomerModalBtn');
            const saveBtn = document.getElementById('saveCustomerBtn');

            if (!openBtn || !modal || !saveBtn) return;

            const customerSelect = document.getElementById('customer_id');
            const searchInput = document.getElementById('productSearch');

            // These ids come from <x-input name="name|phone|address" ... />
            const nameInput = document.getElementById('name');
            const phoneInput = document.getElementById('phone');
            const addressInput = document.getElementById('address');

            const errorNameEl = document.getElementById('error-name');
            const errorPhoneEl = document.getElementById('error-phone');
            const errorAddressEl = document.getElementById('error-address');

            function setError(el, msg) {
                if (!el) return;
                el.textContent = msg || '';
                el.classList.toggle('hidden', !msg);
            }

            function clearErrors() {
                setError(errorNameEl, '');
                setError(errorPhoneEl, '');
                setError(errorAddressEl, '');
            }

            function openModal() {
                clearErrors();
                modal.classList.remove('hidden');
                nameInput?.focus();
            }

            function closeModal() {
                modal.classList.add('hidden');
                clearErrors();
            }

            openBtn.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            cancelBtn?.addEventListener('click', closeModal);
            backdrop?.addEventListener('click', closeModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    e.preventDefault();
                    closeModal();
                }
            });

            function showLoading(isLoading) {
                if (!saveBtn) return;
                saveBtn.disabled = isLoading;
                const loadingText = saveBtn.getAttribute('data-loading-text');
                if (!loadingText) return;

                if (isLoading) {
                    if (!saveBtn.dataset.originalLabel) saveBtn.dataset.originalLabel = saveBtn.innerHTML;
                    saveBtn.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/60 border-t-white"></span>
                            ${loadingText}
                        </span>
                    `;
                } else if (saveBtn.dataset.originalLabel) {
                    saveBtn.innerHTML = saveBtn.dataset.originalLabel;
                }
            }

            saveBtn.addEventListener('click', async function () {
                const name = String(nameInput?.value ?? '').trim();
                const phone = String(phoneInput?.value ?? '').trim();
                const address = String(addressInput?.value ?? '').trim();

                clearErrors();
                showLoading(true);

                try {
                    const tokenEl = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = tokenEl ? tokenEl.getAttribute('content') : '';

                    const res = await fetch("{{ route('customers.store') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name: name,
                            phone: phone,
                            address: address
                        })
                    });

                    if (!res.ok) {
                        const data = await res.json().catch(() => ({}));
                        const errors = data.errors || {};
                        setError(errorNameEl, errors.name?.[0]);
                        setError(errorPhoneEl, errors.phone?.[0]);
                        setError(errorAddressEl, errors.address?.[0]);
                        return;
                    }

                    const data = await res.json();

                    // Add to dropdown and select.
                    if (customerSelect) {
                        const existing = customerSelect.querySelector(`option[value="${data.id}"]`);
                        if (!existing) {
                            const opt = document.createElement('option');
                            opt.value = data.id;
                            opt.textContent = data.name;
                            customerSelect.appendChild(opt);
                        }
                        customerSelect.value = String(data.id);
                        customerSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    // Reset and close.
                    if (nameInput) nameInput.value = '';
                    if (phoneInput) phoneInput.value = '';
                    if (addressInput) addressInput.value = '';
                    closeModal();
                    searchInput?.focus();
                } catch (err) {
                    // Fallback: show a generic message in name error area.
                    setError(errorNameEl, 'Failed to create customer. Please try again.');
                } finally {
                    showLoading(false);
                }
            });
        })();
    </script>
</x-app-layout>
