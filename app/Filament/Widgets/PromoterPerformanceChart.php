<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class PromoterPerformanceChart extends ChartWidget
{
    protected ?string $heading = 'Performance por Promoter';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $eventId = session('selected_event_id');

        $query = User::where('role', UserRole::PROMOTER)
            ->withCount([
                'guests as total_count' => function ($q) use ($eventId) {
                    if ($eventId) {
                        $q->where('event_id', $eventId);
                    }
                },
                'guests as present_count' => function ($q) use ($eventId) {
                    $q->where('is_checked_in', true);
                    if ($eventId) {
                        $q->where('event_id', $eventId);
                    }
                },
            ])
            ->having('total_count', '>', 0);

        $promoters = $query->get();

        $data = $promoters->map(fn ($p) => [
            'name' => $p->name,
            'conversion' => $p->total_count > 0 ? round(($p->present_count / $p->total_count) * 100, 1) : 0,
            'total' => $p->total_count,
            'present' => $p->present_count,
        ])->sortByDesc('conversion')->values();

        $colors = $data->map(fn ($item) => $this->getColorForConversion($item['conversion']))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Conversão %',
                    'data' => $data->pluck('conversion')->toArray(),
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getDescription(): ?string
    {
        $eventId = session('selected_event_id');

        $query = User::where('role', UserRole::PROMOTER)
            ->withCount([
                'guests as total_count' => function ($q) use ($eventId) {
                    if ($eventId) {
                        $q->where('event_id', $eventId);
                    }
                },
                'guests as present_count' => function ($q) use ($eventId) {
                    $q->where('is_checked_in', true);
                    if ($eventId) {
                        $q->where('event_id', $eventId);
                    }
                },
            ])
            ->having('total_count', '>', 0);

        $promoters = $query->get();

        if ($promoters->isEmpty()) {
            return 'Sem dados de promoters';
        }

        $totalGuests = $promoters->sum('total_count');
        $totalPresent = $promoters->sum('present_count');
        $avgConversion = $totalGuests > 0 ? round(($totalPresent / $totalGuests) * 100, 1) : 0;

        return "Média geral: {$avgConversion}% ({$totalPresent}/{$totalGuests})";
    }

    private function getColorForConversion(float $conversion): string
    {
        if ($conversion >= 70) {
            return '#10B981'; // Verde
        }

        if ($conversion >= 40) {
            return '#F59E0B'; // Amarelo
        }

        return '#EF4444'; // Vermelho
    }
}
