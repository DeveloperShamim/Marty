<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stock starts at 10; each order holds 2, so 8 is "order active" and 10 is "stock back".
 */
class OrderStockLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private Order $order;
    private User $admin;

    private function makeOrder(string $status = 'pending', ?Coupon $coupon = null): void
    {
        $category = Category::create(['name' => 'C', 'slug' => 'c', 'is_active' => true, 'position' => 1]);
        $this->product = Product::create([
            'category_id' => $category->id, 'name' => 'Shoe', 'slug' => 'shoe',
            'regular_price' => 1000, 'stock_quantity' => 8, 'is_published' => true,
        ]);
        $this->order = Order::create([
            'order_number' => 'ORD-STOCK', 'customer_name' => 'A', 'customer_phone' => '01712345678',
            'shipping_address' => 'X', 'city' => 'Dhaka', 'subtotal' => 2000, 'shipping_charge' => 60,
            'total' => 2060, 'payment_method' => 'cod', 'status' => $status, 'coupon_id' => $coupon?->id,
        ]);
        OrderItem::create([
            'order_id' => $this->order->id, 'product_id' => $this->product->id, 'product_name' => 'Shoe',
            'unit_price' => 1000, 'quantity' => 2, 'line_total' => 2000,
        ]);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function setStatus(string $status): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.orders.update', $this->order), ['status' => $status, 'payment_status' => 'pending'])
            ->assertRedirect();
        $this->order->refresh();
    }

    private function assertStock(int $expected): void
    {
        $this->assertSame($expected, (int) $this->product->fresh()->stock_quantity);
    }

    public function test_cancel_restores_stock_once(): void
    {
        $this->makeOrder();
        $this->setStatus('cancelled');
        $this->assertStock(10);
    }

    public function test_reactivating_a_cancelled_order_takes_stock_and_coupon_use_back(): void
    {
        $coupon = Coupon::create(['code' => 'X', 'type' => 'fixed', 'value' => 50, 'is_active' => true, 'used_count' => 1]);
        $this->makeOrder('pending', $coupon);

        $this->setStatus('cancelled');
        $this->assertSame(0, (int) $coupon->fresh()->used_count);

        $this->setStatus('pending');
        $this->assertStock(8);
        $this->assertSame(1, (int) $coupon->fresh()->used_count);
    }

    public function test_return_then_cancel_does_not_restock_twice(): void
    {
        $this->makeOrder('shipped');
        $this->setStatus('returned');
        $this->setStatus('cancelled');
        $this->assertStock(10);
    }

    public function test_cancel_then_return_does_not_restock_twice(): void
    {
        $this->makeOrder();
        $this->setStatus('cancelled');
        $this->setStatus('returned');
        $this->assertStock(10);
    }

    public function test_reactivated_return_restocks_again_if_returned_a_second_time(): void
    {
        $this->makeOrder('shipped');
        $this->setStatus('returned');
        $this->setStatus('shipped');
        $this->assertStock(8);
        $this->setStatus('returned');
        $this->assertStock(10);
    }

    public function test_rejecting_payment_of_a_returned_order_does_not_restock_again(): void
    {
        $this->makeOrder('shipped');
        $this->setStatus('returned');
        $this->actingAs($this->admin)->post(route('admin.orders.reject', $this->order));
        $this->assertStock(10);
    }

    public function test_deleting_orders_only_restocks_goods_that_never_left(): void
    {
        $this->makeOrder('delivered');
        $this->actingAs($this->admin)->delete(route('admin.orders.destroy', $this->order));
        $this->assertStock(8);
    }

    public function test_deleting_a_pending_order_restocks(): void
    {
        $this->makeOrder('pending');
        $this->actingAs($this->admin)->delete(route('admin.orders.destroy', $this->order));
        $this->assertStock(10);
    }

    public function test_deleting_a_returned_order_does_not_restock_again(): void
    {
        $this->makeOrder('shipped');
        $this->setStatus('returned');
        $this->actingAs($this->admin)->delete(route('admin.orders.destroy', $this->order));
        $this->assertStock(10);
    }
}
