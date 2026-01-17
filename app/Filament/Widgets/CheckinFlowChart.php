<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class CheckinFlowChart extends ChartWidget
{
    protected ?string $heading = 'Fluxo de Check-in (Hoje)';

    protected function getData(): array
    {
        $data = \App\Models\Guest::where('is_checked_in', true)
            ->whereDate('checked_in_at', now()->today())
            ->selectRaw('HOUR(checked_in_at) as hour, count(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        $chartData = [];
        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = "{$i}h";
            $chartData[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Entradas',
                    'data' => $chartData,
                    'fill' => 'start',
                    'borderColor' => '#10B981',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected int|string|array $columnSpan = 'full';
}
