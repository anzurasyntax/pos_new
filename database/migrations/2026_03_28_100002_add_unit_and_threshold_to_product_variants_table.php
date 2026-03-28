<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('unit_type', 32)->nullable()->after('variant_name');
            $table->string('unit_value', 64)->nullable()->after('unit_type');
            $table->unsignedInteger('low_stock_threshold')->nullable()->after('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['unit_type', 'unit_value', 'low_stock_threshold']);
        });
    }
};
