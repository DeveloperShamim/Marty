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

    public function test_each_tracking_card_has_a_guide_in_english_and_bangla(): void
    {
        $page = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.integrations.index'))->assertOk()->getContent();

        foreach (['googlelogin', 'google', 'meta', 'tiktok', 'custom'] as $card) {
            $this->assertStringContainsString('data-guide-open="' . $card . '"', $page);
            $this->assertStringContainsString('id="guide-' . $card . '"', $page);
        }
        $this->assertSame(5, substr_count($page, 'data-guide-body="en"'));
        $this->assertSame(5, substr_count($page, 'data-guide-body="bn"'));
        $this->assertStringContainsString('Measurement ID', $page);
        $this->assertStringContainsString('সেভ করুন', $page);
    }

    public function test_a_browser_filled_login_password_is_never_saved_as_a_secret(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => bcrypt('my-login-pass')]);
        Setting::put('tracking_meta_capi_token', 'my-login-pass'); // filled by a password manager earlier

        $page = $this->actingAs($admin)->get(route('admin.integrations.index'))->assertOk()->getContent();
        $this->assertEmpty(setting('tracking_meta_capi_token'));
        $this->assertStringNotContainsString('type="password" class=', $page);
        $this->assertStringContainsString('name="tracking_ga4_api_secret" type="password" autocomplete="new-password"', $page);

        $this->actingAs($admin)->put(route('admin.integrations.update', 'tracking'), [
            'tracking_ga4_id' => 'G-ABC123', 'tracking_ga4_api_secret' => 'my-login-pass', 'tracking_meta_capi_token' => 'EAAreal',
        ])->assertSessionHasNoErrors();

        $this->assertEmpty(setting('tracking_ga4_api_secret'));
        $this->assertSame('EAAreal', setting('tracking_meta_capi_token'));
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

    public function test_browser_shop_events_are_forwarded_to_meta_with_the_pixel_event_id(): void
    {
        $this->saveAll();
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $customer = User::factory()->create(['phone' => '01712345678', 'email' => 'Karim@Example.com']);

        $html = $this->get('/')->getContent();
        $this->assertStringContainsString("fbq('track', 'PageView', {}, { eventID: id })", $html, 'The page view pixel carries an event id');
        $this->assertStringContainsString("window.vtCapi('PageView', id)", $html);
        $this->assertStringContainsString("fetch('/se'", $html);

        $this->actingAs($customer)->withCredentials()->withUnencryptedCookies(['_fbp' => 'fb.1.111.222'])
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) Safari/604.1'])
            ->postJson(route('tracking.meta'), [
                'event' => 'AddToCart', 'event_id' => 'AddToCart-1761200000000-abc123', 'url' => url('/product/bifold'),
                'custom' => ['value' => 1000, 'content_ids' => ['7'], 'contents' => [['id' => '7', 'quantity' => 1, 'item_price' => 1000]],
                    'content_name' => 'Bifold', 'num_items' => 1],
            ])->assertNoContent();

        Http::assertSent(function (HttpRequest $r) use ($customer) {
            $e = $r['data'][0];

            return str_contains($r->url(), 'graph.facebook.com/v21.0/123456789012/events')
                && $e['event_name'] === 'AddToCart'
                && $e['event_id'] === 'AddToCart-1761200000000-abc123'
                && $e['event_source_url'] === url('/product/bifold')
                && $e['user_data']['fbp'] === 'fb.1.111.222'
                && $e['user_data']['ph'] === hash('sha256', '8801712345678')
                && $e['user_data']['em'] === hash('sha256', 'karim@example.com')
                && $e['user_data']['external_id'] === hash('sha256', 'user-' . $customer->id)
                && $e['custom_data']['value'] == 1000
                && $e['custom_data']['contents'] === [['id' => '7', 'quantity' => 1, 'item_price' => 1000.0]]
                && $e['custom_data']['currency'] === 'BDT';
        });
    }

    public function test_bots_bad_events_and_a_switched_off_conversions_api_send_nothing(): void
    {
        Http::fake();
        $browser = ['User-Agent' => 'Mozilla/5.0 (Linux; Android 14) Chrome/130.0 Mobile Safari/537.36'];
        $pageView = ['event' => 'PageView', 'event_id' => 'PageView-1-abc', 'url' => 'https://elsewhere.example/page'];

        // Conversions API off: the endpoint quietly does nothing and the page has no relay
        $this->withHeaders($browser)->postJson(route('tracking.meta'), $pageView)->assertNoContent();
        $this->assertStringNotContainsString('window.vtCapi', $this->get('/')->getContent());

        $this->saveAll();
        $this->withHeaders(['User-Agent' => 'facebookexternalhit/1.1'])->postJson(route('tracking.meta'), $pageView)->assertNoContent();
        $this->withHeaders($browser)->postJson(route('tracking.meta'), ['event' => 'Purchase', 'event_id' => 'x'])->assertStatus(422);
        $this->withHeaders($browser)->postJson(route('tracking.meta'), ['event' => 'PageView', 'event_id' => '<script>'])->assertStatus(422);
        Http::assertNothingSent();

        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $this->withHeaders($browser)->postJson(route('tracking.meta'), $pageView)->assertNoContent();
        Http::assertSent(fn (HttpRequest $r) => $r['data'][0]['event_name'] === 'PageView'
            && ! isset($r['data'][0]['custom_data'])
            && $r['data'][0]['event_source_url'] === url('/'));
    }

    public function test_admin_is_warned_when_tag_manager_loads_the_same_pixel_or_ga4(): void
    {
        $this->saveAll(['tracking_gtm_id' => 'gtm-abc123']);
        $admin = User::factory()->create(['role' => 'admin']);
        // A container with a Meta Pixel tag (same pixel id) and a GA4 tag, like the one on Unilife
        Http::fake(['www.googletagmanager.com/*' => Http::response('var data={"macros":[{"function":"__c","vtp_value":"G-ABC123"},{"function":"__c","vtp_value":"123456789012"}]};"https://connect.facebook.net/en_US/fbevents.js"')]);

        $page = $this->actingAs($admin)->get(route('admin.integrations.index'))->assertOk()->getContent();
        $this->assertStringContainsString('data-gtm-overlap', $page);
        $this->assertStringContainsString('Your Tag Manager container GTM-ABC123 also loads Meta Pixel 123456789012 and GA4 G-ABC123.', $page);
        $this->assertStringContainsString('never match the server-side ones', $page);
        Http::assertSent(fn (HttpRequest $r) => $r->url() === 'https://www.googletagmanager.com/gtm.js?id=GTM-ABC123');

        // Kept for a day: reloading the page does not fetch Tag Manager again
        $this->actingAs($admin)->get(route('admin.integrations.index'))->assertOk();
        Http::assertSentCount(1);
    }

    public function test_check_again_picks_up_a_cleaned_tag_manager_container(): void
    {
        $this->saveAll(['tracking_gtm_id' => 'GTM-ABC123']);
        $admin = User::factory()->create(['role' => 'admin']);
        Http::fake(['www.googletagmanager.com/*' => Http::sequence()
            ->push('{"vtp_value":"123456789012"}')
            ->push('{"function":"__clarity"}')]);

        $this->actingAs($admin)->get(route('admin.integrations.index'))->assertSee('also loads Meta Pixel 123456789012', false);

        $this->actingAs($admin)->post(route('admin.integrations.check-gtm'))
            ->assertRedirect(route('admin.integrations.index', ['tab' => 'tracking']))
            ->assertSessionHas('status', 'Tag Manager checked: no duplicate tags.');
        $this->actingAs($admin)->get(route('admin.integrations.index'))->assertOk()->assertDontSee('data-gtm-overlap', false);
    }

    public function test_no_warning_without_tag_manager_and_a_clear_note_when_it_cannot_be_checked(): void
    {
        $this->saveAll();
        $admin = User::factory()->create(['role' => 'admin']);
        Http::fake(['www.googletagmanager.com/*' => Http::response('Not found', 404)]);
        $this->actingAs($admin)->get(route('admin.integrations.index'))->assertOk()->assertDontSee('data-gtm-overlap', false);
        Http::assertNothingSent();

        $this->saveAll(['tracking_gtm_id' => 'GTM-NOPE1']);
        $this->actingAs($admin)->get(route('admin.integrations.index'))->assertOk()
            ->assertSee('Could not check Tag Manager container GTM-NOPE1')
            ->assertSee('Tag Manager has no published container with this ID.');
    }
}
