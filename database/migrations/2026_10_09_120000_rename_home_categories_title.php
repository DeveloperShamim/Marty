<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // The homepage category strip is now headed "Featured Categories". Only the old stock titles are
    // replaced; a title the shop owner typed in Admin → Settings stays as it is.
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'home_categories_title')
            ->whereIn('value', ['Shop by Category', 'Shop by Categories', 'Explore Categories'])
            ->update(['value' => 'Featured Categories']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', 'home_categories_title')
            ->where('value', 'Featured Categories')
            ->update(['value' => 'Shop by Category']);
    }
};
