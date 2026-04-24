<?php

namespace App\Filament\Admin\Pages;

use App\Enums\NavigationGroup;
use App\Services\GuestImportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class ImportGuestsPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static ?string $navigationLabel = 'Importar Convidados';

    protected static ?string $title = 'Importar Convidados';

    protected static ?string $slug = 'import-guests';

    protected static ?int $navigationSort = 98;

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::CONFIGURACOES;

    protected string $view = 'filament.admin.pages.import-guests';

    public ?array $data = [];

    public ?string $fileContent = null;

    public array $preview = [];

    public array $previewSummary = [];

    public array $importResult = [];

    public array $parsedEvent = [];

    public bool $showPreview = false;

    public bool $showResult = false;

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
                    ->description('Envie o arquivo .md ou .txt com a lista de convidados')
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

    protected function loadFileContent(string $path): void
    {
        $fullPath = Storage::disk('local')->path($path);

        if (file_exists($fullPath)) {
            $this->fileContent = file_get_contents($fullPath);
            $this->parsePreview();
        }
    }

    public function parsePreview(): void
    {
        if (empty($this->fileContent)) {
            return;
        }

        $service = app(GuestImportService::class);
        $service->parseFile($this->fileContent);

        $this->preview = $service->preview;
        $this->previewSummary = $service->getPreviewSummary();
        $this->parsedEvent = $service->parsedEvent;
        $this->showPreview = true;
        $this->showResult = false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Importar Convidados')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(fn () => $this->import())
                ->disabled(! $this->showPreview),
        ];
    }

    public function import(): void
    {
        if (empty($this->fileContent)) {
            \Filament\Notifications\Notification::make()
                ->title('Nenhum arquivo carregado')
                ->danger()
                ->send();

            return;
        }

        $service = app(GuestImportService::class);
        $service->parseFile($this->fileContent);

        $this->importResult = $service->import(auth()->id());

        $this->showPreview = false;
        $this->showResult = true;

        if ($this->importResult['imported'] > 0) {
            \Filament\Notifications\Notification::make()
                ->title('Importação concluída!')
                ->body("{$this->importResult['imported']} convidados importados.")
                ->success()
                ->send();
        } else {
            \Filament\Notifications\Notification::make()
                ->title('Nenhum convidado importado')
                ->body('Verifique os erros abaixo.')
                ->warning()
                ->send();
        }
    }

    public function resetForm(): void
    {
        $this->reset(['data', 'fileContent', 'preview', 'previewSummary', 'importResult', 'parsedEvent']);
        $this->showPreview = false;
        $this->showResult = false;
        $this->form->fill();
    }
}
