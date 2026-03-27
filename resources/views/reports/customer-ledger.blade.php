<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Customer Ledger Report
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-5">
                <form method="GET" action="{{ route('reports.customer-ledger') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date From</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date To</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Customer</label>
                        <select name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
                            <option value="">All</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" @selected((string)($filters['customer_id'] ?? '') === (string)$customer->id)>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-4 flex items-end justify-end gap-3">
                        <a href="{{ route('reports.customer-ledger') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                            Clear
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2 rounded-md bg-gray-900 text-white font-medium hover:bg-black">
                            Apply
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @forelse ($ledger as $entry)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                    {{ optional($entry->created_at)->format('d M Y') }}
                                </td>
                                <td class="px-3 py-3 tabular-nums text-gray-900">
                                    {{ number_format((float) $entry->debit, 2) }}
                                </td>
                                <td class="px-3 py-3 tabular-nums text-gray-900">
                                    {{ number_format((float) $entry->credit, 2) }}
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums font-semibold {{ (float)$entry->balance > 0 ? 'text-red-700' : 'text-gray-900' }}">
                                    {{ number_format((float) $entry->balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-gray-600">No data found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 flex items-center justify-end">
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Totals</div>
                        <div class="text-sm text-gray-700">
                            Debit: {{ number_format((float) ($totals['total_debit'] ?? 0), 2) }}
                        </div>
                        <div class="text-sm text-gray-700">
                            Credit: {{ number_format((float) ($totals['total_credit'] ?? 0), 2) }}
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $ledger->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

