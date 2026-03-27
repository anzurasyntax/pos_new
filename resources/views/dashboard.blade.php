<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Total Sales</div>
                    <div class="text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format((float) $totalSales, 2) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Total Purchases</div>
                    <div class="text-2xl font-semibold tabular-nums text-gray-900">
                        {{ number_format((float) $totalPurchases, 2) }}
                    </div>
                </div>

                @php
                    $isProfitGood = (float) $profit >= 0;
                @endphp
                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Profit</div>
                    <div class="text-2xl font-semibold tabular-nums {{ $isProfitGood ? 'text-green-700' : 'text-red-700' }}">
                        {{ number_format((float) $profit, 2) }}
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">Low Stock Items</div>
                    <div class="text-2xl font-semibold tabular-nums {{ $lowStockCount > 0 ? 'text-red-700' : 'text-gray-900' }}">
                        {{ (int) $lowStockCount }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Low stock = stock <= {{ (int) $lowStockThreshold }}
                    </div>
                </div>
            </div>

            <!-- Low stock list (simple) -->
            @if ($lowStockCount > 0)
                <div class="mt-5 bg-white shadow-sm rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-800 mb-3">Low Stock Items</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($lowStockItems as $item)
                            <div class="border border-gray-200 rounded-md p-3">
                                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                <div class="text-sm text-gray-600">SKU: {{ $item->sku }}</div>
                                <div class="text-sm mt-1 font-semibold text-red-700 tabular-nums">
                                    Stock: {{ (int) $item->stock_quantity }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent sales -->
            <div class="mt-5 bg-white shadow-sm rounded-lg p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-medium text-gray-800">
                        Recent Sales
                    </div>
                </div>

                <div class="overflow-x-auto mt-3">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Amount
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($recentSales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        {{ optional($sale->created_at)->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        {{ $sale->customer?->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-right tabular-nums font-medium text-gray-900">
                                        {{ number_format((float) $sale->total_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-6 text-gray-600">
                                        No sales yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
