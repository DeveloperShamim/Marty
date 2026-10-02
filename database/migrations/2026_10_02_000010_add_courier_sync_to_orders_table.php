<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Latest status reported by the courier (API sync or webhook). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier_status_message', 255)->nullable()->after('courier_status');
            $table->timestamp('courier_synced_at')->nullable()->after('courier_status_message');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier_status_message', 'courier_synced_at']);
        });
    }
};
