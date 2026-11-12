<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Which homepage sections show and in what order (Admin → Homepage → Sections).
 * Saved as JSON in the `home_sections` setting: [{"key": "categories", "on": true}, …].
 * The hero always stays first and the trust strip under it (they can only be hidden, not moved);
 * the rest follow in the saved order. Each section still hides itself when it has nothing to show.
 */
class HomeSections
{
    /** key => [name, what it shows] in the default order */
    public const SECTIONS = [
        'trust'         => ['Trust strip', 'Icons under the banner, e.g. cash on delivery, fast delivery'],
        'categories'    => ['Categories', 'Category slider with pictures'],
        'deals'         => ['Flash deals / Top selling', 'Flash sale with countdown while one runs, otherwise top-selling products'],
        'curated'       => ['Best sellers & New arrivals', 'Two tabs of products'],
        'brands'        => ['Brands', 'Brand logos (shows when you have more than one brand)'],
        'category_rows' => ['Category rows', 'One row of products for each category marked “Featured on home”'],
        'reviews'       => ['Customer reviews', 'Approved product reviews'],
        'just_for_you'  => ['Just for you', 'More products picked for each visitor'],
        'coupons'       => ['Coupons', 'Active coupon codes customers can copy'],
    ];

    /** Sections that keep their place at the top; they can be hidden but not reordered. */
    public const FIXED = ['trust'];

    /**
     * The saved list, completed with any section the saved list does not know yet (new sections
     * appear on, at the end), in display order.
     *
     * @return list<array{key: string, on: bool}>
     */
    public static function all(): array
    {
        $saved = json_decode((string) setting('home_sections', ''), true);
        $list = [];

        if (is_array($saved)) {
            foreach ($saved as $row) {
                $key = is_array($row) ? ($row['key'] ?? null) : null;
                if (is_string($key) && isset(self::SECTIONS[$key]) && ! isset($list[$key])) {
                    $list[$key] = ['key' => $key, 'on' => (bool) ($row['on'] ?? true)];
                }
            }
        }

        foreach (array_keys(self::SECTIONS) as $key) {
            if (! isset($list[$key])) {
                // Before this page existed, brands and reviews had their own on/off switches
                $on = match ($key) {
                    'brands' => setting('show_featured_brands', '1') === '1',
                    'reviews' => setting('show_home_reviews', '1') !== '0',
                    default => true,
                };
                $list[$key] = ['key' => $key, 'on' => $on];
            }
        }

        // Fixed sections first, in their own order
        $fixed = array_values(array_filter($list, fn ($r) => in_array($r['key'], self::FIXED, true)));
        $rest = array_values(array_filter($list, fn ($r) => ! in_array($r['key'], self::FIXED, true)));

        return [...$fixed, ...$rest];
    }

    public static function isOn(string $key): bool
    {
        foreach (self::all() as $row) {
            if ($row['key'] === $key) {
                return $row['on'];
            }
        }

        return false;
    }

    /** Keys of the movable sections that are switched on, in display order. @return list<string> */
    public static function ordered(): array
    {
        return array_values(array_map(
            fn ($r) => $r['key'],
            array_filter(self::all(), fn ($r) => $r['on'] && ! in_array($r['key'], self::FIXED, true)),
        ));
    }

    /**
     * Save a new order and on/off state. Unknown keys are dropped; missing ones are added (on) at the end.
     *
     * @param  list<string>  $order  section keys in display order
     * @param  list<string>  $on  keys that are switched on
     */
    public static function save(array $order, array $on): void
    {
        $rows = [];
        foreach ($order as $key) {
            if (is_string($key) && isset(self::SECTIONS[$key]) && ! isset($rows[$key])) {
                $rows[$key] = ['key' => $key, 'on' => in_array($key, $on, true)];
            }
        }
        foreach (array_keys(self::SECTIONS) as $key) {
            $rows[$key] ??= ['key' => $key, 'on' => true];
        }

        Setting::put('home_sections', json_encode(array_values($rows)));
        // Keep the old switches in step for anything that still reads them
        Setting::put('show_featured_brands', $rows['brands']['on'] ? '1' : '0');
        Setting::put('show_home_reviews', $rows['reviews']['on'] ? '1' : '0');
    }
}
