<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Cleans HTML written in the admin (product descriptions) before it is shown on the store.
 * Keeps what the description editor makes (text formatting, links, images, tables, uploaded videos,
 * YouTube / Vimeo embeds) and drops scripts, event handlers (onclick=…) and javascript: links, so a
 * staff account or an imported CSV can't run code in a shopper's or the owner's browser.
 */
class SafeHtml
{
    private static ?HtmlSanitizer $sanitizer = null;

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return self::sanitizer()->sanitize($html);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->allowRelativeMedias()
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowMediaSchemes(['https', 'http'])
                ->allowAttribute('class', '*')
                ->allowAttribute('style', '*')
                ->allowElement('video', ['src', 'controls', 'playsinline', 'preload', 'poster', 'muted', 'loop', 'class', 'style'])
                ->allowElement('source', ['src', 'type'])
                ->allowElement('iframe', ['src', 'allow', 'allowfullscreen', 'class', 'style', 'title', 'loading'])
                ->withAttributeSanitizer(new class implements AttributeSanitizerInterface {
                    private const EMBED_HOSTS = ['www.youtube-nocookie.com', 'www.youtube.com', 'youtube.com', 'player.vimeo.com'];

                    public function getSupportedElements(): ?array
                    {
                        return ['iframe'];
                    }

                    public function getSupportedAttributes(): ?array
                    {
                        return ['src'];
                    }

                    // Embeds only from YouTube and Vimeo; any other frame source is removed.
                    public function sanitizeAttribute(string $element, string $attribute, string $value, HtmlSanitizerConfig $config): ?string
                    {
                        $host = strtolower((string) parse_url($value, PHP_URL_HOST));
                        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

                        return $scheme === 'https' && in_array($host, self::EMBED_HOSTS, true) ? $value : null;
                    }
                })
                ->withMaxInputLength(500_000)
        );
    }
}
