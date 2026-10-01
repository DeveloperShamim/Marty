<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // True while this order's items are back in inventory (cancelled / restocked return).
            $table->boolean('stock_restored')->default(false)->after('return_restocked');
        });

        // Existing cancelled orders and restocked returns already had their stock put back.
        DB::table('orders')
            ->where('status', 'cancelled')
            ->orWhere('return_restocked', true)
            ->update(['stock_restored' => true]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('stock_restored');
        });
    }
};
