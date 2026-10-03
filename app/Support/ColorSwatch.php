<?php

namespace App\Support;

use App\Models\ProductAttributeValue;

/**
 * The dot colour for a colour option: the shade picked in Catalog → Variations,
 * otherwise a built-in shade for common colour names, otherwise none.
 */
class ColorSwatch
{
    public const NAMES = [
        'black' => '#1c1917', 'brown' => '#7c4a21', 'dark brown' => '#4a2c17', 'tan' => '#c08a52', 'coffee' => '#5b3a24',
        'chocolate' => '#4e2a16', 'camel' => '#c19a6b', 'beige' => '#e8d9c0', 'cream' => '#f3ead8', 'white' => '#ffffff',
        'grey' => '#9ca3af', 'gray' => '#9ca3af', 'navy' => '#1e2a4a', 'blue' => '#2563eb', 'red' => '#b91c1c',
        'maroon' => '#6b1d2a', 'burgundy' => '#6d1a2c', 'green' => '#15803d', 'olive' => '#6b6b2a', 'silver' => '#c0c0c0',
        'gold' => '#c9a227', 'rose gold' => '#d4a49a', 'pink' => '#ec4899', 'orange' => '#ea580c', 'yellow' => '#eab308',
        'purple' => '#7e22ce',
    ];

    /** @var array<string, string>|null saved shades by lower-case option name */
    private static ?array $saved = null;

    public static function isColorType(string $type): bool
    {
        $type = strtolower($type);

        return str_contains($type, 'color') || str_contains($type, 'colour');
    }

    public static function for(string $type, string $value): ?string
    {
        if (! self::isColorType($type)) {
            return null;
        }
        $key = strtolower(trim($value));

        return self::saved()[$key] ?? self::NAMES[$key] ?? null;
    }

    /** Built-in shade only (used to pre-fill the admin picker). */
    public static function fallback(string $value): ?string
    {
        return self::NAMES[strtolower(trim($value))] ?? null;
    }

    public static function flush(): void
    {
        self::$saved = null;
    }

    private static function saved(): array
    {
        if (self::$saved === null) {
            self::$saved = ProductAttributeValue::query()
                ->whereNotNull('color_hex')
                ->whereHas('type', fn ($q) => $q->where('name', 'like', '%colo%r%'))
                ->get(['value', 'color_hex'])
                ->mapWithKeys(fn ($v) => [strtolower(trim($v->value)) => $v->color_hex])
                ->all();
        }

        return self::$saved;
    }
}
