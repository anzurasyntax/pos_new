<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Inventory
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-5 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 leading-relaxed">
                <span class="font-semibold text-slate-900">How stock is updated:</span>
                stock <span class="font-medium text-emerald-800">increases</span> when you save a <a href="{{ route('purchase.index') }}" class="text-emerald-700 font-semibold hover:underline">purchase</a>
                and <span class="font-medium text-rose-800">decreases</span> when you save a <a href="{{ route('sales.index') }}" class="text-emerald-700 font-semibold hover:underline">sale</a>.
                Manual changes on the <a href="{{ route('products.index') }}" class="text-emerald-700 font-semibold hover:underline">product</a> form are logged as adjustments.
            </div>

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <!-- Search -->
                <form method="GET" action="{{ route('inventory.index') }}" class="mb-5">
                    <label for="q" class="block text-sm font-medium text-gray-700">
                        Search product
                    </label>
                    <div class="mt-2 flex flex-col sm:flex-row gap-3 sm:items-end">
                        <input id="q" name="q" type="text" value="{{ $q }}"
                            placeholder="Type name or SKU..."
                            class="flex-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"/>
                        @if (filled($q))
                            <a href="{{ route('inventory.index') }}"
                                class="inline-flex items-center justify-center px-5 py-2 rounded-md border border-gray-300 text-gray-700 font-medium hover:bg-gray-50">
                                Clear
                            </a>
                        @endif
                        <button type="submit"
                            class="inline-flex items-center justify-center px-5 py-2 rounded-md bg-gray-900 text-white font-medium hover:bg-black">
                            Search
                        </button>
                    </div>
                </form>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SKU
                                </th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock
                                </th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($products as $product)
                                @php
                                    $qty = (int) $product->stock_quantity;
                                    $isLow = $qty <= (int) ($lowStockThreshold ?? 5);
                                @endphp
                                <tr class="{{ $isLow ? 'bg-red-50' : '' }}">
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">
                                            {{ $product->name }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-700">
                                        {{ $product->sku }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-right tabular-nums text-gray-900">
                                        {{ $qty }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        @if ($isLow)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                Low stock
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                Good stock
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-3 py-6 text-gray-600" colspan="4">
                                        No products found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
