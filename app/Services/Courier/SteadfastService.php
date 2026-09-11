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

        // Clean BD phone number (e.g. 01XXXXXXXXX)
        $phone = preg_replace('/[^0-9]/', '', (string) $order->customer_phone);
        if (str_starts_with($phone, '880')) {
            $phone = substr($phone, 2);
        }

        $payload = [
            'invoice'           => $order->order_number,
            'recipient_name'    => $order->customer_name,
            'recipient_phone'   => $phone,
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

    /**
     * Live courier delivery success & fraud check via Steadfast API
     */
    public function checkDeliveryHistory(?string $phone, bool $forceRefresh = false): array
    {
        $apiKey = trim((string) setting('steadfast_api_key'));
        $secretKey = trim((string) setting('steadfast_secret_key'));

        if (! $apiKey || ! $secretKey) {
            return [
                'configured'   => false,
                'success'      => false,
                'message'      => 'Steadfast API Key or Secret Key is not configured.',
                'phone'        => $phone,
                'total'        => 0,
                'delivered'    => 0,
                'cancelled'    => 0,
                'fraud'        => 0,
                'rate'         => null,
                'risk_level'   => 'unconfigured',
                'rating_label' => 'API Not Configured',
                'rating_color' => 'slate',
            ];
        }

        // Clean BD phone number (e.g. 01XXXXXXXXX)
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $phone);
        if (str_starts_with($cleanPhone, '880')) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        if (strlen($cleanPhone) < 10) {
            return [
                'configured'   => true,
                'success'      => false,
                'message'      => 'Invalid phone number format.',
                'phone'        => $phone,
                'total'        => 0,
                'delivered'    => 0,
                'cancelled'    => 0,
                'fraud'        => 0,
                'rate'         => null,
                'risk_level'   => 'unknown',
                'rating_label' => 'Invalid Phone Number',
                'rating_color' => 'slate',
            ];
        }

        $cacheKey = "steadfast_fraud_check_{$cleanPhone}";
        if ($forceRefresh) {
            cache()->forget($cacheKey);
        }

        return cache()->remember($cacheKey, now()->addMinutes(30), function () use ($apiKey, $secretKey, $cleanPhone) {
            try {
                $response = Http::withHeaders([
                    'Api-Key'      => $apiKey,
                    'Secret-Key'   => $secretKey,
                    'Content-Type' => 'application/json',
                ])->timeout(10)->get($this->baseUrl . '/fraud_check/' . urlencode($cleanPhone));

                if (! $response->successful()) {
                    return [
                        'configured'   => true,
                        'success'      => false,
                        'message'      => 'Steadfast API returned HTTP ' . $response->status(),
                        'phone'        => $cleanPhone,
                        'total'        => 0,
                        'delivered'    => 0,
                        'cancelled'    => 0,
                        'fraud'        => 0,
                        'rate'         => null,
                        'risk_level'   => 'unknown',
                        'rating_label' => 'Check Failed (HTTP ' . $response->status() . ')',
                        'rating_color' => 'slate',
                    ];
                }

                $data = $response->json() ?? [];

                $total = (int) ($data['Total_parcels'] ?? $data['total_parcels'] ?? 0);
                $delivered = (int) ($data['total_delivered'] ?? 0);
                $cancelled = (int) ($data['total_cancelled'] ?? 0);
                $fraudReports = is_array($data['total_fraud_reports'] ?? null)
                    ? count($data['total_fraud_reports'])
                    : (int) ($data['total_fraud_reports'] ?? 0);

                $rate = $total > 0 ? round(($delivered / $total) * 100, 1) : null;

                // Risk categorization
                if ($total === 0) {
                    $riskLevel = 'new';
                    $ratingLabel = 'New Buyer (No History)';
                    $ratingColor = 'slate';
                } elseif ($fraudReports > 0 || ($rate !== null && $rate < 50)) {
                    $riskLevel = 'high';
                    $ratingLabel = 'High Return Risk';
                    $ratingColor = 'rose';
                } elseif ($rate !== null && $rate < 80) {
                    $riskLevel = 'medium';
                    $ratingLabel = 'Moderate Delivery Ratio';
                    $ratingColor = 'amber';
                } else {
                    $riskLevel = 'low';
                    $ratingLabel = 'High Success Ratio';
                    $ratingColor = 'emerald';
                }

                return [
                    'configured'   => true,
                    'success'      => true,
                    'phone'        => $cleanPhone,
                    'total'        => $total,
                    'delivered'    => $delivered,
                    'cancelled'    => $cancelled,
                    'fraud'        => $fraudReports,
                    'rate'         => $rate,
                    'risk_level'   => $riskLevel,
                    'rating_label' => $ratingLabel,
                    'rating_color' => $ratingColor,
                    'raw'          => $data,
                ];
            } catch (\Throwable $e) {
                Log::warning('Steadfast fraud check exception: ' . $e->getMessage(), ['phone' => $cleanPhone]);

                return [
                    'configured'   => true,
                    'success'      => false,
                    'message'      => 'Could not reach Steadfast server.',
                    'phone'        => $cleanPhone,
                    'total'        => 0,
                    'delivered'    => 0,
                    'cancelled'    => 0,
                    'fraud'        => 0,
                    'rate'         => null,
                    'risk_level'   => 'unknown',
                    'rating_label' => 'Connection Timeout',
                    'rating_color' => 'slate',
                ];
            }
        });
    }
}
