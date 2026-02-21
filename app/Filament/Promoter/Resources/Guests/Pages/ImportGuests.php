<?php

namespace App\Filament\Promoter\Resources\Guests\Pages;

use App\Filament\Promoter\Resources\Guests\GuestResource;
use App\Imports\GuestsImport;
use App\Models\Guest;
use App\Models\Sector;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportGuests extends Page
{
    use WithFileUploads;
    use \App\Traits\HasGuestImport;

    protected static string $resource = GuestResource::class;

    protected static ?string $title = 'Importar Convidados';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected string $view = 'filament.promoter.pages.import-guests';

    // Aba ativa
    public string $activeTab = 'file';

    // Upload de arquivo
    public $file = null;

    public ?int $sectorId = null;

    // Importação por texto
    public string $textContent = '';

    public string $delimiter = 'newline';

    public ?int $textSectorId = null;

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
     * Processa a importação do arquivo.
     */
    public function import(): void
    {
        if (! $this->file) {
            Notification::make()->title('Selecione um arquivo')->danger()->send();
            return;
        }

        if (! $this->sectorId) {
            Notification::make()->title('Selecione um setor')->danger()->send();
            return;
        }

        $eventId = session('selected_event_id');
        $promoterId = auth()->id();

        $import = new GuestsImport($eventId, $this->sectorId, $promoterId);

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

    /**
     * Processa a importação por texto colado.
     */
    public function importFromText(): void
    {
        if (empty($this->textContent)) {
            Notification::make()->title('Cole o texto com os convidados')->danger()->send();
            return;
        }

        if (! $this->textSectorId) {
            Notification::make()->title('Selecione um setor')->danger()->send();
            return;
        }

        $eventId = session('selected_event_id');
        $promoterId = auth()->id();

        $lines = $this->parseText($this->textContent, $this->delimiter);

        $imported = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $documentNormalized = preg_replace('/\D/', '', $line['document']);

            // Verifica duplicidade
            if (!empty($documentNormalized)) {
                $exists = Guest::query()
                    ->where('event_id', $eventId)
                    ->where('document_normalized', $documentNormalized)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }
            }

            Guest::create([
                'event_id' => $eventId,
                'sector_id' => $this->textSectorId,
                'promoter_id' => $promoterId,
                'name' => $line['name'],
                'document' => $line['document'] ?: '',
            ]);

            $imported++;
        }

        Notification::make()
            ->title('Importação concluída!')
            ->body("{$imported} convidados importados, {$skipped} ignorados (duplicados).")
            ->success()
            ->send();

        $this->textContent = '';
        $this->textSectorId = null;
    }
}
