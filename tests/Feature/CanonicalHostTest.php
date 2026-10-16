<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanonicalHostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
        config(['app.url' => 'https://www.vantbd.com']);
    }

    public function test_bare_domain_redirects_to_www_keeping_the_path(): void
    {
        $this->get('https://vantbd.com/shop?sort=newest')
            ->assertStatus(301)
            ->assertRedirect('https://www.vantbd.com/shop?sort=newest');
    }

    public function test_plain_http_redirects_to_https(): void
    {
        $this->get('http://www.vantbd.com/track')->assertStatus(301)->assertRedirect('https://www.vantbd.com/track');
        $this->get('http://vantbd.com/')->assertStatus(301)->assertRedirect('https://www.vantbd.com/');
    }

    public function test_https_behind_a_proxy_is_not_redirected(): void
    {
        $this->get('http://www.vantbd.com/', ['X-Forwarded-Proto' => 'https'])->assertOk();
    }

    public function test_main_address_is_served_and_named_as_canonical(): void
    {
        $html = $this->get('https://www.vantbd.com/shop?sort=newest')->assertOk()->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="https://www.vantbd.com/shop">', $html);

        $html = $this->get('https://www.vantbd.com/shop?page=2')->assertOk()->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="https://www.vantbd.com/shop?page=2">', $html);

        $html = $this->get('https://www.vantbd.com/')->assertOk()->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="https://www.vantbd.com">', $html);
    }

    public function test_form_posts_and_other_hosts_are_left_alone(): void
    {
        $this->assertNotSame(301, $this->post('https://vantbd.com/cart/add', [])->getStatusCode());
        $this->get('https://staging.example.com/')->assertOk();
    }
}
