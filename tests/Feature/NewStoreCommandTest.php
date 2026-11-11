<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** php artisan store:new turns a fresh copy into a blank store for a new client. */
class NewStoreCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    public function test_a_fresh_copy_becomes_the_new_clients_store(): void
    {
        $this->seed(DatabaseSeeder::class); // Vant's settings, categories, brand, coupons and banners

        $this->artisan('store:new', [
            '--name' => 'Glow Mart', '--type' => 'beauty', '--phone' => '01811-223344',
            '--email' => 'hello@glowmart.com', '--admin-email' => 'owner@glowmart.com',
        ])->expectsConfirmation('Set this copy up as "Glow Mart" (Beauty & skincare)? Its categories, coupons, banners and trust strip are replaced.', 'yes')
            ->assertSuccessful();
        Setting::forgetCache();

        $this->assertSame('Glow Mart', setting('site_name'));
        $this->assertSame('GM', setting('order_number_prefix'));
        $this->assertEmpty(setting('logo'));
        $this->assertEmpty(setting('bkash_number'));
        $this->assertSame('nunito', setting('theme_font'));
        $this->assertSame(['Skincare', 'Makeup', 'Hair care', 'Fragrance', 'Bath & body', 'Men\'s grooming'], Category::orderBy('position')->pluck('name')->all());
        $this->assertSame(0, Brand::count());
        $this->assertSame(['WELCOME10'], Coupon::pluck('code')->all());
        $this->assertSame(1, Banner::count());
        $this->assertTrue(User::where('email', 'owner@glowmart.com')->where('role', 'admin')->exists());

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('Glow Mart', $html);
        $this->assertStringNotContainsStringIgnoringCase('vant', strip_tags($html), 'No trace of the old shop on the homepage');
        $this->assertStringContainsString('data:image/svg+xml;base64,', $html, 'Favicon is the shop initial until an icon is uploaded');
    }

    public function test_it_refuses_a_store_that_already_has_products(): void
    {
        $cat = Category::create(['name' => 'Bags', 'slug' => 'bags', 'is_active' => true]);
        Product::create(['category_id' => $cat->id, 'name' => 'Tote', 'slug' => 'tote', 'regular_price' => 900, 'stock_quantity' => 3, 'is_published' => true]);

        $this->artisan('store:new', ['--name' => 'X', '--type' => 'general'])->assertFailed();
        $this->assertSame(1, Category::count());
    }

    public function test_unknown_type_is_refused(): void
    {
        $this->artisan('store:new', ['--name' => 'X', '--type' => 'cars'])->assertFailed();
    }

    public function test_order_prefix_from_the_name(): void
    {
        $this->assertSame('KZB', \App\Support\StarterPacks::initials('Kick Zone BD'));
        $this->assertSame('GLO', \App\Support\StarterPacks::initials('Glowmart'));
        $this->assertSame('ORD', \App\Support\StarterPacks::initials('!!!'));
    }
}
