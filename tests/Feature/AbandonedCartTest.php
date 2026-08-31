<?php

namespace Tests\Feature;

use App\Models\AbandonedCart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbandonedCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_without_phone_does_not_create_abandoned_cart(): void
    {
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sample Wireless Earbuds',
            'slug' => 'sample-wireless-earbuds',
            'regular_price' => 1000,
            'stock_quantity' => 10,
            'is_published' => true,
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 2]);
        $response = $this->get(route('checkout.show'));
        $response->assertStatus(200);

        // Should NOT create abandoned cart when no phone number was entered
        $this->assertDatabaseMissing('abandoned_carts', [
            'subtotal' => 2000,
        ]);
    }

    public function test_guest_contact_sync_updates_abandoned_cart_details(): void
    {
        $category = Category::create(['name' => 'Tech', 'slug' => 'tech']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Smart Watch',
            'slug' => 'smart-watch',
            'regular_price' => 1500,
            'stock_quantity' => 5,
            'is_published' => true,
        ]);

        $this->post(route('cart.add'), ['product_id' => $product->id, 'qty' => 1]);

        $response = $this->postJson(route('checkout.sync-contact'), [
            'customer_name'  => 'Rahim Uddin',
            'customer_phone' => '01712345678',
            'customer_email' => 'rahim@example.com',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('abandoned_carts', [
            'customer_name'  => 'Rahim Uddin',
            'customer_phone' => '01712345678',
            'customer_email' => 'rahim@example.com',
        ]);
    }

    public function test_one_click_recovery_link_restores_cart_session(): void
    {
        $cart = AbandonedCart::create([
            'session_id'     => 'test-session-123',
            'customer_name'  => 'Test Customer',
            'customer_phone' => '01711000000',
            'customer_email' => 'customer@example.com',
            'cart_data'      => [
                '10||' => [
                    'product_id' => 10,
                    'qty'        => 2,
                    'variant'    => null,
                    'sku_id'     => null,
                ]
            ],
            'subtotal'       => 1000,
            'total'          => 1060,
            'status'         => 'abandoned',
            'recovery_token' => 'test-recovery-token-xyz',
        ]);

        $response = $this->get(route('cart.recover', 'test-recovery-token-xyz'));
        $response->assertRedirect(route('checkout.show'));

        $this->assertEquals(2, session('cart')['10||']['qty'] ?? null);
    }

    public function test_admin_can_access_abandoned_carts_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.abandoned-carts.index'));
        $response->assertStatus(200);
        $response->assertSee('Abandoned Cart Recovery');
    }
}
