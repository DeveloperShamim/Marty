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

    /**
     * @return list<array{key: string, done: bool, label: string, hint: string, url: string, action: string}>
     */
    public static function items(): array
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
