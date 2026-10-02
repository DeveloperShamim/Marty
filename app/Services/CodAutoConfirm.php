<?php

namespace App\Services;

use App\Models\Blacklist;
use App\Models\CustomerCourierCheck;
use App\Models\Order;

/**
 * Confirms new cash-on-delivery orders from customers who are safe to send to, so staff only call the risky ones.
 * Only the order status changes: the payment stays pending until the parcel is delivered and the cash collected.
 * Switch: Store Settings → Payments → "Auto-confirm safe COD orders" (off by default).
 */
class CodAutoConfirm
{
    public static function enabled(): bool
    {
        return setting('cod_auto_confirm', '0') === '1';
    }

    /** Why the order is safe, or null when it should wait for a staff check. */
    public static function reason(Order $order, ?CustomerCourierCheck $check): ?string
    {
        if ($order->payment_method !== 'cod' || $order->order_type === 'pos' || $order->status !== 'pending') {
            return null;
        }
        // Anything the checkout fraud check or the courier history flagged needs a person.
        if ($order->fraudRiskLevel() !== 'low' || Blacklist::isBlacklisted('phone', $order->customer_phone)) {
            return null;
        }

        $earlier = Order::where('customer_phone', $order->customer_phone)->where('id', '!=', $order->id)
            ->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');
        $delivered = (int) ($earlier['delivered'] ?? 0);
        $returned = (int) ($earlier['returned'] ?? 0);
        if ($delivered > 0 && $returned === 0) {
            return $delivered . ' earlier ' . ($delivered === 1 ? 'order' : 'orders') . ' delivered in your shop';
        }

        if ($check && $check->total_parcels > 0 && $check->riskLevel() === 'low') {
            $rate = rtrim(rtrim(number_format((float) $check->success_ratio, 1), '0'), '.');

            return "{$rate}% courier delivery success ({$check->delivered} of {$check->total_parcels} parcels)";
        }

        return null;
    }

    public static function apply(Order $order, ?CustomerCourierCheck $check): bool
    {
        if (! self::enabled() || ! ($reason = self::reason($order, $check))) {
            return false;
        }

        $order->update(['status' => 'confirmed', 'auto_confirmed_reason' => $reason]);
        ActivityLogger::log('Auto-confirmed COD Order', "Order #{$order->order_number} ({$order->customer_name}): {$reason}");

        return true;
    }
}
