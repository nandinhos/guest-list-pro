<?php

namespace App\Filament\Bilheteria\Pages;

use App\Enums\PaymentMethod;
use App\Models\TicketSale;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class CashClosing extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?string $navigationLabel = 'Fechamento de Caixa';

    protected static ?string $title = 'Fechamento de Caixa';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.bilheteria.pages.cash-closing';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'date' => now()->toDateString(),
            'start_time' => '00:00',
            'end_time' => '23:59',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filtrar Período')
                    ->description('Selecione o período para o fechamento de caixa')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Data')
                            ->required()
                            ->default(now())
                            ->live(),

                        TimePicker::make('start_time')
                            ->label('Hora Início')
                            ->required()
                            ->seconds(false)
                            ->default('00:00')
                            ->live(),

                        TimePicker::make('end_time')
                            ->label('Hora Fim')
                            ->required()
                            ->seconds(false)
                            ->default('23:59')
                            ->live(),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    #[Computed]
    public function sales(): Collection
    {
        $eventId = session('selected_event_id');

        if (! $eventId || ! $this->data['date']) {
            return collect();
        }

        $startDateTime = Carbon::parse($this->data['date'].' '.($this->data['start_time'] ?? '00:00'));
        $endDateTime = Carbon::parse($this->data['date'].' '.($this->data['end_time'] ?? '23:59'));

        return TicketSale::query()
            ->where('event_id', $eventId)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->with(['seller', 'guest'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function salesByPaymentMethod(): array
    {
        $grouped = $this->sales->groupBy('payment_method');

        $result = [];
        foreach (PaymentMethod::cases() as $method) {
            $methodSales = $grouped->get($method->value, collect());
            $result[$method->value] = [
                'label' => $method->getLabel(),
                'icon' => $method->getIcon(),
                'color' => $method->getColor(),
                'count' => $methodSales->count(),
                'total' => $methodSales->sum('value'),
            ];
        }

        return $result;
    }

    #[Computed]
    public function totalSales(): float
    {
        return $this->sales->sum('value');
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->sales->count();
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
                ->disabled(fn () => $this->sales->isEmpty()),

            Action::make('refresh')
                ->label('Atualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    unset($this->sales, $this->salesByPaymentMethod, $this->totalSales, $this->totalCount);
                    Notification::make()
                        ->title('Dados atualizados')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function exportPdf()
    {
        $eventId = session('selected_event_id');
        $event = \App\Models\Event::find($eventId);

        $data = [
            'event' => $event,
            'date' => $this->data['date'],
            'start_time' => $this->data['start_time'],
            'end_time' => $this->data['end_time'],
            'sales' => $this->sales,
            'salesByPaymentMethod' => $this->salesByPaymentMethod,
            'totalSales' => $this->totalSales,
            'totalCount' => $this->totalCount,
            'generatedBy' => Auth::user()->name,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ];

        // Log do fechamento de caixa
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'event_id' => $eventId,
                'date' => $this->data['date'],
                'start_time' => $this->data['start_time'],
                'end_time' => $this->data['end_time'],
                'total_sales' => $this->totalSales,
                'total_count' => $this->totalCount,
            ])
            ->log('Fechamento de caixa exportado');

        $pdf = Pdf::loadView('pdf.cash-closing', $data);

        $filename = 'fechamento-caixa-'.$this->data['date'].'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
