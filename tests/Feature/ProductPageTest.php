<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $extra = []): Product
    {
        $category = Category::create(['name' => 'Wallets', 'slug' => 'wallets', 'is_active' => true, 'position' => 1]);

        return Product::create(array_merge(['category_id' => $category->id, 'name' => 'Bifold', 'slug' => 'bifold',
            'regular_price' => 1000, 'sale_price' => 800, 'stock_quantity' => 20, 'is_published' => true], $extra));
    }

    private function review(Product $product, int $rating, string $email): void
    {
        ProductReview::create(['product_id' => $product->id, 'author_name' => 'Karim', 'author_email' => $email,
            'rating' => $rating, 'body' => 'Good leather.', 'status' => ProductReview::STATUS_APPROVED, 'is_verified_purchase' => true]);
    }

    /** The product data Google reads for price, stock and stars is printed on the page. */
    public function test_page_prints_product_data_for_google(): void
    {
        $product = $this->product();
        $this->review($product, 5, 'a@example.com');
        $this->review($product, 4, 'b@example.com');

        $html = $this->get(route('product.show', $product))->assertOk()->getContent();

        $this->assertSame(1, preg_match('#<script type="application/ld\+json">(\{"@context":"https://schema.org","@type":"Product".*?)</script>#s', $html, $m));
        $data = json_decode($m[1], true);
        $this->assertSame('Bifold', $data['name']);
        $this->assertSame('800.00', $data['offers']['price']);
        $this->assertSame('https://schema.org/InStock', $data['offers']['availability']);
        $this->assertEquals(4.5, $data['aggregateRating']['ratingValue']);
        $this->assertSame(2, $data['aggregateRating']['reviewCount']);
    }

    public function test_star_rating_shows_under_the_title_and_above_the_reviews(): void
    {
        $product = $this->product();
        $this->review($product, 5, 'a@example.com');
        $this->review($product, 4, 'b@example.com');

        $this->get(route('product.show', $product))->assertOk()
            ->assertSee('data-pdp-rating', false)
            ->assertSee('data-reviews-summary', false)
            ->assertSee('(2 reviews)')
            ->assertSee('out of 5 · 2 reviews');
    }

    public function test_no_star_rating_without_reviews(): void
    {
        $this->get(route('product.show', $this->product()))->assertOk()
            ->assertDontSee('data-pdp-rating', false)
            ->assertDontSee('data-reviews-summary', false)
            ->assertDontSee('aggregateRating', false);
    }

    /** Looking at a product must never add a brand to the shop. */
    public function test_viewing_a_product_does_not_create_a_brand(): void
    {
        $product = $this->product(['brand' => 'Unknown Maker']);

        $this->get(route('product.show', $product))->assertOk()->assertSee('Unknown Maker');

        $this->assertSame(0, Brand::count());
    }

    /** A review with a missing field reopens the form and says what is wrong. */
    public function test_review_form_shows_its_validation_errors(): void
    {
        $product = $this->product();
        $this->actingAs(User::create(['name' => 'Karim', 'email' => 'karim@example.com', 'password' => bcrypt('secret-pass'), 'role' => 'customer']));

        // Send the form and land back on the product page, as the customer does
        $html = $this->followingRedirects()->from(route('product.show', $product))
            ->post(route('product.reviews.store', $product), ['rating' => 5, 'author_name' => 'Karim', 'author_email' => 'not-an-email', 'body' => ''])
            ->assertOk()
            ->assertSee('data-review-errors', false)
            ->assertSee('The body field is required.')
            ->getContent();

        $this->assertSame(1, preg_match('#<div id="reviewFormDrawer" class="([^"]*)"#', $html, $m));
        $this->assertStringNotContainsString('hidden', $m[1]);
    }

    /** One short line is a plain sentence; the "Key Highlights" list needs at least two points. */
    public function test_key_highlights_need_two_or_more_points(): void
    {
        $one = $this->product(['short_description' => 'Neat stitching, made for everyday use.']);
        $this->get(route('product.show', $one))->assertOk()
            ->assertSee('data-pdp-summary', false)
            ->assertSee('Neat stitching, made for everyday use.')
            ->assertDontSee('Key Highlights');

        $two = Product::create(['category_id' => $one->category_id, 'name' => 'Trifold', 'slug' => 'trifold', 'regular_price' => 900,
            'stock_quantity' => 5, 'is_published' => true, 'short_description' => "Full-grain leather\nSix card slots"]);
        $this->get(route('product.show', $two))->assertOk()
            ->assertSee('data-pdp-highlights', false)
            ->assertSee('Key Highlights')
            ->assertSee('Six card slots')
            ->assertDontSee('data-pdp-summary', false);
    }

    /** A product that already meets the free-delivery minimum says so, instead of quoting the minimum. */
    public function test_free_delivery_line_matches_the_product_price(): void
    {
        foreach (['bkash_number' => '01700000000', 'free_delivery_online' => '1', 'free_delivery_online_min' => '500', 'free_delivery_online_zones' => 'both'] as $k => $v) {
            Setting::put($k, $v);
        }

        $this->get(route('product.show', $this->product()))->assertOk() // sells at 800
            ->assertSee('Free delivery on this product when you pay with bKash')
            ->assertDontSee('on orders of');

        $cheap = Product::create(['category_id' => Category::first()->id, 'name' => 'Key ring', 'slug' => 'key-ring', 'regular_price' => 300,
            'stock_quantity' => 5, 'is_published' => true]);
        $this->get(route('product.show', $cheap))->assertOk()
            ->assertSee('Free delivery when you pay with bKash on orders of')
            ->assertDontSee('Free delivery on this product');
    }

    /** Phones: dots instead of arrows and thumbnails, delivery folded to one line, WhatsApp in its own colour. */
    public function test_mobile_gallery_dots_and_delivery_fold(): void
    {
        $product = $this->product();
        $product->images()->create(['path' => 'products/a.jpg', 'position' => 0]);
        $product->images()->create(['path' => 'products/b.jpg', 'position' => 1]);
        Setting::put('contact_phone', '01775075543');

        $html = $this->get(route('product.show', $product))->assertOk()
            ->assertSee('data-gallery-dots', false)
            ->assertSee('data-pdp-fold', false)
            ->assertSee('data-pdp-whatsapp', false)
            ->getContent();

        $this->assertSame(2, substr_count($html, 'data-dot="'), 'One dot per photo');
        $this->assertMatchesRegularExpression('#id="pdPrevImg" class="hidden sm:flex#', $html, 'Arrows are hidden on phones');
        $this->assertStringContainsString('Delivery</b> <span class="text-stone-600">· from ৳60', $html);
    }

    /** Reviews need an account, so a guest gets a log-in link, not a form that cannot be sent. */
    public function test_guest_is_asked_to_log_in_to_review(): void
    {
        $this->get(route('product.show', $this->product()))->assertOk()
            ->assertSee('data-review-login', false)
            ->assertDontSee('id="toggleReviewFormBtn"', false);
    }
}
