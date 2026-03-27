<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Customer
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 gap-4">
                        <x-input
                            label="Name"
                            name="name"
                            type="text"
                            placeholder="Customer name"
                            value="{{ old('name') }}"
                            autofocus
                        />

                        <x-input
                            label="Phone"
                            name="phone"
                            type="text"
                            placeholder="Optional phone"
                            value="{{ old('phone') }}"
                        />

                        <x-input
                            label="Address"
                            name="address"
                            type="text"
                            placeholder="Optional address"
                            value="{{ old('address') }}"
                        />
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 mt-6">
                        <a
                            href="{{ route('customers.index') }}"
                            class="inline-flex items-center justify-center px-5 py-3 rounded-md border border-gray-300 text-gray-700 font-medium hover:bg-gray-50"
                        >
                            Cancel
                        </a>

                        <x-button
                            type="primary"
                            htmlType="submit"
                            text="Save Customer"
                            loadingText="Saving..."
                        />
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

