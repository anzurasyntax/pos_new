<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Products
            </h2>

            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2 sm:gap-3">
                <button type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-price-wizard', { detail: { mode: 'sale' } }))"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-900 text-sm font-semibold hover:bg-emerald-100">
                    <svg class="w-4 h-4 me-2 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                    Update sale prices
                </button>
                <button type="button"
                    onclick="window.dispatchEvent(new CustomEvent('open-price-wizard', { detail: { mode: 'purchase' } }))"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-900 text-sm font-semibold hover:bg-sky-100">
                    <svg class="w-4 h-4 me-2 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a2 2 0 012 2v2a2 2 0 01-2 2H6v2h2v2H6v3a1 1 0 11-2 0v-3H3v-2h1V9H3a2 2 0 01-2-2V5a2 2 0 012-2h1V3a1 1 0 011-1zm0 5v2h6V7H5z" clip-rule="evenodd"/>
                    </svg>
                    Update purchase prices
                </button>
                <a href="{{ route('products.create') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    <svg class="w-5 h-5 me-2 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    Add Product
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6"
        x-data="priceWizardModal(@js($priceWizardSteps))"
        @open-price-wizard.window="openWizard($event.detail.mode)">
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
                <!-- Search -->
                <form method="GET" action="{{ route('products.index') }}" class="mb-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <label for="q" class="block text-sm font-medium text-gray-700">
                                Search by name
                            </label>
                            <input id="q" name="q" type="text" value="{{ old('q', $q) }}"
                                placeholder="Type product name..."
                                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"/>
                        </div>

                        <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-gray-900 text-white font-medium hover:bg-black">
                            <svg class="w-5 h-5 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.39 4.39a1 1 0 01-1.414 1.414l-4.39-4.39zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/>
                            </svg>
                            Search
                        </button>

                        @if (filled($q))
                            <a href="{{ route('products.index') }}"
                                class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 text-gray-700 font-medium hover:bg-gray-50">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Catalog group
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SKU
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Prices (Purchase / Sale)
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3 text-sm text-gray-600">
                                        @if ($product->category)
                                            <span class="text-gray-500">{{ $product->category->parent?->name ?? '—' }}</span>
                                            <span class="text-gray-400 mx-1" aria-hidden="true">›</span>
                                            <span class="font-medium text-gray-800">{{ $product->category->name }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        {{ $product->name }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800 font-mono text-xs">
                                        {{ $product->sku }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        {{ $product->stock_quantity }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        @if ($product->variants->count() > 0)
                                            @php
                                                $minPurchase = (float) $product->variants->min('purchase_price');
                                                $maxPurchase = (float) $product->variants->max('purchase_price');
                                                $minSale = (float) $product->variants->min('sale_price');
                                                $maxSale = (float) $product->variants->max('sale_price');
                                            @endphp
                                            <div class="flex flex-col">
                                                <span>Purchase: {{ number_format($minPurchase, 2) }} - {{ number_format($maxPurchase, 2) }}</span>
                                                <span>Sale: {{ number_format($minSale, 2) }} - {{ number_format($maxSale, 2) }}</span>
                                            </div>
                                        @else
                                            {{ number_format((float) $product->purchase_price, 2) }} / {{ number_format((float) $product->sale_price, 2) }}
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                            <a href="{{ route('products.edit', $product) }}"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-indigo-50 text-indigo-700 font-medium border border-indigo-200 hover:bg-indigo-100">
                                                <svg class="w-4 h-4 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-9.9 9.9a1 1 0 01-.56.277l-2.6.52a1 1 0 01-1.175-1.175l.52-2.6a1 1 0 01.277-.56l9.9-9.9z"/>
                                                </svg>
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('products.destroy', $product) }}"
                                                onsubmit="return confirm('Delete this product?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-red-50 text-red-700 font-medium border border-red-200 hover:bg-red-100">
                                                    <svg class="w-4 h-4 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H3a1 1 0 100 2h1v10a2 2 0 002 2h6a2 2 0 002-2V6h1a1 1 0 100-2h-2V3a1 1 0 00-1-1H6zm0 4h8v10H6V6zm3 2a1 1 0 10-2 0v6a1 1 0 102 0V8zm4 0a1 1 0 10-2 0v6a1 1 0 102 0V8z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-3 py-6 text-gray-600" colspan="6">
                                        No products found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        <!-- Price update wizard modal -->
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="'price-wizard-title'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="close()" aria-hidden="true"></div>

            <div
                class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200/80 overflow-hidden"
                @click.stop
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            >
                <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 bg-slate-50/80">
                    <div>
                        <h3 id="price-wizard-title" class="text-lg font-bold text-slate-900" x-text="mode === 'sale' ? 'Update sale prices' : 'Update purchase prices'"></h3>
                        <p class="text-sm text-slate-500 mt-0.5" x-show="totalSteps > 0">
                            Item <span class="font-semibold tabular-nums text-slate-800" x-text="currentIndex + 1"></span>
                            of <span class="font-semibold tabular-nums" x-text="totalSteps"></span>
                        </p>
                    </div>
                    <button type="button" @click="close()" class="rounded-lg p-2 text-slate-500 hover:bg-slate-200/80 hover:text-slate-800" aria-label="Close">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                <template x-if="totalSteps === 0">
                    <div class="p-6 text-center text-slate-600">
                        <p class="font-medium text-slate-800">No products yet</p>
                        <p class="text-sm mt-1">Add products first, then run this wizard.</p>
                        <button type="button" @click="close()" class="mt-4 px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold">Close</button>
                    </div>
                </template>

                <template x-if="totalSteps > 0 && currentStep">
                    <form class="p-5 sm:p-6 space-y-4" @submit.prevent="saveAndNext()">
                        <div class="rounded-xl bg-slate-50 ring-1 ring-slate-200/80 p-4 space-y-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500" x-text="currentStep.subtitle"></p>
                            <p class="text-base font-semibold text-slate-900 leading-snug" x-text="currentStep.title"></p>
                            <p class="text-xs font-mono text-slate-500">SKU: <span x-text="currentStep.sku"></span></p>
                        </div>

                        <div>
                            <label :for="priceInputId" class="block text-sm font-semibold text-slate-700" x-text="mode === 'sale' ? 'Sale price' : 'Purchase price'"></label>
                            <input
                                :id="priceInputId"
                                type="number"
                                name="wizard_price"
                                min="0"
                                step="0.01"
                                x-model="inputValue"
                                x-ref="priceInput"
                                class="mt-2 block w-full rounded-lg border-slate-200 shadow-sm px-3 py-3 text-lg font-semibold tabular-nums text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                                autocomplete="off"
                                @keydown.enter.prevent="saveAndNext()"
                            />
                        </div>

                        <p x-show="errorMessage" x-text="errorMessage" class="text-sm font-medium text-rose-600" x-cloak></p>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
                            <button type="button" @click="close()" class="inline-flex justify-center items-center px-4 py-2.5 rounded-lg border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="inline-flex justify-center items-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                                :disabled="saving"
                            >
                                <span x-show="!saving && currentIndex < totalSteps - 1">Save &amp; next</span>
                                <span x-show="!saving && currentIndex >= totalSteps - 1">Save &amp; finish</span>
                                <span x-show="saving" class="inline-flex items-center gap-2">
                                    <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white/50 border-t-white"></span>
                                    Saving…
                                </span>
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

        <style>
            [x-cloak] { display: none !important; }
        </style>

        <script>
            function priceWizardModal(steps) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const base = @json(url(''));

                return {
                    open: false,
                    mode: 'sale',
                    steps: Array.isArray(steps) ? steps : [],
                    currentIndex: 0,
                    inputValue: '',
                    saving: false,
                    errorMessage: '',
                    get totalSteps() {
                        return this.steps.length;
                    },
                    get currentStep() {
                        return this.steps[this.currentIndex] ?? null;
                    },
                    get priceInputId() {
                        return 'price-wizard-input';
                    },
                    openWizard(mode) {
                        this.mode = mode === 'purchase' ? 'purchase' : 'sale';
                        this.currentIndex = 0;
                        this.errorMessage = '';
                        this.open = true;
                        this.prefillFromStep();
                        this.$nextTick(() => {
                            this.$refs.priceInput?.focus?.();
                            this.$refs.priceInput?.select?.();
                        });
                    },
                    prefillFromStep() {
                        const s = this.currentStep;
                        if (!s) {
                            this.inputValue = '';
                            return;
                        }
                        this.inputValue = this.mode === 'sale' ? s.sale_price : s.purchase_price;
                    },
                    close() {
                        this.open = false;
                        this.errorMessage = '';
                        this.saving = false;
                    },
                    productUrl(id) {
                        return base.replace(/\/$/, '') + '/products/' + id + '/quick-price';
                    },
                    variantUrl(productId, variantId) {
                        return base.replace(/\/$/, '') + '/products/' + productId + '/variants/' + variantId + '/quick-price';
                    },
                    async saveAndNext() {
                        if (!this.currentStep || this.saving) return;

                        const field = this.mode === 'sale' ? 'sale_price' : 'purchase_price';
                        const raw = String(this.inputValue ?? '').trim();
                        if (raw === '') {
                            this.errorMessage = 'Enter a price.';
                            return;
                        }
                        const value = parseFloat(raw.replace(',', '.'));
                        if (!Number.isFinite(value) || value < 0) {
                            this.errorMessage = 'Enter a valid number (0 or greater).';
                            return;
                        }

                        this.errorMessage = '';
                        this.saving = true;

                        const step = this.currentStep;
                        const url = step.kind === 'variant'
                            ? this.variantUrl(step.productId, step.variantId)
                            : this.productUrl(step.productId);

                        try {
                            const res = await fetch(url, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ field, value }),
                            });

                            const data = await res.json().catch(() => ({}));

                            if (!res.ok) {
                                const msg = data.message || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Could not save.';
                                this.errorMessage = typeof msg === 'string' ? msg : 'Could not save.';
                                this.saving = false;
                                return;
                            }

                            if (this.currentIndex >= this.totalSteps - 1) {
                                this.close();
                                window.location.reload();
                                return;
                            }

                            this.currentIndex += 1;
                            this.prefillFromStep();
                            this.saving = false;
                            this.$nextTick(() => {
                                this.$refs.priceInput?.focus?.();
                                this.$refs.priceInput?.select?.();
                            });
                        } catch (e) {
                            this.errorMessage = 'Network error. Try again.';
                            this.saving = false;
                        }
                    },
                };
            }
        </script>
    </div>
</x-app-layout>

