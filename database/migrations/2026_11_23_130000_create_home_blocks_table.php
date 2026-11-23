<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Homepage sections the owner adds: product rows, promo bands, wide banners, card grids, stats rows.
        // Their place and on/off live in the home_sections setting, like the built-in sections.
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('title')->nullable();
            $table->string('subtitle', 500)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        // A separate picture for phones on each hero slide
        Schema::table('banners', function (Blueprint $table) {
            $table->string('image_mobile')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_blocks');
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('image_mobile');
        });
    }
};
