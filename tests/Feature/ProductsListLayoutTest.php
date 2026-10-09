<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsListLayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets']);
        foreach ([['Plenty', 40, true], ['Nearly gone', 2, true], ['Sold out', 0, true], ['Unfinished', 12, false]] as [$name, $stock, $published]) {
            Product::create(['category_id' => $category->id, 'name' => $name, 'slug' => str($name)->slug(),
                'regular_price' => 1000, 'stock_quantity' => $stock, 'is_published' => $published]);
        }
    }

    private function names(string $show): array
    {
        $res = $this->actingAs($this->admin)->get(route('admin.products.index', ['show' => $show]))->assertOk();

        return $res->viewData('products')->pluck('name')->sort()->values()->all();
    }

    public function test_quick_views_filter_the_list_and_show_counts(): void
    {
        $this->assertSame(['Nearly gone'], $this->names('low'));
        $this->assertSame(['Sold out'], $this->names('out'));
        $this->assertSame(['Unfinished'], $this->names('draft'));
        $this->assertCount(3, $this->names('published'));
        $this->assertCount(4, $this->names('nonsense'));

        $res = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $this->assertSame(['all' => 4, 'published' => 3, 'draft' => 1, 'low' => 1, 'out' => 1], $res->viewData('counts'));
    }

    public function test_rows_show_stock_chips_and_keep_delete_in_the_menu(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.products.index'))->getContent();

        $this->assertStringContainsString('Out of stock</span>', $html);
        $this->assertStringContainsString('Low · 2', $html);
        $this->assertSame(8, substr_count($html, 'data-product-menu'));
        $this->assertStringNotContainsString('title="Delete product"', $html);
        $this->assertStringNotContainsString('>Filter</button>', $html);
    }

    public function test_bulk_publish_and_hide(): void
    {
        $ids = Product::whereIn('name', ['Plenty', 'Unfinished'])->pluck('id')->implode(',');

        $this->actingAs($this->admin)->post(route('admin.products.bulk-status'), ['ids' => $ids, 'status' => 'hide'])
            ->assertSessionHas('status', 'Moved to draft 2 products.');
        $this->assertSame(2, Product::where('is_published', false)->count());

        $this->actingAs($this->admin)->post(route('admin.products.bulk-status'), ['ids' => $ids, 'status' => 'publish'])
            ->assertSessionHas('status', 'Published 2 products.');
        $this->assertSame(0, Product::where('is_published', false)->count());

        $this->actingAs($this->admin)->post(route('admin.products.bulk-status'), ['ids' => $ids, 'status' => 'delete'])
            ->assertSessionHasErrors('status');
    }
}
