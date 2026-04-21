<?php

namespace App\Filament\Widgets;

use App\Models\CheckinAttempt;
use App\Models\TicketSale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesTimelineChart extends ChartWidget
{
    protected ?string $heading = 'Check-ins e Vendas por Horário';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '400px';

    protected function getData(): array
    {
        $eventId = session('selected_event_id');

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        $hourExpr = $isSqlite
            ? "strftime('%H', created_at)"
            : 'HOUR(created_at)';

        $checkinsByHour = CheckinAttempt::query()
            ->selectRaw("{$hourExpr} as hour, COUNT(*) as total")
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->whereDate('created_at', today())
            ->where('result', 'success')
            ->groupByRaw('hour')
            ->orderByRaw('hour')
            ->get();

        $salesByHour = TicketSale::query()
            ->selectRaw("{$hourExpr} as hour, COUNT(*) as total")
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->whereDate('created_at', today())
            ->groupByRaw('hour')
            ->orderByRaw('hour')
            ->get();

        $labels = [];
        $checkinsData = [];
        $salesData = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $labels[] = sprintf('%02d:00', $hour);
            $checkinsFound = $checkinsByHour->firstWhere('hour', $hour);
            $salesFound = $salesByHour->firstWhere('hour', $hour);
            $checkinsData[] = $checkinsFound?->total ?? 0;
            $salesData[] = $salesFound?->total ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Entradas',
                    'data' => $checkinsData,
                    'borderColor' => '#22C55E',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Vendas',
                    'data' => $salesData,
                    'borderColor' => '#F97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): ?string
    {
        $eventId = session('selected_event_id');

        $todayCheckins = CheckinAttempt::query()
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->whereDate('created_at', today())
            ->where('result', 'success')
            ->count();

        $todaySales = TicketSale::query()
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->whereDate('created_at', today())
            ->count();

        return "Entradas: {$todayCheckins} | Vendas: {$todaySales}";
    }
}
