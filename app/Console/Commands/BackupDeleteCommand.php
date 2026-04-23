<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDeleteCommand extends Command
{
    protected $signature = 'backup:delete {filename}';
    protected $description = 'Delete a database backup';

    public function handle(): int
    {
        $filename = $this->argument('filename');
        $backupDir = storage_path('app/backups');
        $filepath = $backupDir . '/' . $filename;

        if (!file_exists($filepath)) {
            $this->error("Backup not found: {$filename}");
            return Command::FAILURE;
        }

        if (!$this->confirm("Delete backup: {$filename}?")) {
            $this->info('Delete cancelled.');
            return Command::SUCCESS;
        }

        unlink($filepath);
        $this->info("Backup deleted: {$filename}");

        activity()
            ->log("Backup deleted: {$filename}");

        return Command::SUCCESS;
    }
}
