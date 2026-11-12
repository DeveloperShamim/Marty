<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // The product page used to print these lines on every product. They are now set in
    // Admin → Store Settings → Delivery. A store that is already running keeps the text it shows
    // today; a new store starts blank and adds only what is true for it.
    private const TEXT = [
        'pdp_authentic_label' => 'Verified Authentic',
        'pdp_quality_badge' => 'Genuine leather',
        'pdp_return_text' => '7-day return',
    ];

    public function up(): void
    {
        if (! DB::table('settings')->exists()) {
            return;
        }

        foreach (self::TEXT as $key => $value) {
            if (! DB::table('settings')->where('key', $key)->exists()) {
                DB::table('settings')->insert(['key' => $key, 'value' => $value, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', array_keys(self::TEXT))->delete();
    }
};
