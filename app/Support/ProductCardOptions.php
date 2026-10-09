<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * What a product card says about a product's variations: colour dots, a short size line
 * ("Sizes 39–44" or "6 sizes"), and a "From" price when the variations cost different amounts.
 * Choosing the actual option happens in the quick picker, never on the card.
 */
class ProductCardOptions
{
    public const MAX_DOTS = 4;

    /** Common colour names mapped to a swatch; anything else gets a neutral dot with its name as a tooltip. */
    private const SWATCHES = [
        'black' => '#1c1917', 'brown' => '#7b4a2a', 'dark brown' => '#4a2c1a', 'coffee' => '#4a2c1a', 'chocolate' => '#4a2c1a',
        'tan' => '#c48a55', 'camel' => '#c19a6b', 'beige' => '#e3d3b8', 'cream' => '#f3ead8', 'white' => '#ffffff',
        'silver' => '#c4c7cb', 'grey' => '#8a8d91', 'gray' => '#8a8d91', 'gold' => '#c9a54a', 'rose gold' => '#d4a08a',
        'navy' => '#1f2f57', 'blue' => '#2f5fb3', 'red' => '#b3282d', 'maroon' => '#6b1d24', 'burgundy' => '#6b1d24',
        'green' => '#2f6b3f', 'olive' => '#6b6b2f', 'pink' => '#e59bb0', 'orange' => '#e07a2c', 'yellow' => '#e8c547',
        'purple' => '#6a3f8f',
    ];

    /**
     * @return array{colors: array<int, array{name: string, swatch: ?string}>, moreColors: int, sizeLabel: ?string, sizeShort: ?string, otherLabel: ?string, fromPrice: ?float}
     */
    public static function for(Product $product): array
    {
        $groups = self::groups($product);

        $colorKey = $groups->keys()->first(fn ($type) => preg_match('/colou?r/i', $type));
        $sizeKey = $groups->keys()->first(fn ($type) => preg_match('/size/i', $type));

        $colors = $colorKey ? $groups[$colorKey] : collect();
        $sizes = $sizeKey ? $groups[$sizeKey] : collect();

        $otherKey = $groups->keys()->first(fn ($type) => $type !== $colorKey && $type !== $sizeKey);
        $other = $otherKey ? $groups[$otherKey] : collect();

        return [
            'colors'     => $colors->take(self::MAX_DOTS)
                ->map(fn ($name) => ['name' => $name, 'swatch' => self::SWATCHES[strtolower(trim($name))] ?? null])
                ->values()->all(),
            'moreColors' => max(0, $colors->count() - self::MAX_DOTS),
            'sizeLabel'  => self::sizeLabel($sizes),
            'sizeShort'  => self::sizeLabel($sizes, short: true),
            'otherLabel' => $other->count() > 1 ? $other->count() . ' ' . strtolower(\Illuminate\Support\Str::plural($otherKey)) : null,
            'fromPrice'  => self::fromPrice($product),
        ];
    }

    /** Variation type => unique values, from the product's variants or, failing that, its SKUs. */
    private static function groups(Product $product): Collection
    {
        $groups = $product->variants && $product->variants->isNotEmpty()
            ? $product->variants->groupBy('type')->map(fn ($items) => $items->pluck('value'))
            : collect();

        if ($groups->isEmpty() && $product->skus && $product->skus->isNotEmpty()) {
            $byType = [];
            foreach ($product->skus as $sku) {
                foreach ($sku->getAttributesData() as $type => $value) {
                    $byType[$type][] = $value;
                }
            }
            $groups = collect($byType)->map(fn ($values) => collect($values));
        }

        return $groups->map(fn ($values) => $values->map(fn ($v) => trim((string) $v))->filter()->unique()->values())
            ->filter(fn ($values) => $values->isNotEmpty());
    }

    /** "Sizes 39–44" (or "39–44" where space is tight on the card's price line), else "6 sizes". */
    private static function sizeLabel(Collection $sizes, bool $short = false): ?string
    {
        if ($sizes->count() < 2) {
            return null;
        }
        if ($sizes->every(fn ($s) => is_numeric($s))) {
            return ($short ? '' : 'Sizes ') . $sizes->min() . '–' . $sizes->max();
        }

        return $sizes->count() . ' sizes';
    }

    /** The lowest price among in-stock variations, only when the variations don't all cost the same. */
    private static function fromPrice(Product $product): ?float
    {
        if (! $product->skus || $product->skus->count() < 2) {
            return null;
        }
        $prices = $product->skus->where('is_active', true)
            ->each(fn ($sku) => $sku->setRelation('product', $product))
            ->map(fn ($sku) => round($sku->getCalculatedSalePrice(), 2))
            ->filter(fn ($p) => $p > 0)
            ->unique();

        return $prices->count() > 1 ? (float) $prices->min() : null;
    }
}
