<?php

namespace App\Support;

use App\Models\Banner;
use App\Models\Product;

/**
 * "Finish setting up your store" on the admin Dashboard: the owner-facing jobs a new shop needs
 * before it can take orders. The server-side checks (APP_ENV, cron, backups) stay in
 * php artisan app:launch-check; this list only holds what the owner can fix in the admin.
 */
class SetupChecklist
{
    /** The logo and mark that ship with the code; a store still showing them has not been branded yet. */
    private const SHIPPED_LOGOS = ['uploads/brand/vant-logo.png', 'uploads/brand/vant-mark.png'];

    /** Setting that holds the steps the owner chose to skip, as a JSON list of keys. */
    public const SKIPPED_SETTING = 'setup_skipped_steps';

    /** @return list<string> keys the owner skipped */
    public static function skipped(): array
    {
        $keys = json_decode((string) setting(self::SKIPPED_SETTING, '[]'), true);

        return is_array($keys) ? array_values(array_filter($keys, 'is_string')) : [];
    }

    /** Skip a step (true) or bring it back (false). Unknown keys are ignored. */
    public static function setSkipped(string $key, bool $skip): void
    {
        if (! in_array($key, array_column(self::steps(), 'key'), true)) {
            return;
        }
        $keys = array_values(array_diff(self::skipped(), [$key]));
        if ($skip) {
            $keys[] = $key;
        }
        \App\Models\Setting::put(self::SKIPPED_SETTING, json_encode($keys));
    }

    /**
     * Every step, with `skipped` set when the owner skipped a step that is not done yet.
     * A skipped step counts as finished, so the card can still go away.
     *
     * @return list<array{key: string, done: bool, skipped: bool, finished: bool, label: string, hint: string, url: string, action: string}>
     */
    public static function items(): array
    {
        $skipped = self::skipped();

        return array_map(function ($step) use ($skipped) {
            $step['skipped'] = ! $step['done'] && in_array($step['key'], $skipped, true);
            $step['finished'] = $step['done'] || $step['skipped'];

            return $step;
        }, self::steps());
    }

    /**
     * @return list<array{key: string, done: bool, label: string, hint: string, url: string, action: string}>
     */
    private static function steps(): array
    {
        $logo = ltrim((string) setting('logo', ''), '/');
        $phone = preg_replace('/\D/', '', (string) setting('contact_phone', ''));

        $wallets = collect(['bkash', 'nagad', 'rocket'])->filter(fn ($w) => setting("pay_{$w}_enabled", '1') === '1');
        $walletsOk = $wallets->every(function ($w) {
            $n = preg_replace('/\D/', '', (string) setting("{$w}_number", ''));

            return strlen($n) === 11 && ! preg_match('/^01[3-9]0{8}$/', $n);
        });
        $anyPayment = setting('pay_cod_enabled', '1') === '1' || $wallets->isNotEmpty();

        $courier = collect([
            setting('steadfast_api_key'), setting('pathao_client_id'), setting('redx_api_token'),
        ])->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty();

        $mailer = (string) setting('mail_mailer', config('mail.default'));

        return [
            [
                'key' => 'brand',
                'done' => $logo !== '' && ! in_array($logo, self::SHIPPED_LOGOS, true),
                'label' => 'Add your logo and colours',
                'hint' => 'Upload your logo, then pick a style pack or “Colours from my logo”.',
                'url' => route('admin.settings.edit'),
                'action' => 'Brand & theme',
            ],
            [
                'key' => 'contact',
                'done' => $phone !== '' && ! str_ends_with($phone, '1700000000'),
                'label' => 'Add your phone number',
                'hint' => 'Shown in the header, footer and order emails so customers can call you.',
                'url' => route('admin.settings.edit'),
                'action' => 'Contact details',
            ],
            [
                'key' => 'payments',
                'done' => $anyPayment && $walletsOk,
                'label' => 'Set up how customers pay',
                'hint' => $walletsOk ? 'Turn on cash on delivery, bKash, Nagad or Rocket.' : 'A bKash, Nagad or Rocket option is on but its number is empty or a sample.',
                'url' => route('admin.settings.edit') . '#payments',
                'action' => 'Payments',
            ],
            [
                'key' => 'products',
                'done' => Product::published()->whereHas('images')->exists(),
                'label' => 'Add your first product with a photo',
                'hint' => 'Name, price, category and one photo are enough to start selling.',
                'url' => route('admin.products.create'),
                'action' => 'New product',
            ],
            [
                'key' => 'banner',
                'done' => Banner::live()->placement('hero')->whereNotNull('image')->where('image', '!=', '')->exists(),
                'label' => 'Add a homepage banner',
                'hint' => 'One wide photo (1500 × 800 px) at the top of your homepage.',
                'url' => route('admin.banners.create'),
                'action' => 'New banner',
            ],
            [
                'key' => 'courier',
                'done' => $courier,
                'label' => 'Connect a courier',
                'hint' => 'Steadfast, Pathao or RedX, to send parcels in one click and track them.',
                'url' => route('admin.integrations.index'),
                'action' => 'Integrations',
            ],
            [
                'key' => 'email',
                'done' => ! in_array($mailer, ['', 'log', 'array'], true),
                'label' => 'Connect email',
                'hint' => 'Needed for order emails, password resets and sign-up codes.',
                'url' => route('admin.integrations.index'),
                'action' => 'Integrations',
            ],
        ];
    }
}
