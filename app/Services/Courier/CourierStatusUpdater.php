<?php

namespace App\Services\Courier;

use App\Models\Order;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Turns Steadfast / Pathao / RedX parcel statuses into one vocabulary and applies them to orders.
 *
 * Only "delivered" changes the order automatically (to Delivered, COD marked paid). Returns,
 * holds and partial deliveries are flagged for staff, because a return still has to be
 * checked and scanned in (restock or damaged, courier charge paid or not).
 */
class CourierStatusUpdater
{
    public const PROVIDERS = ['steadfast', 'pathao', 'redx'];

    /** Statuses staff should look at; listed on the courier scan page. */
    public const ATTENTION = ['partial_delivered', 'hold', 'returning', 'returned', 'cancelled'];

    public const LABELS = [
        'in_transit'        => 'In transit',
        'delivered'         => 'Delivered',
        'partial_delivered' => 'Partly delivered',
        'hold'              => 'On hold',
        'returning'         => 'Returning to you',
        'returned'          => 'Returned by courier',
        'cancelled'         => 'Pickup cancelled',
        'unknown'           => 'Unknown status',
    ];

    /** Raw courier statuses (lower-case, "_" separated) mapped to our vocabulary. */
    private const MAP = [
        // Steadfast
        'pending' => 'in_transit', 'in_review' => 'in_transit', 'unknown' => 'unknown', 'unknown_approval_pending' => 'unknown',
        'delivered' => 'delivered', 'delivered_approval_pending' => 'delivered',
        'partial_delivered' => 'partial_delivered', 'partial_delivered_approval_pending' => 'partial_delivered',
        'cancelled' => 'returning', 'cancelled_approval_pending' => 'returning', 'hold' => 'hold',
        // Pathao
        'pickup_requested' => 'in_transit', 'assigned_for_pickup' => 'in_transit', 'picked' => 'in_transit',
        'pickup_failed' => 'hold', 'pickup_cancelled' => 'cancelled', 'at_the_sorting_hub' => 'in_transit',
        'in_transit' => 'in_transit', 'received_at_last_mile_hub' => 'in_transit', 'assigned_for_delivery' => 'in_transit',
        'partial_delivery' => 'partial_delivered', 'delivery_failed' => 'hold', 'on_hold' => 'hold',
        'return' => 'returning', 'returned' => 'returned', 'paid_return' => 'returned', 'payment_invoice' => 'delivered',
        'exchange' => 'partial_delivered',
        // RedX
        'pickup_pending' => 'in_transit', 'ready_for_delivery' => 'in_transit', 'delivery_in_progress' => 'in_transit',
        'agent_area_change' => 'in_transit', 'agent_hold' => 'hold', 'agent_returning' => 'returning',
    ];

    public function __construct(
        private SteadfastService $steadfast,
        private PathaoService $pathao,
        private RedxService $redx,
    ) {}

    public static function normalize(?string $raw): string
    {
        $s = strtolower(trim((string) $raw));
        $s = preg_replace('/^order[._]/', '', $s);            // Pathao webhook events: "order.delivered"
        $s = trim(preg_replace('/[^a-z0-9]+/', '_', $s), '_');

        if (isset(self::MAP[$s])) {
            return self::MAP[$s];
        }

        return match (true) {
            str_contains($s, 'partial')                         => 'partial_delivered',
            str_contains($s, 'return')                          => 'returning',
            str_contains($s, 'cancel')                          => 'returning',
            str_contains($s, 'hold') || str_contains($s, 'fail') => 'hold',
            str_contains($s, 'transit') || str_contains($s, 'pick') || str_contains($s, 'hub') => 'in_transit',
            default                                             => 'unknown',
        };
    }

    public static function label(?string $status): ?string
    {
        return $status ? (self::LABELS[$status] ?? Str::headline($status)) : null;
    }

    /** Ask the courier for the parcel's current status and apply it. */
    public function refresh(Order $order): array
    {
        $service = match (strtolower((string) $order->courier_name)) {
            'steadfast' => $this->steadfast,
            'pathao'    => $this->pathao,
            'redx'      => $this->redx,
            default     => null,
        };
        if (! $service || ! $order->courier_tracking_code) {
            return ['success' => false, 'message' => 'This order was not booked through a courier API.'];
        }

        $result = $service->trackOrder($order);
        if (! $result['success']) {
            $order->forceFill(['courier_synced_at' => now()])->saveQuietly();

            return $result;
        }

        $status = $this->apply($order, $result['status'], $result['message'] ?? null, 'sync');

        return ['success' => true, 'status' => $status, 'message' => self::label($status)];
    }

    /** Apply a raw courier status to the order. Safe to call repeatedly with the same status. */
    public function apply(Order $order, string $raw, ?string $message = null, string $source = 'sync'): string
    {
        $status = self::normalize($raw);
        if ($status === 'unknown') {
            Log::info('Unmapped courier status', ['order' => $order->order_number, 'courier' => $order->courier_name, 'raw' => $raw]);
        }

        $attrs = [
            'courier_status'         => $status,
            'courier_status_message' => Str::limit(trim(($message ?: Str::headline($raw))), 250, ''),
            'courier_synced_at'      => now(),
        ];

        $previous = $order->courier_status;
        if ($status === 'delivered' && in_array($order->status, ['confirmed', 'processing', 'shipped'], true)) {
            $attrs['status'] = 'delivered';
            if ($order->payment_method === 'cod' && $order->payment_status === 'pending') {
                $attrs['payment_status'] = 'verified'; // cash collected by the courier
            }
        }

        $order->forceFill($attrs)->save();

        if ($previous !== $status && ($status === 'delivered' || in_array($status, self::ATTENTION, true))) {
            ActivityLogger::log(
                'Courier Status Update',
                "{$order->courierLabel()} reported " . self::label($status) . " for order #{$order->order_number}"
                    . (isset($attrs['status']) ? ' (order marked delivered)' : '') . " via {$source}."
            );
        }

        return $status;
    }
}
