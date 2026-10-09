<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        foreach (['steadfast_enabled' => '1', 'steadfast_api_key' => 'k', 'steadfast_secret_key' => 's'] as $k => $v) {
            Setting::put($k, $v);
        }
    }

    private function order(string $number, array $attrs = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => $number, 'customer_name' => 'Karim', 'customer_phone' => '01711000000', 'city' => 'Dhaka',
            'shipping_address' => 'House 1, Mirpur 10', 'subtotal' => 1000, 'shipping_charge' => 60, 'total' => 1060,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'status' => 'pending',
        ], $attrs));
        $order->items()->create(['product_name' => 'Runner', 'unit_price' => 1000, 'quantity' => 1, 'line_total' => 1000]);

        return $order;
    }

    private function bulk(array $body)
    {
        return $this->actingAs($this->admin)->postJson(route('admin.orders.bulk'), $body);
    }

    public function test_bulk_confirm_only_touches_new_cod_or_paid_orders(): void
    {
        $this->order('ORD-NEW');
        $this->order('ORD-BK', ['payment_method' => 'bkash']);
        $this->order('ORD-DONE', ['status' => 'shipped']);

        $res = $this->bulk(['action' => 'confirm', 'orders' => ['ORD-NEW', 'ORD-BK', 'ORD-DONE']])->assertOk();

        $this->assertSame(['ORD-NEW'], $res['done']);
        $this->assertSame(['payment needs checking first', 'already shipped'], array_column($res['skipped'], 'reason'));
        $this->assertSame('confirmed', Order::where('order_number', 'ORD-NEW')->value('status'));
        $this->assertSame('pending', Order::where('order_number', 'ORD-BK')->value('status'));
    }

    public function test_bulk_courier_books_ready_orders_and_reports_the_rest(): void
    {
        Http::fakeSequence('portal.packzy.com/api/v1/create_order')
            ->push(['status' => 200, 'consignment' => ['tracking_code' => 'SF1']])
            ->push(['status' => 400, 'message' => 'Invalid address'], 400);
        $this->order('ORD-A', ['status' => 'confirmed']);
        $this->order('ORD-B', ['status' => 'processing']);
        $this->order('ORD-NEW');
        $this->order('ORD-BOOKED', ['status' => 'confirmed', 'courier_tracking_code' => 'X1', 'courier_name' => 'steadfast']);

        $res = $this->bulk(['action' => 'courier', 'provider' => 'steadfast', 'orders' => ['ORD-A', 'ORD-B', 'ORD-NEW', 'ORD-BOOKED']])->assertOk();

        $this->assertSame(['ORD-A'], $res['done']);
        $this->assertSame(['ORD-B'], array_column($res['failed'], 'order'));
        $this->assertSame(['ORD-NEW', 'ORD-BOOKED'], array_column($res['skipped'], 'order'));
        Http::assertSentCount(2); // never books a new or already-booked order
        $a = Order::where('order_number', 'ORD-A')->first();
        $this->assertSame(['shipped', 'SF1'], [$a->status, $a->courier_tracking_code]);
        $this->assertSame('processing', Order::where('order_number', 'ORD-B')->value('status'));
    }

    public function test_bulk_courier_needs_a_connected_courier(): void
    {
        $this->order('ORD-A', ['status' => 'confirmed']);
        $this->bulk(['action' => 'courier', 'provider' => 'redx', 'orders' => ['ORD-A']])->assertStatus(422);
    }

    public function test_bulk_cancel_restocks_and_skips_parcels_with_the_courier(): void
    {
        $category = \App\Models\Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold',
            'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true]);
        $open = $this->order('ORD-OPEN', ['status' => 'confirmed']);
        $open->items()->update(['product_id' => $product->id, 'quantity' => 2]);
        $this->order('ORD-SHIP', ['status' => 'shipped', 'courier_sent_at' => now()]);

        $res = $this->bulk(['action' => 'cancel', 'orders' => ['ORD-OPEN', 'ORD-SHIP']])->assertOk();

        $this->assertSame(['ORD-OPEN'], $res['done']);
        $this->assertSame('already with the courier', $res['skipped'][0]['reason']);
        $this->assertSame(['cancelled', 'rejected'], [$open->fresh()->status, $open->fresh()->payment_status]);
        $this->assertSame(7, (int) $product->fresh()->stock_quantity);
        $this->assertSame('shipped', Order::where('order_number', 'ORD-SHIP')->value('status'));
    }

    public function test_order_list_offers_the_bulk_actions(): void
    {
        $this->actingAs($this->admin)->get(route('admin.orders.index'))->assertOk()
            ->assertSee('id="bulkConfirm"', false)->assertSee('Send to courier')->assertSee('Cancel orders')
            ->assertSee('"steadfast":"Steadfast"', false);
    }
}
