<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_name')->nullable()->after('status');
            $table->string('courier_tracking_code')->nullable()->after('courier_name');
            $table->string('courier_status')->nullable()->after('courier_tracking_code');
            $table->timestamp('courier_sent_at')->nullable()->after('courier_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier_name', 'courier_tracking_code', 'courier_status', 'courier_sent_at']);
        });
    }
};
