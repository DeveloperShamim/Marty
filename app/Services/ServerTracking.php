<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Purchases sent from the server, next to the browser pixels: Meta Conversions API and the GA4 Measurement
 * Protocol. They still count when an ad-blocker or iPhone privacy setting stops the browser pixel. The same
 * event id ("purchase-<order number>") and transaction id are used in the browser, so Meta and GA4 count
 * each order once.
 */
class ServerTracking
{
    public const META_API = 'https://graph.facebook.com/v21.0';
    public const GA4_API = 'https://www.google-analytics.com/mp/collect';

    public static function purchaseEventId(Order $order): string
    {
        return 'purchase-' . $order->order_number;
    }

    /**
     * Browser details taken at checkout, kept for the call made after the response:
     * ip, user agent, page url, and the _fbp / _fbc / _ga cookies.
     */
    public static function context(\Illuminate\Http\Request $request): array
    {
        $fbc = (string) $request->cookie('_fbc', '');
        if ($fbc === '' && ($clickId = (string) session('fbclid', '')) !== '') {
            $fbc = 'fb.1.' . (int) (microtime(true) * 1000) . '.' . $clickId;
        }

        return [
            'ip'         => (string) $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'url'        => (string) $request->headers->get('referer', url('/checkout')),
            'fbp'        => (string) $request->cookie('_fbp', ''),
            'fbc'        => $fbc,
            'ga'         => (string) $request->cookie('_ga', ''),
        ];
    }

    /** Sends the order's Purchase to every server-side destination that is set up. */
    public function sendPurchase(Order $order, array $context): void
    {
        $order->loadMissing('items');

        if (tracking_meta_capi_ready()) {
            $this->sendMeta([$this->metaPurchase($order, $context)]);
        }
        if (tracking_ga4_server_ready()) {
            $this->sendGa4Purchase($order, $context);
        }
    }

    public function metaPurchase(Order $order, array $context): array
    {
        $contents = $order->items->map(fn ($i) => [
            'id'         => (string) $i->product_id,
            'quantity'   => (int) $i->quantity,
            'item_price' => (float) $i->unit_price,
        ])->values()->all();

        return [
            'event_name'       => 'Purchase',
            'event_time'       => ($order->created_at ?? now())->timestamp,
            'event_id'         => self::purchaseEventId($order),
            'action_source'    => 'website',
            'event_source_url' => $context['url'] ?? url('/checkout'),
            'user_data'        => array_filter([
                'ph'                => self::hashed(self::phoneE164($order->customer_phone)),
                'em'                => self::hashed($order->customer_email),
                'fn'                => self::hashed(strtok((string) $order->customer_name, ' ') ?: null),
                'ct'                => self::hashed(preg_replace('/[^a-z]/', '', strtolower((string) $order->city)) ?: null),
                'country'           => self::hashed('bd'),
                'external_id'       => self::hashed($order->user_id ? 'user-' . $order->user_id : self::phoneE164($order->customer_phone)),
                'client_ip_address' => $context['ip'] ?? null,
                'client_user_agent' => $context['user_agent'] ?? null,
                'fbp'               => ($context['fbp'] ?? '') ?: null,
                'fbc'               => ($context['fbc'] ?? '') ?: null,
            ]),
            'custom_data'      => [
                'currency'     => setting('currency_code', 'BDT'),
                'value'        => (float) $order->total,
                'order_id'     => $order->order_number,
                'content_type' => 'product',
                'content_ids'  => array_column($contents, 'id'),
                'contents'     => $contents,
                'num_items'    => (int) $order->items->sum('quantity'),
            ],
        ];
    }

    /**
     * Posts events to Meta. Returns ['ok' => bool, 'message' => string]; never throws, so a Meta outage
     * can't break checkout.
     */
    public function sendMeta(array $events): array
    {
        $payload = ['data' => $events, 'access_token' => trim((string) setting('tracking_meta_capi_token', ''))];
        if (($code = trim((string) setting('tracking_meta_capi_test_code', ''))) !== '') {
            $payload['test_event_code'] = $code;
        }

        try {
            $res = Http::timeout(6)->asJson()->post(self::META_API . '/' . tracking_meta_pixel_id() . '/events', $payload);
            if ($res->successful()) {
                return ['ok' => true, 'message' => 'Meta received ' . (int) $res->json('events_received', 0) . ' event(s).'];
            }
            $message = (string) $res->json('error.message', 'HTTP ' . $res->status());
        } catch (\Throwable $e) {
            $message = $e->getMessage();
        }
        Log::warning('Meta Conversions API: ' . $message);

        return ['ok' => false, 'message' => $message];
    }

    public function sendGa4Purchase(Order $order, array $context): bool
    {
        $body = [
            'client_id' => self::gaClientId($context['ga'] ?? '') ?? ('server.' . $order->id),
            'events'    => [[
                'name'   => 'purchase',
                'params' => [
                    'transaction_id' => $order->order_number,
                    'currency'       => setting('currency_code', 'BDT'),
                    'value'          => (float) $order->total,
                    'shipping'       => (float) $order->shipping_charge,
                    'tax'            => (float) $order->tax,
                    'coupon'         => $order->coupon_code ?: null,
                    'items'          => $order->items->map(fn ($i) => [
                        'item_id'      => (string) $i->product_id,
                        'item_name'    => $i->product_name,
                        'item_variant' => $i->variant,
                        'price'        => (float) $i->unit_price,
                        'quantity'     => (int) $i->quantity,
                    ])->values()->all(),
                ],
            ]],
        ];
        if ($order->user_id) {
            $body['user_id'] = 'user-' . $order->user_id;
        }

        try {
            $res = Http::timeout(6)->asJson()->post(self::GA4_API . '?' . http_build_query([
                'measurement_id' => tracking_ga4_id(),
                'api_secret'     => trim((string) setting('tracking_ga4_api_secret', '')),
            ]), $body);

            return $res->successful();
        } catch (\Throwable $e) {
            Log::warning('GA4 Measurement Protocol: ' . $e->getMessage());

            return false;
        }
    }

    /** "GA1.1.123456.1700000000" → "123456.1700000000" */
    public static function gaClientId(string $cookie): ?string
    {
        return preg_match('/^GA\d+\.\d+\.(\d+\.\d+)$/', trim($cookie), $m) ? $m[1] : null;
    }

    /** Bangladeshi mobile number in Meta's format: 8801XXXXXXXXX. */
    public static function phoneE164(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (preg_match('/^(?:880|0)?(1\d{9})$/', $digits, $m)) {
            return '880' . $m[1];
        }

        return $digits !== '' ? $digits : null;
    }

    public static function hashed(?string $value): ?string
    {
        $value = strtolower(trim((string) $value));

        return $value === '' ? null : hash('sha256', $value);
    }
}
