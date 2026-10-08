<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartDrawerLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function test_raising_quantity_past_the_limit_returns_a_readable_message(): void
    {
        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold',
            'regular_price' => 1000, 'stock_quantity' => 10, 'is_published' => true]);

        $this->postJson(route('cart.add'), ['product_id' => $product->id])->assertOk();
        $key = array_key_first(session('cart'));

        $this->postJson(route('cart.update'), ['key' => $key, 'qty' => 3])->assertOk()->assertJsonPath('cart.count', 3);
        // The drawer's + button sends 4: the shopper sees why, not a validation code.
        $this->postJson(route('cart.update'), ['key' => $key, 'qty' => 4])->assertStatus(422)
            ->assertJsonPath('message', 'Maximum 3 items allowed per product variant.');

        $product->update(['stock_quantity' => 2]);
        $this->postJson(route('cart.update'), ['key' => $key, 'qty' => 3])->assertStatus(422)
            ->assertJsonPath('message', 'Only 2 of "Bifold" available in stock.');
    }
}
