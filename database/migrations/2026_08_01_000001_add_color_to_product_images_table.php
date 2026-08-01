<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_images') && ! Schema::hasColumn('product_images', 'color')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->string('color')->nullable()->after('alt');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_images') && Schema::hasColumn('product_images', 'color')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }
    }
};
