<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pure-PHP MySQL dump (the host disables proc_open, so mysqldump is not an option).
 * Writes gzip files to storage/app/backups and prunes anything older than --keep days.
 */
class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : Days of backups to retain}';

    protected $description = 'Dump the database to storage/app/backups (gzip) and prune old backups';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            $this->error('Cannot create ' . $dir);

            return self::FAILURE;
        }

        $pdo = DB::connection()->getPdo();
        $name = DB::connection()->getDatabaseName();
        $file = $dir . '/' . preg_replace('/[^\w.-]/', '_', basename((string) $name)) . '_' . now()->format('Ymd_His') . '.sql.gz';
        $gz = gzopen($file, 'wb9');
        if (! $gz) {
            $this->error('Cannot write ' . $file);

            return self::FAILURE;
        }

        gzwrite($gz, "-- Trinetraa backup " . now()->toDateTimeString() . "\nSET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n");
        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        foreach ($tables as $row) {
            $table = array_values((array) $row)[0];
            $create = (array) DB::selectOne("SHOW CREATE TABLE `{$table}`");
            gzwrite($gz, "DROP TABLE IF EXISTS `{$table}`;\n" . array_values($create)[1] . ";\n\n");

            $stmt = $pdo->query("SELECT * FROM `{$table}`", \PDO::FETCH_ASSOC);
            $batch = [];
            $cols = null;
            while ($r = $stmt->fetch()) {
                $cols ??= '`' . implode('`,`', array_keys($r)) . '`';
                $batch[] = '(' . implode(',', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), $r)) . ')';
                if (count($batch) >= 200) {
                    gzwrite($gz, "INSERT INTO `{$table}` ({$cols}) VALUES\n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }
            if ($batch) {
                gzwrite($gz, "INSERT INTO `{$table}` ({$cols}) VALUES\n" . implode(",\n", $batch) . ";\n");
            }
            gzwrite($gz, "\n");
        }
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);

        $cutoff = now()->subDays(max(1, (int) $this->option('keep')))->getTimestamp();
        foreach (glob($dir . '/*.sql.gz') ?: [] as $old) {
            if ($old !== $file && filemtime($old) < $cutoff) {
                @unlink($old);
            }
        }

        $this->info(sprintf('Backup written: %s (%s KB)', basename($file), number_format(filesize($file) / 1024, 1)));

        return self::SUCCESS;
    }
}
