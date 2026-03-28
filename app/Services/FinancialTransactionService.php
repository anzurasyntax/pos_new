<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use Illuminate\Validation\ValidationException;

class FinancialTransactionService
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function record(
        string $type,
        float $amount,
        string $accountType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $customerId = null,
        ?int $supplierId = null,
        ?int $userId = null,
        ?array $meta = null,
    ): FinancialTransaction {
        $amount = round($amount, 2);
        if ($amount < 0) {
            throw ValidationException::withMessages([
                'financial_transaction' => ['Amount cannot be negative.'],
            ]);
        }

        $allowedTypes = [
            FinancialTransaction::TYPE_SALE,
            FinancialTransaction::TYPE_PURCHASE,
            FinancialTransaction::TYPE_EXPENSE,
            FinancialTransaction::TYPE_PAYMENT_IN,
            FinancialTransaction::TYPE_PAYMENT_OUT,
            FinancialTransaction::TYPE_ADJUSTMENT,
        ];

        if (! in_array($type, $allowedTypes, true)) {
            throw ValidationException::withMessages([
                'financial_transaction' => ['Invalid financial transaction type.'],
            ]);
        }

        $allowedAccountTypes = [
            FinancialTransaction::ACCOUNT_TYPE_CASH,
            FinancialTransaction::ACCOUNT_TYPE_BANK,
            FinancialTransaction::ACCOUNT_TYPE_CREDIT,
        ];

        if (! in_array($accountType, $allowedAccountTypes, true)) {
            throw ValidationException::withMessages([
                'financial_transaction' => ['Invalid account type.'],
            ]);
        }

        return FinancialTransaction::create([
            'type' => $type,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'account_type' => $accountType,
            'customer_id' => $customerId,
            'supplier_id' => $supplierId,
            'user_id' => $userId,
            'meta' => $meta,
        ]);
    }
}
