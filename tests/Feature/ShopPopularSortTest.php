<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopPopularSortTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, array $attrs = []): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'leather'], ['name' => 'Leather', 'is_active' => true]);

        return Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'regular_price' => 1000, 'stock_quantity' => 10, 'is_published' => true] + $attrs);
    }

    private function sell(Product $product, int $qty, string $status = 'delivered'): void
    {
        $order = Order::create(['order_number' => 'VB-' . uniqid(), 'customer_name' => 'Buyer', 'customer_phone' => '01711000000',
            'shipping_address' => 'House 1', 'city' => 'Dhaka', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => $status]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => $product->name,
            'unit_price' => 1000, 'quantity' => $qty, 'line_total' => 1000 * $qty]);
    }

    public function test_popular_puts_best_sellers_first_then_most_sold_then_newest(): void
    {
        $old = $this->product('Old Seller');
        $old->forceFill(['created_at' => now()->subDays(10)])->save();
        $this->product('Flagged Best', ['is_best_seller' => true])->forceFill(['created_at' => now()->subDays(20)])->save();
        $this->product('Brand New');
        $this->sell($old, 5);
        $this->sell(Product::where('name', 'Brand New')->first(), 9, 'cancelled');

        $html = $this->get(route('shop'))->assertOk()->getContent();
        $this->assertTrue(strpos($html, 'Flagged Best') < strpos($html, 'Old Seller'), 'Marked Best seller first');
        $this->assertTrue(strpos($html, 'Old Seller') < strpos($html, 'Brand New'), 'Then most sold; cancelled orders do not count');

        $html = $this->get(route('shop', ['sort' => 'newest']))->assertOk()->getContent();
        $this->assertTrue(strpos($html, 'Brand New') < strpos($html, 'Old Seller'), 'Newest sort is unchanged');
    }
}
