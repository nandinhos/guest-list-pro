<?php

namespace App\Filament\Admin\Pages;

use App\Enums\NavigationGroup;
use App\Jobs\ResetDatabaseJob;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Computed;
use UnitEnum;

class BackupManagement extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCloudArrowUp;

    protected static ?string $navigationLabel = 'Gestão de Backups';

    protected static ?string $title = 'Gestão de Backups';

    protected static ?string $slug = 'backups';

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::CONFIGURACOES;

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.admin.pages.backup-management';

    public function mount(): void
    {
        if (auth()->user()->role !== \App\Enums\UserRole::ADMIN) {
            abort(403);
        }
    }

    #[Computed]
    public function backups(): array
    {
        $backupDir = storage_path('app/backups');

        if (! is_dir($backupDir)) {
            return [];
        }

        return collect(scandir($backupDir))
            ->filter(fn ($f) => str_ends_with($f, '.sql') || str_ends_with($f, '.sqlite'))
            ->map(fn ($f) => [
                'filename' => $f,
                'size' => number_format(filesize($backupDir.'/'.$f) / 1024, 2).' KB',
                'modified' => \Carbon\Carbon::createFromTimestamp(filemtime($backupDir.'/'.$f))->format('d/m/Y H:i:s'),
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
                    redirect()->setIntendedUrl('/admin/backups');
                    \Filament\Notifications\Notification::make()
                        ->title('Backup criado com sucesso!')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function deleteBackup(string $filename): void
    {
        Artisan::call('backup:delete', ['filename' => $filename, '--force' => true]);
        redirect('/admin/backups');
    }

    public function restoreBackup(string $filename): void
    {
        if (auth()->user()->role !== \App\Enums\UserRole::ADMIN) {
            abort(403);
        }

        Artisan::call('backup:restore', ['filename' => $filename, '--force' => true]);
        redirect('/admin/backups');
    }

    public function resetDatabase(): void
    {
        if (! app()->environment(['local', 'development'])) {
            \Filament\Notifications\Notification::make()
                ->title('Ação não permitida')
                ->body('Esta ação só está disponível em ambiente de desenvolvimento.')
                ->danger()
                ->send();

            return;
        }

        dispatch_sync(new ResetDatabaseJob);

        \Filament\Notifications\Notification::make()
            ->title('Banco resetado com sucesso!')
            ->body('Redirecionando para login...')
            ->success()
            ->send();

        redirect('/login');
    }
}
