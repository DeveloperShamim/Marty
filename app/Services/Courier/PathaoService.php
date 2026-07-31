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
            ? 'https://openapi-sandbox.pathao.com'
            : 'https://api-hermes.pathao.com';
    }

    protected function getAccessToken(): ?string
    {
        $baseUrl = $this->getBaseUrl();

        try {
            $response = Http::asJson()->timeout(15)->post($baseUrl . '/aladdin/api/v1/issue-token', [
                'client_id'     => trim((string) setting('pathao_client_id')),
                'client_secret' => trim((string) setting('pathao_client_secret')),
                'username'      => trim((string) setting('pathao_username')),
                'password'      => trim((string) setting('pathao_password')),
                'grant_type'    => 'password',
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('Pathao Token Error', ['response' => $response->json()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Pathao Token Exception', ['error' => $e->getMessage()]);
            return null;
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

        $codAmount = $order->payment_status === 'verified' ? 0 : (float) $order->total;
        $totalItems = $order->items->sum('quantity') ?: 1;

        $payload = [
            'store_id'          => (int) setting('pathao_store_id'),
            'merchant_order_id' => $order->order_number,
            'recipient_name'    => $order->customer_name,
            'recipient_phone'   => $order->customer_phone,
            'recipient_address' => $order->shipping_address . ($order->city ? ', ' . $order->city : ''),
            'recipient_city'    => (int) setting('pathao_default_city_id', 1), // Default Dhaka 1
            'recipient_zone'    => (int) setting('pathao_default_zone_id', 1),
            'delivery_type'     => 48, // 48 Hours / Standard
            'item_type'         => 2,  // Parcel
            'special_instruction' => $order->internal_note ?: 'Handle parcel carefully.',
            'item_quantity'     => $totalItems,
            'item_weight'       => 0.5,
            'amount_to_collect' => (int) $codAmount,
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
