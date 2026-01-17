<?php

namespace App\Filament\Validator\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ValidatorOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = \App\Models\Guest::count();
        $confirmed = \App\Models\Guest::where('is_checked_in', true)->count();
        $pending = $total - $confirmed;

        return [
            Stat::make('Total de Convidados', $total)
                ->description('Cadastrados no sistema')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Check-ins Realizados', $confirmed)
                ->description('Entradas confirmadas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Aguardando Entrada', $pending)
                ->description('Convidados pendentes')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
