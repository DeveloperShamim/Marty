<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /** Read a value from the settings table. */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('generate_3_color_matching_theme')) {
    /** Automatically calculate hover states, border highlights, and soft tints from 3 user core colors. */
    function generate_3_color_matching_theme(?string $primaryHex = null, ?string $darkHex = null, ?string $surfaceHex = null): array
    {
        $primary = ltrim(trim((string) ($primaryHex ?: setting('theme_primary_color', '#E8751B'))), '#');
        $dark    = ltrim(trim((string) ($darkHex    ?: setting('theme_dark_color', '#353535'))), '#');
        $surface = ltrim(trim((string) ($surfaceHex ?: setting('theme_surface_color', '#F8FAFC'))), '#');

        if (strlen($primary) === 3) $primary = $primary[0].$primary[0].$primary[1].$primary[1].$primary[2].$primary[2];
        if (strlen($dark) === 3)    $dark    = $dark[0].$dark[0].$dark[1].$dark[1].$dark[2].$dark[2];
        if (strlen($surface) === 3) $surface = $surface[0].$surface[0].$surface[1].$surface[1].$surface[2].$surface[2];

        if (! preg_match('/^[a-fA-F0-9]{6}$/', $primary)) $primary = 'E8751B';
        if (! preg_match('/^[a-fA-F0-9]{6}$/', $dark))    $dark    = '353535';
        if (! preg_match('/^[a-fA-F0-9]{6}$/', $surface) || $surface === '000000') $surface = 'F8FAFC';

        $hexToRgb = function ($h) {
            return [hexdec(substr($h, 0, 2)), hexdec(substr($h, 2, 2)), hexdec(substr($h, 4, 2))];
        };

        $rgbToHex = function ($r, $g, $b) {
            return sprintf('#%02X%02X%02X', min(255, max(0, round($r))), min(255, max(0, round($g))), min(255, max(0, round($b))));
        };

        [$pr, $pg, $pb] = $hexToRgb($primary);
        $primaryHover  = $rgbToHex($pr * 0.82, $pg * 0.82, $pb * 0.82);
        $primarySoftBg = $rgbToHex($pr * 0.12 + 255 * 0.88, $pg * 0.12 + 255 * 0.88, $pb * 0.12 + 255 * 0.88);
        $primaryBorder = $rgbToHex($pr * 0.30 + 255 * 0.70, $pg * 0.30 + 255 * 0.70, $pb * 0.30 + 255 * 0.70);

        [$dr, $dg, $db] = $hexToRgb($dark);
        $darkHover = $rgbToHex($dr * 0.85, $dg * 0.85, $db * 0.85);

        return [
            'primary'         => '#' . strtoupper($primary),
            'primary_hover'   => $primaryHover,
            'primary_soft_bg' => $primarySoftBg,
            'primary_border'  => $primaryBorder,
            'dark'            => '#' . strtoupper($dark),
            'dark_hover'      => $darkHover,
            'surface'         => '#' . strtoupper($surface),
        ];
    }
}

if (! function_exists('generate_brand_color_scale')) {
    /** Generate a harmonious 10-step Tailwind color scale (50-900) from any Hex color. */
    function generate_brand_color_scale(string $hex): array
    {
        $cleanHex = ltrim(trim($hex), '#');
        if (strlen($cleanHex) === 3) {
            $cleanHex = $cleanHex[0].$cleanHex[0].$cleanHex[1].$cleanHex[1].$cleanHex[2].$cleanHex[2];
        }
        if (! preg_match('/^[a-fA-F0-9]{6}$/', $cleanHex)) {
            $cleanHex = 'E8751B';
        }

        $r = hexdec(substr($cleanHex, 0, 2)) / 255;
        $g = hexdec(substr($cleanHex, 2, 2)) / 255;
        $b = hexdec(substr($cleanHex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if ($d == 0) {
            $h = $s = 0;
        } else {
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
            switch ($max) {
                case $r: $h = ($g - $b) / $d + ($g < $b ? 6 : 0); break;
                case $g: $h = ($b - $r) / $d + 2; break;
                case $b: $h = ($r - $g) / $d + 4; break;
            }
            $h /= 6;
        }

        $hDeg = $h * 360;
        $sPct = $s * 100;
        $lPct = $l * 100;

        $hslToHex = function ($h, $s, $l) {
            $h /= 360; $s /= 100; $l /= 100;
            if ($s == 0) {
                $r = $g = $b = $l;
            } else {
                $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
                $p = 2 * $l - $q;
                $calc = function ($p, $q, $t) {
                    if ($t < 0) $t += 1;
                    if ($t > 1) $t -= 1;
                    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
                    if ($t < 1/2) return $q;
                    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
                    return $p;
                };
                $r = $calc($p, $q, $h + 1/3);
                $g = $calc($p, $q, $h);
                $b = $calc($p, $q, $h - 1/3);
            }
            return sprintf('#%02x%02x%02x', round($r * 255), round($g * 255), round($b * 255));
        };

        $base = '#' . strtoupper($cleanHex);

        return [
            50  => $hslToHex($hDeg, min(100, $sPct * 0.5), 97),
            100 => $hslToHex($hDeg, min(100, $sPct * 0.6), 93),
            200 => $hslToHex($hDeg, min(100, $sPct * 0.7), 84),
            300 => $hslToHex($hDeg, min(100, $sPct * 0.85), 72),
            400 => $hslToHex($hDeg, $sPct, max(20, $lPct * 1.15)),
            500 => $base,
            600 => $base,
            700 => $hslToHex($hDeg, min(100, $sPct * 1.05), max(15, $lPct * 0.80)),
            800 => $hslToHex($hDeg, min(100, $sPct * 1.1), max(12, $lPct * 0.60)),
            900 => $hslToHex($hDeg, min(100, $sPct * 1.15), max(8, $lPct * 0.40)),
        ];
    }
}

if (! function_exists('extract_dominant_color_from_image')) {
    /** Automatically extract dominant vibrant primary brand color from a logo image file. */
    function extract_dominant_color_from_image(string $filePath): ?string
    {
        if (! file_exists($filePath) || ! is_readable($filePath)) {
            return null;
        }

        $info = @getimagesize($filePath);
        if (! $info) {
            return null;
        }

        $mime = $info['mime'] ?? '';
        $im = match ($mime) {
            'image/png'  => @imagecreatefrompng($filePath),
            'image/jpeg' => @imagecreatefromjpeg($filePath),
            'image/webp' => @imagecreatefromwebp($filePath),
            default      => null,
        };

        if (! $im) {
            return null;
        }

        $width = imagesx($im);
        $height = imagesy($im);

        $sampleStepX = max(1, (int) floor($width / 80));
        $sampleStepY = max(1, (int) floor($height / 80));

        $colorCounts = [];

        for ($x = 0; $x < $width; $x += $sampleStepX) {
            for ($y = 0; $y < $height; $y += $sampleStepY) {
                $rgba = imagecolorat($im, $x, $y);
                $colors = imagecolorsforindex($im, $rgba);

                if (isset($colors['alpha']) && $colors['alpha'] > 70) {
                    continue;
                }

                $r = $colors['red'];
                $g = $colors['green'];
                $b = $colors['blue'];

                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $diff = $max - $min;

                if ($max > 240 && $min > 240) continue;
                if ($max < 30 && $min < 30) continue;
                if ($diff < 15) continue;

                $qr = (int) (round($r / 16) * 16);
                $qg = (int) (round($g / 16) * 16);
                $qb = (int) (round($b / 16) * 16);

                $hex = sprintf('#%02X%02X%02X', min(255, $qr), min(255, $qg), min(255, $qb));
                $colorCounts[$hex] = ($colorCounts[$hex] ?? 0) + 1;
            }
        }

        imagedestroy($im);

        if (empty($colorCounts)) {
            return null;
        }

        arsort($colorCounts);
        return key($colorCounts);
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
        $name = trim((string) setting('site_name', config('app.name', 'ShodeshiFood')));

        return $name !== '' ? $name : 'ShodeshiFood';
    }
}

if (! function_exists('favicon_url')) {
    /** The site favicon — a custom upload if set, otherwise fallback icon. */
    function favicon_url(): string
    {
        $f = setting('favicon');
        if ($f) {
            if (str_starts_with($f, 'http://') || str_starts_with($f, 'https://')) {
                return $f;
            }
            if (str_starts_with($f, '/uploads/') || str_starts_with($f, 'uploads/')) {
                return asset(ltrim($f, '/'));
            }
            return asset('storage/' . ltrim($f, '/'));
        }

        return asset('uploads/favicon.png');
    }
}

if (! function_exists('logo_url')) {
    /**
     * The site logo URL.
     * Custom logo setting wins; otherwise the uploaded logo.
     */
    function logo_url(string $variant = 'default'): string
    {
        $l = setting('logo');
        if ($l) {
            if (str_starts_with($l, 'http://') || str_starts_with($l, 'https://')) {
                return $l;
            }
            if (str_starts_with($l, '/uploads/') || str_starts_with($l, 'uploads/')) {
                return asset(ltrim($l, '/'));
            }
            return asset('storage/' . ltrim($l, '/'));
        }

        return asset('uploads/logo.png');
    }
}

if (! function_exists('has_custom_logo')) {
    /** Whether an admin-uploaded logo is set (not the bundled default). */
    function has_custom_logo(): bool
    {
        return (bool) setting('logo');
    }
}

if (! function_exists('store_phone')) {
    /** Store contact phone number (Settings → contact_phone). */
    function store_phone(): string
    {
        $phone = trim((string) setting('contact_phone', ''));

        return $phone !== '' ? $phone : '01700000000';
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
