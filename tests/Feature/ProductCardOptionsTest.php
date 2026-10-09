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
        $this->assertSame('39–44', ProductCardOptions::for($shoe)['sizeShort'], 'Short form for the price line');

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

    public function test_card_is_short_one_order_now_button_and_options_beside_the_price(): void
    {
        $this->product('Derby', array_map(fn ($s) => [['Size' => (string) $s], null, 2], range(39, 44)));
        $html = $this->get(route('shop'))->assertOk()->getContent();
        $card = substr($html, strpos($html, '<article class="fk-card'));
        $card = substr($card, 0, strpos($card, '</article>'));

        $this->assertSame(1, substr_count($card, '<button'), 'One button: Order now (the picker has Add to cart)');
        $this->assertStringNotContainsString('fk-icon-only', $card);
        $this->assertStringNotContainsString('uppercase tracking', $card, 'No brand label above the name');
        $this->assertMatchesRegularExpression('/fk-card-price.*?<\/div>\s*<span[^>]*data-card-options[^>]*>39–44</s', $card, 'Sizes sit on the price line');
    }

    public function test_simple_product_card_has_buy_now_and_a_bag_that_shows_a_tick_when_in_cart(): void
    {
        $cat = Category::firstOrCreate(['slug' => 'leather'], ['name' => 'Leather', 'is_active' => true]);
        $pad = Product::create(['category_id' => $cat->id, 'name' => 'Mouse Pad', 'slug' => 'mouse-pad',
            'regular_price' => 650, 'stock_quantity' => 10, 'is_published' => true]);

        $html = $this->get(route('shop'))->assertOk()->getContent();
        $this->assertStringContainsString('>Buy now</span>', $html);
        $this->assertStringContainsString('data-cart-toggle', $html);
        $this->assertStringNotContainsString('is-in-cart', $html);

        $this->postJson(route('cart.add'), ['product_id' => $pad->id, 'qty' => 1])->assertOk()
            ->assertJsonPath('cart.inCart.' . $pad->id, $pad->id . '||');

        $html = $this->get(route('shop'))->assertOk()->getContent();
        $this->assertStringContainsString('is-in-cart', $html);
        $this->assertStringContainsString('data-cart-key="' . $pad->id . '||"', $html);

        $this->postJson(route('cart.remove'), ['key' => $pad->id . '||'])->assertOk()
            ->assertJsonMissingPath('cart.inCart.' . $pad->id);
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

    public function test_option_picker_leads_with_order_now_and_a_live_status_line(): void
    {
        $html = $this->get(route('shop'))->assertOk()->getContent();
        preg_match('/<div id="quickSelectModal".*?<\/div>\s*<\/div>\s*<\/div>/s', $html, $m);
        $sheet = $m[0] ?? '';
        $this->assertStringContainsString('id="qmBuyTotal"', $sheet, 'Order now carries the total');
        $this->assertStringContainsString('aria-live="polite"', $sheet, 'Status line under the price');
        $this->assertStringNotContainsString('animate-pulse', $sheet);
        $this->assertStringNotContainsString('bg-red-500', $sheet, 'Discount badge uses the theme colour');
        $this->assertStringNotContainsString('Please choose your options below', $sheet);
    }
}
