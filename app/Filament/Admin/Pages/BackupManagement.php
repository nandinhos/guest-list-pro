<?php

namespace App\Filament\Admin\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;

class BackupManagement extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloudArrowUp;

    protected static ?string $navigationLabel = 'Gestão de Backups';

    protected static ?string $title = 'Gestão de Backups';

    protected static ?string $slug = 'backups';

    protected static UnitEnum|string|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.admin.pages.backup-management';

    public function mount(): void
    {
        if (!auth()->user()->can('backup')) {
            abort(403);
        }
    }

    #[Computed]
    public function backups(): array
    {
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            return [];
        }

        return collect(scandir($backupDir))
            ->filter(fn ($f) => str_ends_with($f, '.sql') || str_ends_with($f, '.sqlite'))
            ->map(fn ($f) => [
                'filename' => $f,
                'size' => number_format(filesize($backupDir . '/' . $f) / 1024, 2) . ' KB',
                'modified' => \Carbon\Carbon::createFromTimestamp(filemtime($backupDir . '/' . $f))->format('d/m/Y H:i:s'),
            ])
            ->sortByDesc('modified')
            ->values()
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Criar Backup')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->action(function () {
                    Artisan::call('backup:create');
                    $this->dispatch('refreshBackups');
                    \Filament\Notifications\Notification::make()
                        ->title('Backup criado')
                        ->success()
                        ->send();
                }),

            Action::make('refresh')
                ->label('Atualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->dispatch('refreshBackups');
                }),
        ];
    }

    public function deleteBackup(string $filename): void
    {
        Artisan::call('backup:delete', ['filename' => $filename]);
        $this->dispatch('refreshBackups');
    }
}
