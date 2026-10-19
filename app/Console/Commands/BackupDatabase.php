<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copy of the whole database as a gzipped SQL file in storage/app/backups. How often it runs and how many files are kept
 * are set in Admin → Settings → Backups (scheduled in routes/console.php through isDue()).
 * Plain PHP, so it works on shared hosting without mysqldump. Keeps the newest --keep files.
 * Restore: gunzip the file and import it (phpMyAdmin → Import, or mysql DB < file.sql).
 */
class BackupDatabase extends Command
{
    protected $signature = 'app:backup-db {--keep= : How many backups to keep (default: the admin setting, 14)}';

    protected $description = 'Save a gzipped SQL copy of the database in storage/app/backups';

    public const DIR = 'app/backups';

    /** Admin choices: setting value => label. */
    public const FREQUENCIES = [
        '6h' => 'Every 6 hours',
        '12h' => 'Every 12 hours',
        'daily' => 'Once a day',
        'weekly' => 'Once a week',
        'off' => 'Off',
    ];

    public function handle(): int
    {
        $dir = storage_path(self::DIR);
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            $this->error("Cannot create $dir");

            return self::FAILURE;
        }

        $at = now();
        while (is_file($path = $dir . '/db-' . $at->format('Y-m-d-His') . '.sql.gz')) {
            $at = $at->addSecond(); // two backups in the same second
        }
        $gz = gzopen($path . '.part', 'wb6');
        $pdo = DB::connection()->getPdo();
        $mysql = DB::connection()->getDriverName() === 'mysql';

        gzwrite($gz, '-- ' . config('app.name') . ' database backup ' . now()->toDateTimeString() . "\n");
        if ($mysql) {
            gzwrite($gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n");
        }

        $tables = collect(Schema::getTables())->pluck('name')->reject(fn ($t) => str_starts_with($t, 'sqlite_'))->sort()->values();
        foreach ($tables as $table) {
            $q = fn (string $name) => $mysql ? '`' . str_replace('`', '``', $name) . '`' : '"' . str_replace('"', '""', $name) . '"';
            $create = $mysql
                ? (array) DB::selectOne('SHOW CREATE TABLE ' . $q($table))
                : ['sql' => DB::table('sqlite_master')->where('type', 'table')->where('name', $table)->value('sql')];
            gzwrite($gz, "\nDROP TABLE IF EXISTS {$q($table)};\n" . end($create) . ";\n");

            $rows = 0;
            if (in_array($table, \App\Support\DatabaseRestore::SKIP_ROWS, true)) {
                continue; // logins and cache: the table only
            }
            DB::table($table)->orderByRaw('1')->lazy(500)->chunk(200)->each(function ($chunk) use ($gz, $pdo, $q, $table, &$rows) {
                $values = $chunk->map(fn ($row) => '(' . collect((array) $row)
                    ->map(fn ($v) => $v === null ? 'NULL' : (is_int($v) || is_float($v) ? (string) $v : $pdo->quote((string) $v)))
                    ->implode(',') . ')');
                $cols = collect(array_keys((array) $chunk->first()))->map($q)->implode(',');
                gzwrite($gz, "INSERT INTO {$q($table)} ($cols) VALUES\n" . $values->implode(",\n") . ";\n");
                $rows += $chunk->count();
            });
            $this->line("  $table: $rows rows", null, 'v');
        }

        if ($mysql) {
            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        }
        gzclose($gz);
        rename($path . '.part', $path);

        // Oldest first out
        $all = glob($dir . '/db-*.sql.gz') ?: [];
        sort($all);
        foreach (array_slice($all, 0, max(0, count($all) - self::keep($this->option('keep')))) as $old) {
            @unlink($old);
        }

        $this->info('Saved ' . basename($path) . ' (' . number_format(filesize($path) / 1024, 1) . ' KB, ' . $tables->count() . ' tables)');

        return self::SUCCESS;
    }

    /** How many files to keep: the --keep option, else the admin setting (1–90). */
    public static function keep(mixed $option = null): int
    {
        return max(1, min(90, (int) ($option ?? setting('backup_keep', '14'))));
    }

    public static function frequency(): string
    {
        $f = (string) setting('backup_frequency', 'daily');

        return array_key_exists($f, self::FREQUENCIES) ? $f : 'daily';
    }

    /** Time of day (H:i, shop time) for daily and weekly backups. */
    public static function time(): string
    {
        $t = (string) setting('backup_time', '03:30');

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t) ? $t : '03:30';
    }

    /**
     * Checked by the scheduler every minute. Hourly choices run when the newest file is old enough; daily and weekly
     * run at the set time, or on the next minute the cron runs if that time was missed.
     */
    public static function isDue(?\Illuminate\Support\Carbon $now = null): bool
    {
        $now ??= now();
        $latest = self::latest();
        $last = $latest ? filemtime($latest) : null;

        return match (self::frequency()) {
            'off' => false,
            '6h', '12h' => $last === null || $now->getTimestamp() - $last >= (int) self::frequency() * 3600 - 300,
            default => (function () use ($now, $last) {
                [$h, $m] = array_map('intval', explode(':', self::time()));
                $slot = $now->copy()->setTime($h, $m);
                if ($slot->greaterThan($now)) {
                    $slot->subDay();
                }
                if (self::frequency() === 'weekly') {
                    $slot->subDays(6);
                }

                return $last === null || $last < $slot->getTimestamp();
            })(),
        };
    }

    /** Hours after which a missing backup means something is wrong, or null when backups are off. */
    public static function maxAgeHours(): ?int
    {
        return match (self::frequency()) {
            'off' => null,
            '6h' => 12,
            '12h' => 24,
            'weekly' => 8 * 24,
            default => 48,
        };
    }

    /** Newest backup file, or null. */
    public static function latest(): ?string
    {
        $all = glob(storage_path(self::DIR) . '/db-*.sql.gz') ?: [];
        sort($all);

        return end($all) ?: null;
    }
}
