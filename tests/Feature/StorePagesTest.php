<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    public function test_owner_creates_a_page_that_shows_in_the_store_and_footer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.pages.index'))->assertOk()->assertSee('No pages yet');

        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'About us', 'body' => "We sell **real** leather.\n\n## Why us\n- Cash on delivery\n- Fast delivery\n\n<script>alert(1)</script>",
            'is_published' => '1', 'show_in_footer' => '1',
        ])->assertRedirect(route('admin.pages.index'));

        $page = Page::firstWhere('slug', 'about-us');
        $this->assertNotNull($page, 'The address is made from the title');

        $html = $this->get('/page/about-us')->assertOk()->getContent();
        $this->assertStringContainsString('<strong>real</strong>', $html);
        $this->assertStringContainsString('<h2>Why us</h2>', $html);
        $this->assertStringContainsString('<li>Cash on delivery</li>', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html, 'HTML typed in the page is escaped');
        $this->assertStringContainsString('href="' . route('page', 'about-us') . '"', $this->get('/')->getContent(), 'Footer links to the page');
        $this->assertStringContainsString(route('page', 'about-us'), $this->get('/sitemap.xml')->getContent());
    }

    public function test_drafts_are_hidden_and_store_addresses_are_reserved(): void
    {
        Page::create(['title' => 'Secret', 'slug' => 'secret', 'body' => 'x', 'is_published' => false]);
        $this->get('/page/secret')->assertNotFound();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('admin.pages.store'), ['title' => 'Contact', 'slug' => 'contact'])
            ->assertSessionHasErrors('slug');
        $this->get('/page/contact')->assertOk(); // the built-in contact page still answers
    }

    public function test_owner_edits_and_deletes_a_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::create(['title' => 'FAQ', 'slug' => 'faq', 'body' => 'Old', 'is_published' => true]);

        $this->actingAs($admin)->put(route('admin.pages.update', $page), ['title' => 'Questions', 'slug' => 'faq', 'body' => 'New answer', 'is_published' => '1'])
            ->assertRedirect();
        $this->get('/page/faq')->assertOk()->assertSee('New answer')->assertSee('Questions');

        $this->actingAs($admin)->delete(route('admin.pages.destroy', $page))->assertRedirect();
        $this->get('/page/faq')->assertNotFound();
    }

    public function test_admin_adds_custom_html_that_runs_in_a_sealed_frame(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::create(['title' => 'Size guide', 'slug' => 'size-guide', 'body' => 'Measure your chest.', 'is_published' => true]);
        $base = ['title' => 'Size guide', 'slug' => 'size-guide', 'body' => 'Measure your chest.', 'is_published' => '1'];
        $html = '<table class="sizes"><tr><td>M</td></tr></table><script>document.cookie</script>';

        $this->actingAs($admin)->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Custom HTML')->assertDontSee('Only admins can change these.');
        $this->actingAs($admin)->put(route('admin.pages.update', $page), $base + [
            'custom' => [['html' => '<p>Top note</p>', 'at' => 'above'], ['html' => ' ', 'at' => 'below'], ['html' => $html, 'at' => 'below']],
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame([['html' => '<p>Top note</p>', 'at' => 'above'], ['html' => $html, 'at' => 'below']], $page->fresh()->custom_html);

        $out = $this->get('/page/size-guide')->assertOk()->getContent();
        $this->assertSame(2, substr_count($out, 'sandbox="allow-scripts allow-popups allow-popups-to-escape-sandbox"'));
        $this->assertStringNotContainsString('allow-same-origin', $out);
        $this->assertStringNotContainsString('<script>document.cookie</script>', $out, 'Only inside the escaped srcdoc');
        $this->assertStringContainsString('&lt;script&gt;document.cookie&lt;/script&gt;', $out);
        $text = strpos($out, 'data-store-page');
        $this->assertLessThan($text, strpos($out, 'data-page-custom-at="above"'));
        $this->assertGreaterThan($text, strpos($out, 'data-page-custom-at="below"'));

        // A store manager can edit the page but not its custom HTML
        $manager = User::factory()->create(['role' => 'store_manager']);
        $this->actingAs($manager)->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Only admins can change these.');
        $this->actingAs($manager)->put(route('admin.pages.update', $page), ['body' => 'Updated', 'custom' => [['html' => '<b>hacked</b>', 'at' => 'above']]] + $base)
            ->assertRedirect();
        $this->assertSame('Updated', $page->fresh()->body);
        $this->assertSame($html, $page->fresh()->custom_html[1]['html']);

        $this->actingAs($admin)->put(route('admin.pages.update', $page), $base + ['custom' => [['html' => 'x', 'at' => 'sidebar']]])
            ->assertSessionHasErrors('custom.0.at');

        // Clearing every block removes the frames
        $this->actingAs($admin)->put(route('admin.pages.update', $page), $base + ['custom' => [['html' => '', 'at' => 'above']]]);
        $this->assertNull($page->fresh()->custom_html);
        $this->assertStringNotContainsString('<iframe', $this->get('/page/size-guide')->getContent());
    }
}
