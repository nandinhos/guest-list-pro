<?php

namespace App\Filament\Widgets;

use App\Enums\RequestStatus;
use App\Models\ApprovalRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RequestsTimelineChart extends ChartWidget
{
    protected ?string $heading = 'Solicitações por Dia';

    protected ?string $description = 'Últimos 7 dias';

    protected ?string $maxHeight = '250px';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        $eventId = session('selected_event_id');
        $days = collect();

        // Últimos 7 dias
        for ($i = 6; $i >= 0; $i--) {
            $days->push(Carbon::today()->subDays($i));
        }

        $labels = $days->map(fn ($day) => $day->format('d/m'))->toArray();

        $baseQuery = ApprovalRequest::query();
        if ($eventId) {
            $baseQuery->where('event_id', $eventId);
        }

        // Solicitações criadas por dia
        $created = $days->map(function ($day) use ($baseQuery) {
            return (clone $baseQuery)
                ->whereDate('created_at', $day)
                ->count();
        })->toArray();

        // Solicitações aprovadas por dia
        $approved = $days->map(function ($day) use ($baseQuery) {
            return (clone $baseQuery)
                ->where('status', RequestStatus::APPROVED)
                ->whereDate('reviewed_at', $day)
                ->count();
        })->toArray();

        // Solicitações rejeitadas por dia
        $rejected = $days->map(function ($day) use ($baseQuery) {
            return (clone $baseQuery)
                ->where('status', RequestStatus::REJECTED)
                ->whereDate('reviewed_at', $day)
                ->count();
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Criadas',
                    'data' => $created,
                    'borderColor' => 'rgb(59, 130, 246)', // blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Aprovadas',
                    'data' => $approved,
                    'borderColor' => 'rgb(34, 197, 94)', // green
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Rejeitadas',
                    'data' => $rejected,
                    'borderColor' => 'rgb(239, 68, 68)', // red
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
