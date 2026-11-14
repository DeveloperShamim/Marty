<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\LaunchMode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaunchModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    private function wallet(string $name, array $attrs = []): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'wallets'], ['name' => 'Wallets', 'is_active' => true]);

        return Product::create(array_merge([
            'category_id' => $cat->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'regular_price' => 2450, 'stock_quantity' => 5, 'is_published' => true,
        ], $attrs));
    }

    public function test_off_by_default_so_the_normal_homepage_shows(): void
    {
        $this->wallet('Prime Trifold');

        $this->get('/')->assertOk()->assertDontSee('lx-page', false)->assertDontSee('The Initial Collection');
    }

    public function test_on_shows_the_picked_products_in_order_and_skips_hidden_ones(): void
    {
        $a = $this->wallet('Prime Trifold');
        $b = $this->wallet('Classic Bifold');
        $hidden = $this->wallet('Secret Wallet', ['is_published' => false]);
        $this->wallet('Not Picked');
        LaunchMode::save(['product_ids' => [$b->id, $hidden->id, $a->id]], true);

        $this->get('/')->assertOk()
            ->assertSee('Batch 01 — Limited Release')
            ->assertSee('The Initial Collection')
            ->assertSeeInOrder(['Classic Bifold', 'Prime Trifold'])
            ->assertDontSee('Secret Wallet')
            ->assertDontSee('Not Picked')
            ->assertSee('Find Your Carry Profile') // 2 products, so the comparison shows
            ->assertSee('Genuine leather wallets.') // default hero headline
            ->assertSee('class="lx-rot"', false) // 2 products, so the hero is the rotating product card
            ->assertSee('Buy now')
            ->assertSee('View product');
    }

    public function test_admin_can_rename_the_batch_pick_products_and_switch_it_off(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $a = $this->wallet('Prime Trifold');

        $this->actingAs($admin)->get(route('admin.launch-mode.index'))->assertOk()->assertSee('Launch mode');

        $this->actingAs($admin)->put(route('admin.launch-mode.update'), [
            'launch_mode' => '1',
            'hero' => ['badge' => 'New', 'title' => 'Wallets that last', 'text' => 'Short line', 'button' => 'Buy now'],
            'collection' => ['label' => 'Batch 02 — Limited Release', 'heading' => 'Second run', 'intro' => ''],
            'product_ids' => [$a->id, '', ''],
            'compare' => ['on' => '1', 'rows' => [['label' => 'Cards', 'values' => [$a->id => '6–9']]]],
        ])->assertRedirect();
        Setting::forgetCache();

        $this->assertTrue(LaunchMode::isOn());
        $this->assertSame([$a->id], LaunchMode::config()['product_ids']);
        $this->get('/')->assertSee('Batch 02 — Limited Release')->assertSee('Prime Trifold')
            ->assertSee('Wallets that last')->assertSee('Buy now');

        $this->actingAs($admin)->put(route('admin.launch-mode.update'), ['launch_mode' => '0'])->assertRedirect();
        Setting::forgetCache();
        $this->assertFalse(LaunchMode::isOn());
        $this->get('/')->assertDontSee('lx-page', false);
    }

    public function test_admin_can_upload_and_remove_material_pictures(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $base = ['launch_mode' => '1', 'material' => ['on' => '1', 'pillars' => [['title' => 'One'], ['title' => 'Two']]]];

        $this->actingAs($admin)->put(route('admin.launch-mode.update'), $base + [
            'material_image' => \Illuminate\Http\UploadedFile::fake()->image('main.jpg', 600, 600),
            'pillar_image' => [1 => \Illuminate\Http\UploadedFile::fake()->image('two.jpg', 600, 600)],
        ])->assertRedirect();
        Setting::forgetCache();
        $m = LaunchMode::config()['material'];
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($m['image']);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($m['pillars'][1]['image']);
        $this->get('/')->assertSee('data-lx-mat-img', false);

        // Saving again without files keeps them; ticking Remove deletes the file
        $this->actingAs($admin)->put(route('admin.launch-mode.update'), $base + ['material_image_remove' => '1'])->assertRedirect();
        Setting::forgetCache();
        $m2 = LaunchMode::config()['material'];
        $this->assertNull($m2['image']);
        $this->assertSame($m['pillars'][1]['image'], $m2['pillars'][1]['image']);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($m['image']);
    }

    public function test_hero_picture_and_colour_pictures_replace_the_product_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $a = $this->wallet('Prime Trifold');

        $this->actingAs($admin)->put(route('admin.launch-mode.update'), [
            'launch_mode' => '1', 'product_ids' => [$a->id],
            'hero_image' => \Illuminate\Http\UploadedFile::fake()->image('hero.jpg', 1200, 1200),
            'hero_color_image' => ['black' => \Illuminate\Http\UploadedFile::fake()->image('black.jpg', 1200, 1200)],
        ])->assertRedirect();
        Setting::forgetCache();
        $h = LaunchMode::config()['hero'];
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($h['image']);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($h['color_images']['black']);
        $this->get('/')->assertSee(basename($h['image']), false);

        $this->actingAs($admin)->put(route('admin.launch-mode.update'), [
            'launch_mode' => '1', 'product_ids' => [$a->id], 'hero_image_remove' => '1', 'hero_color_image_remove' => ['black' => '1'],
        ])->assertRedirect();
        Setting::forgetCache();
        $h2 = LaunchMode::config()['hero'];
        $this->assertNull($h2['image']);
        $this->assertSame([], $h2['color_images']);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($h['image']);
    }

    public function test_rotating_card_shows_with_two_products_and_can_be_switched_off(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $a = $this->wallet('Prime Trifold');
        $b = $this->wallet('Classic Bifold', ['stock_quantity' => 0]);

        // One product: the single photo with its colour dots, no card
        LaunchMode::save(['product_ids' => [$a->id]], true);
        Setting::forgetCache();
        $this->get('/')->assertOk()->assertDontSee('class="lx-rot"', false)->assertSee('class="lx-hero-card"', false);

        // Two products: one slide per product, a dot each, Buy now goes to checkout, sold out can't be bought
        LaunchMode::save(['product_ids' => [$a->id, $b->id]], true);
        Setting::forgetCache();
        $html = $this->get('/')->assertOk()->assertSee('class="lx-rot"', false)->assertDontSee('class="lx-hero-card"', false)->getContent();
        $this->assertSame(2, substr_count($html, 'class="lx-rot-slide"'));
        $this->assertSame(2, substr_count($html, 'data-lx-rot-dot="'));
        $this->assertSame(1, substr_count($html, '>Buy now</button>'));
        $this->assertStringContainsString('aria-label="2 of 2: Classic Bifold"', $html);

        // The admin switch turns it off and the single photo comes back
        $this->actingAs($admin)->get(route('admin.launch-mode.index'))->assertSee('Rotating product card');
        $this->actingAs($admin)->put(route('admin.launch-mode.update'), [
            'launch_mode' => '1', 'product_ids' => [$a->id, $b->id], 'hero' => ['rotate' => '0'],
        ])->assertRedirect();
        Setting::forgetCache();
        $this->assertFalse(LaunchMode::config()['hero']['rotate']);
        $this->get('/')->assertDontSee('class="lx-rot"', false)->assertSee('class="lx-hero-card"', false);
    }

    public function test_product_page_leaves_out_the_empty_reviews_box_and_the_deals_ticker_only_in_launch_mode(): void
    {
        $p = $this->wallet('Prime Trifold');
        Setting::put('header_promo_text', 'Use code HELLO for a deal');

        $this->get(route('product.show', $p))->assertOk()
            ->assertSee('id="reviews"', false)
            ->assertSee('class="promo-marquee', false);

        LaunchMode::save(['product_ids' => [$p->id]], true);
        Setting::forgetCache();

        $this->get(route('product.show', $p))->assertOk()
            ->assertDontSee('id="reviews"', false)
            ->assertDontSee('class="promo-marquee', false);
    }

    public function test_desktop_scroll_hint_shows_for_five_or_more_products_only(): void
    {
        $ids = collect(range(1, 5))->map(fn ($i) => $this->wallet("Wallet {$i}")->id)->all();

        LaunchMode::save(['product_ids' => array_slice($ids, 0, 4)], true);
        $this->get('/')->assertOk()->assertDontSee('Scroll the table sideways');

        LaunchMode::save(['product_ids' => $ids], true);
        Setting::forgetCache();
        $this->get('/')->assertOk()->assertSee('Scroll the table sideways')->assertSee('lx-table-many', false);
    }
}
