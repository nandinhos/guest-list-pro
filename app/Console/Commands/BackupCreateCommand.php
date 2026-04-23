<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupCreateCommand extends Command
{
    protected $signature = 'backup:create';
    protected $description = 'Create a database backup';

    public function handle(): int
    {
        $this->info('Creating database backup...');

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup_' . Carbon::now()->format('Y_m_d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        $dumpCmd = "mysqldump -h{$dbHost} -P{$dbPort} -u{$dbUser}";
        if ($dbPass) {
            $dumpCmd .= " -p" . escapeshellarg($dbPass);
        }
        $dumpCmd .= " {$dbName} > {$filepath}";

        $fullCmd = "docker compose exec -T laravel.test bash -c " . escapeshellarg($dumpCmd);

        exec($fullCmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
            $this->error('Unable to create backup.');
            return Command::FAILURE;
        }

        $size = filesize($filepath);
        $this->info("Backup created: {$filename}");
        $this->info("Size: " . number_format($size / 1024, 2) . " KB");

        activity()
            ->log("Backup created: {$filename}");

        return Command::SUCCESS;
    }
}
