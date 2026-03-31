<?php

namespace App\Services;

use App\Models\Account;

class SalePaymentAccountResolver
{
    /**
     * @return array{id:int,label:string}
     */
    public function resolve(string $key): array
    {
        $key = strtolower(trim($key));

        return match ($key) {
            'cash' => $this->cash(),
            'jazzcash' => $this->bankWallet('JazzCash'),
            'easypaisa' => $this->bankWallet('Easypaisa'),
            'bank_mezzan' => $this->bankWallet('Bank Mezzan'),
            default => throw new \InvalidArgumentException('Invalid payment method.'),
        };
    }

    /**
     * @return array{id:int,label:string}
     */
    private function cash(): array
    {
        $acc = Account::query()->firstOrCreate([
            'type' => 'cash',
            'name' => 'Cash',
        ]);

        return ['id' => (int) $acc->id, 'label' => 'Cash'];
    }

    /**
     * @return array{id:int,label:string}
     */
    private function bankWallet(string $name): array
    {
        $acc = Account::query()->firstOrCreate([
            'type' => 'bank',
            'name' => $name,
        ]);

        return ['id' => (int) $acc->id, 'label' => $name];
    }
}
