<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, int $stock): Product
    {
        $category = Category::firstOrCreate(['slug' => 'shoes'], ['name' => 'Shoes', 'is_active' => true, 'position' => 1]);

        return Product::create(['category_id' => $category->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'regular_price' => 1000, 'stock_quantity' => $stock, 'is_published' => true]);
    }

    public function test_sold_out_products_are_not_low_stock(): void
    {
        $two = $this->product('Two Left Runner', 2);
        $out = $this->product('Sold Out Runner', 0);
        $this->product('Plenty Runner', 20);

        $this->assertTrue($two->isLowStock());
        $this->assertFalse($out->isLowStock());
        $this->assertTrue($out->isOutOfStock());

        $admin = User::factory()->create(['role' => 'admin']);
        $dash = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(1, $dash->viewData('lowStockCount'));
        $this->assertSame(['Two Left Runner'], $dash->viewData('lowStockProducts')->pluck('name')->all());
        $this->assertSame(1, $dash->viewData('outOfStockCount'));

        $low = $this->actingAs($admin)->get(route('admin.inventory.index', ['filter' => 'low_stock']))->assertOk();
        $this->assertSame(['Two Left Runner'], $low->viewData('products')->pluck('name')->all());
        $this->assertSame(1, $low->viewData('lowStockCount'));

        $sold = $this->actingAs($admin)->get(route('admin.inventory.index', ['filter' => 'out_of_stock']))->assertOk();
        $this->assertSame(['Sold Out Runner'], $sold->viewData('products')->pluck('name')->all());
    }
}
