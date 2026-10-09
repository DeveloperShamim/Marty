<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use App\Support\ProductCardOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCardOptionsTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, array $skus): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'leather'], ['name' => 'Leather', 'is_active' => true]);
        $product = Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'regular_price' => 1000, 'stock_quantity' => 10, 'is_published' => true]);
        foreach ($skus as $i => [$attrs, $sale, $stock]) {
            ProductSku::create(['product_id' => $product->id, 'sku' => strtoupper(substr($product->slug, 0, 4)) . "-$i",
                'attributes' => $attrs, 'sale_price' => $sale, 'stock_quantity' => $stock, 'is_active' => true]);
        }

        return $product->fresh(['variants', 'skus']);
    }

    public function test_colours_become_dots_and_number_sizes_a_range(): void
    {
        $wallet = $this->product('Bifold', [
            [['Color' => 'Brown'], null, 3], [['Color' => 'Black'], null, 3], [['Color' => 'Tan'], null, 0],
            [['Color' => 'Navy'], null, 1], [['Color' => 'Olive'], null, 1], [['Color' => 'Mint'], null, 1],
        ]);
        $o = ProductCardOptions::for($wallet);
        $this->assertSame(['Brown', 'Black', 'Tan', 'Navy'], array_column($o['colors'], 'name'));
        $this->assertSame('#1c1917', $o['colors'][1]['swatch']);
        $this->assertSame(2, $o['moreColors']);
        $this->assertNull($o['sizeLabel']);
        $this->assertNull($o['fromPrice'], 'Same price for every colour, so no From');

        $shoe = $this->product('Derby', array_map(fn ($s) => [['Size' => (string) $s], null, 2], range(39, 44)));
        $this->assertSame('Sizes 39–44', ProductCardOptions::for($shoe)['sizeLabel']);

        $tee = $this->product('Tee', [[['Size' => 'S'], null, 1], [['Size' => 'M'], null, 1], [['Size' => 'L'], null, 1]]);
        $this->assertSame('3 sizes', ProductCardOptions::for($tee)['sizeLabel']);
    }

    public function test_from_price_shows_when_variations_cost_different_amounts(): void
    {
        $belt = $this->product('Belt', [[['Waist Size' => '32'], 1290, 2], [['Waist Size' => '40'], 1490, 2]]);
        $this->assertSame(1290.0, ProductCardOptions::for($belt)['fromPrice']);

        $html = $this->get(route('shop'))->assertOk()->getContent();
        $this->assertStringContainsString('From</span>', $html);
        $this->assertStringContainsString('Order now', $html);
        $this->assertStringNotContainsString('View Details', $html, 'The photo and name open the product page');
        $this->assertStringContainsString('data-order-now="true"', $html);
    }

    public function test_sold_out_only_when_every_variation_is_out(): void
    {
        $this->product('Partly out', [[['Size' => '40'], null, 0], [['Size' => '41'], null, 2]]);
        $html = $this->get(route('shop'))->assertOk()->getContent();
        $this->assertStringNotContainsString('>Sold out</button>', $html);

        Product::query()->delete();
        $this->product('All out', [[['Size' => '40'], null, 0], [['Size' => '41'], null, 0]]);
        $html = $this->get(route('shop'))->assertOk()->getContent();
        $this->assertStringContainsString('>Sold out</button>', $html);
    }
}
