<?php

namespace App\Filament\Widgets;

use App\Models\Guest;
use Filament\Widgets\ChartWidget;

class CheckinFlowChart extends ChartWidget
{
    protected ?string $heading = 'Fluxo de Check-in por Hora';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $eventId = session('selected_event_id');

        $query = Guest::where('is_checked_in', true)
            ->whereDate('checked_in_at', now()->today());

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $data = $query
            ->selectRaw('HOUR(checked_in_at) as hour, count(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        $chartData = [];
        $labels = [];
        $peakHour = null;
        $peakCount = 0;

        for ($i = 0; $i < 24; $i++) {
            $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT).':00';
            $count = $data[$i] ?? 0;
            $chartData[] = $count;

            if ($count > $peakCount) {
                $peakCount = $count;
                $peakHour = $i;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Entradas',
                    'data' => $chartData,
                    'fill' => 'start',
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.3,
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

        $query = Guest::where('is_checked_in', true)
            ->whereDate('checked_in_at', now()->today());

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $data = $query
            ->selectRaw('HOUR(checked_in_at) as hour, count(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();

        if ($data && $data->count > 0) {
            $hour = str_pad($data->hour, 2, '0', STR_PAD_LEFT);

            return "Pico: {$hour}:00 - {$data->count} entradas";
        }

        return 'Sem entradas hoje';
    }
}
