<?php

namespace App\Filament\Resources\Events\Pages;

use App\Enums\NavigationGroup;
use App\Services\ExcursoesImportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ImportExcursoes extends Page
{
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Importar Excursões';

    protected static ?string $title = 'Importar Excursões';

    protected static ?string $slug = 'import-excursoes';

    protected static ?int $navigationSort = 99;

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::CONFIGURACOES;

    protected string $view = 'filament.resources.events.pages.import-excursoes';

    public ?array $data = [];

    public ?string $fileContent = null;

    public array $preview = [];

    public array $previewSummary = [];

    public array $importResult = [];

    public bool $showPreview = false;

    public bool $showResult = false;

    public ?int $selectedEventId = null;

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->statePath('data')
            ->components([
                Section::make('Arquivo')
                    ->description('Envie o arquivo .md com a lista de excursões')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Arquivo')
                            ->acceptedFileTypes(['text/plain', 'text/markdown', 'text/x-markdown'])
                            ->maxSize(10240)
                            ->live()
                            ->storeFileNamesIn('original_filename')
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    $this->fileContent = file_get_contents($state->getRealPath());
                                    $this->parsePreview();
                                }
                            }),
                    ]),
            ]);
    }

    public function parsePreview(): void
    {
        if (empty($this->fileContent)) {
            return;
        }

        $service = app(ExcursoesImportService::class);
        $service->parseFile($this->fileContent);

        $this->preview = $service->preview;
        $this->previewSummary = $service->previewSummary;
        $this->showPreview = true;
        $this->showResult = false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importar Excursões')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(fn () => $this->import())
                ->disabled(! $this->showPreview || ! $this->selectedEventId),
        ];
    }

    public function import(): void
    {
        if (empty($this->fileContent) || ! $this->selectedEventId) {
            \Filament\Notifications\Notification::make()
                ->title('Selecione um evento e carregue um arquivo')
                ->danger()
                ->send();

            return;
        }

        $service = app(ExcursoesImportService::class);
        $service->parseFile($this->fileContent);

        $this->importResult = $service->import($this->selectedEventId, auth()->id());

        $this->showPreview = false;
        $this->showResult = true;

        if ($this->importResult['monitores_created'] > 0 || $this->importResult['veiculos_created'] > 0) {
            \Filament\Notifications\Notification::make()
                ->title('Importação concluída!')
                ->body("{$this->importResult['monitores_created']} monitores e {$this->importResult['veiculos_created']} veículos importados.")
                ->success()
                ->send();
        } else {
            \Filament\Notifications\Notification::make()
                ->title('Nenhum dado importado')
                ->body('Verifique os erros abaixo.')
                ->warning()
                ->send();
        }
    }

    public function resetForm(): void
    {
        $this->reset(['data', 'fileContent', 'preview', 'previewSummary', 'importResult', 'selectedEventId']);
        $this->showPreview = false;
        $this->showResult = false;
        $this->form->fill();
    }
}
