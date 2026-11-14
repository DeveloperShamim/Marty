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
            ->assertSee('lx-dark', false);
    }

    public function test_admin_can_rename_the_batch_pick_products_and_switch_it_off(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $a = $this->wallet('Prime Trifold');

        $this->actingAs($admin)->get(route('admin.launch-mode.index'))->assertOk()->assertSee('Launch mode');

        $this->actingAs($admin)->put(route('admin.launch-mode.update'), [
            'launch_mode' => '1', 'dark' => '0',
            'collection' => ['label' => 'Batch 02 — Limited Release', 'heading' => 'Second run', 'intro' => ''],
            'product_ids' => [$a->id, '', ''],
            'compare' => ['on' => '1', 'rows' => [['label' => 'Cards', 'values' => [$a->id => '6–9']]]],
        ])->assertRedirect();
        Setting::forgetCache();

        $this->assertTrue(LaunchMode::isOn());
        $this->assertFalse(LaunchMode::isDark());
        $this->assertSame([$a->id], LaunchMode::config()['product_ids']);
        $this->get('/')->assertSee('Batch 02 — Limited Release')->assertSee('Prime Trifold')->assertDontSee('lx-dark', false);

        $this->actingAs($admin)->put(route('admin.launch-mode.update'), ['launch_mode' => '0'])->assertRedirect();
        Setting::forgetCache();
        $this->assertFalse(LaunchMode::isOn());
        $this->get('/')->assertDontSee('lx-page', false);
    }
}
