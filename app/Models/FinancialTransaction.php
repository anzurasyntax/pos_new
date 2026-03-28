<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    public const TYPE_SALE = 'sale';

    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_EXPENSE = 'expense';

    public const TYPE_PAYMENT_IN = 'payment_in';

    public const TYPE_PAYMENT_OUT = 'payment_out';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const ACCOUNT_TYPE_CASH = 'cash';

    public const ACCOUNT_TYPE_BANK = 'bank';

    public const ACCOUNT_TYPE_CREDIT = 'credit';

    protected $fillable = [
        'type',
        'amount',
        'reference_type',
        'reference_id',
        'account_type',
        'customer_id',
        'supplier_id',
        'user_id',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reference_id' => 'integer',
            'meta' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
