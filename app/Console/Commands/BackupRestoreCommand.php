<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class BackupRestoreCommand extends Command
{
    protected $signature = 'backup:restore {filename} {--force : Skip confirmation}';
    protected $description = 'Restore a database backup';

    public function handle(): int
    {
        $filename = $this->argument('filename');
        $backupDir = storage_path('app/backups');
        $filepath = $backupDir . '/' . $filename;

        if (!file_exists($filepath)) {
            $this->error("Backup not found: {$filename}");
            return Command::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This will overwrite the current database. Are you sure?')) {
            $this->info('Restore cancelled.');
            return Command::SUCCESS;
        }

        $this->info('Restoring database...');

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $importCmd = "mysql -h{$dbHost} -P{$dbPort} -u{$dbUser}";
        if ($dbPass) {
            $importCmd .= " -p" . escapeshellarg($dbPass);
        }
        $importCmd .= " {$dbName} < {$filepath}";

        exec($importCmd, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Failed to restore database.');
            return Command::FAILURE;
        }

        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        $this->info("Database restored successfully from: {$filename}");

        activity()
            ->log("Backup restored: {$filename}");

        return Command::SUCCESS;
    }
}
