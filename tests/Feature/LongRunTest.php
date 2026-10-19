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

    public function test_admin_sets_the_backup_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()->assertSee('Save backup settings')->assertSee('data-backup-status', false);

        $this->actingAs($admin)->putJson(route('admin.settings.update-section', 'backups'), ['backup_frequency' => 'hourly', 'backup_time' => '03:30', 'backup_keep' => 14])
            ->assertStatus(422)->assertJsonValidationErrors('backup_frequency');
        $this->actingAs($admin)->putJson(route('admin.settings.update-section', 'backups'), ['backup_frequency' => 'weekly', 'backup_time' => '02:15', 'backup_keep' => 30])
            ->assertOk();

        $this->assertSame('weekly', BackupDatabase::frequency());
        $this->assertSame('02:15', BackupDatabase::time());
        $this->assertSame(30, BackupDatabase::keep());
        $this->assertSame(5, BackupDatabase::keep(5), 'The --keep option still wins');
    }

    public function test_backup_is_due_according_to_the_schedule(): void
    {
        $dir = storage_path(BackupDatabase::DIR);
        @mkdir($dir, 0750, true);
        array_map('unlink', glob("$dir/db-*.sql.gz"));
        $file = "$dir/db-2026-10-19-033000.sql.gz";
        $at = fn (string $time) => \Illuminate\Support\Carbon::parse("2026-10-19 $time", config('app.timezone'));

        try {
            Setting::put('backup_frequency', 'daily');
            Setting::put('backup_time', '03:30');
            $this->assertTrue(BackupDatabase::isDue($at('01:00')), 'No backup yet: run now');

            touch($file, $at('03:30')->getTimestamp());
            $this->assertFalse(BackupDatabase::isDue($at('23:59')));
            $this->assertTrue(BackupDatabase::isDue($at('03:30')->addDay()));
            $this->assertTrue(BackupDatabase::isDue($at('09:00')->addDay()), 'A missed time catches up');

            Setting::put('backup_frequency', 'weekly');
            $this->assertFalse(BackupDatabase::isDue($at('03:30')->addDays(6)));
            $this->assertTrue(BackupDatabase::isDue($at('03:30')->addDays(7)));

            Setting::put('backup_frequency', '6h');
            $this->assertFalse(BackupDatabase::isDue($at('08:00')));
            $this->assertTrue(BackupDatabase::isDue($at('09:30')));

            Setting::put('backup_frequency', 'off');
            $this->assertFalse(BackupDatabase::isDue($at('03:30')->addDays(30)));
            $this->assertNull(BackupDatabase::maxAgeHours());
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
