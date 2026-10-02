<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_saves_ticker_settings_and_the_storefront_shows_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Coupon::create(['code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.news-ticker.index'))->assertOk()->assertSee('News Ticker')->assertSee('SAVE10');

        $this->actingAs($admin)->put(route('admin.news-ticker.update'), [
            'header_promo_text'  => "Cash on delivery all over Bangladesh\nUse code SAVE10 for 10% OFF",
            'ticker_label'       => 'Notice',
            'ticker_label_style' => 'red',
            // countdown switch left off
        ])->assertRedirect();

        $this->assertSame('0', Setting::get('ticker_show_countdown'));
        Setting::forgetCache();

        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('Cash on delivery all over Bangladesh', $html);
        $this->assertStringContainsString('Notice', $html);
        $this->assertStringContainsString('bg-red-600', $html);
        $this->assertStringContainsString('data-copy-code="SAVE10"', $html);
    }

    public function test_flash_countdown_follows_the_switch_and_end_time(): void
    {
        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
        Product::create(['category_id' => $category->id, 'name' => 'Runner', 'slug' => 'runner', 'regular_price' => 1000,
            'stock_quantity' => 5, 'is_published' => true, 'is_flash_sale' => true]);
        Setting::put('header_promo_text', 'Hello');
        Setting::put('flash_sale_ends_at', now()->addDay()->format('Y-m-d H:i:s'));
        Setting::put('ticker_show_countdown', '1');
        Setting::forgetCache();

        $this->assertStringContainsString('Flash Sale ends in', $this->get(route('home'))->getContent());

        Setting::put('ticker_show_countdown', '0');
        Setting::forgetCache();
        $this->assertStringNotContainsString('Flash Sale ends in', $this->get(route('home'))->getContent());

        Setting::put('ticker_show_countdown', '1');
        Setting::put('flash_sale_ends_at', now()->subMinute()->format('Y-m-d H:i:s'));
        Setting::forgetCache();
        $this->assertStringNotContainsString('Flash Sale ends in', $this->get(route('home'))->getContent());
    }

    public function test_store_manager_can_edit_the_ticker_but_other_staff_cannot(): void
    {
        $manager = User::factory()->create(['role' => 'store_manager']);
        $this->actingAs($manager)->get(route('admin.news-ticker.index'))->assertOk();
        $this->actingAs($manager)->put(route('admin.news-ticker.update'), [
            'header_promo_text' => "One || Two\n\nThree", 'ticker_label' => 'Offers', 'ticker_label_style' => 'dark',
            'ticker_show_countdown' => '1',
        ])->assertRedirect();
        $this->assertSame("One\nTwo\nThree", Setting::get('header_promo_text'));
        $this->assertSame('1', Setting::get('ticker_show_countdown'));

        foreach (['order_manager', 'inventory_manager'] as $role) {
            $staff = User::factory()->create(['role' => $role]);
            $this->actingAs($staff)->get(route('admin.news-ticker.index'))->assertRedirect();
            $this->actingAs($staff)->put(route('admin.news-ticker.update'), ['ticker_label_style' => 'red'])->assertRedirect();
        }
        $this->assertSame('dark', Setting::get('ticker_label_style'));
    }
}
