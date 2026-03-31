<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Estimate #{{ $estimate->id }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $estimate->customer?->name ?? 'Customer' }} · Total {{ number_format((float) $estimate->total_amount, 2) }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-slate-900">Line items</h2>
                    @if ($estimate->status === \App\Models\Estimate::STATUS_OPEN)
                        <button type="button" id="openConvertEstimateBtn" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                            Convert to sale
                        </button>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Product</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Variant</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Qty</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Price</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($estimate->items as $item)
                                @php $lt = (int) $item->quantity * (float) $item->price; @endphp
                                <tr>
                                    <td class="px-5 py-3 font-medium text-slate-900">{{ $item->product?->name ?? '—' }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $item->variant?->variant_name ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right tabular-nums">{{ (int) $item->quantity }}</td>
                                    <td class="px-5 py-3 text-right tabular-nums">{{ number_format((float) $item->price, 2) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold tabular-nums">{{ number_format($lt, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 bg-slate-50/80 border-t border-slate-100 flex justify-end">
                    <div class="text-right">
                        <div class="text-xs font-medium text-slate-500 uppercase">Grand total</div>
                        <div class="text-2xl font-bold tabular-nums text-slate-900">{{ number_format((float) $estimate->total_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($estimate->status === \App\Models\Estimate::STATUS_OPEN)
        <div id="convertModal" class="fixed inset-0 z-50 hidden">
            <div id="convertModalBackdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            <div class="relative max-w-md mx-auto mt-20 px-4">
                <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-slate-200 p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-lg font-bold text-slate-900">Create sale</h3>
                        <button type="button" id="closeConvertModalBtn" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg" aria-label="Close">&times;</button>
                    </div>
                    <p class="text-sm text-slate-600 mt-1">Amount paid and payment method.</p>

                    <div id="convertError" class="hidden mt-3 rounded-xl bg-rose-50 border border-rose-200 px-3 py-2 text-sm text-rose-800"></div>

                    <form id="convertForm" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700">Amount paid</label>
                            <input type="number" name="amount" min="0" step="0.01" value="{{ number_format((float) $estimate->total_amount, 2, '.', '') }}" required class="mt-1 block w-full rounded-xl border-slate-200 px-3 py-2.5 text-sm tabular-nums" />
                        </div>
                        <fieldset>
                            <legend class="text-sm font-semibold text-slate-700">Paid by</legend>
                            <div class="mt-2 space-y-2">
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="cash" checked class="text-emerald-600" />
                                    <span class="text-sm">Cash</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="jazzcash" class="text-emerald-600" />
                                    <span class="text-sm">JazzCash</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="easypaisa" class="text-emerald-600" />
                                    <span class="text-sm">Easypaisa</span>
                                </label>
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer">
                                    <input type="radio" name="payment_method" value="bank_mezzan" class="text-emerald-600" />
                                    <span class="text-sm">Bank Mezzan</span>
                                </label>
                            </div>
                        </fieldset>
                        <p class="text-xs text-slate-500">Set amount to 0 for a sale with no payment recorded.</p>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" id="cancelConvertBtn" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700">Cancel</button>
                            <button type="submit" id="submitConvertBtn" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-sm font-bold">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('convertModal');
                const openBtn = document.getElementById('openConvertEstimateBtn');
                const closeBtn = document.getElementById('closeConvertModalBtn');
                const cancelBtn = document.getElementById('cancelConvertBtn');
                const backdrop = document.getElementById('convertModalBackdrop');
                const form = document.getElementById('convertForm');
                const errEl = document.getElementById('convertError');
                const submitBtn = document.getElementById('submitConvertBtn');

                function open() {
                    modal.classList.remove('hidden');
                    errEl.classList.add('hidden');
                    errEl.textContent = '';
                }
                function close() {
                    modal.classList.add('hidden');
                }

                openBtn?.addEventListener('click', open);
                closeBtn?.addEventListener('click', close);
                cancelBtn?.addEventListener('click', close);
                backdrop?.addEventListener('click', close);

                form?.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    errEl.classList.add('hidden');
                    const fd = new FormData(form);
                    const amt = parseFloat(String(fd.get('amount') || '0'));
                    const body = { amount: amt };
                    if (amt > 0) body.payment_method = fd.get('payment_method');

                    submitBtn.disabled = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const res = await fetch(@json(route('estimates.convert', $estimate)), {
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
                            errEl.textContent = data.message || (data.errors && Object.values(data.errors).flat().join(' ')) || 'Could not create sale.';
                            errEl.classList.remove('hidden');
                            return;
                        }
                        window.location.href = data.redirect_url;
                    } catch (x) {
                        errEl.textContent = 'Network error.';
                        errEl.classList.remove('hidden');
                    } finally {
                        submitBtn.disabled = false;
                    }
                });
            })();
        </script>
    @endif
</x-app-layout>
