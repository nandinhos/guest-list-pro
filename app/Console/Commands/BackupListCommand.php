<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupListCommand extends Command
{
    protected $signature = 'backup:list';
    protected $description = 'List all database backups';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            $this->info('No backups found.');
            return Command::SUCCESS;
        }

        $files = collect(scandir($backupDir))
            ->filter(fn ($f) => str_ends_with($f, '.sql') || str_ends_with($f, '.sqlite'))
            ->map(fn ($f) => [
                'filename' => $f,
                'size' => number_format(filesize($backupDir . '/' . $f) / 1024, 2) . ' KB',
                'modified' => Carbon::createFromTimestamp(filemtime($backupDir . '/' . $f))->format('Y-m-d H:i:s'),
            ])
            ->sortByDesc('modified')
            ->values()
            ->toArray();

        if (empty($files)) {
            $this->info('No backups found.');
            return Command::SUCCESS;
        }

        $this->table(['Filename', 'Size', 'Modified'], $files);

        return Command::SUCCESS;
    }
}
