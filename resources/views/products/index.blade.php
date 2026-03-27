<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Products
            </h2>

            <a href="{{ route('products.create') }}"
                class="inline-flex items-center justify-center px-5 py-3 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                <svg class="w-5 h-5 me-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Add Product
            </a>
        </div>
    </x-slot>

    <div class="py-6">
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
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        {{ $product->name }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
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
                                    <td class="px-3 py-6 text-gray-600" colspan="5">
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
    </div>
</x-app-layout>

