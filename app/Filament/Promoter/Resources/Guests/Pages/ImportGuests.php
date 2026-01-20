<?php

namespace App\Filament\Promoter\Resources\Guests\Pages;

use App\Filament\Promoter\Resources\Guests\GuestResource;
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

    protected string $view = 'filament.promoter.pages.import-guests';

    public $file = null;

    public ?int $sectorId = null;

    /**
     * Retorna os setores disponíveis para o promoter no evento selecionado.
     */
    public function getSectorsProperty(): array
    {
        $eventId = session('selected_event_id');

        return Sector::query()
            ->where('event_id', $eventId)
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
            Notification::make()
                ->title('Selecione um arquivo')
                ->danger()
                ->send();

            return;
        }

        if (! $this->sectorId) {
            Notification::make()
                ->title('Selecione um setor')
                ->danger()
                ->send();

            return;
        }

        $eventId = session('selected_event_id');
        $promoterId = auth()->id();

        $import = new GuestsImport($eventId, $this->sectorId, $promoterId);

        // Obtém a extensão original e importa passando tipo explícito
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

        // Limpa o formulário
        $this->file = null;
        $this->sectorId = null;
    }
}
