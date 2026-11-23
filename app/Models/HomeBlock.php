<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * A homepage section the owner builds (Admin → Homepage → Sections → Add section).
 * It joins the section list as "block_{id}", so it is ordered and switched on or off like the built-in ones.
 */
class HomeBlock extends Model
{
    /** type => [name, what it shows] */
    public const TYPES = [
        'products'     => ['Product row', 'A sliding row of products from one category or the whole shop'],
        'promo_band'   => ['Promo band', 'A coloured strip with a headline, a line of text and a button'],
        'image_banner' => ['Wide banner', 'One full-width picture with a link, with its own phone picture'],
        'card_grid'    => ['Card grid', '2 to 6 cards with a picture, title, text and link'],
        'stats_row'    => ['Stats row', 'Big numbers with labels, e.g. 10,000+ happy customers'],
    ];

    /** How a product row is sorted */
    public const SORTS = [
        'latest'     => 'Newest first',
        'best'       => 'Best sellers first',
        'featured'   => 'Featured first',
        'price_low'  => 'Price: low to high',
        'price_high' => 'Price: high to low',
        'random'     => 'Random each visit',
    ];

    public const MAX_CARDS = 6;
    public const MAX_STATS = 6;

    protected $guarded = [];

    protected $casts = ['data' => 'array'];

    public function key(): string
    {
        return 'block_' . $this->id;
    }

    public static function idFromKey(string $key): ?int
    {
        return preg_match('/^block_(\d+)$/', $key, $m) ? (int) $m[1] : null;
    }

    public function typeName(): string
    {
        return self::TYPES[$this->type][0] ?? $this->type;
    }

    /** The name in the admin section list */
    public function label(): string
    {
        return $this->title ?: $this->typeName();
    }

    public function get(string $field, mixed $default = null): mixed
    {
        return data_get($this->data ?? [], $field, $default);
    }

    public function category(): ?Category
    {
        $id = $this->get('category_id');

        return $id ? Category::find($id) : null;
    }

    /** One-line summary for the admin list */
    public function hint(): string
    {
        return match ($this->type) {
            'products' => ($this->category()?->name ?? 'All products') . ' · ' . (self::SORTS[$this->get('sort', 'latest')] ?? '') . ' · ' . $this->get('limit', 8) . ' products',
            'card_grid' => count($this->cards()) . ' cards',
            'stats_row' => count($this->stats()) . ' numbers',
            default => self::TYPES[$this->type][1] ?? '',
        };
    }

    /** Products for a product row, published, in the picked order */
    public function products(): Collection
    {
        $q = Product::query()->published()->with('images', 'category', 'brand', 'variants', 'skus');
        if ($cat = $this->category()) {
            $q->whereIn('category_id', $cat->familyIds());
        }

        match ($this->get('sort', 'latest')) {
            'best' => $q->orderByDesc('is_best_seller')->latest(),
            'featured' => $q->orderByDesc('is_featured')->latest(),
            'price_low' => $q->orderByRaw('COALESCE(sale_price, regular_price) asc'),
            'price_high' => $q->orderByRaw('COALESCE(sale_price, regular_price) desc'),
            'random' => $q->inRandomOrder(),
            default => $q->latest(),
        };

        return $q->take(max(1, min(24, (int) $this->get('limit', 8))))->get();
    }

    /** Where "See all" goes: the link typed in, else the category, else the shop */
    public function seeAllHref(): string
    {
        $link = (string) $this->get('link_url', '');
        if ($link !== '') {
            return self::href($link);
        }

        return ($cat = $this->category()) ? route('shop.category', $cat) : route('shop');
    }

    /** @return list<array{image: ?string, title: string, text: string, link: string}> */
    public function cards(): array
    {
        return array_values(array_filter((array) $this->get('cards', []), fn ($c) => is_array($c) && (($c['title'] ?? '') !== '' || ! empty($c['image']))));
    }

    /** @return list<array{value: string, label: string}> */
    public function stats(): array
    {
        return array_values(array_filter((array) $this->get('stats', []), fn ($s) => is_array($s) && ($s['value'] ?? '') !== ''));
    }

    /** Every picture this block uses, for clean-up */
    public function imagePaths(): array
    {
        return array_values(array_filter([
            $this->get('image'), $this->get('image_mobile'),
            ...array_map(fn ($c) => $c['image'] ?? null, (array) $this->get('cards', [])),
        ]));
    }

    public static function href(?string $link): string
    {
        $link = trim((string) $link);
        if ($link === '') {
            return route('shop');
        }

        return preg_match('#^(https?:)?//#', $link) ? $link : url($link);
    }
}
