<?php

namespace App\Support;

use App\Models\Order;

/**
 * Order IDs like "VB-482913" (shop) and "VB-P-482913" (POS): the store prefix plus 6 random digits.
 * Digits only, so customers can read them over the phone; random, so they don't reveal sales volume.
 * The prefix is set in Store Settings → Payments → "Order ID prefix".
 */
class OrderNumber
{
    public static function prefix(): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) setting('order_number_prefix', 'VB')));

        return $prefix !== '' ? substr($prefix, 0, 6) : 'VB';
    }

    public static function generate(bool $pos = false): string
    {
        $base = self::prefix() . ($pos ? '-P-' : '-');

        do {
            $number = $base . random_int(100000, 999999);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Order numbers a typed value could mean: "vb-482913" → "VB-482913", "482913" → "VB-482913" or "VB-P-482913".
     *
     * @return list<string>
     */
    public static function candidates(string $input): array
    {
        $clean = strtoupper(trim($input));
        $list = [$clean];
        if (preg_match('/^\d{6}$/', $clean)) {
            $list[] = self::prefix() . '-' . $clean;
            $list[] = self::prefix() . '-P-' . $clean;
        }

        return array_values(array_unique($list));
    }
}
