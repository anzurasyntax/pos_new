<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use Illuminate\Validation\ValidationException;

class AccountTransactionService
{
    /**
     * Cashbook line + optional cached balance on cash/bank `accounts` rows.
     *
     * @param  array<string, mixed>|null  $meta
     */
    public function record(
        int $accountId,
        string $direction,
        float $amount,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $meta = null,
    ): AccountTransaction {
        if (! in_array($direction, [AccountTransaction::TYPE_IN, AccountTransaction::TYPE_OUT], true)) {
            throw ValidationException::withMessages([
                'account_transaction' => ['Direction must be in or out.'],
            ]);
        }

        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'account_transaction' => ['Amount must be greater than 0.'],
            ]);
        }

        $account = Account::query()->whereKey($accountId)->lockForUpdate()->firstOrFail();

        $row = AccountTransaction::create([
            'account_id' => $accountId,
            'type' => $direction,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'meta' => $meta,
        ]);

        if (in_array($account->type, ['cash', 'bank'], true)) {
            if ($direction === AccountTransaction::TYPE_IN) {
                $account->increment('balance', $amount);
            } else {
                $account->decrement('balance', $amount);
            }
        }

        return $row;
    }
}
