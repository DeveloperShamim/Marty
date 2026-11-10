<?php

namespace App\Support;

/**
 * The storefront's look beyond colours: a font pair and a corner style, picked in
 * Admin → Settings → Brand & theme. Both are applied as CSS variables by the store layout,
 * so changing them needs no CSS rebuild. Hind Siliguri always follows as the Bangla fallback.
 */
class StoreStyle
{
    /** key => [label, heading font, body font, Google Fonts families (without Hind Siliguri)] */
    public const FONTS = [
        'jakarta'  => ['Modern (Plus Jakarta Sans)', 'Plus Jakarta Sans', 'Plus Jakarta Sans', ['Plus+Jakarta+Sans:wght@400;500;600;700;800']],
        'inter'    => ['Clean (Inter)', 'Inter', 'Inter', ['Inter:wght@400;500;600;700;800']],
        'poppins'  => ['Friendly (Poppins)', 'Poppins', 'Poppins', ['Poppins:wght@400;500;600;700;800']],
        'nunito'   => ['Soft (Nunito)', 'Nunito', 'Nunito', ['Nunito:wght@400;500;600;700;800']],
        'oswald'   => ['Bold (Oswald headings)', 'Oswald', 'Inter', ['Oswald:wght@500;600;700', 'Inter:wght@400;500;600;700;800']],
        'playfair' => ['Elegant (Playfair headings)', 'Playfair Display', 'Inter', ['Playfair+Display:wght@500;600;700;800', 'Inter:wght@400;500;600;700;800']],
    ];

    /** key => [label, tags & small, buttons & inputs, cards, large panels] in px */
    public const CORNERS = [
        'rounded' => ['Rounded', 6, 10, 16, 24],
        'soft'    => ['Extra round', 8, 14, 22, 28],
        'sharp'   => ['Square', 2, 4, 6, 8],
    ];

    /** One-click looks: colours + font + corners together. key => [label, primary, dark, surface, font, corners] */
    public const PACKS = [
        'classic' => ['Classic', '#8B5A2B', '#2B1D14', '#FAF7F2', 'jakarta', 'rounded'],
        'soft'    => ['Soft', '#DB2777', '#4A1D35', '#FFF7FA', 'nunito', 'soft'],
        'bold'    => ['Bold', '#2563EB', '#0F172A', '#F8FAFC', 'oswald', 'sharp'],
        'minimal' => ['Minimal', '#111827', '#111827', '#FFFFFF', 'playfair', 'sharp'],
        'fresh'   => ['Fresh', '#16A34A', '#14532D', '#F7FBF5', 'poppins', 'rounded'],
    ];

    public static function font(): string
    {
        $key = (string) setting('theme_font', 'jakarta');

        return array_key_exists($key, self::FONTS) ? $key : 'jakarta';
    }

    public static function corners(): string
    {
        $key = (string) setting('theme_corners', 'rounded');

        return array_key_exists($key, self::CORNERS) ? $key : 'rounded';
    }

    /** Google Fonts stylesheet URL for the given font key (or the saved one), Bangla fallback included. */
    public static function fontsUrl(?string $key = null): string
    {
        $families = self::FONTS[$key ?? self::font()][3];
        $families[] = 'Hind+Siliguri:wght@400;500;600;700';

        return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', array_unique($families)) . '&display=swap';
    }

    /** Google Fonts URL with every font, for the admin preview. */
    public static function allFontsUrl(): string
    {
        $families = [];
        foreach (self::FONTS as $font) {
            array_push($families, ...$font[3]);
        }

        return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', array_unique($families)) . '&display=swap';
    }

    /** CSS custom properties for :root, e.g. "--font-body: 'Inter', 'Hind Siliguri', sans-serif; --r-md: 10px; …" */
    public static function cssVars(): string
    {
        [, $heading, $body] = self::FONTS[self::font()];
        [, $sm, $md, $card, $panel] = self::CORNERS[self::corners()];

        return "--font-display: '{$heading}', 'Hind Siliguri', sans-serif; "
            . "--font-body: '{$body}', 'Hind Siliguri', sans-serif; "
            . "--r-sm: {$sm}px; --r-md: {$md}px; --r-card: {$card}px; --r-panel: {$panel}px;";
    }
}
