<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Customer Ledger
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <div class="flex flex-col gap-4">
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700">
                            Select customer
                        </label>
                        <div class="mt-2 flex items-end gap-3 flex-col sm:flex-row">
                            <form method="GET" action="{{ route('customers.index') }}" class="w-full">
                                <select id="customer_id" name="customer_id"
                                    onchange="this.form.submit()"
                                    class="w-full block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2">
                                    <option value="0">Choose...</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            @selected((int)$selectedCustomerId === (int)$customer->id)>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
                        <div class="text-sm text-gray-600">Current Balance</div>
                        @php
                            $isDue = ((float) $currentBalance) > 0;
                        @endphp
                        <div class="text-3xl font-semibold tabular-nums {{ $isDue ? 'text-red-700' : 'text-gray-900' }}">
                            {{ number_format((float) $currentBalance, 2) }}
                        </div>
                        <div class="text-sm {{ $isDue ? 'text-red-600' : 'text-gray-600' }}">
                            {{ $isDue ? 'Due amount (customer owes)' : 'No due amount' }}
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    @if ($selectedCustomerId > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Debit
                                        </th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Credit
                                        </th>
                                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Balance
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($ledgerEntries as $entry)
                                        @php
                                            $desc = 'Ledger Entry';
                                            if ((float)$entry->debit > 0 && (float)$entry->credit <= 0) {
                                                $desc = 'Sale';
                                            } elseif ((float)$entry->credit > 0 && (float)$entry->debit <= 0) {
                                                $desc = 'Payment / Credit';
                                            }
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                                {{ optional($entry->created_at)->format('d M Y') }}
                                            </td>
                                            <td class="px-3 py-3 text-gray-800">
                                                {{ $desc }}
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap text-right tabular-nums">
                                                {{ number_format((float)$entry->debit, 2) }}
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap text-right tabular-nums">
                                                {{ number_format((float)$entry->credit, 2) }}
                                            </td>
                                            <td class="px-3 py-3 whitespace-nowrap text-right tabular-nums {{ ((float)$entry->balance > 0) ? 'text-red-700 font-semibold' : 'text-gray-900' }}">
                                                {{ number_format((float)$entry->balance, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-3 py-6 text-gray-600">
                                                No ledger entries yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-gray-600">
                            Please select a customer to view the ledger.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

