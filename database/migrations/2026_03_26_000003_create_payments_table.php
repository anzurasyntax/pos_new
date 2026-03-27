<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Payable polymorphic fields (spec: payable_type, payable_id)
            $table->string('payable_type');
            $table->unsignedBigInteger('payable_id');

            $table->decimal('amount', 12, 2);

            // e.g. cash/bank/card/upi etc (spec: method)
            $table->string('method');

            // Cash/bank account used for the payment (spec: account_id)
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['payable_type', 'payable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

