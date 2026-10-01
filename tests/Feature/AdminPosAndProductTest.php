<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminPosAndProductTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'admin']);
        $this->category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
    }

    private function makeProduct(int $stock = 5): Product
    {
        return Product::create([
            'category_id' => $this->category->id, 'name' => 'Runner', 'slug' => 'runner-' . uniqid(),
            'regular_price' => 1000, 'stock_quantity' => $stock, 'is_published' => true,
        ]);
    }

    private function posSale(array $items)
    {
        return $this->actingAs($this->staff)->postJson(route('admin.pos.order'), [
            'items' => $items,
            'payment_method' => 'cash',
        ]);
    }

    /* ---------------- POS ---------------- */

    public function test_pos_sale_creates_paid_order_and_reduces_stock(): void
    {
        $product = $this->makeProduct(5);
        ProductImage::create(['product_id' => $product->id, 'path' => 'uploads/products/a.jpg', 'is_primary' => true]);

        $this->posSale([['product_id' => $product->id, 'quantity' => 2, 'price' => 1000]])
            ->assertOk()->assertJson(['success' => true]);

        $order = Order::with('items')->first();
        $this->assertSame('pos', $order->order_type);
        $this->assertEquals(2000, (float) $order->total);
        $this->assertSame('uploads/products/a.jpg', $order->items->first()->image);
        $this->assertSame(3, (int) $product->fresh()->stock_quantity);
    }

    public function test_pos_cannot_sell_more_than_stock(): void
    {
        $product = $this->makeProduct(2);

        // Two lines for the same product together exceed stock.
        $this->posSale([
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 1000],
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 1000],
        ])->assertStatus(422)->assertJson(['success' => false]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(2, (int) $product->fresh()->stock_quantity);
    }

    public function test_pos_sku_sale_reduces_sku_and_keeps_product_total_in_sync(): void
    {
        $product = $this->makeProduct();
        $sku40 = ProductSku::create(['product_id' => $product->id, 'attributes' => ['Size' => '40'], 'stock_quantity' => 3]);
        ProductSku::create(['product_id' => $product->id, 'attributes' => ['Size' => '41'], 'stock_quantity' => 4]);
        $product->syncTotalStock();

        $this->posSale([['product_id' => $product->id, 'product_sku_id' => $sku40->id, 'quantity' => 1, 'price' => 1000]])
            ->assertOk();

        $this->assertSame(2, (int) $sku40->fresh()->stock_quantity);
        $this->assertSame(6, (int) $product->fresh()->stock_quantity);
    }

    public function test_pos_rejects_option_from_another_product_and_missing_option(): void
    {
        $product = $this->makeProduct();
        ProductSku::create(['product_id' => $product->id, 'attributes' => ['Size' => '40'], 'stock_quantity' => 3]);
        $foreignSku = ProductSku::create(['product_id' => $this->makeProduct()->id, 'attributes' => ['Size' => '50'], 'stock_quantity' => 3]);

        $this->posSale([['product_id' => $product->id, 'product_sku_id' => $foreignSku->id, 'quantity' => 1, 'price' => 1]])
            ->assertStatus(422);
        $this->posSale([['product_id' => $product->id, 'quantity' => 1, 'price' => 1]])
            ->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
    }

    /* ---------------- Product form ---------------- */

    private function productPayload(array $overrides = []): array
    {
        return array_merge([
            'category_id' => $this->category->id,
            'name' => 'Trail Shoe',
            'regular_price' => 2000,
            'stock_quantity' => 0,
            'is_published' => '1',
        ], $overrides);
    }

    public function test_admin_can_create_product_with_option_matrix(): void
    {
        $this->actingAs($this->staff)->post(route('admin.products.store'), $this->productPayload([
            'sku_matrix_submitted' => '1',
            'sku_matrix' => [
                ['attributes' => ['Size' => '40'], 'stock' => 3],
                ['attributes' => ['Size' => '41'], 'stock' => 2],
            ],
        ]))->assertRedirect();

        $product = Product::where('name', 'Trail Shoe')->firstOrFail();
        $this->assertSame('trail-shoe', $product->slug);
        $this->assertCount(2, $product->skus);
        $this->assertSame(5, (int) $product->stock_quantity, 'Product stock should be the sum of its options');
        $this->assertNotEmpty($product->skus->first()->sku, 'Option codes are auto-generated');
    }

    public function test_editing_matrix_updates_kept_options_and_removes_dropped_ones(): void
    {
        $product = $this->makeProduct();
        $keep = ProductSku::create(['product_id' => $product->id, 'attributes' => ['Size' => '40'], 'stock_quantity' => 3]);
        ProductSku::create(['product_id' => $product->id, 'attributes' => ['Size' => '41'], 'stock_quantity' => 3]);

        $this->actingAs($this->staff)->put(route('admin.products.update', $product), $this->productPayload([
            'name' => 'Runner',
            'sku_matrix_submitted' => '1',
            'sku_matrix' => [['id' => $keep->id, 'attributes' => ['Size' => '40'], 'stock' => 7]],
        ]))->assertRedirect();

        $this->assertSame([$keep->id], $product->skus()->pluck('id')->all());
        $this->assertSame(7, (int) $keep->fresh()->stock_quantity);
        $this->assertSame(7, (int) $product->fresh()->stock_quantity);
    }

    public function test_renaming_an_option_in_the_picker_keeps_existing_variants(): void
    {
        $product = $this->makeProduct();
        $sku40 = ProductSku::create(['product_id' => $product->id, 'sku' => 'R-40', 'attributes' => ['Shoe Size' => 'EU 40'], 'stock_quantity' => 3]);

        // What the picker submits after renaming "Shoe Size" to "Size" and adding EU 41.
        $this->actingAs($this->staff)->put(route('admin.products.update', $product), $this->productPayload([
            'name' => 'Runner',
            'sizes' => '', 'colors' => '',
            'sku_matrix_submitted' => '1',
            'sku_matrix' => [
                ['id' => $sku40->id, 'attributes' => ['Size' => 'EU 40'], 'sku' => 'R-40', 'stock' => 3, 'is_active' => '1'],
                ['attributes' => ['Size' => 'EU 41'], 'sku' => '', 'stock' => 0, 'is_active' => '1'],
            ],
        ]))->assertRedirect();

        $sku40->refresh();
        $this->assertSame(['Size' => 'EU 40'], $sku40->getAttributesData());
        $this->assertSame(3, (int) $sku40->stock_quantity);
        $this->assertCount(2, $product->skus()->get());
        $this->assertSame(['EU 40', 'EU 41'], $product->variants()->orderBy('position')->pluck('value')->all());
    }

    public function test_duplicate_product_names_get_unique_slugs(): void
    {
        $this->actingAs($this->staff)->post(route('admin.products.store'), $this->productPayload());
        $this->actingAs($this->staff)->post(route('admin.products.store'), $this->productPayload());

        $this->assertEqualsCanonicalizing(['trail-shoe', 'trail-shoe-2'], Product::pluck('slug')->all());
    }

    public function test_description_media_upload_rejects_scripts_and_svg(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'png');
        imagepng(imagecreatetruecolor(4, 4), $tmp);

        foreach (['shell.php', 'shell.phtml'] as $name) {
            $this->actingAs($this->staff)->postJson(route('admin.products.upload-description-media'), [
                'file' => new UploadedFile($tmp, $name, null, null, true),
            ])->assertStatus(422);
        }

        $this->actingAs($this->staff)->postJson(route('admin.products.upload-description-media'), [
            'file' => UploadedFile::fake()->createWithContent('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        ])->assertStatus(422);

        $response = $this->actingAs($this->staff)->postJson(route('admin.products.upload-description-media'), [
            'file' => new UploadedFile($tmp, 'photo.png', null, null, true),
        ])->assertOk();

        $this->assertStringEndsWith('.png', $response->json('path'));
        @unlink(public_path($response->json('path')));
    }

    public function test_product_image_alt_text_uses_store_name(): void
    {
        \App\Models\Setting::put('site_name', 'SoleBd');
        $product = $this->makeProduct();

        $this->actingAs($this->staff)->put(route('admin.products.update', $product), $this->productPayload([
            'name' => 'Runner',
            'images' => [UploadedFile::fake()->image('a.jpg')],
        ]));

        $image = $product->images()->firstOrFail();
        $this->assertStringContainsString('SoleBd', $image->alt);
        $this->assertStringNotContainsString('Organic', $image->alt);
        @unlink(public_path($image->path));
    }
}
