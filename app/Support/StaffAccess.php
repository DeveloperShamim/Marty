<?php

namespace App\Support;

use App\Models\User;

/**
 * Which staff role may open which admin area. Used by the routes (middleware "area:<key>")
 * and by the sidebar, so the menu and the server can't disagree. Admins may open everything.
 */
class StaffAccess
{
    private const STORE = 'store_manager';
    private const ORDERS = 'order_manager';
    private const STOCK = 'inventory_manager';

    public const AREAS = [
        // Overview & money
        'dashboard'       => [self::STORE, self::STOCK],   // inventory managers see it without money figures
        'analytics'       => [self::STORE],
        'expenses'        => [self::STORE],
        'cache'           => [self::STORE],
        // Sales
        'orders'          => [self::STORE, self::ORDERS],
        'courier-scan'    => [self::STORE, self::ORDERS],
        'pos'             => [self::STORE, self::ORDERS],
        'abandoned-carts' => [self::STORE, self::ORDERS],
        'customers'       => [self::STORE, self::ORDERS],
        'blacklist'       => [self::STORE, self::ORDERS],
        'reviews'         => [self::STORE, self::ORDERS, self::STOCK],
        // Catalog & storefront
        'products'        => [self::STORE, self::STOCK],
        'inventory'       => [self::STORE, self::STOCK],
        'categories'      => [self::STORE, self::STOCK],
        'brands'          => [self::STORE, self::STOCK],
        'variations'      => [self::STORE, self::STOCK],
        'barcodes'        => [self::STORE, self::STOCK],
        'media'           => [self::STORE, self::STOCK],
        'size-guide'      => [self::STORE, self::STOCK],
        'features'        => [self::STORE, self::STOCK],
        // Marketing
        'banners'         => [self::STORE],
        'coupons'         => [self::STORE],
        'flash-sale'      => [self::STORE],
        'free-delivery'   => [self::STORE],
        'news-ticker'     => [self::STORE],
        // Admin only
        'staff'           => [],
        'activity-logs'   => [],
        'settings'        => [],
        'integrations'    => [],
    ];

    public static function allows(?User $user, string $area): bool
    {
        if (! $user || ! $user->isStaff()) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }

        return in_array($user->role, self::AREAS[$area] ?? [], true);
    }

    /** Where a staff member lands after login or when refused a page. */
    public static function home(User $user): string
    {
        return self::allows($user, 'dashboard') ? route('admin.dashboard') : route('admin.orders.index');
    }
}
