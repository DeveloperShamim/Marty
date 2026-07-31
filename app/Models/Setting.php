<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $guarded = [];

    /** Request-scoped cache of key => value pairs. */
    protected static ?array $bag = null;

    protected static function loadBag(): array
    {
        if (static::$bag === null) {
            // Guard against calls before the table exists (e.g. during migrate).
            if (! Schema::hasTable('settings')) {
                return static::$bag = [];
            }
            static::$bag = static::query()->pluck('value', 'key')->all();
        }

        return static::$bag;
    }

    public static function get(string $key, $default = null)
    {
        $val = static::loadBag()[$key] ?? null;
        if ($val !== null && $val !== '') {
            return $val;
        }

        $envVal = env(strtoupper($key));

        return ($envVal !== null && $envVal !== '') ? $envVal : $default;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::forgetCache();
    }

    public static function forgetCache(): void
    {
        static::$bag = null;
    }
}
