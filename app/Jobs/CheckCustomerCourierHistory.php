<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\CodAutoConfirm;
use App\Services\Courier\BdCourierService;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Runs right after the customer's checkout page is sent (no queue worker needed):
 * looks up the buyer's courier delivery history and adds it to the order's fraud score,
 * then auto-confirms the order if the customer is safe (see CodAutoConfirm).
 */
class CheckCustomerCourierHistory
{
    use Dispatchable;

    public function __construct(public int $orderId) {}

    public function handle(BdCourierService $bdCourier): void
    {
        $order = Order::find($this->orderId);
        if (! $order) {
            return;
        }

        if (self::shouldCheck($order) && $bdCourier->isConfigured()) {
            $result = $bdCourier->check($order->customer_phone);
            if ($result['success']) {
                self::applyToFraudScore($order, $result['check']);
            }
        }

        // Safe cash-on-delivery customers are confirmed straight away (when switched on).
        CodAutoConfirm::apply($order->fresh(), $bdCourier->saved($order->customer_phone));
    }

    /** Cash-on-delivery web orders from customers we haven't already delivered to twice. */
    public static function shouldCheck(Order $order): bool
    {
        if (setting('bdcourier_auto_check', '1') !== '1' || $order->payment_method !== 'cod' || $order->order_type === 'pos') {
            return false;
        }

        $trusted = Order::where('customer_phone', $order->customer_phone)->where('status', 'delivered')->count() >= 2;

        return ! $trusted;
    }

    public static function applyToFraudScore(Order $order, \App\Models\CustomerCourierCheck $check): void
    {
        $flags = $order->fraud_flags ?? [];
        foreach ($flags as $flag) {
            if (str_starts_with((string) $flag, 'Courier history:')) {
                return; // already applied
            }
        }

        $points = 0;
        $reports = count($check->reports ?? []);
        if ($reports > 0) {
            $points += 30;
            $flags[] = "Courier history: reported for fraud {$reports}× by other merchants (BD Courier)";
        }
        if ($check->total_parcels >= 3 && $check->success_ratio !== null && $check->success_ratio < 80) {
            $points += $check->success_ratio < 50 ? 30 : 15;
            $flags[] = "Courier history: only {$check->delivered} of {$check->total_parcels} parcels delivered ({$check->success_ratio}%)";
        }

        if ($points > 0) {
            $order->forceFill([
                'fraud_score' => min(100, (int) $order->fraud_score + $points),
                'fraud_flags' => $flags,
            ])->saveQuietly();
        }
    }
}
