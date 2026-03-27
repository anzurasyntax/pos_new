<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reports
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $role = Auth::user()?->role ?? 'sales_user';
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('reports.sales') }}"
                   class="bg-white shadow-sm rounded-lg p-5 hover:bg-gray-50 border border-gray-100">
                    <div class="text-gray-900 font-semibold text-lg">Sales Report</div>
                    <div class="text-sm text-gray-600 mt-1">Filter by date range, customer, product.</div>
                </a>

                @if (in_array($role, ['manager', 'super_admin'], true))
                    <a href="{{ route('reports.purchases') }}"
                       class="bg-white shadow-sm rounded-lg p-5 hover:bg-gray-50 border border-gray-100">
                        <div class="text-gray-900 font-semibold text-lg">Purchase Report</div>
                        <div class="text-sm text-gray-600 mt-1">Filter by date range, supplier, product.</div>
                    </a>

                    <a href="{{ route('reports.profit-loss') }}"
                       class="bg-white shadow-sm rounded-lg p-5 hover:bg-gray-50 border border-gray-100">
                        <div class="text-gray-900 font-semibold text-lg">Profit & Loss</div>
                        <div class="text-sm text-gray-600 mt-1">Profit = Sales - Purchases - Expenses.</div>
                    </a>

                    <a href="{{ route('reports.expenses') }}"
                       class="bg-white shadow-sm rounded-lg p-5 hover:bg-gray-50 border border-gray-100">
                        <div class="text-gray-900 font-semibold text-lg">Expense Report</div>
                        <div class="text-sm text-gray-600 mt-1">Filter by date range and expense account.</div>
                    </a>
                @endif

                <a href="{{ route('reports.customer-ledger') }}"
                   class="bg-white shadow-sm rounded-lg p-5 hover:bg-gray-50 border border-gray-100 md:col-span-2">
                    <div class="text-gray-900 font-semibold text-lg">Customer Ledger Report</div>
                    <div class="text-sm text-gray-600 mt-1">Filter by date range and customer.</div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

