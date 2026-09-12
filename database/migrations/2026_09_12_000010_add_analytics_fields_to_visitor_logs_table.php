<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('visitor_logs', 'hits')) {
                $table->unsignedInteger('hits')->default(1)->after('device_type');
            }
            if (!Schema::hasColumn('visitor_logs', 'browser')) {
                $table->string('browser', 50)->nullable()->after('device_type');
            }
            if (!Schema::hasColumn('visitor_logs', 'utm_source')) {
                $table->string('utm_source', 100)->nullable()->after('referrer');
            }
            if (!Schema::hasColumn('visitor_logs', 'utm_medium')) {
                $table->string('utm_medium', 100)->nullable()->after('utm_source');
            }
            if (!Schema::hasColumn('visitor_logs', 'utm_campaign')) {
                $table->string('utm_campaign', 100)->nullable()->after('utm_medium');
            }
            if (!Schema::hasColumn('visitor_logs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('browser');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropColumn(['hits', 'browser', 'utm_source', 'utm_medium', 'utm_campaign', 'user_id']);
        });
    }
};
