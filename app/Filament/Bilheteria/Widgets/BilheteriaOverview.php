<?php

namespace App\Filament\Bilheteria\Widgets;

use App\Models\Event;
use App\Models\TicketSale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BilheteriaOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $selectedEventId = session('selected_event_id');

        if (! $selectedEventId) {
            return [
                Stat::make('Aviso', 'Selecione um evento')
                    ->description('Selecione um evento para visualizar as estatísticas')
                    ->color('gray'),
            ];
        }

        $event = Event::find($selectedEventId);
        $sales = TicketSale::where('event_id', $selectedEventId);

        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('value');
        $todaySales = $sales->clone()->whereDate('created_at', today())->count();
        $todayRevenue = $sales->clone()->whereDate('created_at', today())->sum('value');

        return [
            Stat::make('Total de Vendas', $totalSales)
                ->description('Ingressos vendidos')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Receita Total', 'R$ '.number_format($totalRevenue, 2, ',', '.'))
                ->description('Valor arrecadado')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Vendas Hoje', $todaySales)
                ->description('R$ '.number_format($todayRevenue, 2, ',', '.'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Preço do Ingresso', $event?->ticket_price
                ? 'R$ '.number_format($event->ticket_price, 2, ',', '.')
                : 'Não definido')
                ->description($event?->bilheteria_enabled ? 'Bilheteria ativa' : 'Bilheteria inativa')
                ->descriptionIcon($event?->bilheteria_enabled ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($event?->bilheteria_enabled ? 'success' : 'danger'),
        ];
    }
}
