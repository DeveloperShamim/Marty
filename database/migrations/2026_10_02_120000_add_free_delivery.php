<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('free_delivery')->default(false)->after('is_flash_sale');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Delivery fee the shop pays the courier itself because the customer got free delivery.
            $table->decimal('shipping_waived', 10, 2)->default(0)->after('shipping_charge');
            $table->string('free_delivery_reason', 20)->nullable()->after('shipping_waived');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_waived', 'free_delivery_reason']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('free_delivery');
        });
    }
};
