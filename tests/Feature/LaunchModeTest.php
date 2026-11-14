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
            ->assertSee('View collection');
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
}
