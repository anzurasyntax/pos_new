<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Profit & Loss
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-5">
                <form method="GET" action="{{ route('reports.profit-loss') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <div class="flex items-end justify-end">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-5 py-2 rounded-md bg-gray-900 text-white font-medium hover:bg-black">
                            Apply
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $profit = (float) ($result['profit'] ?? 0);
                    $profitGood = $profit >= 0;
                @endphp

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Total Sales</div>
                    <div class="text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format((float) ($result['total_sales'] ?? 0), 2) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Total Purchases</div>
                    <div class="text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format((float) ($result['total_purchases'] ?? 0), 2) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Total Expenses</div>
                    <div class="text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format((float) ($result['total_expenses'] ?? 0), 2) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4 md:col-span-3">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">Profit</div>
                        <div class="text-3xl font-semibold tabular-nums {{ $profitGood ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format($profit, 2) }}
                        </div>
                    </div>
                    <div class="mt-1 text-sm text-gray-600">
                        Profit = Sales - Purchases - Expenses
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

