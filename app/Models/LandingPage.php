<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A one-product offer page at /offer/{slug} (Marketing → Landing pages), made for Facebook ads:
 * Bangla sales text, photos, video, feature blocks, specs, FAQ, contact box and an order form on the page.
 * Everything a section shows lives in `content`; a missing key falls back to defaults().
 */
class LandingPage extends Model
{
    /** Sections that can be switched off, in page order: key => label shown in the editor. */
    public const SECTIONS = [
        'ticker'     => 'Top announcement bar (scrolling)',
        'video'      => 'Video section',
        'features'   => '3 feature blocks',
        'specs'      => 'Specifications table',
        'reviews'    => 'Customer review pictures',
        'faq'        => 'Questions and answers (FAQ)',
        'contact'    => 'Call / WhatsApp box',
        'sticky_bar' => 'Order bar fixed at the bottom on phones',
    ];

    /**
     * Looks the admin can pick: key => [name, one line on the look, default accent colour, [page, card, text] for the picker swatch].
     * The page markup is the same for all of them; each template restyles it and some change the layout.
     */
    public const TEMPLATES = [
        'classic' => ['Classic', 'One centred column: headline, photos, price, then each section in turn. Safe for any product.', '#f48722', ['#ffffff', '#f9fafb', '#111827']],
        'bold'    => ['Bold dark', 'Dark full-width hero with the photo beside a huge headline, feature cards, a tile grid of details and a review wall.', '#facc15', ['#0b0b0f', '#17171d', '#ffffff']],
        'minimal' => ['Editorial', 'Magazine style: photos stacked on the left while the story, numbered points and details scroll on the right.', '#111111', ['#ffffff', '#f5f5f4', '#111111']],
        'festive' => ['Festive', 'Framed festive banner with a big discount seal, a coupon-style offer box and round feature pictures. For Eid and Puja.', '#b91c1c', ['#fff8ec', '#fdecc8', '#3b0a0a']],
        'split'   => ['Quick order', 'The order form sits right next to the photos at the top, so buyers order without scrolling. Details follow below.', '#16a34a', ['#ffffff', '#f0fdf4', '#052e16']],
    ];

    /** Where a custom HTML block can go: key => label in the editor */
    public const CUSTOM_SPOTS = [
        'top'      => 'Very top, under the announcement bar',
        'hero'     => 'After the headline and photos',
        'video'    => 'After the video',
        'features' => 'After the feature blocks',
        'specs'    => 'After the specifications',
        'reviews'  => 'After the review pictures',
        'faq'      => 'After the questions and answers',
        'contact'  => 'After the call / WhatsApp box',
        'form'     => 'After the order form (bottom)',
    ];

    public const MAX_CUSTOM = 3;

    public const MAX_GALLERY = 5;
    public const MAX_REVIEW_IMAGES = 8;
    public const MAX_TICKER = 6;
    public const MAX_SPECS = 12;
    public const MAX_FAQ = 10;
    /** Same rule as the cart: at most 3 of one product or size per order. */
    public const MAX_QTY = 3;

    protected $guarded = [];

    protected $casts = [
        'is_published'  => 'boolean',
        'regular_price' => 'decimal:2',
        'offer_price'   => 'decimal:2',
        'content'       => 'array',
        'views'         => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function url(): string
    {
        return route('landing.show', $this->slug);
    }

    /** The price a customer pays here: the offer price, else the product's own price. */
    public function price(): float
    {
        if ($this->offer_price !== null && (float) $this->offer_price > 0) {
            return (float) $this->offer_price;
        }

        return (float) ($this->product?->price ?? 0);
    }

    /** The crossed-out price, only when it is higher than what the customer pays. */
    public function comparePrice(): ?float
    {
        $regular = $this->regular_price !== null && (float) $this->regular_price > 0
            ? (float) $this->regular_price
            : (float) ($this->product?->regular_price ?? 0);

        return $regular > $this->price() ? $regular : null;
    }

    public function savings(): float
    {
        $compare = $this->comparePrice();

        return $compare ? $compare - $this->price() : 0.0;
    }

    /**
     * A custom HTML block as a full document for a sandboxed iframe (srcdoc): the page font, links opening in a
     * new tab, and a small script that tells the landing page how tall the content is so the frame fits it.
     */
    public static function customDocument(string $html, int $index): string
    {
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<base target="_blank">'
            // The font loads without holding up the block (and its height report) when the font server is slow
            . '<link rel="stylesheet" media="print" onload="this.media=\'all\'" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap">'
            . '<style>html,body{margin:0;padding:0}body{font-family:"Hind Siliguri",system-ui,sans-serif;color:#111827}img,video,iframe{max-width:100%;height:auto}</style>'
            . '</head><body>' . $html
            . '<script>(function(){function s(){parent.postMessage({lpCustomHeight:Math.ceil(document.body.getBoundingClientRect().height),lpCustom:' . $index . '},"*")}'
            . 'addEventListener("load",s);if(window.ResizeObserver){new ResizeObserver(s).observe(document.body)}setTimeout(s,50);setTimeout(s,1500)})()</script>'
            . '</body></html>';
    }

    /** Folders the editor's "Choose from Media library" lists: public-disk folders, then public/uploads folders */
    public const LIBRARY_DISK_DIRS = ['landing', 'products', 'banners', 'home', 'categories', 'branding', 'brands/logos', 'brands/banners'];
    public const LIBRARY_UPLOAD_DIRS = ['uploads/media', 'uploads/products', 'uploads/products/description'];
    private const IMAGE_EXT = '/\.(jpe?g|png|gif|webp|avif)$/i';

    /**
     * Pictures in the media library the editor can pick, newest first: path (as saved on the page), url, name.
     *
     * @return list<array{path: string, url: string, name: string}>
     */
    public static function libraryImages(string $search = '', int $limit = 240): array
    {
        $found = [];
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        foreach (self::LIBRARY_DISK_DIRS as $dir) {
            foreach ($disk->exists($dir) ? $disk->files($dir) : [] as $path) {
                if (preg_match(self::IMAGE_EXT, $path)) {
                    $found[$path] = $disk->lastModified($path);
                }
            }
        }
        foreach (self::LIBRARY_UPLOAD_DIRS as $dir) {
            foreach (is_dir(public_path($dir)) ? \Illuminate\Support\Facades\File::files(public_path($dir)) : [] as $file) {
                if (preg_match(self::IMAGE_EXT, $file->getFilename())) {
                    $found[$dir . '/' . $file->getFilename()] = $file->getMTime();
                }
            }
        }
        arsort($found);
        $search = mb_strtolower(trim($search));

        return collect(array_keys($found))
            ->filter(fn ($p) => $search === '' || str_contains(mb_strtolower($p), $search))
            ->take($limit)
            ->map(fn ($p) => ['path' => $p, 'url' => image_url($p), 'name' => basename($p)])
            ->values()->all();
    }

    /** A picked library path is a real picture inside the library folders (never outside them) */
    public static function isLibraryImage(mixed $path): bool
    {
        if (! is_string($path) || str_contains($path, '..') || ! preg_match('#^[\w\-./]+$#', $path) || ! preg_match(self::IMAGE_EXT, $path)) {
            return false;
        }
        $dir = dirname($path);
        if (in_array($dir, self::LIBRARY_UPLOAD_DIRS, true)) {
            return is_file(public_path($path));
        }

        return in_array($dir, self::LIBRARY_DISK_DIRS, true) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    }

    /** The picked template's key, or classic for an unknown one */
    public function template(): string
    {
        return isset(self::TEMPLATES[$this->template]) ? $this->template : 'classic';
    }

    /** The accent colour set on the page, else the template's own */
    public function accent(): string
    {
        $c = (string) $this->accent_color;

        return preg_match('/^#[0-9a-fA-F]{6}$/', $c) ? $c : self::TEMPLATES[$this->template()][2];
    }

    /** Saved content laid over the defaults, so every key a view reads exists. */
    public function data(): array
    {
        return array_replace_recursive(self::defaults($this->product), $this->content ?? []);
    }

    public function sectionOn(string $key): bool
    {
        return (bool) ($this->data()['sections'][$key] ?? true);
    }

    /** Gallery pictures: the page's own, else the product's photos. */
    public function galleryUrls(): array
    {
        $own = collect($this->data()['gallery'] ?? [])->filter()->map(fn ($p) => image_url($p, $this->title));
        if ($own->isNotEmpty()) {
            return $own->values()->all();
        }

        return $this->product
            ? $this->product->images->take(self::MAX_GALLERY)->map(fn ($img) => $img->url())->values()->all()
            : [];
    }

    /** Spec rows: the page's own, else the product's specifications. */
    public function specRows(): array
    {
        $rows = collect($this->data()['specs']['rows'] ?? [])
            ->filter(fn ($r) => trim((string) ($r['label'] ?? '')) !== '' || trim((string) ($r['value'] ?? '')) !== '')
            ->values();

        return $rows->isNotEmpty() ? $rows->all() : ($this->product?->specificationRows() ?? []);
    }

    /** YouTube video id from a watch, share, shorts or embed link; null when it isn't one. */
    public static function youtubeId(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (preg_match('~(?:youtu\.be/|youtube(?:-nocookie)?\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/|v/))([A-Za-z0-9_-]{11})~', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    public function conversionRate(int $orders): float
    {
        return $this->views > 0 ? round($orders / $this->views * 100, 1) : 0.0;
    }

    /** A slug no other landing page uses, based on $base. */
    public static function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $base = $base !== '' ? $base : 'offer';
        $slug = $base;
        $n = 2;
        while (self::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    /**
     * Starter content in Bangla, filled from the product, so a new page is ready to edit.
     * Pictures stay empty: the page then shows the product's own photos.
     */
    public static function defaults(?Product $product = null): array
    {
        $name = $product?->name ?: 'আমাদের পণ্য';
        $phone = (string) setting('contact_phone', '');
        $whatsapp = (string) setting('whatsapp_number', '');
        $inside = (float) setting('shipping_inside_dhaka', 60);
        $outside = (float) setting('shipping_outside_dhaka', 120);

        return [
            'sections' => array_fill_keys(array_keys(self::SECTIONS), true),
            'ticker' => [
                'সারা বাংলাদেশে ক্যাশ অন ডেলিভারি',
                'পণ্য হাতে পেয়ে মূল্য পরিশোধ করুন',
                'আজকের অর্ডারে বিশেষ ছাড়',
            ],
            'hero' => [
                'badge'     => 'সীমিত সময়ের অফার',
                'part1'     => 'প্রতিদিনের জন্য সেরা পছন্দ',
                'part2'     => $name,
                'part3'     => 'এখন বিশেষ দামে।',
                'button'    => 'অর্ডার করতে চাই',
            ],
            'gallery' => [],
            'video' => [
                'heading' => 'ভিডিওতে দেখুন',
                'url'     => '',
            ],
            'features' => [
                'heading' => "কেন {$name} আপনার সেরা পছন্দ?",
                'items' => [
                    ['image' => null, 'title' => 'প্রিমিয়াম কোয়ালিটি', 'text' => 'বাছাই করা উপকরণ আর যত্নে তৈরি, যাতে অনেক দিন ভালো থাকে।'],
                    ['image' => null, 'title' => 'আরামদায়ক ও স্টাইলিশ', 'text' => 'প্রতিদিনের ব্যবহারে আরাম আর দেখতেও সুন্দর।'],
                    ['image' => null, 'title' => 'নিশ্চিন্তে কিনুন', 'text' => 'পণ্য হাতে পেয়ে দেখে মূল্য পরিশোধ করুন।'],
                ],
            ],
            'specs' => [
                'heading' => 'পণ্যের বিস্তারিত তথ্য',
                'rows'    => [],
            ],
            'reviews' => [
                'heading'  => 'আমাদের কাস্টমাররা যা বলছেন',
                'subtitle' => '',
                'images'   => [],
            ],
            'faq' => [
                'heading'  => 'সচরাচর জিজ্ঞাসিত প্রশ্নাবলী',
                'subtitle' => 'প্রশ্নে ক্লিক করলেই উত্তর দেখতে পাবেন',
                'items' => [
                    ['q' => 'অর্ডার করতে কি আগে টাকা দিতে হবে?', 'a' => 'না, কোনো অগ্রিম টাকা দিতে হবে না। পণ্য হাতে পেয়ে মূল্য পরিশোধ করবেন।'],
                    ['q' => 'ডেলিভারি চার্জ কত?', 'a' => 'ঢাকার ভিতরে ' . self::bn($inside) . ' টাকা এবং ঢাকার বাইরে ' . self::bn($outside) . ' টাকা।'],
                    ['q' => 'কত দিনে ডেলিভারি পাব?', 'a' => 'ঢাকার ভিতরে ১-২ দিন এবং ঢাকার বাইরে ২-৪ দিনের মধ্যে।'],
                    ['q' => 'পণ্য হাতে পেয়ে চেক করার সুযোগ আছে?', 'a' => 'হ্যাঁ, ডেলিভারি ম্যানের সামনে পণ্য দেখে নিতে পারবেন।'],
                ],
            ],
            'contact' => [
                'heading'   => 'অর্ডার বা পণ্য সম্পর্কে জানতে যোগাযোগ করুন',
                'phone'     => $phone,
                'whatsapp'  => $whatsapp,
                'messenger' => (string) setting('messenger_url', ''),
            ],
            'form' => [
                'heading'          => 'অর্ডার করতে নিচের ফর্মটি পূরণ করুন',
                'subheading'       => 'ক্যাশ অন ডেলিভারি · পণ্য হাতে পেয়ে মূল্য পরিশোধ করুন',
                'delivery_heading' => 'ডেলিভারি তথ্য',
                'summary_heading'  => 'আপনার অর্ডার',
                'subtotal_label'   => 'সাবটোটাল',
                'delivery_label'   => 'ডেলিভারি চার্জ',
                'total_label'      => 'সর্বমোট',
                'button'           => 'অর্ডার নিশ্চিত করুন',
            ],
        ];
    }

    /** Digits in Bangla (৬০), for the starter text. */
    public static function bn(float|int|string $n): string
    {
        $n = is_float($n) && floor($n) == $n ? (string) (int) $n : (string) $n;

        return strtr($n, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }
}
