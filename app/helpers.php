<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Read a value from the settings table. */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('testing_mode')) {
    /**
     * Whether admin write actions are locked (TESTING_MODE in Site 3/shop/.env).
     * Reads live env / .env file so toggling works without config:cache surprises.
     */
    function testing_mode(): bool
    {
        return once(function () {
            foreach ([$_ENV, $_SERVER] as $bag) {
                if (array_key_exists('TESTING_MODE', $bag) && $bag['TESTING_MODE'] !== '' && $bag['TESTING_MODE'] !== null) {
                    return filter_var($bag['TESTING_MODE'], FILTER_VALIDATE_BOOLEAN);
                }
            }

            $path = base_path('.env');
            if (is_readable($path)) {
                $lines = file($path, FILE_IGNORE_NEW_LINES);
                if ($lines !== false) {
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line === '' || str_starts_with($line, '#')) {
                            continue;
                        }
                        if (! preg_match('/^TESTING_MODE\s*=\s*(.*)$/i', $line, $m)) {
                            continue;
                        }
                        $raw = trim($m[1], " \t\"'");

                        return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
                    }
                }
            }

            return filter_var(config('app.testing_mode', false), FILTER_VALIDATE_BOOLEAN);
        });
    }
}

if (! function_exists('money')) {
    /** Format an amount using the store currency symbol from Settings. */
        function money($amount): string
    {
        $symbol = trim((string) setting('currency_symbol', '৳'));
        if ($symbol === '') {
            $symbol = '৳';
        }

        return $symbol . number_format((float) $amount, 0);
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol(): string
    {
        $symbol = trim((string) setting('currency_symbol', '৳'));

        return $symbol !== '' ? $symbol : '৳';
    }
}

if (! function_exists('shipping_zone_label')) {
    function shipping_zone_label(string $zone): string
    {
        return match ($zone) {
            'inside_dhaka' => (string) setting('shipping_inside_label', 'Inside Dhaka'),
            'outside_dhaka' => (string) setting('shipping_outside_label', 'Outside Dhaka'),
            default => $zone,
        };
    }
}

if (! function_exists('configure_mail_from_settings')) {
    /**
     * Apply the admin-managed mail configuration at runtime and return the active mailer.
     * Falls back to the "log" mailer (writes emails to the Laravel log) when SMTP is not set up,
     * so the OTP flow keeps working even before real credentials are entered.
     */
    function configure_mail_from_settings(): string
    {
        $mailer = setting('mail_mailer', 'log') ?: 'log';

        if ($mailer === 'smtp') {
            $encryption = setting('mail_encryption', 'tls');
            config([
                'mail.default'                 => 'smtp',
                'mail.mailers.smtp.host'       => setting('mail_host'),
                'mail.mailers.smtp.port'       => (int) setting('mail_port', 587),
                'mail.mailers.smtp.username'   => setting('mail_username') ?: null,
                'mail.mailers.smtp.password'   => setting('mail_password') ?: null,
                'mail.mailers.smtp.encryption' => ($encryption === 'none') ? null : $encryption,
            ]);
        } else {
            config(['mail.default' => 'log']);
            $mailer = 'log';
        }

        config([
            'mail.from.address' => setting('mail_from_address') ?: config('mail.from.address'),
            'mail.from.name'    => setting('mail_from_name') ?: site_name(),
        ]);

        return $mailer;
    }
}

if (! function_exists('otp_enabled')) {
    /** Whether email OTP verification is switched on in the admin panel. */
    function otp_enabled(): bool
    {
        return (string) setting('otp_enabled', '1') === '1';
    }
}

if (! function_exists('site_name')) {
    /** Canonical storefront / admin site name (Settings → site_name). */
    function site_name(): string
    {
        $name = trim((string) setting('site_name', config('app.name', 'FreshKart')));

        return $name !== '' ? $name : 'FreshKart';
    }
}

if (! function_exists('favicon_url')) {
    /** The site favicon — a custom upload if set, otherwise the bundled FreshKart icon. */
    function favicon_url(): string
    {
        $f = setting('favicon');
        if ($f) {
            return (str_starts_with($f, 'http://') || str_starts_with($f, 'https://'))
                ? $f
                : asset('storage/' . ltrim($f, '/'));
        }

        return asset('theme/favicon.svg');
    }
}

if (! function_exists('logo_url')) {
    /**
     * The site logo URL.
     * Custom admin upload wins; otherwise the bundled FreshKart mark.
     */
    function logo_url(string $variant = 'default'): string
    {
        $l = setting('logo');
        if ($l) {
            return (str_starts_with($l, 'http://') || str_starts_with($l, 'https://'))
                ? $l
                : asset('storage/' . ltrim($l, '/'));
        }

        return asset('theme/logo.svg');
    }
}

if (! function_exists('has_custom_logo')) {
    /** Whether an admin-uploaded logo is set (not the bundled default). */
    function has_custom_logo(): bool
    {
        return (bool) setting('logo');
    }
}

if (! function_exists('tracking_gtm_id')) {
    /** Valid Google Tag Manager container ID (GTM-XXXX), or null when unset/invalid. */
    function tracking_gtm_id(): ?string
    {
        $id = strtoupper(trim((string) setting('tracking_gtm_id', '')));

        return preg_match('/^GTM-[A-Z0-9]+$/', $id) ? $id : null;
    }
}

if (! function_exists('tracking_ga4_id')) {
    /** Valid GA4 measurement ID (G-XXXX), or null when unset/invalid. */
    function tracking_ga4_id(): ?string
    {
        $id = strtoupper(trim((string) setting('tracking_ga4_id', '')));

        return preg_match('/^G-[A-Z0-9]+$/', $id) ? $id : null;
    }
}

if (! function_exists('tracking_meta_pixel_id')) {
    /** Valid Meta (Facebook) Pixel ID (numeric), or null when unset/invalid. */
    function tracking_meta_pixel_id(): ?string
    {
        $id = trim((string) setting('tracking_meta_pixel_id', ''));

        return preg_match('/^\d+$/', $id) ? $id : null;
    }
}

if (! function_exists('tracking_any_enabled')) {
    /** Whether any storefront tracking tag is configured. */
    function tracking_any_enabled(): bool
    {
        return tracking_gtm_id() || tracking_ga4_id() || tracking_meta_pixel_id();
    }
}

if (! function_exists('image_url')) {
    /**
     * Resolve an image reference to a usable URL.
     * - Full URLs (http…) are returned as-is.
     * - Stored paths resolve against the public storage disk.
     * - Missing values fall back to a neutral local SVG placeholder (not stock photos).
     */
    function image_url(?string $path, string $seed = 'FreshKart'): string
    {
        if ($path) {
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            // Direct public uploads (e.g. uploads/products/abc.jpg)
            if (str_starts_with($path, '/uploads/') || str_starts_with($path, 'uploads/')) {
                return asset(ltrim($path, '/'));
            }
            if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
                return asset(ltrim($path, '/'));
            }
            return asset('storage/' . ltrim($path, '/'));
        }

        $label = e(mb_substr(trim($seed) !== '' ? $seed : 'No image', 0, 28));
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="720" viewBox="0 0 600 720">
  <rect width="600" height="720" fill="#f5f5f4"/>
  <rect x="40" y="40" width="520" height="640" rx="24" fill="#e7e5e4"/>
  <text x="300" y="370" text-anchor="middle" fill="#a8a29e" font-family="system-ui,sans-serif" font-size="26">{$label}</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
    }
}
