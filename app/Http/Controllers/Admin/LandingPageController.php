<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Marketing → Landing pages: one-product offer pages at /offer/{slug} with their own price and order form. */
class LandingPageController extends Controller
{
    private const DIR = 'landing';

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $pages = LandingPage::with('product.images')
            ->withCount(['orders' => fn ($o) => $o->where('status', '!=', 'cancelled')])
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"));
            }))
            ->when($status === 'published', fn ($query) => $query->where('is_published', true))
            ->when($status === 'draft', fn ($query) => $query->where('is_published', false))
            ->latest('id')
            ->get();

        $views = (int) LandingPage::sum('views');
        $orders = Order::whereNotNull('landing_page_id')->where('status', '!=', 'cancelled')->count();

        return view('admin.landing-pages.index', [
            'pages'    => $pages,
            'q'        => $q,
            'status'   => $status,
            'stats'    => [
                'pages'      => LandingPage::count(),
                'published'  => LandingPage::where('is_published', true)->count(),
                'views'      => $views,
                'orders'     => $orders,
                'conversion' => $views > 0 ? round($orders / $views * 100, 1) : 0,
            ],
            'products' => Product::orderBy('name')->get(['id', 'name', 'is_published']),
        ]);
    }

    /** "New landing page": pick a product; the page starts as a draft filled with Bangla starter text. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'template'   => ['nullable', Rule::in(array_keys(LandingPage::TEMPLATES))],
        ]);
        $product = Product::with('images')->findOrFail($data['product_id']);

        $page = LandingPage::create([
            'product_id'    => $product->id,
            'title'         => $product->name,
            'slug'          => LandingPage::uniqueSlug(Str::slug($product->slug ?: $product->name)),
            'template'      => $data['template'] ?? 'classic',
            'is_published'  => false,
            'regular_price' => $product->regular_price,
            'offer_price'   => $product->price,
            'content'       => LandingPage::defaults($product),
        ]);
        ActivityLogger::log('Landing Page Created', "Created the landing page \"{$page->title}\" (/offer/{$page->slug}).");

        return redirect()->route('admin.landing-pages.edit', $page)->with('status', 'Draft created with starter text. Edit it, then publish.');
    }

    public function edit(LandingPage $landingPage)
    {
        $landingPage->load('product.images');

        return view('admin.landing-pages.edit', [
            'page'     => $landingPage,
            'd'        => $landingPage->data(),
            'products' => Product::with('images')->orderBy('name')->get(),
            'orders'   => $landingPage->orders()->where('status', '!=', 'cancelled')->count(),
        ]);
    }

    public function update(Request $request, LandingPage $landingPage)
    {
        $request->merge(['slug' => Str::slug((string) $request->input('slug'))]);
        $text = ['nullable', 'string', 'max:200'];
        $long = ['nullable', 'string', 'max:1000'];

        $v = $request->validate([
            'product_id'      => ['required', 'integer', Rule::exists('products', 'id')],
            'title'           => ['required', 'string', 'max:160'],
            'slug'            => ['required', 'string', 'max:120', Rule::unique('landing_pages', 'slug')->ignore($landingPage->id)],
            'regular_price'   => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'offer_price'     => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'discount_text'   => ['nullable', 'string', 'max:120'],
            'discount_badge'  => ['nullable', 'string', 'max:40'],
            'accent_color'    => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'template'        => ['nullable', Rule::in(array_keys(LandingPage::TEMPLATES))],
            'meta_title'      => ['nullable', 'string', 'max:160'],
            'meta_description'=> ['nullable', 'string', 'max:300'],
            'action'          => ['nullable', Rule::in(['save', 'publish', 'unpublish'])],

            'sections'        => ['nullable', 'array'],
            'sections.*'      => ['nullable', 'boolean'],
            'ticker'          => ['nullable', 'array', 'max:' . LandingPage::MAX_TICKER],
            'ticker.*'        => ['nullable', 'string', 'max:160'],
            'hero.badge'      => $text,
            'hero.part1'      => $text,
            'hero.part2'      => $text,
            'hero.part3'      => $text,
            'hero.button'     => ['nullable', 'string', 'max:60'],
            'video.heading'   => $text,
            'video.url'       => ['nullable', 'string', 'max:300', function ($attr, $value, $fail) {
                if (filled($value) && ! LandingPage::youtubeId($value)) {
                    $fail('The video link must be a YouTube link (youtube.com/watch?v=… or youtu.be/…).');
                }
            }],
            'features.heading'        => $text,
            'features.items'          => ['nullable', 'array', 'max:3'],
            'features.items.*.title'  => ['nullable', 'string', 'max:120'],
            'features.items.*.text'   => ['nullable', 'string', 'max:600'],
            'specs.heading'           => $text,
            'specs.rows'              => ['nullable', 'array', 'max:' . LandingPage::MAX_SPECS],
            'specs.rows.*.label'      => ['nullable', 'string', 'max:160'],
            'specs.rows.*.value'      => ['nullable', 'string', 'max:300'],
            'reviews.heading'         => $text,
            'reviews.subtitle'        => $text,
            'faq.heading'             => $text,
            'faq.subtitle'            => $text,
            'faq.items'               => ['nullable', 'array', 'max:' . LandingPage::MAX_FAQ],
            'faq.items.*.q'           => ['nullable', 'string', 'max:200'],
            'faq.items.*.a'           => $long,
            'contact.heading'         => $text,
            'contact.phone'           => ['nullable', 'string', 'max:40'],
            'contact.whatsapp'        => ['nullable', 'string', 'max:40'],
            'contact.messenger'       => ['nullable', 'string', 'max:200'],
            'form.heading'            => $text,
            'form.subheading'         => $text,
            'form.delivery_heading'   => ['nullable', 'string', 'max:120'],
            'form.summary_heading'    => ['nullable', 'string', 'max:120'],
            'form.subtotal_label'     => ['nullable', 'string', 'max:60'],
            'form.delivery_label'     => ['nullable', 'string', 'max:60'],
            'form.total_label'        => ['nullable', 'string', 'max:60'],
            'form.button'             => ['nullable', 'string', 'max:60'],

            'gallery_upload'          => ['nullable', 'array', 'max:' . LandingPage::MAX_GALLERY],
            'gallery_upload.*'        => ['image', 'max:4096'],
            'gallery_remove'          => ['nullable', 'array'],
            'gallery_remove.*'        => ['string'],
            'gallery_main'            => ['nullable', 'string'],
            'feature_image'           => ['nullable', 'array', 'max:3'],
            'feature_image.*'         => ['nullable', 'image', 'max:4096'],
            'feature_image_remove'    => ['nullable', 'array'],
            'review_upload'           => ['nullable', 'array', 'max:' . LandingPage::MAX_REVIEW_IMAGES],
            'review_upload.*'         => ['image', 'max:4096'],
            'review_remove'           => ['nullable', 'array'],
            'review_remove.*'         => ['string'],
        ]);

        $old = $landingPage->data();
        $clean = fn ($s) => trim((string) $s);
        $pick = fn (array $src, array $keys) => collect($keys)->mapWithKeys(fn ($k) => [$k => $clean($src[$k] ?? '')])->all();

        // Pictures: keep what's saved, drop the ticked ones, add uploads
        $gallery = $this->pictures($old['gallery'] ?? [], $v['gallery_remove'] ?? [], $request->file('gallery_upload', []), LandingPage::MAX_GALLERY, $landingPage);
        if (filled($v['gallery_main'] ?? null) && in_array($v['gallery_main'], $gallery, true)) {
            $gallery = array_values(array_unique([$v['gallery_main'], ...$gallery]));
        }
        $reviewImages = $this->pictures($old['reviews']['images'] ?? [], $v['review_remove'] ?? [], $request->file('review_upload', []), LandingPage::MAX_REVIEW_IMAGES, $landingPage);

        $features = [];
        for ($i = 0; $i < 3; $i++) {
            $image = $old['features']['items'][$i]['image'] ?? null;
            if ($request->hasFile("feature_image.$i")) {
                $this->forget($image, $landingPage);
                $image = $request->file("feature_image.$i")->store(self::DIR, 'public');
            } elseif ($request->boolean("feature_image_remove.$i")) {
                $this->forget($image, $landingPage);
                $image = null;
            }
            $features[] = [
                'image' => $image,
                'title' => $clean($v['features']['items'][$i]['title'] ?? ''),
                'text'  => $clean($v['features']['items'][$i]['text'] ?? ''),
            ];
        }

        $content = [
            'sections' => collect(LandingPage::SECTIONS)->keys()->mapWithKeys(fn ($k) => [$k => (bool) ($v['sections'][$k] ?? false)])->all(),
            'ticker'   => collect($v['ticker'] ?? [])->map($clean)->filter()->values()->all(),
            'hero'     => $pick($v['hero'] ?? [], ['badge', 'part1', 'part2', 'part3', 'button']),
            'gallery'  => $gallery,
            'video'    => $pick($v['video'] ?? [], ['heading', 'url']),
            'features' => ['heading' => $clean($v['features']['heading'] ?? ''), 'items' => $features],
            'specs'    => [
                'heading' => $clean($v['specs']['heading'] ?? ''),
                'rows'    => collect($v['specs']['rows'] ?? [])
                    ->map(fn ($r) => ['label' => $clean($r['label'] ?? ''), 'value' => $clean($r['value'] ?? '')])
                    ->filter(fn ($r) => $r['label'] !== '' || $r['value'] !== '')->values()->all(),
            ],
            'reviews'  => $pick($v['reviews'] ?? [], ['heading', 'subtitle']) + ['images' => $reviewImages],
            'faq'      => [
                'heading'  => $clean($v['faq']['heading'] ?? ''),
                'subtitle' => $clean($v['faq']['subtitle'] ?? ''),
                'items'    => collect($v['faq']['items'] ?? [])
                    ->map(fn ($r) => ['q' => $clean($r['q'] ?? ''), 'a' => $clean($r['a'] ?? '')])
                    ->filter(fn ($r) => $r['q'] !== '')->values()->all(),
            ],
            'contact'  => $pick($v['contact'] ?? [], ['heading', 'phone', 'whatsapp', 'messenger']),
            'form'     => $pick($v['form'] ?? [], ['heading', 'subheading', 'delivery_heading', 'summary_heading', 'subtotal_label', 'delivery_label', 'total_label', 'button']),
        ];

        $published = match ($v['action'] ?? 'save') {
            'publish'   => true,
            'unpublish' => false,
            default     => $landingPage->is_published,
        };

        // A new template brings its own accent colour, unless the admin picked a colour of their own
        $template = $v['template'] ?? $landingPage->template();
        $accent = $v['accent_color'] ?? null;
        if ($accent !== null && strcasecmp($accent, LandingPage::TEMPLATES[$landingPage->template()][2]) === 0
            && ($template !== $landingPage->template() || ! $landingPage->accent_color)) {
            $accent = null;
        }

        $landingPage->update([
            'product_id'       => $v['product_id'],
            'template'         => $template,
            'title'            => $clean($v['title']),
            'slug'             => $v['slug'],
            'regular_price'    => $v['regular_price'] ?? null,
            'offer_price'      => $v['offer_price'] ?? null,
            'discount_text'    => $clean($v['discount_text'] ?? '') ?: null,
            'discount_badge'   => $clean($v['discount_badge'] ?? '') ?: null,
            'accent_color'     => $accent,
            'meta_title'       => $clean($v['meta_title'] ?? '') ?: null,
            'meta_description' => $clean($v['meta_description'] ?? '') ?: null,
            'content'          => $content,
            'is_published'     => $published,
        ]);
        ActivityLogger::log('Landing Page Updated', "Updated the landing page \"{$landingPage->title}\".");

        $msg = match ($v['action'] ?? 'save') {
            'publish'   => 'Published. The page is live at /offer/' . $landingPage->slug . '.',
            'unpublish' => 'Moved to draft. Customers can no longer open it.',
            default     => 'Landing page saved.',
        };

        return redirect()->route('admin.landing-pages.edit', $landingPage)->with('status', $msg);
    }

    public function publish(LandingPage $landingPage)
    {
        $landingPage->update(['is_published' => ! $landingPage->is_published]);
        $state = $landingPage->is_published ? 'Published' : 'Unpublished';
        ActivityLogger::log("Landing Page {$state}", "{$state} the landing page \"{$landingPage->title}\".");

        return back()->with('status', "{$state} \"{$landingPage->title}\".");
    }

    public function duplicate(LandingPage $landingPage)
    {
        $copy = $landingPage->replicate(['views']);
        $copy->title = Str::limit($landingPage->title . ' (copy)', 160, '');
        $copy->slug = LandingPage::uniqueSlug($landingPage->slug . '-copy');
        $copy->is_published = false;
        $copy->views = 0;
        $copy->save();
        ActivityLogger::log('Landing Page Duplicated', "Copied \"{$landingPage->title}\" to /offer/{$copy->slug}.");

        return redirect()->route('admin.landing-pages.edit', $copy)->with('status', 'Copy made as a draft. Change what you need, then publish.');
    }

    public function destroy(LandingPage $landingPage)
    {
        $data = $landingPage->content ?? [];
        $files = array_merge(
            $data['gallery'] ?? [],
            $data['reviews']['images'] ?? [],
            collect($data['features']['items'] ?? [])->pluck('image')->filter()->all(),
        );
        $title = $landingPage->title;
        $landingPage->delete();
        foreach ($files as $path) {
            $this->forget($path, null);
        }
        ActivityLogger::log('Landing Page Deleted', "Deleted the landing page \"{$title}\". Its orders keep their details.");

        return redirect()->route('admin.landing-pages.index')->with('status', "Deleted \"{$title}\".");
    }

    /** Saved pictures minus the removed ones, plus new uploads, up to $max. */
    private function pictures(array $saved, array $remove, array $uploads, int $max, LandingPage $page): array
    {
        $keep = [];
        foreach ($saved as $path) {
            if (in_array($path, $remove, true)) {
                $this->forget($path, $page);
            } else {
                $keep[] = $path;
            }
        }
        foreach ($uploads as $file) {
            if (count($keep) >= $max) {
                break;
            }
            $keep[] = $file->store(self::DIR, 'public');
        }

        return array_values($keep);
    }

    /** Deletes an uploaded picture unless another landing page (e.g. a copy) still shows it. */
    private function forget(?string $path, ?LandingPage $page): void
    {
        if (! $path || ! str_starts_with($path, self::DIR . '/')) {
            return;
        }
        $usedElsewhere = LandingPage::where('content', 'like', '%' . str_replace('/', '%', $path) . '%')
            ->when($page, fn ($q) => $q->where('id', '!=', $page->id))
            ->exists();
        if (! $usedElsewhere) {
            Storage::disk('public')->delete($path);
        }
    }
}
