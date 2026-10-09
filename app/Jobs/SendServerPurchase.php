<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ServerTracking;
use Illuminate\Foundation\Bus\Dispatchable;

/** Runs right after the thank-you redirect is sent (no queue worker needed): server-side Purchase to Meta and GA4. */
class SendServerPurchase
{
    use Dispatchable;

    public function __construct(public int $orderId, public array $context) {}

    public function handle(ServerTracking $tracking): void
    {
        if ($order = Order::find($this->orderId)) {
            $tracking->sendPurchase($order, $this->context);
        }
    }
}
