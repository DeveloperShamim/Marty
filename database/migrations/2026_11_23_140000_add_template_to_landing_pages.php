<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Which look the page uses (LandingPage::TEMPLATES); existing pages keep the original one
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->string('template', 20)->default('classic')->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn('template');
        });
    }
};
