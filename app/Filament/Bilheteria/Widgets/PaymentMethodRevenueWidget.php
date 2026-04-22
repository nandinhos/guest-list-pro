<?php

namespace App\Filament\Bilheteria\Widgets;

use App\Enums\PaymentMethod;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PaymentMethodRevenueWidget extends \Filament\Widgets\StatsOverviewWidget
{
    protected function getStats(): array
    {
        $eventId = session('selected_event_id');

        if (! $eventId) {
            return [
                Stat::make('Aviso', 'Selecione um evento')
                    ->description('para visualizar receitas'),
            ];
        }

        $stats = DB::table('ticket_sales')
            ->where('event_id', $eventId)
            ->selectRaw('payment_method, COUNT(*) as total_sales, SUM(value) as total_value')
            ->groupBy('payment_method')
            ->get();

        if ($stats->isEmpty()) {
            return [
                Stat::make('Nenhuma venda', '0')
                    ->description('Sem registros ainda'),
            ];
        }

        $totalRevenue = $stats->sum('total_value');
        $statsArray = [];

        foreach ($stats as $stat) {
            $method = PaymentMethod::from($stat->payment_method);
            $percentage = $totalRevenue > 0 ? round(($stat->total_value / $totalRevenue) * 100) : 0;

            $statsArray[] = Stat::make($method->getLabel(), $stat->total_sales.' vendas')
                ->description('R$ '.number_format($stat->total_value, 2, ',', '.').' ('.$percentage.'%)')
                ->descriptionIcon($method->getIcon())
                ->color(match ($method) {
                    PaymentMethod::Cash => 'success',
                    PaymentMethod::Pix => 'info',
                    PaymentMethod::CreditCard => 'warning',
                    PaymentMethod::DebitCard => 'danger',
                });
        }

        return $statsArray;
    }
}
