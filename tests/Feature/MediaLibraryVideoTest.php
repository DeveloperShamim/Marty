<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MediaLibraryVideoTest extends TestCase
{
    use RefreshDatabase;

    private string $video = 'uploads/products/description/media-test-clip.mp4';

    protected function tearDown(): void
    {
        File::delete(public_path($this->video));
        parent::tearDown();
    }

    private function makeVideo(): void
    {
        File::ensureDirectoryExists(public_path('uploads/products/description'));
        // smallest valid-looking MP4 header ("ftyp" box) is enough for the library to list it
        File::put(public_path($this->video), hex2bin('00000018667479706d703432000000006d70343269736f6d'));
    }

    public function test_description_video_shows_in_the_media_library_with_the_product_using_it(): void
    {
        $this->makeVideo();
        $category = Category::create(['name' => 'Bags', 'slug' => 'bags', 'is_active' => true, 'position' => 1]);
        Product::create(['category_id' => $category->id, 'name' => 'Tote', 'slug' => 'tote', 'regular_price' => 900,
            'stock_quantity' => 1, 'is_published' => true,
            'description' => '<video controls><source src="' . asset($this->video) . '" type="video/mp4"></video>']);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.media.index', ['type' => 'videos']))
            ->assertOk()
            ->assertSee('media-test-clip.mp4')
            ->assertSee('Description: Tote')
            ->assertSee('<video', false);
    }

    public function test_product_editor_can_list_library_videos(): void
    {
        $this->makeVideo();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->getJson(route('admin.products.description-videos'))
            ->assertOk()
            ->assertJsonFragment(['filename' => 'media-test-clip.mp4', 'url' => asset($this->video)]);
    }

    public function test_video_can_be_deleted_but_not_optimized(): void
    {
        $this->makeVideo();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.media.optimize'), ['relative_path' => $this->video])
            ->assertSessionHasErrors('image');
        $this->assertFileExists(public_path($this->video));

        $this->actingAs($admin)->delete(route('admin.media.destroy'), ['relative_path' => $this->video])
            ->assertSessionHasNoErrors();
        $this->assertFileDoesNotExist(public_path($this->video));
    }

    public function test_a_file_that_vanished_while_listing_is_skipped_not_a_crash(): void
    {
        // Windows keeps a deleted-but-open file in the folder listing while stat on it fails
        $method = new \ReflectionMethod(\App\Http\Controllers\Admin\MediaController::class, 'statFile');
        $this->assertNull($method->invoke(null, new \SplFileInfo(public_path('uploads/media/gone-' . uniqid() . '.mp4'))));

        $this->makeVideo();
        $stat = $method->invoke(null, new \SplFileInfo(public_path($this->video)));
        $this->assertGreaterThan(0, $stat['size']);
    }

    public function test_deleting_a_product_video_from_the_library_removes_it_and_the_page_still_loads(): void
    {
        $category = Category::create(['name' => 'Bags', 'slug' => 'bags', 'is_active' => true, 'position' => 1]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Tote', 'slug' => 'tote', 'regular_price' => 900,
            'stock_quantity' => 1, 'is_published' => true]);
        $path = 'uploads/products/library-delete-test.mp4';
        File::ensureDirectoryExists(public_path('uploads/products'));
        File::put(public_path($path), hex2bin('00000018667479706d703432000000006d70343269736f6d'));
        \App\Models\ProductImage::create(['product_id' => $product->id, 'path' => $path, 'position' => 1]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.media.index', ['type' => 'videos']))->assertOk()->assertSee('Product: Tote');
        $this->actingAs($admin)->delete(route('admin.media.destroy'), ['relative_path' => $path])->assertSessionHasNoErrors();

        $this->assertFileDoesNotExist(public_path($path));
        $this->assertCount(0, $product->fresh()->videos);
        $this->actingAs($admin)->get(route('admin.media.index', ['type' => 'videos']))->assertOk()->assertDontSee('library-delete-test.mp4');
    }
}
