<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddExpenseRequest;
use App\Models\Account;
use App\Models\Expense;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService
    ) {
    }

    public function index(): View
    {
        $expenseAccounts = Account::query()
            ->where('type', 'expense')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $cashBankAccounts = Account::query()
            ->whereIn('type', ['cash', 'bank'])
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        $expenses = Expense::query()
            ->with(['account', 'cashBankAccount'])
            ->orderByDesc('date')
            ->paginate(15);

        return view('expenses.index', [
            'expenseAccounts' => $expenseAccounts,
            'cashBankAccounts' => $cashBankAccounts,
            'expenses' => $expenses,
        ]);
    }

    public function store(AddExpenseRequest $request): RedirectResponse
    {
        $this->expenseService->addExpense(
            $request->input('title'),
            (float) $request->input('amount'),
            (int) $request->input('account_id'),
            (string) $request->input('date'),
            $request->input('notes'),
            (int) $request->input('cash_bank_account_id')
        );

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense saved successfully.');
    }
}

