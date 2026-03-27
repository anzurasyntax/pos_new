<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Customers
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    Manage customer details for sales and ledger.
                </p>
            </div>

            <x-button
                type="primary"
                htmlType="button"
                text="Add Customer"
                class="px-5 py-3"
                onclick="window.location='{{ route('customers.create') }}'"
            />
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <form method="GET" action="{{ route('customers.index') }}" class="mb-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <x-input
                                label="Search"
                                name="q"
                                type="text"
                                placeholder="Name, phone, or address"
                                value="{{ $q }}"
                            />
                        </div>

                        <x-button
                            type="secondary"
                            htmlType="submit"
                            text="Search"
                            class="px-5 py-2.5"
                        />
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Phone
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Address
                                </th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($customers as $customer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        {{ $customer->name }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        {{ $customer->phone ?: '-' }}
                                    </td>
                                    <td class="px-3 py-3 text-gray-800">
                                        {{ $customer->address ?: '-' }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('customers.edit', $customer) }}"
                                                class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 font-medium hover:bg-gray-50">
                                                Edit
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('customers.destroy', $customer) }}"
                                                onsubmit="return confirm('Delete this customer?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <x-button
                                                    type="danger"
                                                    htmlType="submit"
                                                    text="Delete"
                                                    class="px-3 py-2"
                                                />
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-gray-600">
                                        No customers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

