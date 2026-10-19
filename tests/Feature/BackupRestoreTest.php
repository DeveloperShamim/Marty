<?php

namespace Tests\Feature;

use App\Console\Commands\BackupDatabase;
use App\Models\Setting;
use App\Models\User;
use App\Support\DatabaseRestore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::forgetCache();
        $this->dir = storage_path(BackupDatabase::DIR);
        @mkdir($this->dir, 0750, true);
        array_map('unlink', glob($this->dir . '/db-*.sql.gz'));
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/db-*.sql.gz'));
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_statements_split_only_outside_strings(): void
    {
        $sql = "INSERT INTO t VALUES ('a;b','it''s; fine');\nINSERT INTO t VALUES ('back\\\\slash\\';x');\nDROP TABLE x;";
        $path = $this->dir . '/split.gz';
        file_put_contents($path, gzencode($sql));

        $gz = gzopen($path, 'rb');
        $mysql = iterator_to_array(DatabaseRestore::statements($gz, true), false);
        gzclose($gz);
        $this->assertSame(["INSERT INTO t VALUES ('a;b','it''s; fine')", "INSERT INTO t VALUES ('back\\\\slash\\';x')", 'DROP TABLE x'], $mysql);
        unlink($path);
    }

    public function test_admin_backs_up_downloads_and_restores(): void
    {
        $admin = $this->admin();
        Setting::put('site_name', "Vant's; Shop");

        $this->actingAs($admin)->post(route('admin.backups.store'))->assertRedirect();
        $file = basename(BackupDatabase::latest());
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()->assertSee($file)->assertSee('Back up now');

        $download = $this->actingAs($admin)->get(route('admin.backups.download', $file))->assertOk();
        $this->assertStringContainsString('attachment', $download->headers->get('content-disposition'));

        Setting::put('site_name', 'Changed');
        User::factory()->create(['email' => 'after@example.com']);

        $this->actingAs($admin)->post(route('admin.backups.restore', $file))->assertRedirect()->assertSessionHasNoErrors();

        Setting::forgetCache();
        $this->assertSame("Vant's; Shop", setting('site_name'));
        $this->assertDatabaseMissing('users', ['email' => 'after@example.com']);
        $this->assertCount(2, glob($this->dir . '/db-*.sql.gz'), 'A copy of the data before the restore is kept');
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk();
    }

    public function test_bad_names_and_foreign_files_are_refused(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.backups.download', '..%2F..%2F.env'))->assertNotFound();
        $this->actingAs($admin)->get(route('admin.backups.download', 'db-2026-01-01-000000.sql.gz'))->assertNotFound();

        $junk = UploadedFile::fake()->createWithContent('x.sql.gz', gzencode("DROP TABLE users;\n"));
        $this->actingAs($admin)->post(route('admin.backups.upload'), ['backup_file' => $junk])->assertSessionHasErrors('backup');
        $this->assertSame([], glob($this->dir . '/db-*.sql.gz'));

        $this->artisan('app:backup-db')->assertSuccessful();
        $good = UploadedFile::fake()->createWithContent('copy.sql.gz', file_get_contents(BackupDatabase::latest()));
        $this->actingAs($admin)->post(route('admin.backups.upload'), ['backup_file' => $good])->assertSessionHasNoErrors();
        $this->assertCount(1, glob($this->dir . '/db-*-upload.sql.gz'));
    }

    public function test_staff_cannot_reach_backups(): void
    {
        $this->artisan('app:backup-db')->assertSuccessful();
        $staff = User::factory()->create(['role' => 'order_manager']);
        $download = $this->actingAs($staff)->get(route('admin.backups.download', basename(BackupDatabase::latest())));
        $this->assertNull($download->headers->get('content-disposition'));
        $this->assertFalse($download->isOk());
        $this->actingAs($staff)->post(route('admin.backups.store'));
        $this->assertCount(1, glob($this->dir . '/db-*.sql.gz'));
    }
}
