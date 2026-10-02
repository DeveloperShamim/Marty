<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Orders are looked up by customer phone (shop history, fraud score, courier history checks),
 * and old audit-log entries are pruned by date.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->index('customer_phone'));
        Schema::table('staff_activity_logs', fn (Blueprint $table) => $table->index('created_at'));
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropIndex(['customer_phone']));
        Schema::table('staff_activity_logs', fn (Blueprint $table) => $table->dropIndex(['created_at']));
    }
};
