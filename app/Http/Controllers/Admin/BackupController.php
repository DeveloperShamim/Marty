<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\BackupDatabase;
use App\Http\Controllers\Controller;
use App\Support\DatabaseRestore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/** Admin → Settings → Backups: back up now, download, upload and restore the files app:backup-db writes. */
class BackupController extends Controller
{
    private const NAME = '/^db-\d{4}-\d{2}-\d{2}-\d{6}(-upload)?\.sql\.gz$/';

    public function store()
    {
        @set_time_limit(0);
        Artisan::call('app:backup-db');

        return $this->back('Backup saved: ' . basename((string) BackupDatabase::latest()));
    }

    public function download(string $file)
    {
        return response()->download($this->path($file));
    }

    public function upload(Request $request)
    {
        $request->validate(['backup_file' => ['required', 'file', 'max:204800']]);

        $dir = storage_path(BackupDatabase::DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
        $name = 'db-' . now()->format('Y-m-d-His') . '-upload.sql.gz';
        $request->file('backup_file')->move($dir, $name);

        try {
            DatabaseRestore::check("$dir/$name");
        } catch (\RuntimeException $e) {
            @unlink("$dir/$name");

            return $this->back(null, $e->getMessage());
        }

        return $this->back("Uploaded as $name. Press Restore on it to load it.");
    }

    public function restore(string $file)
    {
        try {
            $safety = DatabaseRestore::run($this->path($file));
        } catch (\Throwable $e) {
            report($e);

            return $this->back(null, 'Restore failed: ' . $e->getMessage());
        }

        \App\Services\ActivityLogger::log('Restored database backup', "$file (copy from before: $safety)");

        return $this->back("Restored $file. The data from just before is saved as $safety.");
    }

    public function destroy(string $file)
    {
        @unlink($this->path($file));

        return $this->back("Deleted $file.");
    }

    private function path(string $file): string
    {
        $path = storage_path(BackupDatabase::DIR . '/' . $file);
        abort_unless(preg_match(self::NAME, $file) && is_file($path), 404);

        return $path;
    }

    private function back(?string $status, ?string $error = null)
    {
        $to = redirect()->to(route('admin.settings.edit') . '#backups');

        return $error ? $to->withErrors(['backup' => $error]) : $to->with('status', $status);
    }
}
