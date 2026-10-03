<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_attribute_values', function (Blueprint $table) {
            // Exact shade for colour options (e.g. #7c4a21), shown as the dot on the product page.
            $table->string('color_hex', 7)->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('product_attribute_values', function (Blueprint $table) {
            $table->dropColumn('color_hex');
        });
    }
};
