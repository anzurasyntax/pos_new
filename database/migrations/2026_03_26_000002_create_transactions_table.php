<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();

            // debit/credit (per spec)
            $table->string('type');
            $table->decimal('amount', 12, 2);

            // Polymorphic-ish reference to the originating business record
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

