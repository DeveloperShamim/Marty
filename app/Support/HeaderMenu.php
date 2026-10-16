<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * The store's top menu bar (Admin → Homepage → Menu bar). Saved as JSON in the "header_menu"
 * setting: a list of items, each optionally with a dropdown of child links. With nothing saved
 * the bar shows Home, All Products, every category and Brands, as it always did.
 */
class HeaderMenu
{
    public const MAX_ITEMS = 15;
    public const MAX_CHILDREN = 12;

    /** type => [admin label, needs a ref (category / brand id), may be a dropdown child] */
    public const TYPES = [
        'home'       => ['Home', null, true],
        'shop'       => ['All Products', null, true],
        'categories' => ['All categories (fits the bar, rest under More)', null, false],
        'category'   => ['A category', 'category', true],
        'brands'     => ['Brands dropdown', null, false],
        'brand'      => ['A brand', 'brand', true],
        'new'        => ['New arrivals', null, true],
        'best'       => ['Best sellers', null, true],
        'featured'   => ['Top selling', null, true],
        'flash'      => ['Flash sale (only while a sale runs)', null, true],
        'track'      => ['Track order', null, true],
        'link'       => ['Custom link', null, true],
    ];

    public static function defaults(): array
    {
        return [['type' => 'home'], ['type' => 'shop'], ['type' => 'categories'], ['type' => 'brands']];
    }

    /** What admin saved, or the default menu. */
    public static function saved(): array
    {
        $items = json_decode((string) setting('header_menu', ''), true);

        return is_array($items) && $items ? $items : self::defaults();
    }

    public static function isCustom(): bool
    {
        $items = json_decode((string) setting('header_menu', ''), true);

        return is_array($items) && $items !== [];
    }

    /** Clean what the admin form sent: known types, real ids, safe links, sensible lengths. */
    public static function sanitize(array $items): array
    {
        $clean = fn (array $item, bool $child) => self::cleanItem($item, $child);

        return collect($items)->take(self::MAX_ITEMS)
            ->map(function ($item) use ($clean) {
                $row = is_array($item) ? $clean($item, false) : null;
                if ($row && ! empty($item['children']) && is_array($item['children'])) {
                    $kids = collect($item['children'])->take(self::MAX_CHILDREN)
                        ->map(fn ($c) => is_array($c) ? $clean($c, true) : null)->filter()->values()->all();
                    if ($kids) {
                        $row['children'] = $kids;
                    }
                }

                return $row;
            })
            ->filter()
            // The automatic categories row and the Brands dropdown can each appear once
            ->unique(fn ($row, $i) => in_array($row['type'], ['categories', 'brands'], true) ? $row['type'] : 'item-' . $i)
            ->values()->all();
    }

    private static function cleanItem(array $item, bool $child): ?array
    {
        $type = (string) ($item['type'] ?? '');
        if (! isset(self::TYPES[$type]) || ($child && ! self::TYPES[$type][2])) {
            return null;
        }
        $row = ['type' => $type];
        $label = trim(strip_tags((string) ($item['label'] ?? '')));
        if ($label !== '') {
            $row['label'] = mb_substr($label, 0, 40);
        }
        if (self::TYPES[$type][1]) {
            $ref = (int) ($item['ref'] ?? 0);
            $model = self::TYPES[$type][1] === 'category' ? Category::class : Brand::class;
            if (! $ref || ! $model::whereKey($ref)->exists()) {
                return null;
            }
            $row['ref'] = $ref;
        }
        if ($type === 'link') {
            $url = trim((string) ($item['url'] ?? ''));
            $safe = str_starts_with($url, '/') && ! str_starts_with($url, '//')
                || preg_match('#^https?://#i', $url)
                || preg_match('#^(tel|mailto):#i', $url);
            if (! $safe || ! isset($row['label'])) {
                return null;
            }
            $row['url'] = mb_substr($url, 0, 255);
        }

        return $row;
    }

    /**
     * The menu ready to draw. Each entry is one of:
     *  ['kind' => 'categories'] / ['kind' => 'brands'] (the automatic blocks), or
     *  ['kind' => 'link', 'label', 'url', 'active', 'children' => [['label', 'url', 'active'], ...]].
     * Items whose category or brand is gone or hidden are left out.
     */
    public static function resolve(Collection $navCategories, Collection $navBrands, ?Category $activeCategory = null, bool $flashRunning = false): array
    {
        $byId = $navCategories->flatMap(fn ($c) => collect([$c])->concat($c->relationLoaded('children') ? $c->children : []))->keyBy('id');
        $brands = $navBrands->keyBy('id');
        $activeId = $activeCategory?->id;

        $link = function (array $item) use ($byId, $brands, $flashRunning, $activeId) {
            $req = request();
            $own = $item['label'] ?? null;
            switch ($item['type']) {
                case 'home':
                    return [$own ?? 'Home', route('home'), $req->routeIs('home')];
                case 'shop':
                    return [$own ?? 'All Products', route('shop'), $req->routeIs('shop') && ! $req->hasAny(['flash', 'brand', 'new', 'filter', 'featured', 'best_seller'])];
                case 'new':
                    return [$own ?? 'New Arrivals', route('shop', ['filter' => 'new']), $req->routeIs('shop') && ($req->boolean('new') || $req->input('filter') === 'new')];
                case 'best':
                    return [$own ?? 'Best Sellers', route('shop', ['filter' => 'best_seller']), $req->routeIs('shop') && ($req->boolean('best_seller') || $req->input('filter') === 'best_seller')];
                case 'featured':
                    return [$own ?? 'Top Selling', route('shop', ['filter' => 'featured']), $req->routeIs('shop') && ($req->boolean('featured') || $req->input('filter') === 'featured')];
                case 'flash':
                    return $flashRunning ? [$own ?? 'Flash Sale', route('shop', ['flash' => 1]), $req->routeIs('shop') && $req->boolean('flash')] : null;
                case 'track':
                    return [$own ?? 'Track Order', route('track'), $req->routeIs('track*')];
                case 'category':
                    $cat = $byId[$item['ref'] ?? 0] ?? null;
                    if (! $cat) {
                        return null;
                    }
                    $family = $cat->relationLoaded('children') ? $cat->children->pluck('id')->push($cat->id) : collect([$cat->id]);

                    return [$own ?? $cat->name, route('shop.category', $cat), $activeId && $family->contains($activeId), $cat];
                case 'brand':
                    $brand = $brands[$item['ref'] ?? 0] ?? null;

                    return $brand ? [$own ?? $brand->name, route('shop.brand', $brand), $req->is('brand/' . $brand->slug)] : null;
                case 'link':
                    return [$item['label'] ?? '', $item['url'] ?? '#', rtrim($req->getRequestUri(), '/') === rtrim((string) ($item['url'] ?? ''), '/')];
            }

            return null;
        };

        $out = [];
        foreach (self::saved() as $item) {
            $type = $item['type'] ?? '';
            if ($type === 'categories' || $type === 'brands') {
                $out[] = ['kind' => $type];
                continue;
            }
            $main = isset(self::TYPES[$type]) ? $link($item) : null;
            if (! $main) {
                continue;
            }
            $children = [];
            // A category's sub-categories open under it on their own
            if (($main[3] ?? null) instanceof Category && $main[3]->relationLoaded('children')) {
                $subs = $main[3]->children->filter(fn ($s) => ($s->products_count ?? 1) > 0);
                if ($subs->isNotEmpty()) {
                    $children[] = ['label' => 'All ' . $main[3]->name, 'url' => $main[1], 'active' => $activeId === $main[3]->id, 'strong' => true];
                    foreach ($subs as $s) {
                        $children[] = ['label' => $s->name, 'url' => route('shop.category', $s), 'active' => $activeId === $s->id];
                    }
                }
            }
            foreach ($item['children'] ?? [] as $child) {
                if ($c = $link($child)) {
                    $children[] = ['label' => $c[0], 'url' => $c[1], 'active' => (bool) $c[2]];
                }
            }
            $out[] = [
                'kind'     => 'link',
                'type'     => $type,
                'label'    => $main[0],
                'url'      => $main[1],
                'active'   => (bool) $main[2] || collect($children)->contains('active', true),
                'children' => $children,
                'external' => $type === 'link' && preg_match('#^https?://#i', $main[1]) && ! str_starts_with($main[1], url('/')),
            ];
        }

        return $out;
    }
}
