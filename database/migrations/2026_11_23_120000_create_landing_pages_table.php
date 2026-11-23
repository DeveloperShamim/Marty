<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Marketing → Landing pages: one-product offer pages at /offer/{slug} with their own order form. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->boolean('is_published')->default(false)->index();
            $table->decimal('regular_price', 12, 2)->nullable();
            $table->decimal('offer_price', 12, 2)->nullable();
            $table->string('discount_text')->nullable();
            $table->string('discount_badge', 40)->nullable();
            $table->string('accent_color', 9)->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 300)->nullable();
            // Section texts, pictures and on/off switches (see App\Models\LandingPage::defaults())
            $table->json('content')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('landing_page_id')->nullable()->after('utm_source')->constrained('landing_pages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('landing_page_id');
        });
        Schema::dropIfExists('landing_pages');
    }
};
