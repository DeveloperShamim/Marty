<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartRecommendationsTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, Category $category, array $attrs = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $category->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true,
        ], $attrs));
    }

    public function test_suggestions_follow_the_cart_and_skip_what_is_already_in_it(): void
    {
        $shoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
        $bags = Category::create(['name' => 'Bags', 'slug' => 'bags', 'is_active' => true, 'position' => 2]);
        $inCart = $this->product('Runner', $shoes);
        $sameCategory = $this->product('Trail Shoe', $shoes, ['is_best_seller' => true]);
        $this->product('Sold Out Shoe', $shoes, ['stock_quantity' => 0]);
        $this->product('Hidden Shoe', $shoes, ['is_published' => false]);
        $otherCategory = $this->product('Tote Bag', $bags);

        $this->postJson(route('cart.add'), ['product_id' => $inCart->id, 'qty' => 1])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['recs']);

        $html = $this->get(route('cart.recommendations'))->assertOk()->getContent();

        $this->assertStringContainsString('You May Also Like', $html);
        $this->assertStringNotContainsString('>Runner<', $html, 'Already in the cart');
        $this->assertStringNotContainsString('Sold Out Shoe', $html);
        $this->assertStringNotContainsString('Hidden Shoe', $html);
        $this->assertLessThan(strpos($html, 'Tote Bag'), strpos($html, 'Trail Shoe'), 'Same category first, other best sellers after');
        $this->assertStringContainsString('class="add-to-cart', $html);
    }

    public function test_products_with_options_open_the_picker_instead_of_adding_directly(): void
    {
        $shoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
        $sized = $this->product('Sized Shoe', $shoes);
        ProductSku::create(['product_id' => $sized->id, 'attributes' => ['Size' => '42'], 'stock_quantity' => 3]);

        $html = $this->get(route('cart.recommendations'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/data-title="Sized Shoe".*?data-has-variants="true"/s', $html);
    }

    public function test_nothing_is_shown_when_there_is_nothing_to_suggest(): void
    {
        $this->assertSame('', trim($this->get(route('cart.recommendations'))->assertOk()->getContent()));
    }
}
