<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Courier\CourierStatusUpdater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Status callbacks from the couriers, set up in each courier's merchant panel:
 *   https://your-site/webhooks/courier/{steadfast|pathao|redx}/{secret}
 * (Steadfast may instead send the secret as "Authorization: Bearer <secret>".)
 */
class CourierWebhookController extends Controller
{
    /** Pathao asks for this header back before it activates a webhook URL. */
    private const PATHAO_INTEGRATION_HEADER = 'X-Pathao-Merchant-Webhook-Integration-Secret';
    private const PATHAO_INTEGRATION_SECRET = 'f3992ecc-59da-4cbe-a049-a13da2018d51';

    public function handle(Request $request, CourierStatusUpdater $updater, string $provider, ?string $token = null)
    {
        $secret = courier_webhook_secret();
        $given = $token ?? $request->bearerToken() ?? '';
        if (! hash_equals($secret, (string) $given)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook token.'], 401);
        }

        $p = $request->all();
        [$numbers, $codes, $status, $message] = match ($provider) {
            'steadfast' => [[$p['invoice'] ?? null], [$p['tracking_code'] ?? null, $p['consignment_id'] ?? null],
                $p['status'] ?? $p['delivery_status'] ?? null, $p['tracking_message'] ?? null],
            'pathao'    => [[$p['merchant_order_id'] ?? null], [$p['consignment_id'] ?? null],
                $p['order_status'] ?? $p['event'] ?? null, $p['reason'] ?? null],
            'redx'      => [[$p['invoice_number'] ?? null], [$p['tracking_number'] ?? null],
                $p['status'] ?? null, $p['message_en'] ?? null],
        };

        $order = $this->findOrder($provider, array_filter($numbers), array_filter($codes));

        if ($order && $status) {
            $updater->apply($order, (string) $status, $message, 'webhook');
        } elseif ($order && $message) {
            // e.g. a Steadfast "tracking_update" with only a message.
            $order->forceFill(['courier_status_message' => mb_substr($message, 0, 250), 'courier_synced_at' => now()])->save();
        } elseif (! $order) {
            Log::info('Courier webhook for unknown parcel', ['provider' => $provider, 'payload' => $p]);
        }

        // Always acknowledge, so the courier doesn't keep retrying.
        $response = response()->json(['status' => 'success', 'message' => 'Webhook received.'], $provider === 'pathao' ? 202 : 200);

        return $provider === 'pathao'
            ? $response->header(self::PATHAO_INTEGRATION_HEADER, self::PATHAO_INTEGRATION_SECRET)
            : $response;
    }

    private function findOrder(string $provider, array $numbers, array $codes): ?Order
    {
        if (! $numbers && ! $codes) {
            return null;
        }

        return Order::where('courier_name', $provider)
            ->where(fn ($q) => $q->when($numbers, fn ($q) => $q->orWhereIn('order_number', array_map('strval', $numbers)))
                ->when($codes, fn ($q) => $q->orWhereIn('courier_tracking_code', array_map('strval', $codes))))
            ->latest('id')
            ->first();
    }
}
