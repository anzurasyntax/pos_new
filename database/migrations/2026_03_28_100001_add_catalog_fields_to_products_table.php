<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('categories')
                ->nullOnDelete();

            $table->string('unit_type', 32)->nullable()->after('sale_price');
            $table->string('unit_value', 64)->nullable()->after('unit_type');
            $table->string('base_sku_prefix', 100)->nullable()->after('unit_value');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['unit_type', 'unit_value', 'base_sku_prefix']);
        });
    }
};
