<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeType;
use App\Models\User;
use App\Support\ColorSwatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColorSwatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_picks_a_shade_and_the_product_page_uses_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $color = ProductAttributeType::create(['name' => 'Color', 'slug' => 'color', 'is_active' => true]);
        $cognac = $color->values()->create(['value' => 'Cognac']);
        $brown = $color->values()->create(['value' => 'Brown']);

        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold',
            'regular_price' => 1000, 'stock_quantity' => 5, 'is_published' => true]);
        $product->variants()->create(['type' => 'Color', 'value' => 'Cognac']);
        $product->variants()->create(['type' => 'Color', 'value' => 'Brown']);

        // Before: no saved shade, "Cognac" has no built-in shade, "Brown" uses the built-in one.
        $this->get(route('product.show', $product))->assertOk()->assertSee('background: #7c4a21', false)->assertDontSee('#9a4f1c');

        $this->actingAs($admin)->patch(route('admin.variations.values.update', $cognac), ['color_hex' => '#9A4F1C'])->assertSessionHas('status');
        $this->actingAs($admin)->patch(route('admin.variations.values.update', $brown), ['color_hex' => 'red'])->assertSessionHasErrors('color_hex');
        $this->assertSame('#9a4f1c', $cognac->fresh()->color_hex);

        ColorSwatch::flush();
        $this->get(route('product.show', $product))->assertOk()->assertSee('background: #9a4f1c', false);

        // Reset goes back to automatic.
        $this->actingAs($admin)->patch(route('admin.variations.values.update', $cognac), ['color_hex' => '']);
        $this->assertNull($cognac->fresh()->color_hex);

        $this->actingAs($admin)->get(route('admin.variations.index', ['selected' => $color->id]))->assertOk()->assertSee('Pick the exact colour');
    }
}
