<?php

namespace App\Filament\Admin\Pages;

use App\Enums\NavigationGroup;
use App\Models\Event;
use App\Models\Excursao;
use App\Models\Monitor;
use App\Models\User;
use App\Models\Veiculo;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use UnitEnum;

class ExcursoesReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Relatório de Excursionistas';

    protected static ?string $title = 'Relatório Nominal de Excursionistas';

    protected static ?string $slug = 'reports/excursoes';

    protected static ?int $navigationSort = 4;

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::RELATORIOS;

    protected string $view = 'filament.admin.pages.excursoes-report';

    public ?int $selectedEventId = null;

    public ?string $dataInicio = null;

    public ?string $dataFim = null;

    public ?int $criadoPorId = null;

    public function mount(): void
    {
        $this->selectedEventId = session('selected_event_id') ?? Event::first()?->id;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Filtros')
                ->schema([
                    Grid::make(4)->schema([
                        Select::make('selectedEventId')
                            ->label('Evento')
                            ->options(fn () => Event::orderByDesc('created_at')->pluck('name', 'id'))
                            ->default($this->selectedEventId)
                            ->live(),

                        Select::make('criadoPorId')
                            ->label('Responsável')
                            ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                            ->placeholder('Todos')
                            ->searchable()
                            ->live(),

                        DatePicker::make('dataInicio')
                            ->label('Cadastrado de')
                            ->displayFormat('d/m/Y')
                            ->live(),

                        DatePicker::make('dataFim')
                            ->label('Cadastrado até')
                            ->displayFormat('d/m/Y')
                            ->live(),
                    ]),
                ]),
        ]);
    }

    #[Computed]
    public function excursoes(): Collection
    {
        if (! $this->selectedEventId) {
            return collect();
        }

        return Excursao::query()
            ->where('event_id', $this->selectedEventId)
            ->with(['criadoPor:id,name', 'veiculos.monitores'])
            ->when($this->criadoPorId, fn ($q) => $q->where('criado_por', $this->criadoPorId))
            ->when($this->dataInicio, fn ($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn ($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function veiculos(): Collection
    {
        if (! $this->selectedEventId) {
            return collect();
        }

        return Veiculo::query()
            ->whereHas('excursao', fn ($q) => $q->where('event_id', $this->selectedEventId))
            ->with(['excursao.criadoPor:id,name', 'monitores'])
            ->when($this->criadoPorId, fn ($q) => $q->whereHas('excursao', fn ($eq) => $eq->where('criado_por', $this->criadoPorId)))
            ->when($this->dataInicio, fn ($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn ($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function monitores(): Collection
    {
        if (! $this->selectedEventId) {
            return collect();
        }

        return Monitor::query()
            ->where('event_id', $this->selectedEventId)
            ->with(['veiculo.excursao', 'criadoPor:id,name'])
            ->when($this->criadoPorId, fn ($q) => $q->where('criado_por', $this->criadoPorId))
            ->when($this->dataInicio, fn ($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn ($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->orderBy('nome')
            ->get();
    }

    #[Computed]
    public function totais(): array
    {
        return [
            'excursoes' => $this->excursoes->count(),
            'veiculos' => $this->veiculos->count(),
            'monitores' => $this->monitores->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(fn () => $this->exportCsv())
                ->disabled(fn () => $this->excursoes->isEmpty()),
        ];
    }

    public function exportCsv(): mixed
    {
        $event = Event::find($this->selectedEventId);
        $rows = [];

        $rows[] = ['EXCURSÃO', 'RESPONSÁVEL', 'VEÍCULOS', 'MONITORES', 'CADASTRADO EM'];
        foreach ($this->excursoes as $excursao) {
            $rows[] = [
                $excursao->nome,
                $excursao->criadoPor->name,
                $excursao->veiculos->count(),
                $excursao->veiculos->flatMap->monitores->count(),
                $excursao->created_at->format('d/m/Y H:i'),
            ];
        }

        $rows[] = [];
        $rows[] = ['VEÍCULO (TIPO)', 'PLACA', 'EXCURSÃO', 'MONITORES', 'CADASTRADO EM'];
        foreach ($this->veiculos as $veiculo) {
            $rows[] = [
                $veiculo->tipo->label(),
                $veiculo->placa ?? '—',
                $veiculo->excursao->nome,
                $veiculo->monitores->count(),
                $veiculo->created_at->format('d/m/Y H:i'),
            ];
        }

        $rows[] = [];
        $rows[] = ['MONITOR (NOME)', 'DOCUMENTO', 'VEÍCULO', 'EXCURSÃO', 'RESPONSÁVEL', 'CADASTRADO EM'];
        foreach ($this->monitores as $monitor) {
            $rows[] = [
                $monitor->nome,
                $monitor->document_type->getLabel().': '.$monitor->document_number,
                $monitor->veiculo ? ($monitor->veiculo->tipo->label().($monitor->veiculo->placa ? ' '.$monitor->veiculo->placa : '')) : '—',
                $monitor->veiculo?->excursao?->nome ?? '—',
                $monitor->criadoPor->name,
                $monitor->created_at->format('d/m/Y H:i'),
            ];
        }

        $filename = 'relatorio-excursionistas-'.($event?->id ?? 'sem-evento').'.csv';
        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
