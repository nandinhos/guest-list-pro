<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ApprovalRequests\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use App\Models\Event;
use App\Models\Guest;
use App\Models\TicketSale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $eventId = session('selected_event_id');

        // Se tiver evento selecionado, mostrar métricas do evento
        if ($eventId) {
            return $this->getEventStats($eventId);
        }

        // Se não tiver evento, mostrar métricas gerais
        return $this->getGlobalStats();
    }

    private function getEventStats(int $eventId): array
    {
        $event = Event::find($eventId);

        $totalGuests = Guest::where('event_id', $eventId)->count();
        $presentGuests = Guest::where('event_id', $eventId)->where('is_checked_in', true)->count();
        $presenceRate = $totalGuests > 0 ? round(($presentGuests / $totalGuests) * 100, 1) : 0;

        $totalTickets = TicketSale::where('event_id', $eventId)->count();
        $ticketRevenue = TicketSale::where('event_id', $eventId)->sum('value');

        $totalEntries = $presentGuests + $totalTickets;

        // Solicitações pendentes do evento
        $pendingRequests = ApprovalRequest::where('event_id', $eventId)->pending()->count();

        return [
            Stat::make('Convidados Presentes', "{$presentGuests}/{$totalGuests}")
                ->description("Taxa de presença: {$presenceRate}%")
                ->descriptionIcon('heroicon-m-user-group')
                ->color($presenceRate >= 70 ? 'success' : ($presenceRate >= 40 ? 'warning' : 'danger'))
                ->chart($this->getCheckinTrend($eventId)),

            Stat::make('Vendas Bilheteria', $totalTickets)
                ->description('R$ '.number_format($ticketRevenue, 2, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getSalesTrend($eventId)),

            Stat::make('Total de Entradas', $totalEntries)
                ->description('Lista + Bilheteria')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            Stat::make('Solicitações Pendentes', $pendingRequests)
                ->description('Aguardando aprovação')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($pendingRequests > 0 ? 'warning' : 'success')
                ->url(ApprovalRequestResource::getUrl('index')),
        ];
    }

    private function getGlobalStats(): array
    {
        $totalEvents = Event::count();
        $totalGuests = Guest::count();
        $presentGuests = Guest::where('is_checked_in', true)->count();
        $presenceRate = $totalGuests > 0 ? round(($presentGuests / $totalGuests) * 100, 1) : 0;

        $totalTicketRevenue = TicketSale::sum('value');

        // Solicitações pendentes globais
        $pendingRequests = ApprovalRequest::pending()->count();

        return [
            Stat::make('Solicitações Pendentes', $pendingRequests)
                ->description('Aguardando sua aprovação')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($pendingRequests > 0 ? 'warning' : 'success')
                ->url(ApprovalRequestResource::getUrl('index')),

            Stat::make('Total de Eventos', $totalEvents)
                ->description('Eventos cadastrados')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Total de Convidados', $totalGuests)
                ->description("{$presentGuests} presentes ({$presenceRate}%)")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Receita Total', 'R$ '.number_format($totalTicketRevenue, 2, ',', '.'))
                ->description('Vendas de bilheteria')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }

    /**
     * @return array<int>
     */
    private function getCheckinTrend(int $eventId): array
    {
        $data = Guest::where('event_id', $eventId)
            ->where('is_checked_in', true)
            ->whereDate('checked_in_at', now()->today())
            ->selectRaw('HOUR(checked_in_at) as hour, count(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        $trend = [];
        for ($i = 0; $i < 24; $i++) {
            $trend[] = $data[$i] ?? 0;
        }

        return $trend;
    }

    /**
     * @return array<int>
     */
    private function getSalesTrend(int $eventId): array
    {
        $data = TicketSale::where('event_id', $eventId)
            ->whereDate('created_at', now()->today())
            ->selectRaw('HOUR(created_at) as hour, count(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        $trend = [];
        for ($i = 0; $i < 24; $i++) {
            $trend[] = $data[$i] ?? 0;
        }

        return $trend;
    }
}
