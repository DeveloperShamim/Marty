<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('payment_txn_id')->index();
            }
            if (! Schema::hasColumn('orders', 'fraud_score')) {
                $table->integer('fraud_score')->default(0)->after('ip_address')->index();
            }
            if (! Schema::hasColumn('orders', 'fraud_flags')) {
                $table->json('fraud_flags')->nullable()->after('fraud_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'fraud_score', 'fraud_flags']);
        });
    }
};
