<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // The original Vant wordmark and V mark ship in public/uploads/brand. Stores that never uploaded
    // their own logo or favicon get them; anything set in Admin → Store Settings stays as it is.
    private const BRAND = [
        'logo' => 'uploads/brand/vant-logo.png',
        'favicon' => 'uploads/brand/vant-mark.png',
    ];

    public function up(): void
    {
        foreach (self::BRAND as $key => $path) {
            $row = DB::table('settings')->where('key', $key)->first();

            if (! $row) {
                DB::table('settings')->insert(['key' => $key, 'value' => $path, 'created_at' => now(), 'updated_at' => now()]);
            } elseif (trim((string) $row->value) === '') {
                DB::table('settings')->where('key', $key)->update(['value' => $path, 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        foreach (self::BRAND as $key => $path) {
            DB::table('settings')->where('key', $key)->where('value', $path)->update(['value' => '', 'updated_at' => now()]);
        }
    }
};
