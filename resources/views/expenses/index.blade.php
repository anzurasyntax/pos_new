<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Expenses
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-lg p-5">
                    <h3 class="text-lg font-semibold text-gray-800">Add Expense</h3>

                    @if ($errors->any())
                        <div class="mt-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-red-800">
                            <div class="font-medium mb-2">Please fix the following:</div>
                            <ul class="list-disc ps-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('expenses.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2"/>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number"
                                   name="amount"
                                   min="0"
                                   step="0.01"
                                   value="{{ old('amount') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2"/>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date"
                                   name="date"
                                   value="{{ old('date') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2"/>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Expense Account</label>
                            <select name="account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
                                <option value="">Select account</option>
                                @foreach ($expenseAccounts as $acc)
                                    <option value="{{ $acc->id }}" @selected((string)old('account_id') === (string)$acc->id)>
                                        {{ $acc->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cash/Bank Account</label>
                            <select name="cash_bank_account_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">
                                <option value="">Select account</option>
                                @foreach ($cashBankAccounts as $acc)
                                    <option value="{{ $acc->id }}" @selected((string)old('cash_bank_account_id') === (string)$acc->id)>
                                        {{ $acc->name }} ({{ $acc->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3" placeholder="Optional note"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm px-3 py-2">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex items-center justify-end pt-2">
                            <button type="submit"
                                    data-loading-text="Saving..."
                                    class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-gray-900 text-white font-medium hover:bg-black">
                                Save Expense
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-5 lg:sticky lg:top-6 h-fit">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Expenses</h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @forelse ($expenses as $expense)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-3 whitespace-nowrap text-gray-800">
                                        {{ optional($expense->date)->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-3 text-gray-900 font-medium">
                                        {{ $expense->title }}
                                    </td>
                                    <td class="px-3 py-3 text-right tabular-nums font-medium text-gray-900">
                                        {{ number_format((float) $expense->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-6 text-gray-600">No expenses yet.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $expenses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

