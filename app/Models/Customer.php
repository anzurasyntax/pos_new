<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(CustomerLedger::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(CustomerProductPrice::class);
    }
}
