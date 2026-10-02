<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\PaymentInstructions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentInstructionsTest extends TestCase
{
    use RefreshDatabase;

    private function cart(): void
    {
        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Runner', 'slug' => 'runner',
            'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true]);
        $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertOk();
    }

    public function test_admin_sets_account_types_and_hotline(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->putJson(route('admin.settings.update-section', 'payments'), [
            'bkash_number' => '01711111111', 'bkash_account_type' => 'merchant',
            'nagad_number' => '01811111111', 'nagad_account_type' => 'agent',
            'rocket_number' => '01911111111', 'rocket_account_type' => 'personal',
            'order_hotline' => '09612-345678',
            'pay_cod_enabled' => '1', 'pay_bkash_enabled' => '1', 'pay_nagad_enabled' => '1', 'pay_rocket_enabled' => '1',
        ])->assertOk();

        $this->assertSame('Payment', PaymentInstructions::action('bkash'));
        $this->assertSame('Cash Out', PaymentInstructions::action('nagad'));
        $this->assertSame('Send Money', PaymentInstructions::action('rocket'));
        $this->assertSame('tel:09612345678', PaymentInstructions::hotlineHref());

        $this->actingAs($admin)->putJson(route('admin.settings.update-section', 'payments'), ['bkash_account_type' => 'bogus'])
            ->assertStatus(422);
    }

    public function test_checkout_shows_steps_for_the_account_type_and_the_hotline(): void
    {
        Setting::put('bkash_number', '01711111111');
        Setting::put('bkash_account_type', 'merchant');
        Setting::put('contact_phone', '01999999999'); // hotline falls back to the contact phone
        $this->cart();

        $html = $this->get(route('checkout.show', ['payment_method' => 'bkash']))->assertOk()->getContent();
        $this->assertStringContainsString('"bkash":"Payment"', $html);
        $this->assertStringContainsString('as the reference', $html);
        $this->assertStringContainsString('*247#', $html);
        $this->assertStringContainsString('Need help ordering? Call', $html);
        $this->assertStringContainsString('tel:01999999999', $html);
    }

    public function test_confirmation_explains_next_steps_and_hotline(): void
    {
        Setting::put('order_hotline', '01555555555');
        $this->cart();

        $this->post(route('checkout.store'), [
            'customer_name' => 'Rahim', 'customer_phone' => '01712345678', 'shipping_address' => 'House 1',
            'city' => 'Dhaka', 'shipping_zone' => 'inside_dhaka', 'payment_method' => 'cod',
        ])->assertRedirect();

        $order = \App\Models\Order::latest('id')->first();
        $this->get(route('order.confirmation', $order->order_number))->assertOk()
            ->assertSee('What happens next')
            ->assertSee('in cash to the delivery person', false)
            ->assertSee('Need help? Call 01555555555')
            ->assertSee('tel:01555555555', false);
    }
}
