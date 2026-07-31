<?php

namespace App\Services;

use App\Models\Blacklist;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class FraudDetectionService
{
    public function analyzeOrder(array $validatedData, float $totalAmount, string $ipAddress): array
    {
        $phone = trim((string) ($validatedData['customer_phone'] ?? ''));
        $email = trim((string) ($validatedData['customer_email'] ?? ''));

        // 1. Blacklist Check (Instant Block)
        if (Blacklist::isBlacklisted('phone', $phone)) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Your phone number is restricted. Please contact customer support to place this order.',
            ]);
        }

        if (Blacklist::isBlacklisted('email', $email)) {
            throw ValidationException::withMessages([
                'customer_email' => 'Your email address is restricted. Please contact customer support.',
            ]);
        }

        if (Blacklist::isBlacklisted('ip', $ipAddress)) {
            throw ValidationException::withMessages([
                'cart' => 'Order submission restricted from your network. Please contact customer support.',
            ]);
        }

        $score = 0;
        $flags = [];

        // 2. Duplicate Mobile Banking Transaction ID Check
        $paymentMethod = $validatedData['payment_method'] ?? 'cod';
        $txnId         = trim((string) ($validatedData['payment_txn_id'] ?? ''));

        if (in_array($paymentMethod, ['bkash', 'nagad', 'rocket'], true) && $txnId !== '') {
            $duplicateOrder = Order::where('payment_txn_id', $txnId)->first();
            if ($duplicateOrder) {
                $score += 40;
                $flags[] = "Duplicate TrxID: '{$txnId}' was previously used on Order #{$duplicateOrder->order_number}";
            }

            // TrxID Format Pattern Check (bKash & Nagad IDs are 10 chars)
            if (strlen($txnId) < 8 || strlen($txnId) > 16) {
                $score += 20;
                $flags[] = "Suspicious TrxID Format: Length is " . strlen($txnId) . " characters (expected 10 chars)";
            }
        }

        // 3. Order Flooding / Velocity Check from IP
        if ($ipAddress !== '') {
            $recentIpOrdersCount = Order::where('ip_address', $ipAddress)
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($recentIpOrdersCount >= 2) {
                $score += 35;
                $flags[] = "High Order Velocity: {$recentIpOrdersCount} orders submitted from this IP address in the past hour";
            }
        }

        // 4. Customer Order Cancellation & Return History
        if ($phone !== '') {
            $cancelledCount = Order::where('customer_phone', $phone)
                ->where('status', 'cancelled')
                ->count();

            if ($cancelledCount >= 2) {
                $score += 30;
                $flags[] = "Cancellation History: Customer has {$cancelledCount} previously cancelled/returned orders";
            }
        }

        // 5. High-Value COD Threshold Check
        if ($paymentMethod === 'cod' && $totalAmount >= 3000) {
            $score += 20;
            $flags[] = "High-Value COD Threshold: Order total is " . money($totalAmount) . " (requires phone confirmation)";
        }

        return [
            'score' => min(100, $score),
            'flags' => $flags,
        ];
    }
}
