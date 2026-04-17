<?php

namespace App\Filament\Bilheteria\Widgets;

use App\Enums\PaymentMethod;
use App\Models\Event;
use App\Models\TicketSale;
use App\Models\TicketType;
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
        $salesWithTicketType = TicketSale::where('event_id', $selectedEventId)->whereNotNull('ticket_type_id');

        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('value');
        $todaySales = TicketSale::where('event_id', $selectedEventId)->whereDate('created_at', today())->count();
        $todayRevenue = TicketSale::where('event_id', $selectedEventId)->whereDate('created_at', today())->sum('value');
        $avgTicketValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        $ticketTypeCount = TicketType::where('event_id', $selectedEventId)->where('is_active', true)->count();

        $moneySales = TicketSale::where('event_id', $selectedEventId)
            ->where('payment_method', PaymentMethod::Cash->value)->count();
        $pixSales = TicketSale::where('event_id', $selectedEventId)
            ->where('payment_method', PaymentMethod::Pix->value)->count();
        $creditSales = TicketSale::where('event_id', $selectedEventId)
            ->where('payment_method', PaymentMethod::CreditCard->value)->count();
        $debitSales = TicketSale::where('event_id', $selectedEventId)
            ->where('payment_method', PaymentMethod::DebitCard->value)->count();

        return [
            Stat::make('Total de Vendas', $totalSales)
                ->description("Hoje: {$todaySales} | Receita: ".format_money($totalRevenue))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Receita Total', format_money($totalRevenue))
                ->description('Média: '.format_money($avgTicketValue))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Vendas Hoje', $todaySales)
                ->description(format_money($todayRevenue))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Tipos Ativos', $ticketTypeCount)
                ->description("Din: {$moneySales} | Pix: {$pixSales} | Cré: {$creditSales} | Déb: {$debitSales}")
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),
        ];
    }
}
