@once
    <style>
        [x-cloak] { display: none !important; }
    </style>
@endonce

@php
    $cfg = [
        'products' => $estimateWizard['productsPayload'],
        'customers' => $estimateWizard['customers']->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values(),
        'pricesByCustomerProducts' => $estimateWizard['pricesByCustomerProducts'],
        'pricesByCustomerVariants' => $estimateWizard['pricesByCustomerVariants'],
        'storeUrl' => route('estimates.store'),
        'estimatesBaseUrl' => url('/estimates'),
    ];
@endphp

<div
    x-data="estimateWizard(@js($cfg))"
    x-on:keydown.escape.window="if (open) close()"
    x-on:open-estimate-wizard.window="openModal()"
>
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4"
        role="dialog"
        aria-modal="true"
    >
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="close()"></div>

        <div class="relative w-full sm:max-w-lg max-h-[92vh] sm:max-h-[85vh] overflow-hidden rounded-t-2xl sm:rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 flex flex-col">
            <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-slate-100 shrink-0">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Add estimate</h2>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="stepTitle"></p>
                </div>
                <button type="button" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100" x-on:click="close()" aria-label="Close">
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                <template x-if="errorBanner">
                    <div class="rounded-xl bg-rose-50 border border-rose-200 px-3 py-2 text-sm text-rose-800" x-text="errorBanner"></div>
                </template>

                {{-- Step 1: Customer --}}
                <div x-show="step === 1" x-cloak class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Customer</label>
                        <p class="text-xs text-slate-500 mt-0.5 mb-2">Choose an existing customer or type a new name to create one.</p>
                        <select x-model="customerId" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/20 px-3 py-2.5 text-sm">
                            <option value="">Select customer…</option>
                            <template x-for="c in customers" :key="c.id">
                                <option :value="String(c.id)" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Or new customer name</label>
                        <input type="text" x-model="newCustomerName" placeholder="Type full name" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm px-3 py-2.5 text-sm" autocomplete="off" />
                    </div>
                </div>

                {{-- Step 2: Lines --}}
                <div x-show="step === 2" x-cloak class="space-y-4">
                    <div class="rounded-xl bg-slate-50 ring-1 ring-slate-200/80 p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-sm font-semibold text-slate-800" x-text="editingLineIndex === null ? 'Add product' : 'Edit line'"></div>
                            <button type="button" x-show="editingLineIndex !== null" x-on:click="cancelEditLine()" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Cancel edit</button>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Product</label>
                            <select x-model="currentProductId" x-on:change="onProductChange()" class="mt-1 block w-full rounded-lg border-slate-200 text-sm py-2">
                                <option value="">Select…</option>
                                <template x-for="p in products" :key="p.id">
                                    <option
                                        :value="String(p.id)"
                                        x-text="productOptionLabel(p)"
                                    ></option>
                                </template>
                            </select>
                        </div>
                        <template x-if="currentProduct && currentProduct.variants && currentProduct.variants.length">
                            <div>
                                <label class="text-xs font-medium text-slate-600">Variant</label>
                                <select x-model="currentVariantId" x-on:change="onVariantChange()" class="mt-1 block w-full rounded-lg border-slate-200 text-sm py-2">
                                    <template x-for="v in currentProduct.variants" :key="v.id">
                                        <option
                                            :value="String(v.id)"
                                            x-text="variantOptionLabel(v)"
                                        ></option>
                                    </template>
                                </select>
                            </div>
                        </template>
                        <div class="rounded-lg bg-white/80 ring-1 ring-slate-200/60 px-3 py-2 text-xs text-slate-600" x-show="currentProductId && stockHintCurrent !== ''">
                            <span class="font-semibold text-slate-700">Stock:</span>
                            <span x-text="stockHintCurrent"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-600">Qty <span class="text-slate-400 font-normal" x-text="maxQtyCurrent > 0 ? '(max ' + maxQtyCurrent + ')' : ''"></span></label>
                                <input
                                    type="number"
                                    :min="qtyInputMin"
                                    :max="qtyInputMax"
                                    step="1"
                                    x-model.number="currentQty"
                                    x-on:change="clampCurrentQty()"
                                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm py-2 tabular-nums"
                                />
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-600">Price</label>
                                <input type="number" min="0" step="0.01" x-model.number="currentPrice" class="mt-1 block w-full rounded-lg border-slate-200 text-sm py-2 tabular-nums" />
                            </div>
                        </div>
                        <button
                            type="button"
                            x-on:click="addLine()"
                            :disabled="addLineBlocked"
                            class="w-full rounded-xl bg-slate-800 text-white text-sm font-semibold py-2.5 hover:bg-slate-900 transition disabled:opacity-50 disabled:cursor-not-allowed"
                            x-text="editingLineIndex === null ? 'Add line' : 'Update line'"
                        ></button>
                    </div>

                    <div x-show="lines.length" class="rounded-xl ring-1 ring-slate-200 overflow-hidden">
                        <div class="px-3 py-2 bg-slate-50 text-xs font-semibold text-slate-600 uppercase tracking-wide">Lines</div>
                        <ul class="divide-y divide-slate-100">
                            <template x-for="(line, idx) in lines" :key="line._key">
                                <li class="px-3 py-2 flex justify-between gap-2 text-sm">
                                    <div class="min-w-0">
                                        <div class="font-medium text-slate-900 truncate" x-text="line.product_name"></div>
                                        <div class="text-xs text-slate-500" x-text="line.variant_name || '—'"></div>
                                        <div class="text-xs tabular-nums text-slate-600 mt-0.5">
                                            <span x-text="line.quantity"></span> × <span x-text="fmt(line.price)"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-1 leading-snug" x-text="lineStockSummary(line, idx)"></div>
                                    </div>
                                    <div class="text-right shrink-0 flex flex-col items-end gap-1">
                                        <div class="font-semibold tabular-nums" x-text="fmt(line.quantity * line.price)"></div>
                                        <button type="button" class="text-xs text-emerald-700 font-semibold" x-on:click="startEditLine(idx)">Edit</button>
                                        <button type="button" class="text-xs text-rose-600 font-semibold" x-on:click="removeLine(idx)">Delete</button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                {{-- Step 3: Saved estimate --}}
                <div x-show="step === 3" x-cloak class="space-y-4">
                    <div class="rounded-xl bg-emerald-50/80 ring-1 ring-emerald-200/60 p-4">
                        <div class="text-xs font-semibold text-emerald-800 uppercase tracking-wide">Estimate</div>
                        <div class="mt-1 text-lg font-bold text-slate-900" x-text="savedEstimate ? savedEstimate.customer_name : ''"></div>
                        <div class="mt-2 text-2xl font-bold tabular-nums text-emerald-900" x-text="savedEstimate ? fmt(savedEstimate.total_amount) : ''"></div>
                    </div>
                    <div class="overflow-x-auto rounded-xl ring-1 ring-slate-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase">
                                <tr>
                                    <th class="px-3 py-2">Product</th>
                                    <th class="px-3 py-2 text-right">Qty</th>
                                    <th class="px-3 py-2 text-right">Price</th>
                                    <th class="px-3 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(it, i) in (savedEstimate ? savedEstimate.items : [])" :key="i">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <div class="font-medium text-slate-900" x-text="it.product_name"></div>
                                            <div class="text-xs text-slate-500" x-text="it.variant_name || '—'"></div>
                                        </td>
                                        <td class="px-3 py-2 text-right tabular-nums" x-text="it.quantity"></td>
                                        <td class="px-3 py-2 text-right tabular-nums" x-text="fmt(it.price)"></td>
                                        <td class="px-3 py-2 text-right font-semibold tabular-nums" x-text="fmt(it.line_total)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" x-on:click="showPay = true" class="w-full rounded-xl bg-emerald-600 text-white text-sm font-bold py-3 hover:bg-emerald-700 shadow-sm">
                        Convert to sale
                    </button>
                </div>
            </div>

            <div class="shrink-0 border-t border-slate-100 px-5 py-4 flex gap-3 justify-between bg-white">
                <button type="button" x-show="step > 1 && step < 3" x-on:click="back()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Back
                </button>
                <div class="flex-1"></div>
                <template x-if="step === 1">
                    <button type="button" x-on:click="nextCustomer()" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700">
                        Next
                    </button>
                </template>
                <template x-if="step === 2">
                    <button type="button" x-on:click="completeEstimate()" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 disabled:opacity-50">
                        <span x-text="saving ? 'Saving…' : 'Complete estimate'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- Payment sub-modal --}}
    <div
        x-show="open && showPay"
        x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-slate-900/60" x-on:click="showPay = false"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 p-5 sm:p-6">
            <h3 class="text-lg font-bold text-slate-900">Create sale</h3>
            <p class="text-sm text-slate-600 mt-1">Enter amount paid and payment method.</p>

            <template x-if="payError">
                <div class="mt-3 rounded-xl bg-rose-50 border border-rose-200 px-3 py-2 text-sm text-rose-800" x-text="payError"></div>
            </template>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700">Amount paid</label>
                    <input type="number" min="0" step="0.01" x-model.number="payAmount" class="mt-1 block w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm tabular-nums" />
                </div>
                <fieldset>
                    <legend class="text-sm font-semibold text-slate-700">Paid by</legend>
                    <div class="mt-2 grid grid-cols-1 gap-2">
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="pm" value="cash" x-model="payMethod" class="text-emerald-600" />
                            <span class="text-sm">Cash</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="pm" value="jazzcash" x-model="payMethod" class="text-emerald-600" />
                            <span class="text-sm">JazzCash</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="pm" value="easypaisa" x-model="payMethod" class="text-emerald-600" />
                            <span class="text-sm">Easypaisa</span>
                        </label>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="pm" value="bank_mezzan" x-model="payMethod" class="text-emerald-600" />
                            <span class="text-sm">Bank Mezzan</span>
                        </label>
                    </div>
                </fieldset>
                <p class="text-xs text-slate-500">Leave amount at 0 for a fully unpaid sale (no payment recorded).</p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="showPay = false" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700">Cancel</button>
                <button type="button" x-on:click="submitConvert()" :disabled="converting" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-sm font-bold disabled:opacity-50">
                    <span x-text="converting ? 'Creating…' : 'Create'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function estimateWizard(config) {
        return {
            open: false,
            step: 1,
            showPay: false,
            saving: false,
            converting: false,
            errorBanner: '',
            customers: config.customers,
            products: config.products,
            pricesByCustomerProducts: config.pricesByCustomerProducts || {},
            pricesByCustomerVariants: config.pricesByCustomerVariants || {},
            storeUrl: config.storeUrl,
            estimatesBaseUrl: config.estimatesBaseUrl,
            customerId: '',
            newCustomerName: '',
            lines: [],
            editingLineIndex: null,
            currentProductId: '',
            currentVariantId: '',
            currentQty: 1,
            currentPrice: 0,
            savedEstimate: null,
            savedEstimateId: null,
            payAmount: 0,
            payMethod: 'cash',
            payError: '',
            _lineKeySeq: 0,

            get stepTitle() {
                if (this.step === 1) return 'Step 1 — Customer';
                if (this.step === 2) return 'Step 2 — Products';
                if (this.step === 3) return 'Step 3 — Estimate';
                return '';
            },

            get currentProduct() {
                if (!this.currentProductId) return null;
                return this.products.find((p) => String(p.id) === String(this.currentProductId)) || null;
            },

            fmt(n) {
                const x = Number.isFinite(Number(n)) ? Number(n) : 0;
                return x.toFixed(2);
            },

            productOptionLabel(p) {
                let t = p.name + (p.sku ? ' (' + p.sku + ')' : '');
                if (!p.variants || !p.variants.length) {
                    t += ' · stock ' + (Number(p.stock_quantity) || 0);
                }
                return t;
            },

            variantOptionLabel(v) {
                return v.variant_name + ' · stock ' + (Number(v.stock_quantity) || 0);
            },

            stockForProduct(productId, variantId) {
                const p = this.products.find((x) => String(x.id) === String(productId));
                if (!p) return 0;
                if (p.variants && p.variants.length > 0) {
                    const v = p.variants.find((x) => String(x.id) === String(variantId));
                    return v ? Math.max(0, parseInt(String(v.stock_quantity), 10) || 0) : 0;
                }
                return Math.max(0, parseInt(String(p.stock_quantity), 10) || 0);
            },

            qtyUsedSameSku(productId, variantId, excludeIndex) {
                let sum = 0;
                this.lines.forEach((l, i) => {
                    if (excludeIndex !== null && excludeIndex !== undefined && i === excludeIndex) return;
                    if (Number(l.product_id) !== Number(productId)) return;
                    const lv = l.variant_id == null ? null : Number(l.variant_id);
                    const rv = variantId == null || variantId === '' ? null : Number(variantId);
                    if (lv !== rv) return;
                    sum += Math.max(0, parseInt(String(l.quantity), 10) || 0);
                });
                return sum;
            },

            get maxQtyCurrent() {
                const pid = this.currentProductId;
                if (!pid) return 0;
                const p = this.currentProduct;
                const hasV = p?.variants?.length > 0;
                const vid = hasV ? this.currentVariantId : null;
                if (hasV && !vid) return 0;
                const stock = this.stockForProduct(pid, vid);
                const used = this.qtyUsedSameSku(pid, vid, this.editingLineIndex);
                return Math.max(0, stock - used);
            },

            get stockHintCurrent() {
                const pid = this.currentProductId;
                if (!pid) return '';
                const p = this.currentProduct;
                const hasV = p?.variants?.length > 0;
                const vid = hasV ? this.currentVariantId : null;
                if (hasV && !vid) return 'Select a variant to see stock.';
                const stock = this.stockForProduct(pid, vid);
                const used = this.qtyUsedSameSku(pid, vid, this.editingLineIndex);
                const rem = Math.max(0, stock - used);
                return stock + ' on hand · ' + used + ' in this estimate · ' + rem + ' available to add';
            },

            get qtyInputMin() {
                return this.maxQtyCurrent > 0 ? 1 : 0;
            },

            get qtyInputMax() {
                const m = this.maxQtyCurrent;
                return m > 0 ? m : 0;
            },

            get addLineBlocked() {
                if (!this.currentProductId) return true;
                const p = this.currentProduct;
                if (p?.variants?.length > 0 && !this.currentVariantId) return true;
                return this.maxQtyCurrent < 1;
            },

            lineStockSummary(line, idx) {
                const stock = this.stockForProduct(line.product_id, line.variant_id);
                const totalInEst = this.qtyUsedSameSku(line.product_id, line.variant_id, null);
                const rem = Math.max(0, stock - totalInEst);
                return 'Stock ' + stock + ' · In estimate ' + totalInEst + ' · Remaining ' + rem;
            },

            unitPrice(productId, variantId) {
                const cid = String(this.customerId || '');
                if (variantId) {
                    const last = this.pricesByCustomerVariants?.[cid]?.[String(variantId)];
                    if (last !== undefined && last !== null && last !== '') {
                        const n = parseFloat(String(last).replace(',', '.'));
                        if (Number.isFinite(n)) return n;
                    }
                    const p = this.products.find((x) => String(x.id) === String(productId));
                    const v = p?.variants?.find((x) => String(x.id) === String(variantId));
                    return v ? Number(v.sale_price) : 0;
                }
                const last = this.pricesByCustomerProducts?.[cid]?.[String(productId)];
                if (last !== undefined && last !== null && last !== '') {
                    const n = parseFloat(String(last).replace(',', '.'));
                    if (Number.isFinite(n)) return n;
                }
                const p = this.products.find((x) => String(x.id) === String(productId));
                return p ? Number(p.sale_price) : 0;
            },

            onProductChange() {
                this.currentVariantId = '';
                const p = this.currentProduct;
                if (p?.variants?.length) {
                    this.currentVariantId = String(p.variants[0].id);
                }
                this.syncPriceFromSelection();
                this.currentQty = this.maxQtyCurrent > 0 ? 1 : 0;
                this.clampCurrentQty();
            },

            onVariantChange() {
                this.syncPriceFromSelection();
                this.currentQty = this.maxQtyCurrent > 0 ? 1 : 0;
                this.clampCurrentQty();
            },

            syncPriceFromSelection() {
                const pid = this.currentProductId;
                const vid = this.currentVariantId || null;
                if (!pid) {
                    this.currentPrice = 0;
                    return;
                }
                this.currentPrice = this.unitPrice(pid, vid);
            },

            clampCurrentQty() {
                const max = this.maxQtyCurrent;
                let q = parseInt(String(this.currentQty), 10);
                if (!Number.isFinite(q)) q = max > 0 ? 1 : 0;
                if (max <= 0) {
                    this.currentQty = 0;
                    return;
                }
                if (q < 1) q = 1;
                if (q > max) q = max;
                this.currentQty = q;
            },

            nextLineKey() {
                this._lineKeySeq += 1;
                return 'L' + Date.now() + '_' + this._lineKeySeq;
            },

            openModal() {
                this.reset();
                this.open = true;
            },

            close() {
                this.open = false;
                this.showPay = false;
            },

            reset() {
                this.step = 1;
                this.showPay = false;
                this.errorBanner = '';
                this.customerId = '';
                this.newCustomerName = '';
                this.lines = [];
                this.editingLineIndex = null;
                this.currentProductId = '';
                this.currentVariantId = '';
                this.currentQty = 1;
                this.currentPrice = 0;
                this.savedEstimate = null;
                this.savedEstimateId = null;
                this.payAmount = 0;
                this.payMethod = 'cash';
                this.payError = '';
                this.saving = false;
                this.converting = false;
                this._lineKeySeq = 0;
            },

            back() {
                this.errorBanner = '';
                if (this.step === 2) this.step = 1;
            },

            nextCustomer() {
                this.errorBanner = '';
                const cid = String(this.customerId || '').trim();
                const nn = String(this.newCustomerName || '').trim();
                if (!cid && !nn) {
                    this.errorBanner = 'Select a customer or enter a new name.';
                    return;
                }
                if (cid && nn) {
                    this.errorBanner = 'Use either a customer from the list or a new name, not both.';
                    return;
                }
                this.step = 2;
            },

            startEditLine(idx) {
                this.errorBanner = '';
                const line = this.lines[idx];
                if (!line) return;
                this.editingLineIndex = idx;
                this.currentProductId = String(line.product_id);
                this.currentVariantId = line.variant_id != null ? String(line.variant_id) : '';
                this.currentQty = Math.max(0, parseInt(String(line.quantity), 10) || 0);
                this.currentPrice = Number(line.price) || 0;
                this.clampCurrentQty();
            },

            cancelEditLine() {
                this.editingLineIndex = null;
                this.currentProductId = '';
                this.currentVariantId = '';
                this.currentQty = 1;
                this.currentPrice = 0;
            },

            removeLine(idx) {
                this.errorBanner = '';
                if (this.editingLineIndex === idx) {
                    this.cancelEditLine();
                } else if (this.editingLineIndex !== null && this.editingLineIndex > idx) {
                    this.editingLineIndex -= 1;
                }
                this.lines.splice(idx, 1);
            },

            addLine() {
                this.errorBanner = '';
                if (this.addLineBlocked) return;
                const pid = this.currentProductId;
                if (!pid) {
                    this.errorBanner = 'Select a product.';
                    return;
                }
                const p = this.currentProduct;
                const hasV = p?.variants?.length > 0;
                const vid = hasV ? this.currentVariantId : '';
                if (hasV && !vid) {
                    this.errorBanner = 'Select a variant.';
                    return;
                }
                this.clampCurrentQty();
                let qty = Math.max(1, parseInt(String(this.currentQty), 10) || 1);
                const max = this.maxQtyCurrent;
                if (max <= 0) {
                    this.errorBanner = 'No stock available for this product.';
                    return;
                }
                if (qty > max) qty = max;
                const price = Math.max(0, Number(this.currentPrice) || 0);
                const vObj = hasV ? p.variants.find((x) => String(x.id) === String(vid)) : null;
                const row = {
                    product_id: Number(pid),
                    variant_id: hasV ? Number(vid) : null,
                    product_name: p.name,
                    variant_name: vObj ? vObj.variant_name : '',
                    quantity: qty,
                    price,
                };
                if (this.editingLineIndex !== null) {
                    const old = this.lines[this.editingLineIndex];
                    row._key = old._key;
                    this.lines[this.editingLineIndex] = row;
                    this.editingLineIndex = null;
                } else {
                    row._key = this.nextLineKey();
                    this.lines.push(row);
                }
                this.currentProductId = '';
                this.currentVariantId = '';
                this.currentQty = 1;
                this.currentPrice = 0;
            },

            async completeEstimate() {
                this.errorBanner = '';
                if (!this.lines.length) {
                    this.errorBanner = 'Add at least one product.';
                    return;
                }
                const cid = String(this.customerId || '').trim();
                const nn = String(this.newCustomerName || '').trim();
                const payload = {
                    items: this.lines.map((l) => ({
                        product_id: l.product_id,
                        variant_id: l.variant_id,
                        quantity: l.quantity,
                        price: l.price,
                    })),
                };
                if (cid) payload.customer_id = Number(cid);
                if (nn) payload.new_customer_name = nn;

                this.saving = true;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch(this.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) {
                        const msg = data.message || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Could not save estimate.';
                        this.errorBanner = msg;
                        return;
                    }
                    this.savedEstimate = data.estimate;
                    this.savedEstimateId = data.estimate.id;
                    this.payAmount = Number(data.estimate.total_amount) || 0;
                    this.step = 3;
                } catch (e) {
                    this.errorBanner = 'Network error. Try again.';
                } finally {
                    this.saving = false;
                }
            },

            async submitConvert() {
                this.errorBanner = '';
                this.payError = '';
                if (!this.savedEstimateId) return;
                const amt = Math.max(0, Number(this.payAmount) || 0);
                const total = Number(this.savedEstimate?.total_amount) || 0;
                if (amt > total) {
                    this.payError = 'Amount paid cannot exceed the estimate total.';
                    return;
                }
                const body = { amount: amt };
                if (amt > 0) body.payment_method = this.payMethod;

                this.converting = true;
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const url = `${this.estimatesBaseUrl}/${this.savedEstimateId}/convert`;
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify(body),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) {
                        const msg = data.message || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Could not create sale.';
                        this.payError = msg;
                        return;
                    }
                    window.location.href = data.redirect_url;
                } catch (e) {
                    this.payError = 'Network error. Try again.';
                } finally {
                    this.converting = false;
                }
            },
        };
    }
</script>
