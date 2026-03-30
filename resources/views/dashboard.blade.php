<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Overview of sales, purchases, and stock at a glance.</p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-soft ring-1 ring-slate-200/60 transition hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">Total Sales</div>
                            <div class="mt-2 text-2xl font-bold tabular-nums text-slate-900 tracking-tight">
                                {{ number_format((float) $totalSales, 2) }}
                            </div>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/></svg>
                        </span>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-soft ring-1 ring-slate-200/60 transition hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">Total Purchases</div>
                            <div class="mt-2 text-2xl font-bold tabular-nums text-slate-900 tracking-tight">
                                {{ number_format((float) $totalPurchases, 2) }}
                            </div>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 ring-1 ring-sky-100">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892a3 3 0 105.88 1.107.999.999 0 00-.01-.002l-1.106-.904a1 1 0 00-.363-.118H8.118l-.071-.285a1 1 0 00-.957-.742H3a1 1 0 000 2h3.645l.166.625 1.195 4.787A2 2 0 007 18h8a2 2 0 001.988-1.789l1.09-5.43A1 1 0 0017 9h-2.351l-.248-.992H3V1z"/><path d="M5 18a2 2 0 100-4 2 2 0 000 4zm8 0a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        </span>
                    </div>
                </div>

                @php
                    $isProfitGood = (float) $profit >= 0;
                @endphp
                <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-soft ring-1 ring-slate-200/60 transition hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">Profit</div>
                            <div class="mt-2 text-2xl font-bold tabular-nums tracking-tight {{ $isProfitGood ? 'text-emerald-700' : 'text-rose-600' }}">
                                {{ number_format((float) $profit, 2) }}
                            </div>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $isProfitGood ? 'bg-emerald-50 text-emerald-600 ring-emerald-100' : 'bg-rose-50 text-rose-600 ring-rose-100' }} ring-1">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
                        </span>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-soft ring-1 ring-slate-200/60 transition hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">Low Stock Items</div>
                            <div class="mt-2 text-2xl font-bold tabular-nums tracking-tight {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                                {{ (int) $lowStockCount }}
                            </div>
                            <div class="mt-2 text-xs text-slate-400 leading-snug">
                                Flagged when stock ≤ {{ (int) $lowStockThreshold }}
                            </div>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        </span>
                    </div>
                </div>
            </div>

            @if ($lowStockCount > 0)
                <div class="rounded-2xl bg-white p-5 sm:p-6 shadow-soft ring-1 ring-slate-200/60">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse" aria-hidden="true"></span>
                        <h2 class="text-base font-semibold text-slate-900">Low stock</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($lowStockItems as $item)
                            <div class="rounded-xl border border-amber-200/60 bg-amber-50/40 p-4 transition hover:border-amber-300/80">
                                <div class="font-semibold text-slate-900">{{ $item->name }}</div>
                                <div class="text-sm text-slate-600 mt-0.5">SKU: {{ $item->sku }}</div>
                                <div class="text-sm mt-2 font-bold text-amber-800 tabular-nums">
                                    Stock: {{ (int) $item->stock_quantity }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                    <h2 class="text-base font-semibold text-slate-900">Recent sales</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th scope="col" class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($recentSales as $sale)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-3.5 whitespace-nowrap text-sm text-slate-700">
                                        {{ optional($sale->created_at)->format('d M Y') }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-sm font-medium text-slate-900">
                                        {{ $sale->customer?->name ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm font-semibold tabular-nums text-slate-900">
                                        {{ number_format((float) $sale->total_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-12 text-center text-sm text-slate-500">
                                        No sales yet. Open <a href="{{ route('sales.index') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">Sales</a> to create one.
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
