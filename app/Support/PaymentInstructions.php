<?php

namespace App\Support;

/**
 * Short step-by-step payment instructions for bKash / Nagad / Rocket, worded for the kind of
 * number the shop gave (Store Settings → Payments): personal, merchant or agent.
 */
class PaymentInstructions
{
    public const TYPES = ['personal' => 'Personal (Send Money)', 'merchant' => 'Merchant (Payment)', 'agent' => 'Agent (Cash Out)'];

    private const WALLETS = [
        'bkash'  => ['name' => 'bKash',  'ussd' => '*247#', 'merchant' => 'Payment'],
        'nagad'  => ['name' => 'Nagad',  'ussd' => '*167#', 'merchant' => 'Merchant Pay'],
        'rocket' => ['name' => 'Rocket', 'ussd' => '*322#', 'merchant' => 'Merchant Pay'],
    ];

    public static function type(string $method): string
    {
        $type = (string) setting("{$method}_account_type", 'personal');

        return array_key_exists($type, self::TYPES) ? $type : 'personal';
    }

    /** The menu option the customer taps in their wallet app. */
    public static function action(string $method): string
    {
        return match (self::type($method)) {
            'merchant' => self::WALLETS[$method]['merchant'] ?? 'Payment',
            'agent'    => 'Cash Out',
            default    => 'Send Money',
        };
    }

    /**
     * Steps as HTML-safe strings; "{amount}" is left in for the checkout script to fill in live.
     *
     * @return list<string>
     */
    public static function steps(string $method): array
    {
        $w = self::WALLETS[$method] ?? null;
        if (! $w) {
            return [];
        }
        $number = e((string) setting("{$method}_number", ''));
        $action = e(self::action($method));
        $type = self::type($method);

        $steps = [
            "Open the <b>{$w['name']}</b> app (or dial <b>{$w['ussd']}</b>).",
            "Tap <b>{$action}</b> and enter <b class=\"font-mono\">{$number}</b>.",
            'Enter the amount <b>{amount}</b>'
                . ($type === 'merchant' ? ' and write your <b>mobile number</b> as the reference.' : '.')
                . ($type === 'agent' ? ' The Cash Out fee is added by ' . $w['name'] . '.' : ''),
            'Confirm with your PIN, then type the <b>TrxID</b> from the confirmation SMS below.',
        ];

        return $steps;
    }

    /** Phone number customers can call for help with an order. */
    public static function hotline(): string
    {
        return trim((string) (setting('order_hotline') ?: setting('contact_phone', '')));
    }

    /** "tel:" link for the hotline (digits and a leading + only). */
    public static function hotlineHref(): string
    {
        return 'tel:' . preg_replace('/[^\d+]/', '', self::hotline());
    }
}
