<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeBlock;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\HomeSections;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Homepage → Sections → Add section, the pencil links, and the hero slider settings. */
class HomeBlockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function catalogue(): Category
    {
        $shirts = Category::create(['name' => 'Shirts', 'slug' => 'shirts', 'is_active' => true]);
        $shoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true]);
        foreach ([['Cheap Shirt', 500, $shirts], ['Dear Shirt', 3000, $shirts], ['Mid Shirt', 1200, $shirts], ['Boot', 4000, $shoes]] as [$name, $price, $cat]) {
            Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'regular_price' => $price, 'stock_quantity' => 5, 'is_published' => true]);
        }

        return $shirts;
    }

    public function test_product_row_is_added_at_the_bottom_and_shows_its_category_sorted(): void
    {
        $shirts = $this->catalogue();
        $admin = $this->admin();

        // A section of another kind before it must not stop the row's products loading
        HomeBlock::create(['type' => 'promo_band', 'title' => 'Sale', 'data' => ['bg' => '#000000', 'fg' => '#ffffff']]);
        $this->actingAs($admin)->get(route('admin.home-blocks.create', ['type' => 'products']))->assertOk()->assertSee('Which products');
        $this->actingAs($admin)->post(route('admin.home-blocks.store'), [
            'type' => 'products', 'title' => 'Shirt picks', 'category_id' => $shirts->id,
            'sort' => 'price_low', 'limit' => 2, 'link_label' => 'More shirts',
        ])->assertRedirect(route('admin.home-sections.index'));
        Setting::forgetCache();

        $block = HomeBlock::where('type', 'products')->firstOrFail();
        $this->assertSame('block_' . $block->id, last(HomeSections::ordered()));
        $this->assertSame(['Cheap Shirt', 'Mid Shirt'], $block->products()->pluck('name')->all());

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-home-block="products"', $html);
        $this->assertStringContainsString('Shirt picks', $html);
        $this->assertStringContainsString('More shirts', $html);
        $this->assertStringNotContainsString('Dear Shirt', substr($html, strpos($html, 'data-home-block="products"')), 'Only the 2 cheapest shirts');

        // It shows in the section list with its own pencil, and hiding it there takes it off the homepage
        $this->actingAs($admin)->get(route('admin.home-sections.index'))->assertOk()
            ->assertSee('Shirt picks')->assertSee('Your section')
            ->assertSee(route('admin.home-blocks.edit', $block), false)
            ->assertSee('data-section-edit="coupons"', false);

        $keys = array_column(HomeSections::all(), 'key');
        $this->actingAs($admin)->put(route('admin.home-sections.update'), ['order' => $keys, 'on' => array_values(array_diff($keys, [$block->key()]))])->assertRedirect();
        Setting::forgetCache();
        $this->assertStringNotContainsString('data-home-block="products"', $this->get('/')->getContent());
    }

    public function test_promo_band_stats_and_card_grid_render_in_order(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.home-blocks.store'), [
            'type' => 'promo_band', 'title' => 'Free delivery over 1500', 'subtitle' => 'All of Bangladesh',
            'button_text' => 'Shop now', 'link_url' => '/shop', 'bg' => '#123456', 'fg' => '#ffffff',
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.home-blocks.store'), [
            'type' => 'stats_row', 'title' => 'Trusted',
            'stats' => [['value' => '10,000+', 'label' => 'Customers'], ['value' => '', 'label' => 'dropped']],
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.home-blocks.store'), [
            'type' => 'card_grid', 'title' => 'Shop by occasion', 'columns' => 2,
            'cards' => [
                ['title' => 'Eid', 'text' => 'Festive picks', 'link' => '/shop', 'image_file' => UploadedFile::fake()->image('eid.jpg')],
                ['title' => '', 'text' => '', 'link' => ''],
            ],
        ])->assertRedirect();
        Setting::forgetCache();

        [$band, $stats, $grid] = HomeBlock::orderBy('id')->get()->all();
        $this->assertCount(1, $stats->stats());
        $this->assertCount(1, $grid->cards());
        Storage::disk('public')->assertExists($grid->cards()[0]['image']);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('background: #123456', $html);
        $this->assertStringContainsString('10,000+', $html);
        $this->assertStringContainsString('Festive picks', $html);
        $this->assertLessThan(strpos($html, 'data-home-block="stats_row"'), strpos($html, 'data-home-block="promo_band"'));
        $this->assertLessThan(strpos($html, 'data-home-block="card_grid"'), strpos($html, 'data-home-block="stats_row"'));

        // Bad colour is refused; deleting removes the picture and the section
        $this->actingAs($admin)->put(route('admin.home-blocks.update', $band), ['title' => 'x', 'bg' => 'red'])->assertSessionHasErrors('bg');
        $path = $grid->cards()[0]['image'];
        $this->actingAs($admin)->delete(route('admin.home-blocks.destroy', $grid))->assertRedirect();
        Storage::disk('public')->assertMissing($path);
        $this->assertStringNotContainsString('data-home-block="card_grid"', $this->get('/')->getContent());
    }

    public function test_wide_banner_needs_a_picture_and_uses_the_phone_picture_on_phones(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.home-blocks.store'), ['type' => 'image_banner', 'title' => 'Eid'])->assertSessionHasErrors('image_file');
        $this->assertSame(0, HomeBlock::count());

        $this->actingAs($admin)->post(route('admin.home-blocks.store'), [
            'type' => 'image_banner', 'title' => 'Eid sale', 'link_url' => '/shop',
            'image_file' => UploadedFile::fake()->image('wide.jpg', 1920, 480),
            'image_mobile_file' => UploadedFile::fake()->image('phone.jpg', 800, 500),
        ])->assertRedirect();
        Setting::forgetCache();

        $block = HomeBlock::firstOrFail();
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-home-block="image_banner"', $html);
        $this->assertStringContainsString('media="(max-width: 639px)" srcset="' . image_url($block->get('image_mobile')), $html);
    }

    public function test_slider_settings_and_hero_video(): void
    {
        $admin = $this->admin();
        Banner::create(['placement' => 'hero', 'title' => 'One', 'is_active' => true, 'image' => 'banners/a.jpg', 'image_mobile' => 'banners/a-phone.jpg']);
        Banner::create(['placement' => 'hero', 'title' => 'Two', 'is_active' => true, 'image' => 'banners/b.jpg']);

        $this->actingAs($admin)->get(route('admin.banners.index'))->assertOk()->assertSee('Slider settings');
        $this->actingAs($admin)->put(route('admin.banners.slider-settings'), ['hero_delay' => 7, 'hero_dots' => '1'])->assertRedirect();
        Setting::forgetCache();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-autoplay="0"', $html);
        $this->assertStringContainsString('data-delay="7000"', $html);
        $this->assertStringNotContainsString('data-hero-arrow-prev class', $html);
        $this->assertStringContainsString('data-hero-dot="1"', $html);
        $this->assertStringContainsString('banners/a-phone.jpg', $html);

        $this->actingAs($admin)->put(route('admin.banners.slider-settings'), ['hero_delay' => 5, 'hero_video_url' => 'https://example.com/page'])
            ->assertSessionHasErrors('hero_video_url');
        $this->actingAs($admin)->put(route('admin.banners.slider-settings'), ['hero_delay' => 5, 'hero_video_url' => 'https://youtu.be/dQw4w9WgXcQ'])->assertRedirect();
        Setting::forgetCache();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ?autoplay=1&mute=1', $html);
        $this->assertStringNotContainsString('id="homeHeroSlider"', $html);
        $this->assertSame(['file' => 'https://cdn.example.com/hero.mp4'], Banner::heroVideo('https://cdn.example.com/hero.mp4'));
    }

    public function test_staff_without_homepage_access_are_kept_out(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'order_manager']))
            ->get(route('admin.home-blocks.create', ['type' => 'products']))->assertRedirect();
        $this->actingAs($this->admin())->get(route('admin.home-blocks.create', ['type' => 'nope']))->assertNotFound();
    }
}
