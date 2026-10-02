<?php

namespace App\Services;

/**
 * Free delivery rules (Admin → Marketing → Free Delivery). Checkout, the admin order page
 * and the profit reports all use this, so they always agree.
 *
 * When an order ships free, its shipping_charge is 0 and shipping_waived holds the normal
 * delivery fee: the shop still pays the courier, so profit reports subtract it.
 */
class FreeDelivery
{
    /** Manual mobile-banking payments that count as "paid online". */
    public const ONLINE_METHODS = ['bkash', 'nagad', 'rocket'];

    public const ZONES = ['both', 'inside_dhaka', 'outside_dhaka'];

    public static function config(): array
    {
        $zones = (string) setting('free_delivery_online_zones', 'both');

        return [
            'online_enabled' => (string) setting('free_delivery_online', '1') === '1',
            'online_min'     => max(0, (float) setting('free_delivery_online_min', 500)),
            'online_zones'   => in_array($zones, self::ZONES, true) ? $zones : 'both',
            'over_amount'    => max(0, (float) setting('free_delivery_over_amount', 0)), // 0 = off
            'fees'           => [
                'inside_dhaka'  => (float) setting('shipping_inside_dhaka', 60),
                'outside_dhaka' => (float) setting('shipping_outside_dhaka', 120),
            ],
        ];
    }

    /** True when the shop accepts at least one online method (it has a merchant number set). */
    public static function onlinePaymentAvailable(): bool
    {
        foreach (self::ONLINE_METHODS as $m) {
            if ((string) setting("pay_{$m}_enabled", '1') === '1' && setting("{$m}_number")) {
                return true;
            }
        }

        return false;
    }

    public static function fee(string $zone): float
    {
        return self::config()['fees'][$zone] ?? self::config()['fees']['outside_dhaka'];
    }

    public static function isOnline(?string $paymentMethod): bool
    {
        return in_array($paymentMethod, self::ONLINE_METHODS, true);
    }

    /**
     * Why this order ships free, or null. Rules that don't depend on payment come first, so an
     * order that qualifies anyway keeps free delivery even if its online payment falls through.
     *
     * @param  float  $orderValue  subtotal after coupon discount
     */
    public static function reason(float $orderValue, string $zone, ?string $paymentMethod, bool $hasFreeProduct): ?string
    {
        $c = self::config();

        if ($hasFreeProduct) {
            return 'product';
        }
        if ($c['over_amount'] > 0 && $orderValue >= $c['over_amount']) {
            return 'order_total';
        }
        if ($c['online_enabled'] && self::isOnline($paymentMethod) && $orderValue >= $c['online_min']
            && ($c['online_zones'] === 'both' || $c['online_zones'] === $zone)) {
            return 'online_payment';
        }

        return null;
    }

    /** ['shipping' => charged to the customer, 'waived' => absorbed by the shop, 'reason' => ?string] */
    public static function quote(float $orderValue, string $zone, ?string $paymentMethod, bool $hasFreeProduct): array
    {
        $fee = self::fee($zone);
        $reason = $fee > 0 ? self::reason($orderValue, $zone, $paymentMethod, $hasFreeProduct) : null;

        return $reason
            ? ['shipping' => 0.0, 'waived' => $fee, 'reason' => $reason]
            : ['shipping' => $fee, 'waived' => 0.0, 'reason' => null];
    }

    public static function label(?string $reason): ?string
    {
        return match ($reason) {
            'product'        => 'Free-delivery product in the order',
            'order_total'    => 'Order total over ' . money(self::config()['over_amount']),
            'online_payment' => 'Paid online (bKash / Nagad / Rocket)',
            default          => null,
        };
    }
}
