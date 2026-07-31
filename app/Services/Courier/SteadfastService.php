<?php

namespace App\Services\Courier;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SteadfastService
{
    protected string $baseUrl = 'https://portal.steadfast.com.bd/api/v1';

    public function isConfigured(): bool
    {
        return (bool) (setting('steadfast_enabled', '0') === '1' && setting('steadfast_api_key') && setting('steadfast_secret_key'));
    }

    public function createOrder(Order $order): array
    {
        $apiKey = trim((string) setting('steadfast_api_key'));
        $secretKey = trim((string) setting('steadfast_secret_key'));

        if (! $apiKey || ! $secretKey) {
            return [
                'success' => false,
                'message' => 'Steadfast API Key or Secret Key is missing in Admin Settings.',
            ];
        }

        $codAmount = $order->payment_status === 'verified' ? 0 : (float) $order->total;

        $payload = [
            'invoice'           => $order->order_number,
            'recipient_name'    => $order->customer_name,
            'recipient_phone'   => $order->customer_phone,
            'recipient_address' => $order->shipping_address . ($order->city ? ', ' . $order->city : ''),
            'cod_amount'        => $codAmount,
            'note'              => $order->internal_note ?: 'Order from ' . site_name(),
        ];

        try {
            $response = Http::withHeaders([
                'Api-Key'      => $apiKey,
                'Secret-Key'   => $secretKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post($this->baseUrl . '/create_order', $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && isset($data['status']) && (int) $data['status'] === 200) {
                $consignment = $data['consignment'] ?? [];
                $trackingCode = $consignment['tracking_code'] ?? $consignment['consignment_id'] ?? $order->order_number;

                return [
                    'success'       => true,
                    'tracking_code' => (string) $trackingCode,
                    'message'       => $data['message'] ?? 'Order dispatched to Steadfast successfully.',
                    'raw'           => $data,
                ];
            }

            $errorMsg = $data['message'] ?? ($data['errors'] ? json_encode($data['errors']) : 'Failed to connect to Steadfast API.');

            return [
                'success' => false,
                'message' => 'Steadfast API Error: ' . $errorMsg,
            ];
        } catch (\Throwable $e) {
            Log::error('Steadfast API Exception', ['error' => $e->getMessage(), 'order' => $order->order_number]);

            return [
                'success' => false,
                'message' => 'Steadfast connection error: ' . $e->getMessage(),
            ];
        }
    }
}
