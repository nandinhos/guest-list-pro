<?php

namespace App\Filament\Widgets;

use App\Models\Sector;
use Filament\Widgets\ChartWidget;

class SectorOccupancyChart extends ChartWidget
{
    protected ?string $heading = 'Ocupação por Setor';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $eventId = session('selected_event_id');

        $query = Sector::query()
            ->withCount([
                'guests as total_guests',
                'guests as present_count' => fn ($q) => $q->where('is_checked_in', true),
            ]);

        if ($eventId) {
            $query->where('event_id', $eventId);
        } else {
            $query->with('event');
        }

        $sectors = $query->get();

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($sectors as $sector) {
            $occupancy = $sector->capacity > 0
                ? round(($sector->present_count / $sector->capacity) * 100, 1)
                : 0;

            $labels[] = $eventId
                ? $sector->name
                : "{$sector->event?->name} - {$sector->name}";

            $values[] = $occupancy;

            // Cor baseada na ocupação: verde < 70%, amarelo 70-90%, vermelho > 90%
            $colors[] = $this->getColorForOccupancy($occupancy);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ocupação %',
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getDescription(): ?string
    {
        $eventId = session('selected_event_id');

        $query = Sector::query()
            ->withCount([
                'guests as present_count' => fn ($q) => $q->where('is_checked_in', true),
            ]);

        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $sectors = $query->get();

        $totalCapacity = $sectors->sum('capacity');
        $totalPresent = $sectors->sum('present_count');

        if ($totalCapacity === 0) {
            return 'Nenhum setor configurado';
        }

        $overallOccupancy = round(($totalPresent / $totalCapacity) * 100, 1);

        return "Ocupação geral: {$overallOccupancy}% ({$totalPresent}/{$totalCapacity})";
    }

    private function getColorForOccupancy(float $occupancy): string
    {
        if ($occupancy >= 90) {
            return '#EF4444'; // Vermelho
        }

        if ($occupancy >= 70) {
            return '#F59E0B'; // Amarelo
        }

        return '#10B981'; // Verde
    }
}
