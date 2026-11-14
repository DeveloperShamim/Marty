<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Collection;

/**
 * Launch mode (Admin → Homepage → Launch mode): a small-catalogue homepage for a brand that starts with a
 * handful of products. While it is on, the homepage shows one hero banner, the "Batch" collection (1 to 8
 * products picked in the admin), a material section, a comparison table and a trust row, and leaves out the
 * shop sections that would look empty (categories, flash sale, best sellers, category rows and so on).
 * Switching it off brings the normal homepage back exactly as it was.
 *
 * Everything is kept in two rows of the existing settings table, so no table changes:
 *   launch_mode  "1" or "0"
 *   launch_page  JSON with the texts, the picked product ids and the comparison values
 */
class LaunchMode
{
    public const MAX_PRODUCTS = 8;
    public const MAX_COMPARE_ROWS = 6;

    public static function isOn(): bool
    {
        return setting('launch_mode', '0') === '1';
    }

    /** The dark leather look applies while launch mode is on and the look is left switched on. */
    public static function isDark(): bool
    {
        return self::isOn() && (bool) (self::config()['dark'] ?? true);
    }

    public static function defaults(): array
    {
        return [
            'dark' => true,
            'collection' => [
                'label' => 'Batch 01 — Limited Release',
                'heading' => 'The Initial Collection',
                'intro' => 'Our first run of full-grain leather wallets, made in small numbers. Pick your colour and order with cash on delivery.',
            ],
            'product_ids' => [],
            'material' => [
                'on' => true,
                'label' => 'Material Architecture',
                'heading' => 'Built to outlast trends. Engineered for BDT currency.',
                'pillars' => [
                    ['title' => 'Full-grain cowhide', 'text' => 'Cut from the strongest top layer of the hide, never split or coated in plastic. It handles humidity and gets richer with use.'],
                    ['title' => 'Waxed saddle stitching', 'text' => 'Strong waxed thread, locked stitch by stitch. If one loop wears through, the rest still hold.'],
                    ['title' => 'Burnished edges', 'text' => 'Edges are bevelled, sanded smooth and sealed with beeswax so they stay clean and don\'t fray.'],
                ],
            ],
            'compare' => [
                'on' => true,
                'label' => 'Specification Matrix',
                'heading' => 'Find Your Carry Profile',
                'rows' => [
                    ['label' => 'Card capacity', 'values' => []],
                    ['label' => 'BDT note fit', 'values' => []],
                    ['label' => 'Dimensions', 'values' => []],
                    ['label' => 'Ideal carry', 'values' => []],
                ],
            ],
            'trust' => [
                'on' => true,
                'items' => [
                    ['icon' => 'cash', 'title' => 'Nationwide Cash on Delivery', 'text' => 'Check your wallet at your door in all 64 districts before you pay.'],
                    ['icon' => 'bolt', 'title' => 'Inside Dhaka 24h Express', 'text' => 'Same-day or next-day delivery in Dhaka. 48 to 72 hours across the country.'],
                    ['icon' => 'shield', 'title' => '2-Year Stitching & Hide Warranty', 'text' => '100% genuine full-grain cowhide. If the stitching or leather fails, we fix or replace it.'],
                ],
            ],
            'show_reviews' => true,
        ];
    }

    /** Saved settings laid over the defaults, so a missing key always has a sensible value. */
    public static function config(): array
    {
        $saved = json_decode((string) setting('launch_page', ''), true);

        return self::merge(self::defaults(), is_array($saved) ? $saved : []);
    }

    /** The picked products, published only, in the order they were picked. */
    public static function products(): Collection
    {
        $ids = collect(self::config()['product_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->take(self::MAX_PRODUCTS)->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $found = Product::query()->published()->whereIn('id', $ids)
            ->with('images', 'category', 'brand', 'variants', 'skus')->get()->keyBy('id');

        return $ids->map(fn ($id) => $found->get($id))->filter()->values();
    }

    /**
     * The data-* attributes the storefront's add-to-cart script reads (the same set the normal product card
     * prints), so Order now / Add to bag here open the colour picker or add straight away like everywhere else.
     */
    public static function cartData(Product $product): array
    {
        $grouped = $product->variants ? $product->variants->groupBy('type')->map(fn ($items) => $items->pluck('value')->unique()->values()) : collect();
        if ($grouped->isEmpty() && $product->skus && $product->skus->isNotEmpty()) {
            $bySku = [];
            foreach ($product->skus as $sku) {
                foreach ($sku->getAttributesData() as $type => $val) {
                    $bySku[$type][] = $val;
                }
            }
            $grouped = collect($bySku)->map(fn ($vals) => collect($vals)->unique()->values());
        }

        return [
            'product-id' => $product->id, 'title' => $product->name, 'stock' => $product->stock_quantity,
            'price' => money($product->price), 'raw-price' => (float) $product->price,
            'regular-price' => $product->on_sale ? money($product->regular_price) : '',
            'raw-regular-price' => $product->on_sale && $product->regular_price ? (float) $product->regular_price : '',
            'discount' => $product->discount_percent, 'image' => $product->imageUrl(), 'url' => route('product.show', $product),
            'has-variants' => $grouped->isNotEmpty() ? 'true' : 'false', 'variants' => json_encode($grouped),
            'skus' => json_encode($product->skus ? $product->skus->map(fn ($s) => ['id' => $s->id, 'attributes' => $s->getAttributesData(), 'stock' => (int) $s->stock_quantity, 'price_adjustment' => (float) $s->price_adjustment, 'regular_price' => $s->getCalculatedRegularPrice(), 'sale_price' => $s->getCalculatedSalePrice()])->values() : []),
        ];
    }

    public static function save(array $config, bool $on): void
    {
        Setting::put('launch_page', json_encode(self::merge(self::defaults(), $config), JSON_UNESCAPED_UNICODE));
        Setting::put('launch_mode', $on ? '1' : '0');
    }

    /** Lists (product ids, pillars, rows, trust items) are replaced whole; keyed arrays are merged key by key. */
    private static function merge(array $base, array $over): array
    {
        foreach ($over as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key]) && ! array_is_list($base[$key]) && ! array_is_list($value)) {
                $base[$key] = self::merge($base[$key], $value);
            } elseif (array_key_exists($key, $base) || ! is_int($key)) {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}
