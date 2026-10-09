<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function product(): Product
    {
        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);

        return Product::create(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold',
            'regular_price' => 1000, 'stock_quantity' => 10, 'is_published' => true]);
    }

    private function saveAll(array $overrides = []): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.integrations.update', 'tracking'), array_merge([
                'tracking_ga4_id' => 'g-abc123', 'tracking_ga4_api_secret' => 'ga-secret',
                'tracking_gtm_id' => '', 'google_site_verification' => '',
                'tracking_google_ads_id' => 'aw-123456789', 'tracking_google_ads_label' => 'AbC-D_efG',
                'tracking_meta_pixel_id' => '123456789012', 'tracking_meta_domain_verification' => '<meta name="facebook-domain-verification" content="abcdef123456" />',
                'tracking_meta_capi_enabled' => '1', 'tracking_meta_capi_token' => 'EAAGtoken', 'tracking_meta_capi_test_code' => 'TEST123',
                'tracking_tiktok_pixel_id' => 'cabc123def456', 'tracking_custom_head' => '<script>window.clarityTest=1</script>',
                'tracking_custom_body' => '<noscript>body-tag-test</noscript>',
            ], $overrides))->assertSessionHasNoErrors();
        Setting::forgetCache();
        auth()->logout();
    }

    public function test_admin_saves_every_tracking_setting_and_keeps_secrets_when_left_blank(): void
    {
        $this->saveAll();
        $this->assertSame('AW-123456789', setting('tracking_google_ads_id'));
        $this->assertSame('CABC123DEF456', setting('tracking_tiktok_pixel_id'));
        $this->assertSame('1', setting('tracking_meta_capi_enabled'));

        $this->saveAll(['tracking_meta_capi_token' => '', 'tracking_ga4_api_secret' => '']);
        $this->assertSame('EAAGtoken', setting('tracking_meta_capi_token'), 'A blank secret field keeps the saved one');
        $this->assertSame('ga-secret', setting('tracking_ga4_api_secret'));

        $this->saveAll(['tracking_meta_capi_token' => '', 'tracking_meta_capi_token_clear' => '1', 'tracking_meta_capi_enabled' => '0']);
        $this->assertEmpty(setting('tracking_meta_capi_token'));
        $this->assertFalse(tracking_meta_capi_ready());

        $page = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.integrations.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('ga-secret', $page, 'Secrets are never printed back');
        $this->assertStringContainsString('Test Meta connection', $page);
    }

    public function test_storefront_loads_every_tag_and_fires_shop_events(): void
    {
        $this->saveAll();
        $product = $this->product();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString("gtag('config', 'G-ABC123')", $html);
        $this->assertStringContainsString("gtag('config', 'AW-123456789')", $html);
        $this->assertStringContainsString('"adsPurchase":"AW-123456789/AbC-D_efG"', $html);
        $this->assertStringContainsString("fbq('init', '123456789012')", $html);
        $this->assertStringContainsString("ttq.load('CABC123DEF456')", $html);
        $this->assertStringContainsString('<meta name="facebook-domain-verification" content="abcdef123456" />', $html);
        $this->assertStringContainsString('<script>window.clarityTest=1</script>', $html);
        $this->assertStringContainsString('<noscript>body-tag-test</noscript>', $html);
        $this->assertStringContainsString('window.vtTrack = function', $html);

        $this->assertStringContainsString("window.vtTrack('view_item'", $this->get(route('product.show', $product))->getContent());

        $added = $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertOk();
        $this->assertEquals(['item_id' => (string) $product->id, 'item_name' => 'Bifold', 'item_variant' => null, 'price' => 1000, 'quantity' => 1], $added->json('tracked'));

        $this->assertStringContainsString("window.vtTrack('begin_checkout'", $this->get(route('checkout.show'))->getContent());
    }

    public function test_order_sends_one_purchase_from_the_browser_and_the_server_with_the_same_id(): void
    {
        $this->saveAll();
        Http::fake([
            'graph.facebook.com/*' => Http::response(['events_received' => 1]),
            'www.google-analytics.com/*' => Http::response('', 204),
        ]);

        $this->postJson(route('cart.add'), ['product_id' => $this->product()->id])->assertOk();
        $this->withUnencryptedCookies(['_fbp' => 'fb.1.111.222', '_ga' => 'GA1.1.12345.67890'])
            ->post(route('checkout.store'), [
                'customer_name' => 'Rahim Uddin', 'customer_phone' => '01712345678', 'customer_email' => 'Rahim@Example.com',
                'shipping_address' => 'House 1', 'city' => 'Dhaka', 'shipping_zone' => 'inside_dhaka', 'payment_method' => 'cod',
            ])->assertSessionHasNoErrors();
        $order = Order::latest('id')->firstOrFail();

        Http::assertSent(function (HttpRequest $r) use ($order) {
            if (! str_contains($r->url(), 'graph.facebook.com/v21.0/123456789012/events')) {
                return false;
            }
            $e = $r['data'][0];

            return $e['event_name'] === 'Purchase'
                && $e['event_id'] === 'purchase-' . $order->order_number
                && $e['user_data']['ph'] === hash('sha256', '8801712345678')
                && $e['user_data']['em'] === hash('sha256', 'rahim@example.com')
                && $e['user_data']['fbp'] === 'fb.1.111.222'
                && $e['custom_data']['value'] == $order->total
                && $r['access_token'] === 'EAAGtoken' && $r['test_event_code'] === 'TEST123';
        });
        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'mp/collect?measurement_id=G-ABC123&api_secret=ga-secret')
            && $r['client_id'] === '12345.67890'
            && $r['events'][0]['params']['transaction_id'] === $order->order_number);

        $page = $this->get(route('order.confirmation', $order->order_number))->assertOk()->getContent();
        $this->assertStringContainsString("window.vtTrack('purchase'", $page);
        $this->assertStringContainsString('"purchase-' . $order->order_number . '"', $page, 'Browser pixel uses the same event id as the server');
    }

    public function test_nothing_is_sent_from_the_server_when_conversions_api_is_off(): void
    {
        Http::fake();
        $this->postJson(route('cart.add'), ['product_id' => $this->product()->id])->assertOk();
        $this->post(route('checkout.store'), [
            'customer_name' => 'Rahim', 'customer_phone' => '01712345678', 'shipping_address' => 'House 1',
            'city' => 'Dhaka', 'shipping_zone' => 'inside_dhaka', 'payment_method' => 'cod',
        ])->assertSessionHasNoErrors();

        Http::assertNothingSent();
        $this->assertStringNotContainsString('window.vtTrack = function', $this->get('/')->getContent());
    }

    public function test_meta_test_button_reports_the_connection(): void
    {
        $this->saveAll();
        $admin = User::factory()->create(['role' => 'admin']);

        Http::fake(['graph.facebook.com/*' => Http::sequence()
            ->push(['events_received' => 1])
            ->push(['error' => ['message' => 'Invalid OAuth access token.']], 400)]);
        $this->actingAs($admin)->postJson(route('admin.integrations.test-meta'))
            ->assertOk()->assertJson(['ok' => true])->assertJsonFragment(['message' => 'Connected. Meta received 1 event(s). Check Events Manager → Test events.']);

        $this->actingAs($admin)->postJson(route('admin.integrations.test-meta'))
            ->assertStatus(422)->assertJson(['ok' => false, 'message' => 'Not connected: Invalid OAuth access token.']);
    }
}
