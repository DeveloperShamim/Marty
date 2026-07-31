<?php

namespace App\Services\Courier;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RedxService
{
    public function isConfigured(): bool
    {
        return (bool) (setting('redx_enabled', '0') === '1' && setting('redx_api_token'));
    }

    protected function getBaseUrl(): string
    {
        return setting('redx_env', 'production') === 'sandbox'
            ? 'https://sandbox.redx.com.bd/v1.0.0'
            : 'https://api.redx.com.bd/v1.0.0';
    }

    public function createOrder(Order $order): array
    {
        $token = trim((string) setting('redx_api_token'));

        if (! $token) {
            return [
                'success' => false,
                'message' => 'RedX API Access Token is missing in Admin Settings.',
            ];
        }

        $codAmount = $order->payment_status === 'verified' ? 0 : (float) $order->total;

        $payload = [
            'customer_name'          => $order->customer_name,
            'customer_phone'         => $order->customer_phone,
            'delivery_area'          => $order->city ?: 'Dhaka',
            'customer_address'       => $order->shipping_address . ($order->city ? ', ' . $order->city : ''),
            'merchant_invoice_id'    => $order->order_number,
            'cash_collection_amount' => $codAmount,
            'parcel_weight'          => 500,
            'instruction'             => $order->internal_note ?: 'Order from ' . site_name(),
        ];

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(15)
                ->post($this->getBaseUrl() . '/parcels', $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && isset($data['tracking_id'])) {
                return [
                    'success'       => true,
                    'tracking_code' => (string) $data['tracking_id'],
                    'message'       => 'Order dispatched to RedX Courier successfully.',
                    'raw'           => $data,
                ];
            }

            $errorMsg = $data['message'] ?? (isset($data['errors']) ? json_encode($data['errors']) : 'Failed to create parcel on RedX.');

            return [
                'success' => false,
                'message' => 'RedX API Error: ' . $errorMsg,
            ];
        } catch (\Throwable $e) {
            Log::error('RedX Order Exception', ['error' => $e->getMessage(), 'order' => $order->order_number]);

            return [
                'success' => false,
                'message' => 'RedX connection error: ' . $e->getMessage(),
            ];
        }
    }
}
