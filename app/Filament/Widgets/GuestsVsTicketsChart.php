<?php

namespace App\Filament\Widgets;

use App\Models\Guest;
use App\Models\TicketSale;
use Filament\Widgets\ChartWidget;

class GuestsVsTicketsChart extends ChartWidget
{
    protected ?string $heading = 'Convidados vs Bilheteria';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $eventId = session('selected_event_id');

        // Convidados da lista (cadastrados por promoters)
        $guestsQuery = Guest::query();
        if ($eventId) {
            $guestsQuery->where('event_id', $eventId);
        }
        $totalGuests = $guestsQuery->count();
        $presentGuests = (clone $guestsQuery)->where('is_checked_in', true)->count();

        // Vendas da bilheteria
        $ticketsQuery = TicketSale::query();
        if ($eventId) {
            $ticketsQuery->where('event_id', $eventId);
        }
        $totalTickets = $ticketsQuery->count();

        return [
            'datasets' => [
                [
                    'label' => 'Quantidade',
                    'data' => [$totalGuests, $totalTickets],
                    'backgroundColor' => [
                        '#6366F1', // Indigo para convidados
                        '#10B981', // Verde para bilheteria
                    ],
                    'borderColor' => [
                        '#4F46E5',
                        '#059669',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Lista de Convidados', 'Bilheteria'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getDescription(): ?string
    {
        $eventId = session('selected_event_id');

        $guestsQuery = Guest::query();
        $ticketsQuery = TicketSale::query();

        if ($eventId) {
            $guestsQuery->where('event_id', $eventId);
            $ticketsQuery->where('event_id', $eventId);
        }

        $totalGuests = $guestsQuery->count();
        $totalTickets = $ticketsQuery->count();
        $total = $totalGuests + $totalTickets;

        if ($total === 0) {
            return 'Nenhum registro';
        }

        $guestsPercent = round(($totalGuests / $total) * 100, 1);
        $ticketsPercent = round(($totalTickets / $total) * 100, 1);

        return "Lista: {$guestsPercent}% | Bilheteria: {$ticketsPercent}%";
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
