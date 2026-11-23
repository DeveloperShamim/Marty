<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function product(array $attrs = []): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'shirts'], ['name' => 'Shirts', 'is_active' => true]);

        return Product::create(array_merge([
            'category_id' => $cat->id, 'name' => 'Oxford Shirt', 'slug' => 'oxford-shirt',
            'regular_price' => 2500, 'sale_price' => 1990, 'stock_quantity' => 10, 'is_published' => true,
        ], $attrs));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function page(Product $product, array $attrs = []): LandingPage
    {
        return LandingPage::create(array_merge([
            'product_id' => $product->id, 'title' => 'Oxford offer', 'slug' => 'oxford',
            'is_published' => true, 'regular_price' => 2500, 'offer_price' => 1500,
            'content' => LandingPage::defaults($product),
        ], $attrs));
    }

    private function orderData(array $extra = []): array
    {
        return array_merge([
            'customer_name' => 'Rahim', 'customer_phone' => '01712345678',
            'shipping_address' => 'House 1, Road 2, Dhanmondi', 'shipping_zone' => 'inside_dhaka', 'qty' => 1,
        ], $extra);
    }

    public function test_admin_creates_a_draft_filled_from_the_product(): void
    {
        $product = $this->product();

        $this->actingAs($this->admin())
            ->post(route('admin.landing-pages.store'), ['product_id' => $product->id])
            ->assertRedirect();

        $page = LandingPage::firstOrFail();
        $this->assertSame('oxford-shirt', $page->slug);
        $this->assertFalse($page->is_published);
        $this->assertSame(1990.0, $page->price());
        $this->assertSame('Oxford Shirt', $page->data()['hero']['part2']);
        $this->assertNotEmpty($page->data()['faq']['items']);

        $this->actingAs($this->admin())->get(route('admin.landing-pages.edit', $page))
            ->assertOk()->assertSee('Save and publish')->assertSee('Oxford Shirt');
        $this->actingAs($this->admin())->get(route('admin.landing-pages.index'))
            ->assertOk()->assertSee('/offer/oxford-shirt')->assertSee('Draft');
    }

    public function test_drafts_are_hidden_from_customers_but_staff_can_preview(): void
    {
        $page = $this->page($this->product(), ['is_published' => false]);

        $this->get('/offer/oxford')->assertNotFound();
        $this->actingAs($this->admin())->get('/offer/oxford')->assertOk()->assertSee('Draft preview');
        $this->assertSame(0, $page->fresh()->views);
    }

    public function test_published_page_shows_sections_and_counts_one_view_per_visitor(): void
    {
        $page = $this->page($this->product());
        $content = $page->content;
        $content['video']['url'] = 'https://youtu.be/dQw4w9WgXcQ';
        $content['sections']['faq'] = false;
        $page->update(['content' => $content]);

        $this->get('/offer/oxford')->assertOk()
            ->assertSee('Oxford Shirt')
            ->assertSee('৳1,500')
            ->assertSee('৳2,500')
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('data-lp-section="features"', false)
            ->assertDontSee('data-lp-section="faq"', false)
            ->assertSee('data-lp-sticky', false);
        $this->get('/offer/oxford')->assertOk();

        $this->assertSame(1, $page->fresh()->views);
    }

    public function test_order_uses_the_offer_price_and_takes_stock(): void
    {
        $product = $this->product();
        $page = $this->page($product);

        $this->post('/offer/oxford/order', $this->orderData(['qty' => 2, 'customer_phone' => '+8801712345678']))
            ->assertRedirect();

        $order = Order::with('items')->firstOrFail();
        $this->assertSame($page->id, $order->landing_page_id);
        $this->assertSame('01712345678', $order->customer_phone);
        $this->assertSame('cod', $order->payment_method);
        $this->assertEquals(3000, $order->subtotal);
        $this->assertEquals(60, $order->shipping_charge);
        $this->assertEquals(3060, $order->total);
        $this->assertEquals(1500, $order->items->first()->unit_price);
        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertSame($order->order_number, session('recent_order'));
    }

    public function test_size_must_be_picked_and_in_stock(): void
    {
        $product = $this->product();
        $m = ProductSku::create(['product_id' => $product->id, 'attributes' => ['Size' => 'M'], 'stock_quantity' => 1, 'is_active' => true]);
        $l = ProductSku::create(['product_id' => $product->id, 'attributes' => ['Size' => 'L'], 'stock_quantity' => 5, 'is_active' => true]);
        $other = ProductSku::create(['product_id' => $this->product(['slug' => 'other', 'name' => 'Other'])->id, 'attributes' => ['Size' => 'S'], 'stock_quantity' => 5, 'is_active' => true]);
        $this->page($product);

        $this->get('/offer/oxford')->assertOk()->assertSee('data-lp-value="M"', false)->assertSee('data-lp-value="L"', false);

        $this->post('/offer/oxford/order', $this->orderData())->assertSessionHasErrors('sku_id');
        $this->post('/offer/oxford/order', $this->orderData(['sku_id' => $other->id]))->assertSessionHasErrors('sku_id');
        $this->post('/offer/oxford/order', $this->orderData(['sku_id' => $m->id, 'qty' => 2]))->assertSessionHasErrors('order');
        $this->assertSame(0, Order::count());

        $this->post('/offer/oxford/order', $this->orderData(['sku_id' => $l->id, 'qty' => 2]))->assertRedirect();
        $this->assertSame('Size: L', Order::firstOrFail()->items->first()->variant);
        $this->assertSame(3, $l->fresh()->stock_quantity);
    }

    public function test_bad_phone_and_unpublished_pages_are_refused(): void
    {
        $page = $this->page($this->product());

        $this->post('/offer/oxford/order', $this->orderData(['customer_phone' => '12345']))->assertSessionHasErrors('customer_phone');
        $page->update(['is_published' => false]);
        $this->post('/offer/oxford/order', $this->orderData())->assertNotFound();
        $this->assertSame(0, Order::count());
    }

    public function test_admin_edits_publishes_duplicates_and_deletes(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $product = $this->product();
        $page = $this->page($product, ['is_published' => false]);

        $this->actingAs($admin)->put(route('admin.landing-pages.update', $page), [
            'action' => 'publish',
            'product_id' => $product->id, 'title' => 'Eid offer', 'slug' => 'Eid Offer',
            'regular_price' => 3000, 'offer_price' => 1800, 'accent_color' => '#112233',
            'ticker' => ['Free gift', '', 'COD'],
            'hero' => ['badge' => 'Eid', 'part1' => 'Best', 'part2' => 'Shirt', 'part3' => 'ever', 'button' => 'Buy'],
            'video' => ['heading' => 'Watch', 'url' => ''],
            'features' => ['heading' => 'Why', 'items' => [['title' => 'Soft', 'text' => 'Cotton']]],
            'specs' => ['heading' => 'Details', 'rows' => [['label' => 'Fabric', 'value' => 'Cotton'], ['label' => '', 'value' => '']]],
            'faq' => ['heading' => 'FAQ', 'items' => [['q' => 'COD?', 'a' => 'Yes'], ['q' => '', 'a' => '']]],
            'sections' => ['ticker' => '1', 'video' => '0', 'faq' => '1'],
            'gallery_upload' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])->assertRedirect();

        $page->refresh();
        $this->assertTrue($page->is_published);
        $this->assertSame('eid-offer', $page->slug);
        $this->assertSame(1800.0, $page->price());
        $this->assertSame(['Free gift', 'COD'], $page->content['ticker']);
        $this->assertCount(1, $page->content['specs']['rows']);
        $this->assertCount(1, $page->content['faq']['items']);
        $this->assertFalse($page->sectionOn('video'));
        $this->assertFalse($page->sectionOn('contact'));
        $this->assertCount(2, $page->content['gallery']);
        Storage::disk('public')->assertExists($page->content['gallery'][0]);

        $this->actingAs($admin)->put(route('admin.landing-pages.update', $page), [
            'product_id' => $product->id, 'title' => 'Eid offer', 'slug' => 'eid-offer',
            'video' => ['url' => 'https://example.com/not-youtube'],
        ])->assertSessionHasErrors('video.url');

        $this->actingAs($admin)->post(route('admin.landing-pages.duplicate', $page))->assertRedirect();
        $copy = LandingPage::where('id', '!=', $page->id)->firstOrFail();
        $this->assertSame('eid-offer-copy', $copy->slug);
        $this->assertFalse($copy->is_published);

        // The copy shares the pictures, so deleting the original keeps them
        $this->actingAs($admin)->delete(route('admin.landing-pages.destroy', $page))->assertRedirect();
        Storage::disk('public')->assertExists($copy->content['gallery'][0]);
        $this->assertSame(1, LandingPage::count());
    }

    public function test_store_managers_can_open_it_but_order_managers_cannot(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'store_manager']))
            ->get(route('admin.landing-pages.index'))->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'order_manager']))
            ->get(route('admin.landing-pages.index'))->assertRedirect();
    }

    public function test_order_page_shows_the_landing_page_and_list_filters_by_it(): void
    {
        $page = $this->page($this->product());
        $this->post('/offer/oxford/order', $this->orderData())->assertRedirect();
        $order = Order::firstOrFail();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()->assertSee('Landing page: Oxford offer');
        $this->actingAs($admin)->get(route('admin.orders.index', ['landing' => $page->id]))->assertOk()->assertSee($order->order_number);
        $this->actingAs($admin)->get(route('admin.orders.index', ['landing' => $page->id + 1]))->assertOk()->assertDontSee($order->order_number);
    }

    public function test_admin_picks_a_template_and_can_preview_others(): void
    {
        $admin = $this->admin();
        $product = $this->product();

        $this->actingAs($admin)->get(route('admin.landing-pages.index'))->assertOk()
            ->assertSee('Pick a template')->assertSee('Bold dark')->assertSee('data-lp-template="festive"', false);
        $this->actingAs($admin)->post(route('admin.landing-pages.store'), ['product_id' => $product->id, 'template' => 'bold'])->assertRedirect();
        $page = LandingPage::firstOrFail();
        $this->assertSame('bold', $page->template);
        $this->assertSame('#facc15', $page->accent());
        $page->update(['is_published' => true]);

        $html = $this->get($page->url())->assertOk()->getContent();
        $this->assertStringContainsString('class="lp-t-bold', $html);
        $this->assertStringContainsString('--lp: #facc15; --lp-on: #111111', $html, 'Dark text on the yellow buttons');

        // Staff try another template without saving; customers can't
        $this->actingAs($admin)->get(route('admin.landing-pages.edit', $page))->assertOk()->assertSee($page->url() . '?template=split', false);
        $this->actingAs($admin)->get($page->url() . '?template=split')->assertOk()
            ->assertSee('class="lp-t-split', false)->assertSee('data-lp-trying', false)->assertSee('--lp: #16a34a', false);
        $this->assertSame('bold', $page->fresh()->template);
        auth()->logout();
        $this->get($page->url() . '?template=split')->assertOk()->assertSee('class="lp-t-bold', false);

        // Switching template brings its colour along, unless a colour of their own was picked
        $base = ['product_id' => $product->id, 'title' => 'T', 'slug' => $page->slug];
        $this->actingAs($admin)->put(route('admin.landing-pages.update', $page), $base + ['template' => 'festive', 'accent_color' => '#facc15'])->assertRedirect();
        $this->assertSame('#b91c1c', $page->fresh()->accent());
        $this->actingAs($admin)->put(route('admin.landing-pages.update', $page), $base + ['template' => 'minimal', 'accent_color' => '#2563eb'])->assertRedirect();
        $this->assertSame(['minimal', '#2563eb'], [$page->fresh()->template, $page->fresh()->accent()]);
        $this->actingAs($admin)->put(route('admin.landing-pages.update', $page), $base + ['template' => 'nope'])->assertSessionHasErrors('template');
    }

    public function test_each_template_has_its_own_layout(): void
    {
        $page = $this->page($this->product());
        $marks = [
            'classic' => 'data-lp-thumb',                        // carousel with thumbnails
            'bold'    => 'uppercase tracking-wider" style="background: var(--lp)', // dark hero badge
            'minimal' => 'snap-mandatory -mx-4 px-4 lg:mx-0',     // stacked editorial photos
            'festive' => 'বিশেষ উৎসব অফার',                       // coupon-style offer box
            'split'   => 'rounded-3xl border-2 py-6',             // order form beside the photos
        ];
        foreach ($marks as $template => $mark) {
            $page->update(['template' => $template]);
            $html = $this->get('/offer/oxford')->assertOk()->getContent();
            $this->assertStringContainsString('id="lpForm"', $html, $template);
            $this->assertStringContainsString($mark, $html, $template);
            foreach (array_diff_key($marks, [$template => 1, 'classic' => 1]) as $other => $otherMark) {
                $this->assertStringNotContainsString($otherMark, $html, "$template shows $other's layout");
            }
        }

        // Quick order puts the form above the details; Classic puts it last
        $page->update(['template' => 'split']);
        $html = $this->get('/offer/oxford')->getContent();
        $this->assertLessThan(strpos($html, 'data-lp-section="faq"'), strpos($html, 'id="lpForm"'));
        $page->update(['template' => 'classic']);
        $html = $this->get('/offer/oxford')->getContent();
        $this->assertGreaterThan(strpos($html, 'data-lp-section="faq"'), strpos($html, 'id="lpForm"'));
    }

    public function test_custom_html_is_admin_only_and_sandboxed(): void
    {
        $product = $this->product();
        $page = $this->page($product);
        $base = ['product_id' => $product->id, 'title' => 'Oxford offer', 'slug' => 'oxford'];
        $html = '<div class="promo">Eid timer</div><script>document.cookie</script>';

        $this->actingAs($this->admin())->put(route('admin.landing-pages.update', $page), $base + [
            'custom' => [['html' => $html, 'at' => 'features'], ['html' => '  ', 'at' => 'top']],
        ])->assertRedirect();
        $this->assertSame([['html' => $html, 'at' => 'features']], $page->fresh()->content['custom']);

        $out = $this->get('/offer/oxford')->assertOk()->getContent();
        $this->assertStringContainsString('sandbox="allow-scripts allow-popups allow-popups-to-escape-sandbox"', $out);
        $this->assertStringNotContainsString('allow-same-origin', $out);
        $this->assertStringContainsString('data-lp-custom-at="features"', $out);
        $this->assertStringNotContainsString('<script>document.cookie</script>', $out, 'Only inside the escaped srcdoc');
        $this->assertStringContainsString('&lt;script&gt;document.cookie&lt;/script&gt;', $out);
        $this->assertLessThan(strpos($out, 'id="lpForm"'), strpos($out, 'data-lp-custom-at="features"'));

        // A store manager can edit the page but not the custom HTML
        $this->actingAs(User::factory()->create(['role' => 'store_manager']))->put(route('admin.landing-pages.update', $page), $base + [
            'custom' => [['html' => '<b>changed</b>', 'at' => 'top']],
        ])->assertRedirect();
        $this->assertSame($html, $page->fresh()->content['custom'][0]['html']);
        $this->actingAs(User::factory()->create(['role' => 'store_manager']))->get(route('admin.landing-pages.edit', $page))
            ->assertOk()->assertSee('Only admins can change these.');

        $this->actingAs($this->admin())->put(route('admin.landing-pages.update', $page), $base + ['custom' => [['html' => 'x', 'at' => 'nowhere']]])
            ->assertSessionHasErrors('custom.0.at');
    }

    public function test_pictures_can_be_picked_from_the_media_library(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/blue.jpg', 'x');
        Storage::disk('public')->put('banners/eid.png', 'x');
        Storage::disk('public')->put('secret/hidden.jpg', 'x');
        $admin = $this->admin();
        $product = $this->product();
        $page = $this->page($product);
        $base = ['product_id' => $product->id, 'title' => 'Oxford offer', 'slug' => 'oxford'];

        $paths = collect($this->actingAs($admin)->getJson(route('admin.landing-pages.media'))->assertOk()->json('images'))->pluck('path');
        $this->assertEqualsCanonicalizing(['products/blue.jpg', 'banners/eid.png'], $paths->all());
        $this->assertSame(['banners/eid.png'], collect($this->actingAs($admin)->getJson(route('admin.landing-pages.media', ['q' => 'eid']))->json('images'))->pluck('path')->all());

        $this->actingAs($admin)->put(route('admin.landing-pages.update', $page), $base + [
            'gallery_pick' => ['products/blue.jpg'], 'review_pick' => ['banners/eid.png'], 'feature_pick' => [1 => 'banners/eid.png'],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $c = $page->fresh()->content;
        $this->assertSame(['products/blue.jpg'], $c['gallery']);
        $this->assertSame(['banners/eid.png'], $c['reviews']['images']);
        $this->assertSame('banners/eid.png', $c['features']['items'][1]['image']);

        foreach (['secret/hidden.jpg', '../.env', 'products/missing.jpg'] as $bad) {
            $this->actingAs($admin)->put(route('admin.landing-pages.update', $page), $base + ['gallery_pick' => [$bad]])
                ->assertSessionHasErrors('gallery_pick.0');
        }

        // Removing a library picture from the page never deletes it from the library
        $this->actingAs($admin)->put(route('admin.landing-pages.update', $page), $base + ['gallery_remove' => ['products/blue.jpg']])->assertRedirect();
        Storage::disk('public')->assertExists('products/blue.jpg');
    }

    public function test_youtube_links_are_recognised(): void
    {
        $this->assertSame('dQw4w9WgXcQ', LandingPage::youtubeId('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=1'));
        $this->assertSame('dQw4w9WgXcQ', LandingPage::youtubeId('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('dQw4w9WgXcQ', LandingPage::youtubeId('https://youtube.com/shorts/dQw4w9WgXcQ'));
        $this->assertNull(LandingPage::youtubeId('https://vimeo.com/123'));
    }
}
