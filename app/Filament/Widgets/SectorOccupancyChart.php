<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SectorOccupancyChart extends ChartWidget
{
    protected ?string $heading = 'Ocupação por Setor (%)';

    protected function getData(): array
    {
        $sectors = \App\Models\Sector::with(['event'])->withCount(['guests as present_count' => function ($query) {
            $query->where('is_checked_in', true);
        }])->get();

        $data = $sectors->map(fn ($sector) => [
            'label' => "{$sector->event?->name} - {$sector->name}",
            'value' => $sector->capacity > 0 ? round(($sector->present_count / $sector->capacity) * 100, 1) : 0,
        ]);

        return [
            'datasets' => [
                [
                    'label' => 'Ocupação %',
                    'data' => $data->pluck('value')->toArray(),
                    'backgroundColor' => ['#36A2EB', '#FF6384', '#4BC0C0', '#FFCE56', '#9966FF'],
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected int|string|array $columnSpan = 1;
}
