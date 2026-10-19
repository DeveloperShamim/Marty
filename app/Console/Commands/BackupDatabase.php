<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Daily copy of the whole database as a gzipped SQL file in storage/app/backups (scheduled in routes/console.php).
 * Plain PHP, so it works on shared hosting without mysqldump. Keeps the newest --keep files.
 * Restore: gunzip the file and import it (phpMyAdmin → Import, or mysql DB < file.sql).
 */
class BackupDatabase extends Command
{
    protected $signature = 'app:backup-db {--keep=14 : How many backups to keep}';

    protected $description = 'Save a gzipped SQL copy of the database in storage/app/backups';

    public const DIR = 'app/backups';

    public function handle(): int
    {
        $dir = storage_path(self::DIR);
        if (! is_dir($dir) && ! mkdir($dir, 0750, true) && ! is_dir($dir)) {
            $this->error("Cannot create $dir");

            return self::FAILURE;
        }

        $path = $dir . '/db-' . now()->format('Y-m-d-His') . '.sql.gz';
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
        foreach (array_slice($all, 0, max(0, count($all) - (int) $this->option('keep'))) as $old) {
            @unlink($old);
        }

        $this->info('Saved ' . basename($path) . ' (' . number_format(filesize($path) / 1024, 1) . ' KB, ' . $tables->count() . ' tables)');

        return self::SUCCESS;
    }

    /** Newest backup file, or null. */
    public static function latest(): ?string
    {
        $all = glob(storage_path(self::DIR) . '/db-*.sql.gz') ?: [];
        sort($all);

        return end($all) ?: null;
    }
}
