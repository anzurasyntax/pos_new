<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">New sale</h1>
            <p class="mt-1 text-sm text-slate-500">Select a customer, search by name or SKU, then save when the cart is ready.</p>
        </div>
    </x-slot>

    <div class="py-6 pb-28 md:pb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 flex gap-3 rounded-xl bg-emerald-50 border border-emerald-200/80 px-4 py-3 text-emerald-900 shadow-sm" role="status">
                    <span class="shrink-0 text-emerald-600 mt-0.5" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </span>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200/80 px-4 py-3 text-rose-900 shadow-sm" role="alert">
                    <div class="font-semibold mb-2 flex items-center gap-2">
                        <svg class="h-5 w-5 text-rose-600 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Please fix the following:
                    </div>
                    <ul class="list-disc ps-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-soft ring-1 ring-slate-200/60 p-4 sm:p-6 lg:p-8">
                <form method="POST" action="{{ route('sales.store') }}" id="salesForm">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                        <div class="lg:col-span-1 rounded-xl bg-slate-50/80 ring-1 ring-slate-200/60 p-4 sm:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                                <div class="flex-1 min-w-0">
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

                                <div class="sm:pb-0.5 shrink-0">
                                    <x-button
                                        type="secondary"
                                        htmlType="button"
                                        text="New customer"
                                        id="openCustomerModalBtn"
                                        class="w-full sm:w-auto px-4 py-2.5 whitespace-nowrap"
                                    />
                                </div>
                            </div>

                            <p class="mt-4 text-sm text-slate-600 leading-relaxed">
                                <span class="font-medium text-slate-700">Tip:</span> pick the customer first, then type a product name or SKU and press <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 text-xs font-mono text-slate-600">Enter</kbd> to add lines quickly.
                            </p>
                        </div>

                        <div class="lg:col-span-2">
                            <label for="productSearch" class="block text-sm font-semibold text-slate-700">
                                Find product
                            </label>
                            <p class="text-xs text-slate-500 mt-0.5 mb-2">Search by product name or SKU</p>

                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <x-input
                                    name="productSearch"
                                    type="text"
                                    placeholder="Start typing…"
                                    class="mt-0 pl-10 pr-14 py-3 text-base border-slate-200"
                                    autocomplete="off"
                                    spellcheck="false"
                                    autofocus
                                />

                                <x-button
                                    type="primary"
                                    htmlType="button"
                                    text=""
                                    id="addProductBtn"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-2 rounded-lg"
                                    aria-label="Add highlighted product"
                                >
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                    </svg>
                                </x-button>

                                <div id="suggestions" class="absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-xl shadow-lg shadow-slate-900/10 overflow-hidden hidden z-20 ring-1 ring-slate-200/80">
                                    <div id="suggestionsInner" class="max-h-64 overflow-auto divide-y divide-slate-100"></div>
                                </div>
                            </div>

                            <div id="searchHint" class="mt-2 text-sm font-medium text-rose-600 min-h-[1.25rem]" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight">Line items</h3>
                            <x-button
                                type="secondary"
                                htmlType="button"
                                text="Clear cart"
                                id="clearBtn"
                                class="px-4 py-2.5 w-full sm:w-auto"
                            />
                        </div>

                        <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200/80">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50/90">
                                    <tr>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Product</th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Variant</th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Qty</th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider"><span class="sr-only">Remove</span></th>
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

                                        <tr class="align-top hover:bg-slate-50/50 transition-colors" data-row="{{ $i }}"
                                            data-product-id="{{ $productId }}"
                                            data-variant-id="{{ $variantId }}">
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-800">
                                                    {{ $product?->name ?? 'Unknown' }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    SKU: {{ $product?->sku ?? '-' }}
                                                </div>
                                                <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $productId }}"/>
                                            </td>

                                            <td class="px-4 py-3" style="min-width: 180px;">
                                                @if ($hasVariants)
                                                    <select name="items[{{ $i }}][variant_id]"
                                                        class="block w-full rounded-lg border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 px-3 py-2 variantSelect"
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

                                            <td class="px-4 py-3" style="min-width: 110px;">
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

                                            <td class="px-4 py-3" style="min-width: 160px;">
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

                                            <td class="px-4 py-3">
                                                <div class="text-slate-900 font-semibold tabular-nums lineTotal">0.00</div>
                                            </td>

                                            <td class="px-4 py-3">
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
                                            <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">
                                                Cart is empty. Use the search field above to add products.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-8 md:mt-6 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-5 sm:p-6 text-white shadow-lg ring-1 ring-slate-700/50">
                        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 sm:gap-4">
                            <div>
                                <div class="text-sm font-medium text-slate-300">Grand total</div>
                                <div class="text-xs text-slate-400 mt-0.5 hidden sm:block">Including all line items below</div>
                            </div>
                            <div class="text-3xl sm:text-4xl font-bold tabular-nums tracking-tight text-white" id="grandTotal">0.00</div>
                        </div>
                        @error('stock')
                            <p class="mt-4 text-sm text-rose-300 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5 hidden md:flex justify-end">
                        <x-button
                            type="primary"
                            htmlType="submit"
                            text=""
                            loadingText="Saving..."
                            class="text-base px-8 py-3.5 rounded-xl"
                            id="saveSaleDesktopBtn"
                        >
                            <svg class="w-5 h-5 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.172 8.707 9.879a1 1 0 00-1.414 1.414l1.414 1.414a1 1 0 001.414 0l3.586-3.586z" clip-rule="evenodd"/>
                            </svg>
                            <span>Save sale</span>
                        </x-button>
                    </div>
                </form>
            </div>

            <!-- Mobile: sticky checkout -->
            <div class="md:hidden fixed bottom-0 left-0 right-0 z-40 border-t border-slate-200 bg-white/95 backdrop-blur-lg px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] shadow-[0_-8px_30px_rgba(15,23,42,0.12)]">
                <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total</div>
                        <div class="text-xl font-bold tabular-nums text-slate-900" id="grandTotalSticky">0.00</div>
                    </div>
                    <button
                        type="submit"
                        form="salesForm"
                        data-loading-text="Saving..."
                        id="saveSaleMobileBtn"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-semibold text-white shadow-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-60 shrink-0"
                    >
                        <svg class="w-5 h-5 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.172 8.707 9.879a1 1 0 00-1.414 1.414l1.414 1.414a1 1 0 001.414 0l3.586-3.586z" clip-rule="evenodd"/>
                        </svg>
                        Save sale
                    </button>
                </div>
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
        <div id="customerModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

        <div class="relative max-w-lg mx-auto mt-12 sm:mt-24 px-4">
            <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200/80 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 id="customerModalTitle" class="text-lg font-bold text-slate-900 tracking-tight">
                            New customer
                        </h3>
                        <p class="mt-1 text-sm text-slate-600 leading-relaxed">
                            They will appear in the customer list immediately after saving.
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
            const grandTotalStickyEl = document.getElementById('grandTotalSticky');
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
                const formatted = moneyFmt(total);
                grandTotalEl.textContent = formatted;
                if (grandTotalStickyEl) grandTotalStickyEl.textContent = formatted;
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
                tr.setAttribute('data-row', String(idx));
                tr.setAttribute('data-product-id', String(productId));
                tr.setAttribute('data-variant-id', String(variantId));

                const variantCell = hasVariants
                    ? `
                        <select name="items[${idx}][variant_id]" class="block w-full rounded-lg border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 px-3 py-2 variantSelect">
                            ${variantOptionsHtml(productId, variantId)}
                        </select>
                      `
                    : `<span class="text-sm text-slate-700">-</span>`;

                tr.className = 'align-top hover:bg-slate-50/50 transition-colors';
                tr.innerHTML = `
                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-900">${product.name}</div>
                        <div class="text-xs text-slate-500">SKU: ${product.sku}</div>
                        <input type="hidden" name="items[${idx}][product_id]" value="${productId}"/>
                    </td>
                    <td class="px-4 py-3" style="min-width: 180px;">
                        ${variantCell}
                    </td>
                    <td class="px-4 py-3" style="min-width: 110px;">
                        <input type="number" min="1" step="1"
                            name="items[${idx}][quantity]"
                            value="${qty}"
                            class="block w-full rounded-lg border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 px-3 py-2 qtyInput" />
                    </td>
                    <td class="px-4 py-3" style="min-width: 160px;">
                        <input type="number" min="0" step="0.01"
                            name="items[${idx}][price]"
                            value="${price}"
                            class="block w-full rounded-lg border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 px-3 py-2 priceInput" />
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-slate-900 font-semibold tabular-nums lineTotal">${moneyFmt(lineTotal)}</div>
                    </td>
                    <td class="px-4 py-3">
                        <button type="button" class="removeRowBtn inline-flex items-center justify-center px-3 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 text-sm font-semibold disabled:opacity-60 disabled:cursor-not-allowed">
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
                    div.className = 'px-4 py-3 cursor-pointer text-sm text-slate-800 hover:bg-emerald-50 active:bg-emerald-100/80';
                    if (i === activeSuggestionIndex) div.className = 'px-4 py-3 cursor-pointer text-sm font-medium bg-emerald-50 text-emerald-900 ring-inset ring-1 ring-emerald-200/80';
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
                            <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">
                                Cart is empty. Use the search field above to add products.
                            </td>
                        </tr>
                    `;
                    updateGrandTotal();
                }
            });

            clearBtn.addEventListener('click', function () {
                itemsBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">
                            Cart is empty. Use the search field above to add products.
                        </td>
                    </tr>
                `;
                updateGrandTotal();
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

            const salesForm = document.getElementById('salesForm');
            const saveMobile = document.getElementById('saveSaleMobileBtn');
            if (salesForm && saveMobile) {
                salesForm.addEventListener('submit', function () {
                    const loadingText = saveMobile.getAttribute('data-loading-text');
                    if (!loadingText || saveMobile.disabled) return;
                    if (!saveMobile.dataset.originalHtml) {
                        saveMobile.dataset.originalHtml = saveMobile.innerHTML;
                    }
                    saveMobile.disabled = true;
                    saveMobile.innerHTML = `
                        <span class="inline-flex items-center gap-2">
                            <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/60 border-t-white"></span>
                            ${loadingText}
                        </span>
                    `;
                });
            }
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
