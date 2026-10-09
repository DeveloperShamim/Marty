<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * The admin menu. Small related pages share one menu entry and show as tabs at the top of the
 * page (see admin.partials.page-tabs); every page keeps its own route and staff permission.
 */
class AdminNav
{
    /** label, route, icon (Lucide paths), search keywords — one per admin area. */
    private static function areas(): array
    {
        return [
            'dashboard' => ['Dashboard', 'admin.dashboard', '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>', 'home overview'],
            'orders' => ['Orders', 'admin.orders.index', '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>', 'sales invoice'],
            'courier-scan' => ['Courier Scan', 'admin.courier-scan.index', '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.62l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>', 'delivery dispatch return shipping in out'],
            'pos' => ['POS Register', 'admin.pos.index', '<rect width="16" height="20" x="4" y="2" rx="2"/><path d="M8 6h8"/><path d="M16 14v4"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/>', 'cash register point of sale counter'],
            'products' => ['Products', 'admin.products.index', '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>', 'items add product'],
            'inventory' => ['Inventory', 'admin.inventory.index', '<path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/><rect width="12" height="12" x="6" y="10"/>', 'stock restock low stock warehouse'],
            'categories' => ['Categories', 'admin.categories.index', '<path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>', 'collections'],
            'brands' => ['Brands', 'admin.brands.index', '<path d="M12.59 2.59A2 2 0 0 0 11.17 2H4a2 2 0 0 0-2 2v7.17a2 2 0 0 0 .59 1.42l8.7 8.7a2.43 2.43 0 0 0 3.42 0l6.58-6.58a2.43 2.43 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>', 'manufacturer producer'],
            'variations' => ['Variations', 'admin.variations.index', '<path d="M21 4h-7"/><path d="M10 4H3"/><path d="M21 12h-9"/><path d="M8 12H3"/><path d="M21 20h-5"/><path d="M12 20H3"/><path d="M14 2v4"/><path d="M8 10v4"/><path d="M16 18v4"/>', 'size color attributes options'],
            'barcodes' => ['Product Barcodes', 'admin.barcodes.index', '<path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M8 7v10"/><path d="M12 7v10"/><path d="M17 7v10"/>', 'barcode labels print sticker'],
            'reviews' => ['Reviews', 'admin.reviews.index', '<path d="M11.52 2.3a.53.53 0 0 1 .95 0l2.31 4.68a2.12 2.12 0 0 0 1.6 1.16l5.16.76a.53.53 0 0 1 .3.9l-3.74 3.64a2.12 2.12 0 0 0-.61 1.88l.88 5.14a.53.53 0 0 1-.77.56l-4.62-2.43a2.12 2.12 0 0 0-1.97 0L6.4 21.01a.53.53 0 0 1-.77-.56l.88-5.14a2.12 2.12 0 0 0-.61-1.88L2.16 9.79a.53.53 0 0 1 .3-.9l5.16-.76a2.12 2.12 0 0 0 1.6-1.16z"/>', 'customer ratings feedback'],
            'banners' => ['Hero Banners', 'admin.banners.index', '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="m2 15 5-4 4 3 4-5 7 6"/>', 'slider homepage'],
            'features' => ['Trust Strip', 'admin.features.index', '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>', 'trust features badges'],
            'size-guide' => ['Size Guide', 'admin.size-guide.index', '<path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z"/><path d="m14.5 12.5 2-2"/><path d="m11.5 9.5 2-2"/><path d="m8.5 6.5 2-2"/><path d="m17.5 15.5 2-2"/>', 'chart measurement'],
            'media' => ['Media Library', 'admin.media.index', '<rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/>', 'images photos uploads files'],
            'coupons' => ['Coupons', 'admin.coupons.index', '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>', 'discount promo code'],
            'flash-sale' => ['Flash Sale', 'admin.flash-sale.index', '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>', 'deal offer countdown'],
            'free-delivery' => ['Free Delivery', 'admin.free-delivery.index', '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>', 'shipping offer courier charge waive'],
            'news-ticker' => ['News Ticker', 'admin.news-ticker.index', '<path d="M15 18h-5"/><path d="M18 14h-8"/><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-4 0v-9a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="10" y="6" rx="1"/>', 'headline announcement promo bar top banner'],
            'abandoned-carts' => ['Abandoned Carts', 'admin.abandoned-carts.index', '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>', 'cart recovery'],
            'analytics' => ['Profit & Analytics', 'admin.analytics.index', '<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="m19 9-5 5-4-4-3 3"/>', 'report aov revenue sales profit'],
            'expenses' => ['Expenses', 'admin.expenses.index', '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/>', 'costs facebook ads fb spend'],
            'customers' => ['Customers', 'admin.customers.index', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', 'buyers clients'],
            'staff' => ['Staff & Roles', 'admin.staff.index', '<path d="M2 21a8 8 0 0 1 13.29-6"/><circle cx="10" cy="8" r="5"/><path d="m16 19 2 2 4-4"/>', 'team users permissions'],
            'activity-logs' => ['Audit Log', 'admin.activity-logs.index', '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>', 'activity history staff logs'],
            'blacklist' => ['Blacklist', 'admin.blacklist.index', '<circle cx="12" cy="12" r="10"/><path d="m4.9 4.9 14.2 14.2"/>', 'fraud block ban phone'],
            'settings' => ['Store Settings', 'admin.settings.edit', '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>', 'configuration logo shipping payment'],
            'integrations' => ['Integrations', 'admin.integrations.index', '<path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4V8Z"/>', 'api courier steadfast pathao sms pixel'],
        ];
    }

    /** Menu sections: each entry is one area, or [label, icon area, [tab areas...], extra keywords]. */
    private const SECTIONS = [
        ''              => ['dashboard'],
        'Sales'         => ['orders', 'courier-scan', 'pos'],
        'Catalog'       => [
            'products',
            ['Inventory', 'inventory', ['inventory' => 'Stock', 'barcodes' => 'Barcodes']],
            ['Catalog setup', 'categories', ['categories' => 'Categories', 'brands' => 'Brands', 'variations' => 'Variations']],
            'reviews',
        ],
        'Store content' => [
            ['Homepage', 'banners', ['banners' => 'Hero banners', 'features' => 'Trust strip', 'news-ticker' => 'News ticker']],
            'size-guide',
            'media',
        ],
        'Marketing'     => [
            'coupons',
            ['Promotions', 'flash-sale', ['flash-sale' => 'Flash sale', 'free-delivery' => 'Free delivery']],
            'abandoned-carts',
        ],
        'Finance'       => ['analytics', 'expenses'],
        'People'        => [
            ['Customers', 'customers', ['customers' => 'Customers', 'blacklist' => 'Blacklist']],
            ['Staff & Roles', 'staff', ['staff' => 'Staff & Roles', 'activity-logs' => 'Audit log']],
        ],
        'Settings'      => ['settings', 'integrations'],
    ];

    /** Phone bottom bar per role: four everyday pages (each still checked against the role), then "More". */
    public const BOTTOM_BAR = [
        'admin'             => ['dashboard', 'orders', 'courier-scan', 'products'],
        'store_manager'     => ['dashboard', 'orders', 'products', 'pos'],
        'order_manager'     => ['orders', 'courier-scan', 'pos', 'customers'],
        'inventory_manager' => ['dashboard', 'products', 'inventory', 'barcodes'],
    ];

    /** The bottom bar's pages for this user, in order. */
    public static function bottomBar(?User $user): array
    {
        $keys = self::BOTTOM_BAR[$user->role ?? ''] ?? self::BOTTOM_BAR['admin'];
        $areas = self::areas();

        return collect($keys)->filter(fn ($key) => StaffAccess::allows($user, $key))->map(function ($key) use ($areas) {
            [$label, $route, $icon] = $areas[$key];

            return ['key' => $key, 'label' => $label, 'route' => $route, 'patterns' => [self::pattern($route)], 'icon' => $icon];
        })->values()->all();
    }

    /** Shorter labels for the bottom bar. */
    public const SHORT = ['dashboard' => 'Home', 'courier-scan' => 'Scan', 'pos' => 'POS', 'barcodes' => 'Barcodes'];

    private static function pattern(string $route): string
    {
        // admin.orders.index → admin.orders.* (covers show/edit/create); admin.dashboard stays exact.
        return preg_match('/\.(index|edit)$/', $route) ? Str::beforeLast($route, '.') . '.*' : $route;
    }

    /**
     * Menu sections for this user. Each item: key, label, route, patterns, icon, keywords, tabs.
     * A tabbed item shows when any of its tabs is allowed, and links to the first allowed one.
     */
    public static function sections(?User $user): array
    {
        $areas = self::areas();
        $sections = [];
        foreach (self::SECTIONS as $group => $entries) {
            foreach ($entries as $entry) {
                [$label, $iconKey, $tabKeys] = is_array($entry) ? $entry : [null, $entry, [$entry => null]];
                $tabs = [];
                foreach ($tabKeys as $key => $tabLabel) {
                    if (StaffAccess::allows($user, $key)) {
                        [$areaLabel, $route] = $areas[$key];
                        $tabs[] = ['key' => $key, 'label' => $tabLabel ?? $areaLabel, 'route' => $route, 'pattern' => self::pattern($route)];
                    }
                }
                if (! $tabs) {
                    continue;
                }
                $sections[$group][] = [
                    'key'      => $tabs[0]['key'],
                    'label'    => $label ?? $areas[$iconKey][0],
                    'route'    => $tabs[0]['route'],
                    'patterns' => array_column($tabs, 'pattern'),
                    'icon'     => $areas[$iconKey][2],
                    'keywords' => collect($tabs)->map(fn ($t) => $t['label'] . ' ' . ($areas[$t['key']][3] ?? ''))->implode(' '),
                    'tabs'     => count($tabs) > 1 ? $tabs : [],
                ];
            }
        }

        return $sections;
    }

    /** Whether the current request is on this menu item (any of its tabs). */
    public static function isOn(array $item): bool
    {
        return request()->routeIs(...$item['patterns']);
    }

    /** The tab strip for the current page, or [] when it isn't a tabbed page. */
    public static function currentTabs(?User $user): array
    {
        foreach (self::sections($user) as $items) {
            foreach ($items as $item) {
                if ($item['tabs'] && self::isOn($item)) {
                    return $item['tabs'];
                }
            }
        }

        return [];
    }
}
