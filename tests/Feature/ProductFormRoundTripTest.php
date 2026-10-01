<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSku;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Opening a product's edit page and pressing "Update" without touching anything
 * must not change the product. The form is read exactly like a browser would
 * submit it (checked boxes only, selected options, textarea contents).
 */
class ProductFormRoundTripTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** Collect the fields the browser would submit for the main product form. */
    private function browserSubmission(string $html, string $action): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8"?>' . $html);
        $xp = new DOMXPath($dom);
        $form = $xp->query("//form[@action='{$action}']")->item(0);
        $this->assertNotNull($form, 'Product form not found');

        $pairs = [];
        foreach ($xp->query('.//input|.//select|.//textarea', $form) as $el) {
            // Fields inside <template> are JS blueprints, never submitted.
            for ($p = $el->parentNode; $p; $p = $p->parentNode) {
                if ($p->nodeName === 'template') {
                    continue 2;
                }
            }
            $name = $el->getAttribute('name');
            if ($name === '' || $el->hasAttribute('disabled')) {
                continue;
            }
            $type = strtolower($el->getAttribute('type'));
            if ($el->nodeName === 'input' && in_array($type, ['checkbox', 'radio'], true) && ! $el->hasAttribute('checked')) {
                continue;
            }
            if ($el->nodeName === 'input' && in_array($type, ['file', 'submit', 'button'], true)) {
                continue;
            }
            if ($el->nodeName === 'select') {
                $value = '';
                foreach ($xp->query('.//option', $el) as $i => $opt) {
                    if ($i === 0 || $opt->hasAttribute('selected')) {
                        $value = $opt->hasAttribute('value') ? $opt->getAttribute('value') : $opt->textContent;
                    }
                }
            } elseif ($el->nodeName === 'textarea') {
                $value = $el->textContent;
            } else {
                $value = $el->hasAttribute('value') ? $el->getAttribute('value') : ($type === 'checkbox' ? 'on' : '');
            }
            $pairs[] = [$name, $value];
        }

        // Encode like a browser, then let PHP build the nested arrays.
        $query = implode('&', array_map(fn ($p) => rawurlencode($p[0]) . '=' . rawurlencode($p[1]), $pairs));
        parse_str($query, $data);

        return $data;
    }

    private function snapshot(Product $product): array
    {
        $product->refresh()->load(['skus', 'images']);

        return [
            'product' => collect($product->only([
                'name', 'slug', 'sku', 'barcode', 'brand_id', 'category_id', 'short_description', 'description',
                'regular_price', 'sale_price', 'cost_price', 'stock_quantity', 'unit',
                'is_published', 'is_featured', 'is_flash_sale', 'is_best_seller', 'is_new_arrival',
                'meta_title', 'meta_description', 'meta_keywords', 'specifications',
            ]))->map(fn ($v) => is_numeric($v) ? (float) $v : ($v === [] ? null : $v))->all(),
            'skus' => $product->skus->sortBy('id')->map(fn ($s) => [
                'id' => $s->id, 'sku' => $s->sku, 'barcode' => $s->barcode, 'attributes' => $s->getAttributesData(),
                'cost_price' => (float) $s->cost_price, 'regular_price' => (float) $s->regular_price,
                'sale_price' => (float) $s->sale_price, 'stock' => (int) $s->stock_quantity, 'active' => (bool) $s->is_active,
            ])->values()->all(),
            // Order and main image matter; the raw position numbers may be renumbered.
            'images' => $product->images->sortBy('position')->map(fn ($i) => $i->only(['id', 'path', 'is_primary', 'color']))->values()->all(),
        ];
    }

    private function roundTrip(Product $product): array
    {
        $before = $this->snapshot($product);
        $html = $this->actingAs($this->admin)->get(route('admin.products.edit', $product))->assertOk()->getContent();
        $data = $this->browserSubmission($html, route('admin.products.update', $product));

        $this->actingAs($this->admin)->post(route('admin.products.update', $product), $data)
            ->assertSessionHasNoErrors()->assertRedirect();

        return [$before, $this->snapshot($product)];
    }

    public function test_saving_a_product_with_variants_without_changes_keeps_everything(): void
    {
        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true, 'position' => 1]);
        $brand = Brand::create(['name' => 'Adidas', 'slug' => 'adidas', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'brand_id' => $brand->id, 'brand' => 'Adidas',
            'name' => 'Runner', 'slug' => 'runner', 'sku' => 'RUN-1', 'barcode' => '1234567890123',
            'short_description' => 'Light runner', 'description' => '<p>Great shoe</p>',
            'regular_price' => 1000, 'sale_price' => 900, 'cost_price' => 500, 'stock_quantity' => 0, 'unit' => 'Pair',
            'is_published' => true, 'is_featured' => false, 'is_best_seller' => true, 'is_new_arrival' => false,
            'specifications' => [['label' => 'Material', 'value' => 'Mesh']],
        ]);
        // Each size has its own cost (set via Inventory restocks) and a custom printed barcode.
        ProductSku::create(['product_id' => $product->id, 'sku' => 'RUN-40', 'barcode' => '8800000000040', 'attributes' => ['Size' => '40'],
            'cost_price' => 480, 'regular_price' => 1000, 'sale_price' => 900, 'stock_quantity' => 3, 'is_active' => true]);
        ProductSku::create(['product_id' => $product->id, 'sku' => 'RUN-41', 'barcode' => '8800000000041', 'attributes' => ['Size' => '41'],
            'cost_price' => 520, 'regular_price' => 1050, 'sale_price' => 950, 'stock_quantity' => 4, 'is_active' => true]);
        $product->syncTotalStock();
        ProductImage::create(['product_id' => $product->id, 'path' => 'uploads/products/a.jpg', 'is_primary' => true, 'position' => 1]);
        ProductImage::create(['product_id' => $product->id, 'path' => 'uploads/products/b.jpg', 'is_primary' => false, 'position' => 2]);

        [$before, $after] = $this->roundTrip($product);

        $this->assertEquals($before['skus'], $after['skus'], 'Variant data changed just by saving');
        $this->assertEquals($before['images'], $after['images'], 'Images changed just by saving');
        $this->assertEquals($before['product'], $after['product'], 'Product fields changed just by saving');
    }

    public function test_deleting_the_main_image_by_ajax_promotes_the_next_one(): void
    {
        $category = Category::create(['name' => 'Bags', 'slug' => 'bags', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Tote', 'slug' => 'tote', 'regular_price' => 500, 'stock_quantity' => 1]);
        $main = ProductImage::create(['product_id' => $product->id, 'path' => 'uploads/products/x1.jpg', 'is_primary' => true, 'position' => 1]);
        $next = ProductImage::create(['product_id' => $product->id, 'path' => 'uploads/products/x2.jpg', 'is_primary' => false, 'position' => 2]);
        $other = Product::create(['category_id' => $category->id, 'name' => 'Other', 'slug' => 'other', 'regular_price' => 1, 'stock_quantity' => 1]);

        // An image can only be deleted through its own product.
        $this->actingAs($this->admin)->deleteJson(route('admin.products.images.destroy', [$other, $main]))->assertNotFound();

        $this->actingAs($this->admin)->deleteJson(route('admin.products.images.destroy', [$product, $main]))
            ->assertOk()->assertJson(['success' => true]);

        $this->assertModelMissing($main);
        $this->assertTrue($next->fresh()->is_primary);
    }

    public function test_saving_a_simple_product_without_changes_adds_no_placeholder_content(): void
    {
        $category = Category::create(['name' => 'Bags', 'slug' => 'bags', 'is_active' => true, 'position' => 1]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Tote', 'slug' => 'tote', 'sku' => 'TOTE-1',
            'regular_price' => 500, 'cost_price' => 200, 'stock_quantity' => 7, 'is_published' => true,
        ]);

        [$before, $after] = $this->roundTrip($product);

        $this->assertEmpty($after['product']['specifications'], 'Placeholder specifications were saved');
        $this->assertEmpty($after['product']['meta_description'], 'Placeholder meta description was saved');
        $this->assertEquals($before['product'], $after['product']);
    }
}
