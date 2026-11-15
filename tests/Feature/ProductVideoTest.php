<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductVideoTest extends TestCase
{
    use RefreshDatabase;

    private array $cleanup = [];

    protected function tearDown(): void
    {
        File::delete($this->cleanup);
        parent::tearDown();
    }

    private function mp4Bytes(): string
    {
        return hex2bin('00000018667479706d703432000000006d70343269736f6d') . str_repeat("\0", 64);
    }

    /** A file whose type is read from its content, like a real browser upload. */
    private function realUpload(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'vid');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function product(array $extra = []): Product
    {
        $category = Category::firstOrCreate(['slug' => 'bags'], ['name' => 'Bags', 'is_active' => true, 'position' => 1]);

        return Product::create(array_merge(['category_id' => $category->id, 'name' => 'Tote', 'slug' => 'tote-' . uniqid(),
            'regular_price' => 900, 'stock_quantity' => 3, 'is_published' => true], $extra));
    }

    private function payload(Product $product, array $extra = []): array
    {
        return array_merge([
            'name' => $product->name, 'slug' => $product->slug, 'category_id' => $product->category_id,
            'regular_price' => 900, 'stock_quantity' => 3, 'is_published' => 1,
            'description' => $product->description,
        ], $extra);
    }

    public function test_video_removed_from_the_description_is_deleted_from_the_library(): void
    {
        File::ensureDirectoryExists(public_path('uploads/products/description'));
        $file = 'uploads/products/description/remove-me-test.mp4';
        File::put(public_path($file), $this->mp4Bytes());
        $this->cleanup[] = public_path($file);

        $html = '<p>Hi</p><video controls><source src="' . asset($file) . '" type="video/mp4"></video>';
        $product = $this->product(['description' => $html]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.products.update', $product), $this->payload($product, ['description' => '<p>Hi</p>']))
            ->assertSessionHasNoErrors();

        $this->assertFileDoesNotExist(public_path($file));
    }

    public function test_description_video_still_used_by_another_product_is_kept(): void
    {
        File::ensureDirectoryExists(public_path('uploads/products/description'));
        $file = 'uploads/products/description/shared-test.mp4';
        File::put(public_path($file), $this->mp4Bytes());
        $this->cleanup[] = public_path($file);

        $html = '<video controls><source src="' . asset($file) . '" type="video/mp4"></video>';
        $product = $this->product(['description' => $html]);
        $this->product(['description' => $html]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.products.update', $product), $this->payload($product, ['description' => '<p>Gone</p>']))
            ->assertSessionHasNoErrors();

        $this->assertFileExists(public_path($file));
    }

    public function test_product_video_upload_plays_first_on_the_product_page_and_stays_out_of_photos(): void
    {
        $product = $this->product();
        ProductImage::create(['product_id' => $product->id, 'path' => 'uploads/products/photo-test.jpg', 'is_primary' => true, 'position' => 1]);
        $admin = User::factory()->create(['role' => 'admin']);

        $video = $this->realUpload('clip.mp4', $this->mp4Bytes());
        $this->actingAs($admin)->put(route('admin.products.update', $product), $this->payload($product, ['videos' => [$video]]))
            ->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertCount(1, $product->videos);
        $this->assertCount(1, $product->images);
        $this->assertStringEndsWith('photo-test.jpg', $product->imageUrl());
        $videoPath = $product->videos->first()->path;
        $this->cleanup[] = public_path($videoPath);
        $this->assertFileExists(public_path($videoPath));

        $this->get(route('product.show', $product))->assertOk()
            ->assertSee('id="galleryVideo"', false)
            ->assertSee('autoplay muted loop playsinline', false)
            ->assertSee(asset($videoPath), false);

        // Deleting it from the edit page removes the file too
        $this->actingAs($admin)->deleteJson(route('admin.products.images.destroy', [$product, $product->videos->first()]))->assertOk();
        $this->assertFileDoesNotExist(public_path($videoPath));
        $this->assertCount(0, $product->fresh()->videos);
    }

    public function test_non_video_file_is_refused_as_a_product_video(): void
    {
        $product = $this->product();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.products.update', $product), $this->payload($product, [
            'videos' => [$this->realUpload('clip.mp4', '<?php echo 1;')],
        ]))->assertSessionHasErrors('videos.0');
    }

    public function test_too_large_upload_gets_a_friendly_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->call('POST', route('admin.products.upload-description-media'), [], [], [], [
            'CONTENT_LENGTH' => 1024 * 1024 * 1024, 'HTTP_ACCEPT' => 'application/json',
        ])->assertStatus(413)->assertJsonFragment(['success' => false]);
    }
}
