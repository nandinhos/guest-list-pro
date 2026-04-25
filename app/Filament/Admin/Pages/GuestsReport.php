<?php

namespace App\Filament\Admin\Pages;

use App\Enums\NavigationGroup;
use App\Models\Event;
use App\Models\Guest;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use UnitEnum;

class GuestsReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Relatório de Cortesias';

    protected static ?string $title = 'Relatório de Cortesias';

    protected static ?string $slug = 'reports/guests-summary';

    protected static ?int $navigationSort = 3;

    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::RELATORIOS;

    protected string $view = 'filament.admin.pages.guests-report';

    public ?int $selectedEventId = null;

    public function mount(): void
    {
        $this->selectedEventId = session('selected_event_id') ?? Event::first()?->id;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filtros')
                    ->schema([
                        Select::make('selectedEventId')
                            ->label('Evento')
                            ->options(fn () => Event::where('status', 'active')->pluck('name', 'id'))
                            ->default($this->selectedEventId)
                            ->live(),
                    ])->columns(1),
            ]);
    }

    #[Computed]
    public function reportData(): Collection
    {
        if (! $this->selectedEventId) {
            return collect();
        }

        return Guest::query()
            ->where('event_id', $this->selectedEventId)
            ->selectRaw('promoter_id, sector_id, COUNT(*) as total, SUM(CASE WHEN is_checked_in = 1 THEN 1 ELSE 0 END) as validated')
            ->with(['promoter:id,name', 'sector:id,name'])
            ->groupBy('promoter_id', 'sector_id')
            ->get()
            ->groupBy('promoter_id')
            ->map(function ($items, $promoterId) {
                $promoterName = $items->first()->promoter->name;

                $pistaGuests = $items->filter(fn ($item) => $item->sector?->name === 'PISTA');
                $backstageGuests = $items->filter(fn ($item) => $item->sector?->name === 'BACKSTAGE');

                $pistaTotal = $pistaGuests->sum('total');
                $pistaValidated = $pistaGuests->sum('validated');
                $backstageTotal = $backstageGuests->sum('total');
                $backstageValidated = $backstageGuests->sum('validated');
                $grandTotal = $pistaTotal + $backstageTotal;

                return [
                    'promoter_name' => $promoterName,
                    'pista_total' => $pistaTotal,
                    'pista_validated' => $pistaValidated,
                    'backstage_total' => $backstageTotal,
                    'backstage_validated' => $backstageValidated,
                    'total' => $grandTotal,
                    'total_validated' => $pistaValidated + $backstageValidated,
                ];
            })
            ->values();
    }

    #[Computed]
    public function totals(): array
    {
        $data = $this->reportData;

        return [
            'pista_total' => $data->sum('pista_total'),
            'pista_validated' => $data->sum('pista_validated'),
            'backstage_total' => $data->sum('backstage_total'),
            'backstage_validated' => $data->sum('backstage_validated'),
            'grand_total' => $data->sum('total'),
            'grand_validated' => $data->sum('total_validated'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->action(function () {
                    return $this->exportPdf();
                })
                ->disabled(fn () => $this->reportData->isEmpty()),

            Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action(function () {
                    return $this->exportExcel();
                })
                ->disabled(fn () => $this->reportData->isEmpty()),
        ];
    }

    public function exportPdf()
    {
        $event = Event::find($this->selectedEventId);

        $data = [
            'eventName' => $event->name,
            'eventDate' => $event->date->format('d/m/Y'),
            'data' => $this->reportData,
            'totals' => $this->totals,
            'generatedBy' => Auth::user()->name,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ];

        activity()
            ->causedBy(Auth::user())
            ->withProperties(['event_id' => $this->selectedEventId])
            ->log('Relatório de cortesias exportado');

        $pdf = Pdf::loadView('pdf.guests-report', $data);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'relatorio-cortesias-'.$event->id.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function exportExcel()
    {
        $event = Event::find($this->selectedEventId);

        $export = new \App\Exports\GuestsReportExport(
            data: $this->reportData->toArray(),
            totals: $this->totals,
            eventName: $event->name,
        );

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'relatorio-cortesias-'.$event->id.'.xlsx');
    }
}
