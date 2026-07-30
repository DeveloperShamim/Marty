<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('sku')->constrained('brands')->nullOnDelete();
        });

        // Migrate existing text brand values into brands table
        $existingBrands = DB::table('products')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand');

        foreach ($existingBrands as $brandName) {
            $brandNameTrimmed = trim($brandName);
            if (empty($brandNameTrimmed)) {
                continue;
            }

            $slug = Str::slug($brandNameTrimmed);
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('brands')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $brandId = DB::table('brands')->insertGetId([
                'name'       => $brandNameTrimmed,
                'slug'       => $slug,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')
                ->where('brand', $brandName)
                ->update(['brand_id' => $brandId]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
        });
    }
};
