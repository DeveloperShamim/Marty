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
            ? 'https://sandbox.redx.com.bd/v1.0.0-beta'
            : 'https://openapi.redx.com.bd/v1.0.0-beta';
    }

    public function getAreas(): array
    {
        $token = trim((string) setting('redx_api_token'));
        if (! $token) {
            return [];
        }

        if (str_starts_with(strtolower($token), 'bearer ')) {
            $token = trim(substr($token, 7));
        }

        $env = setting('redx_env', 'production');

        return \Illuminate\Support\Facades\Cache::remember("redx_areas_{$env}", now()->addHours(6), function () use ($token) {
            try {
                $response = Http::withHeaders([
                    'API-ACCESS-TOKEN' => 'Bearer ' . $token,
                    'Authorization'    => 'Bearer ' . $token,
                    'Accept'           => 'application/json',
                ])->timeout(10)->get($this->getBaseUrl() . '/areas');

                return $response->json('areas') ?? [];
            } catch (\Throwable $e) {
                Log::warning('RedX Areas Fetch Failed', ['error' => $e->getMessage()]);
                return [];
            }
        });
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

        // Strip leading 'Bearer ' if provided in token setting
        if (str_starts_with(strtolower($token), 'bearer ')) {
            $token = trim(substr($token, 7));
        }

        $codAmount = $order->payment_status === 'verified' ? 0 : (float) $order->total;

        // Clean BD phone number (e.g. 01XXXXXXXXX)
        $phone = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 2);
        }

        // Resolve delivery area and area ID
        $areas = $this->getAreas();
        $matchedArea = null;

        $searchTerms = strtolower(trim(($order->shipping_address ?? '') . ' ' . ($order->city ?? '')));
        $postalCode = trim((string) ($order->postal_code ?? ''));

        // 1. Try matching postal code
        if ($postalCode !== '') {
            foreach ($areas as $area) {
                if (isset($area['post_code']) && (string) $area['post_code'] === $postalCode) {
                    $matchedArea = $area;
                    break;
                }
            }
        }

        // 2. Try matching area name
        if (! $matchedArea && $searchTerms !== '') {
            foreach ($areas as $area) {
                if (! empty($area['name']) && str_contains($searchTerms, strtolower((string) $area['name']))) {
                    $matchedArea = $area;
                    break;
                }
            }
        }

        // 3. Fallback to default area from settings or first available area
        $deliveryAreaId = (int) setting('redx_default_area_id', 0);
        $deliveryAreaName = $order->city ?: 'Dhaka';

        if ($matchedArea) {
            $deliveryAreaId = (int) ($matchedArea['id'] ?? $deliveryAreaId);
            $deliveryAreaName = (string) ($matchedArea['name'] ?? $deliveryAreaName);
        } elseif (! empty($areas)) {
            $deliveryAreaId = (int) ($areas[0]['id'] ?? 1);
            $deliveryAreaName = (string) ($areas[0]['name'] ?? $deliveryAreaName);
        } elseif ($deliveryAreaId === 0) {
            $deliveryAreaId = 1; // Default fallback ID for RedX sandbox / central hub
        }

        $payload = [
            'customer_name'          => $order->customer_name,
            'customer_phone'         => $phone,
            'delivery_area'          => $deliveryAreaName,
            'delivery_area_id'       => $deliveryAreaId,
            'customer_address'       => $order->shipping_address . ($order->city ? ', ' . $order->city : ''),
            'merchant_invoice_id'    => $order->order_number,
            'cash_collection_amount' => (int) $codAmount,
            'parcel_weight'          => 500,
            'value'                  => (int) $order->total,
            'instruction'            => $order->internal_note ?: 'Order from ' . site_name(),
        ];

        try {
            $response = Http::withHeaders([
                'API-ACCESS-TOKEN' => 'Bearer ' . $token,
                'Authorization'    => 'Bearer ' . $token,
                'Accept'           => 'application/json',
                'Content-Type'     => 'application/json',
            ])
            ->timeout(15)
            ->post($this->getBaseUrl() . '/parcel', $payload);

            $data = $response->json() ?? [];

            if ($response->successful() && isset($data['tracking_id'])) {
                return [
                    'success'       => true,
                    'tracking_code' => (string) $data['tracking_id'],
                    'message'       => 'Order dispatched to RedX Courier successfully.',
                    'raw'           => $data,
                ];
            }

            // Unpack validation errors if provided by RedX
            $details = [];
            if (! empty($data['validation_errors']) && is_array($data['validation_errors'])) {
                foreach ($data['validation_errors'] as $item) {
                    if (is_array($item)) {
                        foreach ($item as $k => $msg) {
                            $details[] = is_string($k) ? "$k: $msg" : $msg;
                        }
                    } elseif (is_string($item)) {
                        $details[] = $item;
                    }
                }
            } elseif (! empty($data['errors'])) {
                if (is_array($data['errors'])) {
                    foreach ($data['errors'] as $k => $v) {
                        $details[] = is_string($k) ? "$k: " . (is_array($v) ? implode(', ', $v) : $v) : (is_array($v) ? implode(', ', $v) : $v);
                    }
                } else {
                    $details[] = (string) $data['errors'];
                }
            }

            $errorMsg = $data['message'] ?? 'Failed to create parcel on RedX.';
            if (! empty($details)) {
                $errorMsg .= ': ' . implode('; ', $details);
            }

            Log::error('RedX Parcel Error', [
                'order'    => $order->order_number,
                'payload'  => $payload,
                'response' => $data,
            ]);

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
