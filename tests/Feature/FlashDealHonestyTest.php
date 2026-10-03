<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashDealHonestyTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $stock): Product
    {
        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);

        return Product::create(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold', 'regular_price' => 1000,
            'sale_price' => 800, 'stock_quantity' => $stock, 'is_published' => true, 'is_flash_sale' => true, 'flash_sale_progress' => 91]);
    }

    private function sell(Product $product, int $qty, string $status): void
    {
        $order = Order::create(['order_number' => 'VB-' . random_int(100000, 999999), 'customer_name' => 'K', 'customer_phone' => '01711000000',
            'city' => 'Dhaka', 'shipping_address' => 'H1', 'subtotal' => 800, 'shipping_charge' => 60, 'total' => 860,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => $status]);
        $order->items()->create(['product_id' => $product->id, 'product_name' => 'Bifold', 'quantity' => $qty, 'unit_price' => 800, 'line_total' => 800 * $qty]);
    }

    public function test_no_made_up_percentage_before_any_sale(): void
    {
        $product = $this->product(40);

        $this->assertSame(['sold' => 0, 'left' => 40, 'percent' => null], $product->flashStats());
        $this->get(route('product.show', $product))->assertOk()
            ->assertDontSee('claimed')->assertDontSee('91%')->assertDontSee('left at this price');
    }

    public function test_counts_only_real_sales(): void
    {
        $product = $this->product(6);
        $this->sell($product, 3, 'delivered');
        $this->sell($product, 5, 'cancelled'); // not counted
        $this->sell($product, 2, 'returned');  // not counted

        $this->assertSame(['sold' => 3, 'left' => 6, 'percent' => 33], $product->flashStats());
        // The product page has no flash-deal box; the product card shows the real low stock.
        $this->get(route('product.show', $product))->assertOk()->assertDontSee('Limited Time Deal');
    }
}
