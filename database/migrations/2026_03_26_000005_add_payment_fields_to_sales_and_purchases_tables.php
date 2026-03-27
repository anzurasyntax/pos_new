<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid');
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid');
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
        });

        // Backfill existing data: unpaid, paid_amount = 0, due_amount = total_amount.
        DB::table('sales')->update([
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
            'due_amount' => DB::raw('COALESCE(total_amount, 0)'),
        ]);

        DB::table('purchases')->update([
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
            'due_amount' => DB::raw('COALESCE(total_amount, 0)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_amount', 'due_amount']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_amount', 'due_amount']);
        });
    }
};

