<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Record a payment against a sale (customer payment).
     *
     * Accounting logic (per spec):
     * - Payment -> Credit customer account
     * - Payment -> Debit cash/bank account
     */
    public function addSalePayment(Sale $sale, float $amount, string $method, int $cashBankAccountId, ?string $notes = null): Payment
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Payment amount must be greater than 0.']]);
        }

        return DB::transaction(function () use ($sale, $amount, $method, $cashBankAccountId, $notes) {
            $sale = Sale::query()->where('id', $sale->id)->lockForUpdate()->firstOrFail();

            $due = (float) $sale->due_amount;
            if ($due <= 0) {
                throw ValidationException::withMessages(['payment_status' => ['This sale has no due amount.']]);
            }
            if ($amount > $due) {
                throw ValidationException::withMessages(['amount' => ['Payment cannot exceed due amount.']]);
            }

            $newPaid = (float) $sale->paid_amount + $amount;
            $newDue = max(0.0, (float) $sale->total_amount - $newPaid);

            $status = 'unpaid';
            if ($newDue <= 0.00001) {
                $status = 'paid';
            } elseif ($newPaid > 0) {
                $status = 'partial';
            }

            $payment = $sale->payments()->create([
                'amount' => $amount,
                'method' => $method,
                'account_id' => $cashBankAccountId,
                'notes' => $notes,
            ]);

            // Accounting transactions (2 lines).
            $cashAccount = Account::query()->findOrFail($cashBankAccountId);
            $customerAccount = $this->getOrCreateCustomerAccount((int) $sale->customer_id);

            DB::table('transactions')->insert([
                [
                    'account_id' => $cashAccount->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'description' => 'Sale payment debit (Cash/Bank)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'account_id' => $customerAccount->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'description' => 'Sale payment credit (Customer AR)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            // Update customer ledger: credit for payment (reduces balance).
            $prevBalance = (float) CustomerLedger::query()
                ->where('customer_id', (int) $sale->customer_id)
                ->orderByDesc('id')
                ->value('balance');

            if (! is_finite($prevBalance)) {
                $prevBalance = 0.0;
            }

            CustomerLedger::create([
                'customer_id' => (int) $sale->customer_id,
                'debit' => 0,
                'credit' => $amount,
                'balance' => $prevBalance - $amount,
            ]);

            // Update payment fields on the sale.
            $sale->update([
                'payment_status' => $status,
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
            ]);

            return $payment;
        });
    }

    /**
     * Record a payment against a purchase (supplier payment).
     *
     * Accounting (standard matching double-entry):
     * - Debit supplier account (reduces AP)
     * - Credit cash/bank account
     */
    public function addPurchasePayment(Purchase $purchase, float $amount, string $method, int $cashBankAccountId, ?string $notes = null): Payment
    {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['Payment amount must be greater than 0.']]);
        }

        return DB::transaction(function () use ($purchase, $amount, $method, $cashBankAccountId, $notes) {
            $purchase = Purchase::query()->where('id', $purchase->id)->lockForUpdate()->firstOrFail();

            $due = (float) $purchase->due_amount;
            if ($due <= 0) {
                throw ValidationException::withMessages(['payment_status' => ['This purchase has no due amount.']]);
            }
            if ($amount > $due) {
                throw ValidationException::withMessages(['amount' => ['Payment cannot exceed due amount.']]);
            }

            $newPaid = (float) $purchase->paid_amount + $amount;
            $newDue = max(0.0, (float) $purchase->total_amount - $newPaid);

            $status = 'unpaid';
            if ($newDue <= 0.00001) {
                $status = 'paid';
            } elseif ($newPaid > 0) {
                $status = 'partial';
            }

            $payment = $purchase->payments()->create([
                'amount' => $amount,
                'method' => $method,
                'account_id' => $cashBankAccountId,
                'notes' => $notes,
            ]);

            $cashAccount = Account::query()->findOrFail($cashBankAccountId);
            $supplierAccount = $this->getOrCreateSupplierAccount((int) $purchase->supplier_id);

            // 2 lines: debit supplier, credit cash/bank
            DB::table('transactions')->insert([
                [
                    'account_id' => $supplierAccount->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'description' => 'Purchase payment debit (Supplier AP)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'account_id' => $cashAccount->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'reference_type' => 'payment',
                    'reference_id' => $payment->id,
                    'description' => 'Purchase payment credit (Cash/Bank)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $purchase->update([
                'payment_status' => $status,
                'paid_amount' => $newPaid,
                'due_amount' => $newDue,
            ]);

            return $payment;
        });
    }

    private function getOrCreateCustomerAccount(int $customerId): Account
    {
        $name = 'Customer AR #'.$customerId;

        return Account::query()->firstOrCreate([
            'type' => 'customer',
            'name' => $name,
        ]);
    }

    private function getOrCreateSupplierAccount(int $supplierId): Account
    {
        $name = 'Supplier AP #'.$supplierId;

        return Account::query()->firstOrCreate([
            'type' => 'supplier',
            'name' => $name,
        ]);
    }
}

