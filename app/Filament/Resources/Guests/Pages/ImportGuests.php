<?php

namespace App\Filament\Resources\Guests\Pages;

use App\Filament\Resources\Guests\GuestResource;
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

    protected string $view = 'filament.admin.pages.import-guests';

    // Aba ativa
    public string $activeTab = 'file';

    // Upload de Arquivo
    public $file = null;

    public ?int $eventId = null;

    public ?int $sectorId = null;

    public ?int $promoterId = null;

    // Importação por Texto
    public string $textContent = '';

    public string $delimiter = 'newline';

    public ?int $textEventId = null;

    public ?int $textSectorId = null;

    public ?int $textPromoterId = null;

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
     * Retorna os setores disponíveis para o evento selecionado (Aba Arquivo).
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
     * Retorna os setores disponíveis para o evento selecionado (Aba Texto).
     */
    public function getTextSectorsProperty(): array
    {
        if (! $this->textEventId) {
            return [];
        }

        return Sector::query()
            ->where('event_id', $this->textEventId)
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
        $this->eventId = null;
        $this->promoterId = null;
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

        if (! $this->textEventId) {
            Notification::make()->title('Selecione um evento')->danger()->send();
            return;
        }

        if (! $this->textSectorId) {
            Notification::make()->title('Selecione um setor')->danger()->send();
            return;
        }

        if (! $this->textPromoterId) {
            Notification::make()->title('Selecione um promoter')->danger()->send();
            return;
        }

        $lines = $this->parseText($this->textContent, $this->delimiter);

        $imported = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            $documentNormalized = preg_replace('/\D/', '', $line['document']);

            if (!empty($documentNormalized)) {
                $exists = Guest::query()
                    ->where('event_id', $this->textEventId)
                    ->where('document_normalized', $documentNormalized)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }
            }

            Guest::create([
                'event_id' => $this->textEventId,
                'sector_id' => $this->textSectorId,
                'promoter_id' => $this->textPromoterId,
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
        $this->textEventId = null;
        $this->textPromoterId = null;
    }
}
