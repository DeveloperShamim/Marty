<?php

namespace Tests\Feature;

use App\Console\Commands\BackupDatabase;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class LongRunTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
    }

    public function test_pages_use_the_built_stylesheets_not_the_tailwind_cdn(): void
    {
        $store = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $store);
        $this->assertStringContainsString('theme/css/tw-store.css?v=', $store);
        $this->assertMatchesRegularExpression('/--vt-primary:\d+ \d+ \d+;/', $store);

        $login = $this->get(route('admin.login'))->assertOk()->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $login);
        $this->assertStringContainsString('theme/css/tw-admin.css?v=', $login);

        $admin = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.dashboard'))->assertOk()->getContent();
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $admin);
        $this->assertStringContainsString('theme/css/tw-admin.css?v=', $admin);
        $this->assertMatchesRegularExpression('/--vt-b600:\d+ \d+ \d+;/', $admin);
    }

    /** The committed CSS must match the views; rebuild with `npm run build:css` after changing classes. */
    public function test_built_stylesheets_are_up_to_date(): void
    {
        $cli = base_path('node_modules/tailwindcss3/lib/cli.js');
        if (! is_file($cli) || (new Process(['node', '--version']))->run() !== 0) {
            $this->markTestSkipped('Node and the tailwindcss3 package are needed (npm install).');
        }

        foreach (['store', 'admin'] as $which) {
            $out = tempnam(sys_get_temp_dir(), 'tw') . '.css';
            $build = new Process(['node', $cli, '-c', "tailwind/$which.config.cjs", '-i', 'tailwind/input.css', '-o', $out, '--minify'], base_path());
            $build->setTimeout(120)->mustRun();
            $this->assertFileEquals($out, public_path("theme/css/tw-$which.css"), "public/theme/css/tw-$which.css is stale: run npm run build:css");
            @unlink($out);
        }
    }

    public function test_database_backup_writes_a_restorable_file_and_keeps_the_newest(): void
    {
        $dir = storage_path(BackupDatabase::DIR);
        @mkdir($dir, 0750, true);
        foreach (['2020-01-01-000000', '2020-01-02-000000', '2020-01-03-000000'] as $old) {
            touch("$dir/db-$old.sql.gz");
        }

        try {
            $this->artisan('app:backup-db', ['--keep' => 2])->assertSuccessful();

            $files = glob("$dir/db-*.sql.gz");
            sort($files);
            $this->assertCount(2, $files, 'Only the newest backups are kept');
            $this->assertSame(BackupDatabase::latest(), end($files));
            $sql = gzdecode(file_get_contents(end($files)));
            $this->assertStringContainsString('CREATE TABLE "orders"', $sql);
            $this->assertStringContainsString('INSERT INTO "settings"', $sql);
        } finally {
            array_map('unlink', glob("$dir/db-*.sql.gz"));
        }
    }

    public function test_env_fallback_survives_a_cached_config(): void
    {
        // After config:cache, env() reads nothing from .env; the value comes from config/settings_env.php
        config(['settings_env.redx_api_token' => 'from-env']);
        $this->assertSame('from-env', setting('redx_api_token'));

        Setting::put('redx_api_token', 'from-admin');
        $this->assertSame('from-admin', setting('redx_api_token'));
    }
}
