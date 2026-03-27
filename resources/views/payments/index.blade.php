<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Payments
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Recent Payments</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Party</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                    {{ optional($payment->created_at)->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 text-gray-800">{{ $payableTypes[$payment->id] ?? '-' }}</td>
                                <td class="px-3 py-3 text-gray-800">{{ $payableNames[$payment->id] ?? '-' }}</td>
                                <td class="px-3 py-3 text-gray-800">{{ $payment->method }}</td>
                                <td class="px-3 py-3 text-gray-800">{{ $payment->account?->name ?? '-' }}</td>
                                <td class="px-3 py-3 text-right tabular-nums font-medium text-gray-900">
                                    {{ number_format((float) $payment->amount, 2) }}
                                </td>
                                <td class="px-3 py-3 text-gray-800">{{ $payment->notes ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-gray-600">No payments found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

