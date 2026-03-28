<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Expense;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        private readonly FinancialTransactionService $financialTransactionService,
        private readonly AccountTransactionService $accountTransactionService,
    ) {}

    public function addExpense(
        string $title,
        float $amount,
        int $expenseAccountId,
        string $date,
        ?string $notes,
        int $cashBankAccountId
    ): Expense {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Amount must be greater than 0.']]);
        }

        return DB::transaction(function () use (
            $title,
            $amount,
            $expenseAccountId,
            $date,
            $notes,
            $cashBankAccountId
        ) {
            $expenseAccount = Account::query()->where('id', $expenseAccountId)->first();
            $cashAccount = Account::query()->where('id', $cashBankAccountId)->first();

            if (! $expenseAccount || $expenseAccount->type !== 'expense') {
                throw ValidationException::withMessages(['account_id' => ['Invalid expense account.']]);
            }

            if (! $cashAccount || ! in_array($cashAccount->type, ['cash', 'bank'], true)) {
                throw ValidationException::withMessages(['cash_bank_account_id' => ['Invalid cash/bank account.']]);
            }

            $expense = Expense::create([
                'title' => $title,
                'amount' => $amount,
                'account_id' => $expenseAccountId,
                'cash_bank_account_id' => $cashBankAccountId,
                'date' => $date,
                'notes' => $notes,
            ]);

            // Double-entry-like accounting transactions:
            // Expense -> Debit expense account, Credit cash/bank
            DB::table('transactions')->insert([
                [
                    'account_id' => $expenseAccount->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'reference_type' => 'expense',
                    'reference_id' => $expense->id,
                    'description' => 'Expense debit (Expense Account)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'account_id' => $cashAccount->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'reference_type' => 'expense',
                    'reference_id' => $expense->id,
                    'description' => 'Expense credit (Cash/Bank)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $ftAccountType = $cashAccount->type === 'bank'
                ? FinancialTransaction::ACCOUNT_TYPE_BANK
                : FinancialTransaction::ACCOUNT_TYPE_CASH;

            $this->financialTransactionService->record(
                FinancialTransaction::TYPE_EXPENSE,
                $amount,
                $ftAccountType,
                'expense',
                (int) $expense->id,
                null,
                null,
                Auth::id(),
            );

            $this->accountTransactionService->record(
                $cashBankAccountId,
                AccountTransaction::TYPE_OUT,
                $amount,
                'expense',
                (int) $expense->id,
            );

            return $expense;
        });
    }
}
