<?php

namespace App\Support;

use App\Console\Commands\BackupDatabase;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Loads a backup made by app:backup-db back into the database (Admin → Settings → Backups → Restore).
 * Takes a fresh backup first, so a wrong restore can itself be undone. Login sessions and the cache are left as they
 * are, so the admin stays signed in. Runs migrations afterwards in case the backup is older than the code.
 */
class DatabaseRestore
{
    /** Tables whose rows are not worth keeping in a backup or replacing on restore. */
    public const SKIP_ROWS = ['sessions', 'cache', 'cache_locks'];

    /** Checks the file is one of our backups for this kind of database; returns its first line. */
    public static function check(string $path): string
    {
        $gz = @gzopen($path, 'rb');
        if (! $gz) {
            throw new RuntimeException('The file cannot be opened.');
        }
        $first = (string) gzgets($gz, 4096);
        $second = (string) gzgets($gz, 4096);
        gzclose($gz);

        if (! str_starts_with($first, '-- ') || ! str_contains($first, 'database backup')) {
            throw new RuntimeException('This is not a backup file made by this store.');
        }
        $mysqlDump = str_starts_with($second, 'SET NAMES');
        $mysqlHere = DB::connection()->getDriverName() === 'mysql';
        if ($mysqlDump !== $mysqlHere) {
            throw new RuntimeException('This backup is from a ' . ($mysqlDump ? 'MySQL' : 'SQLite') . ' database and cannot be loaded into this one.');
        }

        return trim($first);
    }

    /** Restores the file; returns the name of the safety backup taken first. */
    public static function run(string $path): string
    {
        self::check($path);
        @set_time_limit(0);

        // Copy first: the safety backup may prune the very file being restored.
        $work = tempnam(storage_path('app'), 'restore');
        copy($path, $work);

        try {
            Artisan::call('app:backup-db', ['--keep' => BackupDatabase::keep() + 1]);
            $safety = basename((string) BackupDatabase::latest());

            $sqlite = DB::connection()->getDriverName() === 'sqlite';
            if ($sqlite) {
                DB::statement('PRAGMA foreign_keys = OFF');
            }

            $gz = gzopen($work, 'rb');
            gzgets($gz, 4096); // header comment
            foreach (self::statements($gz, ! $sqlite) as $sql) {
                if (! self::skip($sql)) {
                    DB::unprepared($sql);
                }
            }
            gzclose($gz);

            if ($sqlite) {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        } finally {
            @unlink($work);
        }

        Artisan::call('migrate', ['--force' => true]);
        Setting::forgetCache();

        return $safety;
    }

    /** Statements of a dump, split on ; outside quoted strings. MySQL strings use backslash escapes, SQLite only ''. */
    public static function statements($gz, bool $backslash): \Generator
    {
        $stmt = '';
        $quoted = false;
        $escaped = false;
        $stops = $backslash ? "'\\" : "'";

        while (! gzeof($gz)) {
            $chunk = (string) gzread($gz, 1 << 20);
            $len = strlen($chunk);
            $start = 0;
            $i = 0;
            while ($i < $len) {
                if ($escaped) {
                    $escaped = false;
                    $i++;
                    continue;
                }
                if ($quoted) {
                    $i += strcspn($chunk, $stops, $i);
                    if ($i >= $len) {
                        break;
                    }
                    if ($chunk[$i] === '\\') {
                        $escaped = true;
                    } else {
                        $quoted = false;
                    }
                    $i++;
                    continue;
                }
                $i += strcspn($chunk, "';", $i);
                if ($i >= $len) {
                    break;
                }
                if ($chunk[$i] === "'") {
                    $quoted = true;
                } else {
                    $sql = trim($stmt . substr($chunk, $start, $i - $start));
                    if ($sql !== '') {
                        yield $sql;
                    }
                    $stmt = '';
                    $start = $i + 1;
                }
                $i++;
            }
            $stmt .= substr($chunk, $start);
        }

        if (trim($stmt) !== '') {
            yield trim($stmt);
        }
    }

    /** Leaves sessions and cache tables alone while they exist here. */
    private static function skip(string $sql): bool
    {
        if (! preg_match('/^(?:DROP TABLE IF EXISTS|CREATE TABLE|INSERT INTO)\s+[`"]?([A-Za-z0-9_]+)/i', $sql, $m)) {
            return false;
        }

        return in_array($m[1], self::SKIP_ROWS, true) && Schema::hasTable($m[1]);
    }
}
