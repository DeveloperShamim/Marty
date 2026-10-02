<?php

namespace App\Services\Courier;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoService
{
    public function isConfigured(): bool
    {
        return (bool) (
            setting('pathao_enabled', '0') === '1' &&
            setting('pathao_client_id') &&
            setting('pathao_client_secret') &&
            setting('pathao_username') &&
            setting('pathao_password') &&
            setting('pathao_store_id')
        );
    }

    protected function getBaseUrl(): string
    {
        return setting('pathao_env', 'production') === 'sandbox'
            ? 'https://courier-api-sandbox.pathao.com'
            : 'https://api-hermes.pathao.com';
    }

    /** Access tokens last for days, so one is cached instead of logging in for every request. */
    protected function getAccessToken(): ?string
    {
        $baseUrl = $this->getBaseUrl();
        $cacheKey = 'pathao_token_' . md5($baseUrl . '|' . setting('pathao_client_id') . '|' . setting('pathao_username'));
        if ($cached = cache()->get($cacheKey)) {
            return $cached;
        }

        try {
            $response = Http::asJson()->timeout(15)->post($baseUrl . '/aladdin/api/v1/issue-token', [
                'client_id'     => trim((string) setting('pathao_client_id')),
                'client_secret' => trim((string) setting('pathao_client_secret')),
                'username'      => trim((string) setting('pathao_username')),
                'password'      => trim((string) setting('pathao_password')),
                'grant_type'    => 'password',
            ]);

            if ($response->successful() && ($token = $response->json('access_token'))) {
                $ttl = max(60, (int) $response->json('expires_in', 3600) - 300);
                cache()->put($cacheKey, $token, now()->addSeconds($ttl));

                return $token;
            }

            Log::error('Pathao Token Error', ['response' => $response->json()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Pathao Token Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /** Current status of a booked parcel by its consignment ID. */
    public function trackOrder(Order $order): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Pathao API is not configured.'];
        }
        $token = $this->getAccessToken();
        if (! $token) {
            return ['success' => false, 'message' => 'Could not log in to the Pathao API.'];
        }

        try {
            $response = Http::withToken($token)->acceptJson()->timeout(15)
                ->get($this->getBaseUrl() . '/aladdin/api/v1/orders/' . rawurlencode((string) $order->courier_tracking_code) . '/info');
            $status = $response->json('data.order_status');

            if ($response->successful() && $status) {
                return ['success' => true, 'status' => (string) $status, 'message' => $response->json('data.order_status_slug') ?: null];
            }

            return ['success' => false, 'message' => 'Pathao: ' . ($response->json('message') ?? 'HTTP ' . $response->status())];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Pathao connection error: ' . $e->getMessage()];
        }
    }

    public function createOrder(Order $order): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Pathao API credentials (Client ID, Secret, Username, Password, Store ID) are missing or disabled in Admin Settings.',
            ];
        }

        $token = $this->getAccessToken();
        if (! $token) {
            return [
                'success' => false,
                'message' => 'Failed to authenticate with Pathao API. Please check your Pathao Client ID/Secret & Login credentials.',
            ];
        }

        // Only cash-on-delivery orders are collected; prepaid (bKash/Nagad/...) orders never are.
        $codAmount = $order->amountToCollect();
        $totalItems = $order->items->sum('quantity') ?: 1;

        // Clean BD phone number (e.g. 01XXXXXXXXX)
        $phone = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 2);
        }

        $payload = [
            'store_id'            => (int) setting('pathao_store_id'),
            'merchant_order_id'   => $order->order_number,
            'recipient_name'      => $order->customer_name,
            'recipient_phone'     => $phone,
            'recipient_address'   => $order->shipping_address . ($order->city ? ', ' . $order->city : ''),
            'delivery_type'       => 48, // 48 Hours / Standard
            'item_type'           => 2,  // Parcel
            'special_instruction' => $order->internal_note ?: 'Handle parcel carefully.',
            'item_quantity'       => $totalItems,
            'item_weight'         => 0.5,
            'item_description'    => 'Order #' . $order->order_number . ' from ' . site_name(),
            'amount_to_collect'   => (int) $codAmount,
        ];

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($this->getBaseUrl() . '/aladdin/api/v1/orders', $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && isset($data['data']['consignment_id'])) {
                $trackingCode = $data['data']['consignment_id'];

                return [
                    'success'       => true,
                    'tracking_code' => (string) $trackingCode,
                    'message'       => 'Order dispatched to Pathao Courier successfully.',
                    'raw'           => $data,
                ];
            }

            $errorMsg = $data['message'] ?? (isset($data['errors']) ? json_encode($data['errors']) : 'Failed to create order on Pathao.');

            return [
                'success' => false,
                'message' => 'Pathao API Error: ' . $errorMsg,
            ];
        } catch (\Throwable $e) {
            Log::error('Pathao Order Exception', ['error' => $e->getMessage(), 'order' => $order->order_number]);

            return [
                'success' => false,
                'message' => 'Pathao connection error: ' . $e->getMessage(),
            ];
        }
    }
}
