<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->decimal('regular_price', 12, 2)->nullable()->after('price_adjustment');
            $table->decimal('sale_price', 12, 2)->nullable()->after('regular_price');
        });
    }

    public function down(): void
    {
        Schema::table('product_skus', function (Blueprint $table) {
            $table->dropColumn(['regular_price', 'sale_price']);
        });
    }
};
