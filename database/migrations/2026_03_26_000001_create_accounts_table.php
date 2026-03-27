<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Expected values (per spec): cash, bank, customer, supplier, expense.
            // In practice you may also store revenue/inventory as string values.
            $table->string('type');
            $table->timestamps();

            $table->index(['type', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};

