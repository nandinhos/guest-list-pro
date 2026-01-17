<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class PromoterPerformanceChart extends ChartWidget
{
    protected ?string $heading = 'Performance por Promoter (Conversão %)';

    protected function getData(): array
    {
        $promoters = \App\Models\User::where('role', \App\Enums\UserRole::PROMOTER)
            ->withCount(['guests as total_count'])
            ->withCount(['guests as present_count' => function ($query) {
                $query->where('is_checked_in', true);
            }])
            ->get();

        $data = $promoters->map(fn ($p) => [
            'name' => $p->name,
            'conversion' => $p->total_count > 0 ? round(($p->present_count / $p->total_count) * 100, 1) : 0,
        ]);

        return [
            'datasets' => [
                [
                    'label' => 'Conversão %',
                    'data' => $data->pluck('conversion')->toArray(),
                    'backgroundColor' => '#6366F1',
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected int|string|array $columnSpan = 1;
}
