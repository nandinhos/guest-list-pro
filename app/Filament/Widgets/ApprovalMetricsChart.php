<?php

namespace App\Filament\Widgets;

use App\Enums\RequestStatus;
use App\Models\ApprovalRequest;
use Filament\Widgets\ChartWidget;

class ApprovalMetricsChart extends ChartWidget
{
    protected ?string $heading = 'Métricas de Solicitações';

    protected ?string $description = 'Distribuição por status';

    protected ?string $maxHeight = '250px';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $eventId = session('selected_event_id');

        $query = ApprovalRequest::query();

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $approved = (clone $query)->where('status', RequestStatus::APPROVED)->count();
        $rejected = (clone $query)->where('status', RequestStatus::REJECTED)->count();
        $pending = (clone $query)->where('status', RequestStatus::PENDING)->count();
        $cancelled = (clone $query)->where('status', RequestStatus::CANCELLED)->count();

        return [
            'datasets' => [
                [
                    'data' => [$approved, $rejected, $pending, $cancelled],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // success/green - aprovados
                        'rgb(239, 68, 68)',   // danger/red - rejeitados
                        'rgb(234, 179, 8)',   // warning/yellow - pendentes
                        'rgb(156, 163, 175)', // gray - cancelados
                    ],
                ],
            ],
            'labels' => ['Aprovados', 'Rejeitados', 'Pendentes', 'Cancelados'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
