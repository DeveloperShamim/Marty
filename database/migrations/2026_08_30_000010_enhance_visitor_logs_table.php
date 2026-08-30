<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('visitor_logs', 'page_url')) {
                $table->string('page_url', 255)->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('visitor_logs', 'referrer')) {
                $table->string('referrer', 255)->nullable()->after('page_url');
            }
            if (!Schema::hasColumn('visitor_logs', 'device_type')) {
                $table->string('device_type', 20)->default('desktop')->after('referrer');
            }

            // High performance indexes
            $table->index('visit_date');
            $table->index('updated_at');
            $table->index('device_type');
            $table->index('page_url');
        });
    }

    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            $table->dropIndex(['visit_date']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['device_type']);
            $table->dropIndex(['page_url']);

            $table->dropColumn(['page_url', 'referrer', 'device_type']);
        });
    }
};
