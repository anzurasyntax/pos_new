<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Sale #{{ $sale->id }}
                </h2>
                <div class="text-sm text-gray-600">
                    Customer: {{ $sale->customer?->name ?? '-' }}
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('sales.invoice', $sale) }}"
                   class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                    Download Invoice
                </a>

                <button type="button"
                        id="openPaymentModalBtn"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-gray-900 text-white font-medium hover:bg-black">
                    Add Payment
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Grand Total</div>
                    <div class="text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format((float) $sale->total_amount, 2) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Paid Amount</div>
                    <div class="text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format((float) $sale->paid_amount, 2) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Due Amount</div>
                    @php
                        $due = (float) $sale->due_amount;
                        $isDue = $due > 0;
                    @endphp
                    <div class="text-2xl font-semibold tabular-nums {{ $isDue ? 'text-red-700' : 'text-green-700' }}">
                        {{ number_format($due, 2) }}
                    </div>
                    <div class="mt-1 text-sm {{ $isDue ? 'text-red-600' : 'text-green-600' }}">
                        Status: {{ $sale->payment_status }}
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Sale Items</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Product
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Variant
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Qty
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Price
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                        @forelse ($sale->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3">
                                    <div class="font-medium text-gray-900">
                                        {{ $item->product?->name ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        SKU: {{ $item->product?->sku ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($item->variant)
                                        <div class="font-medium text-gray-900">
                                            {{ $item->variant->variant_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            SKU: {{ $item->variant->sku ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums text-gray-900">
                                    {{ (int) $item->quantity }}
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums text-gray-900">
                                    {{ number_format((float) $item->price, 2) }}
                                </td>
                                @php $lineTotal = (int) $item->quantity * (float) $item->price; @endphp
                                <td class="px-3 py-3 text-right tabular-nums font-medium text-gray-900">
                                    {{ number_format($lineTotal, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-gray-600">No items.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Payments</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Method
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Account
                            </th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Notes
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @forelse ($sale->payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                    {{ optional($payment->created_at)->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-gray-800">{{ $payment->method }}</td>
                                <td class="px-3 py-3 text-gray-800">
                                    {{ $payment->account?->name ?? '-' }}
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums font-medium text-gray-900">
                                    {{ number_format((float) $payment->amount, 2) }}
                                </td>
                                <td class="px-3 py-3 text-gray-800">
                                    {{ $payment->notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-gray-600">No payments yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden">
        <div id="paymentModalBackdrop" class="absolute inset-0 bg-gray-900/40"></div>

        <div class="relative max-w-lg mx-auto mt-20">
            <div class="bg-white rounded-lg shadow-lg p-4 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Add Payment</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Due: {{ number_format((float) $sale->due_amount, 2) }}
                        </p>
                    </div>
                    <button type="button" id="closePaymentModalBtn" class="px-2 py-2 text-gray-600 hover:text-gray-900">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="paymentForm" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                        <input type="number"
                               name="amount"
                               min="0"
                               step="0.01"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"/>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Method</label>
                        <input type="text"
                               name="method"
                               required
                               placeholder="cash / bank / UPI / card"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"/>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cash/Bank Account</label>
                        <select name="account_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                                required>
                            <option value="">Select account</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->type }})</option>
                            @endforeach
                        </select>
                        @if ($accounts->isEmpty())
                            <p class="mt-2 text-sm text-red-600">No cash/bank accounts configured in `accounts`.</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes"
                                  rows="3"
                                  placeholder="Optional note"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button"
                                id="cancelPaymentBtn"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>

                        <button type="submit"
                                id="savePaymentBtn"
                                data-loading-text="Saving..."
                                class="inline-flex items-center justify-center px-5 py-2 rounded-md bg-gray-900 text-white font-medium hover:bg-black">
                            Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toastContainer" class="fixed bottom-5 right-5 z-[100] space-y-2"></div>

    <script>
        (function () {
            const openBtn = document.getElementById('openPaymentModalBtn');
            const modal = document.getElementById('paymentModal');
            const backdrop = document.getElementById('paymentModalBackdrop');
            const closeBtn = document.getElementById('closePaymentModalBtn');
            const cancelBtn = document.getElementById('cancelPaymentBtn');
            const form = document.getElementById('paymentForm');
            const saveBtn = document.getElementById('savePaymentBtn');

            const toastContainer = document.getElementById('toastContainer');

            function showToast(message, type) {
                const bg = type === 'error'
                    ? 'bg-red-600'
                    : 'bg-green-600';

                const el = document.createElement('div');
                el.className = `px-4 py-3 rounded-md text-white shadow ${bg}`;
                el.textContent = message;
                toastContainer.appendChild(el);
                setTimeout(() => el.remove(), 3500);
            }

            function setLoading(isLoading) {
                if (!saveBtn) return;
                if (isLoading) {
                    saveBtn.disabled = true;
                    const loadingText = saveBtn.getAttribute('data-loading-text') || 'Saving...';
                    if (!saveBtn.dataset.originalHtml) saveBtn.dataset.originalHtml = saveBtn.innerHTML;
                    saveBtn.innerHTML = loadingText;
                } else {
                    saveBtn.disabled = false;
                    if (saveBtn.dataset.originalHtml) saveBtn.innerHTML = saveBtn.dataset.originalHtml;
                }
            }

            function openModal() {
                modal.classList.remove('hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
            }

            openBtn?.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            cancelBtn?.addEventListener('click', closeModal);
            backdrop?.addEventListener('click', closeModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
            });

            form?.addEventListener('submit', async function (e) {
                e.preventDefault();
                setLoading(true);

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const url = '{{ route('sales.payments.store', $sale) }}';

                    const formData = new FormData(form);
                    const payload = {
                        amount: formData.get('amount'),
                        method: formData.get('method'),
                        account_id: formData.get('account_id'),
                        notes: formData.get('notes'),
                    };

                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success !== true) {
                        showToast(data.message || 'Failed to save payment', 'error');
                        return;
                    }

                    showToast('Payment saved successfully', 'success');
                    closeModal();
                    location.reload();
                } catch (err) {
                    showToast('Failed to save payment', 'error');
                } finally {
                    setLoading(false);
                }
            });
        })();
    </script>
</x-app-layout>

