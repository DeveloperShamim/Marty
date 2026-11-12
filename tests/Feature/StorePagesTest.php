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
}
