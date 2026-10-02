<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MediaOptimizeTest extends TestCase
{
    use RefreshDatabase;

    private string $png = 'uploads/media/optimize-test.png';

    protected function tearDown(): void
    {
        File::delete([public_path($this->png), public_path('uploads/media/optimize-test.webp')]);
        parent::tearDown();
    }

    public function test_optimize_works_when_no_logo_or_favicon_is_set(): void
    {
        if (!function_exists('imagewebp')) {
            $this->markTestSkipped('GD with WebP is not installed.');
        }

        File::ensureDirectoryExists(public_path('uploads/media'));
        $img = imagecreatetruecolor(40, 40);
        imagefill($img, 0, 0, imagecolorallocate($img, 139, 90, 43));
        imagepng($img, public_path($this->png));

        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold',
            'regular_price' => 1000, 'stock_quantity' => 1, 'is_published' => true]);
        ProductImage::create(['product_id' => $product->id, 'path' => '/' . $this->png, 'position' => 0]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.media.bulk-optimize'), ['paths' => [$this->png]])
            ->assertRedirect()->assertSessionHasNoErrors();

        $this->assertFileExists(public_path('uploads/media/optimize-test.webp'));
        $this->assertFileDoesNotExist(public_path($this->png));
        $this->assertSame('/uploads/media/optimize-test.webp', $product->images()->value('path'));
    }

    public function test_files_outside_the_media_library_cannot_be_deleted(): void
    {
        $canary = base_path('media-guard-test.png');
        File::put($canary, 'x');
        $admin = User::factory()->create(['role' => 'admin']);

        try {
            $this->actingAs($admin)->delete(route('admin.media.destroy'), ['relative_path' => '../media-guard-test.png'])
                ->assertSessionHasErrors('image');
            $this->actingAs($admin)->post(route('admin.media.bulk-delete'), ['paths' => ['uploads/../../media-guard-test.png', 'index.php']]);

            $this->assertFileExists($canary);
            $this->assertFileExists(public_path('index.php'));
        } finally {
            File::delete($canary);
        }
    }
}
