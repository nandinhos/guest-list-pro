<?php

namespace App\Filament\Resources\Guests\Pages;

use App\Filament\Resources\Guests\GuestResource;
use App\Imports\GuestsImport;
use App\Models\Sector;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportGuests extends Page
{
    use WithFileUploads;

    protected static string $resource = GuestResource::class;

    protected static ?string $title = 'Importar Convidados';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected string $view = 'filament.admin.pages.import-guests';

    public $file = null;

    public ?int $sectorId = null;

    public ?int $eventId = null;

    public ?int $promoterId = null;

    /**
     * Retorna os eventos disponíveis (Admin vê todos).
     */
    public function getEventsProperty(): array
    {
        return \App\Models\Event::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Retorna os setores disponíveis para o evento selecionado.
     */
    public function getSectorsProperty(): array
    {
        if (! $this->eventId) {
            return [];
        }

        return Sector::query()
            ->where('event_id', $this->eventId)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Retorna os usuários que podem ser responsáveis por convidados (Admin ou Promoter).
     */
    public function getPromotersProperty(): array
    {
        return \App\Models\User::query()
            ->whereIn('role', [
                \App\Enums\UserRole::ADMIN,
                \App\Enums\UserRole::PROMOTER,
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Ação de baixar o template de importação.
     */
    public function downloadTemplateAction(): Action
    {
        return Action::make('downloadTemplate')
            ->label('Baixar Modelo')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->action(function () {
                return response()->download(
                    storage_path('app/templates/modelo-importacao-convidados.xlsx')
                );
            });
    }

    /**
     * Processa a importação do arquivo.
     */
    public function import(): void
    {
        if (! $this->file) {
            Notification::make()->title('Selecione um arquivo')->danger()->send();

            return;
        }

        if (! $this->eventId) {
            Notification::make()->title('Selecione um evento')->danger()->send();

            return;
        }

        if (! $this->sectorId) {
            Notification::make()->title('Selecione um setor')->danger()->send();

            return;
        }

        if (! $this->promoterId) {
            Notification::make()->title('Selecione um promoter')->danger()->send();

            return;
        }

        $import = new GuestsImport($this->eventId, $this->sectorId, $this->promoterId);

        $extension = $this->file->getClientOriginalExtension();
        $readerType = match (strtolower($extension)) {
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        Excel::import($import, $this->file->getRealPath(), null, $readerType);

        $imported = $import->getImportedCount();
        $skipped = $import->getSkippedCount();

        Notification::make()
            ->title('Importação concluída!')
            ->body("{$imported} convidados importados, {$skipped} ignorados (duplicados).")
            ->success()
            ->send();

        $this->file = null;
        $this->sectorId = null;
    }
}
