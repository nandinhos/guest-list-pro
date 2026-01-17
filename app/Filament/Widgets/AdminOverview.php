<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalGuests = \App\Models\Guest::count();
        $presentGuests = \App\Models\Guest::where('is_checked_in', true)->count();
        $presenceRate = $totalGuests > 0 ? round(($presentGuests / $totalGuests) * 100, 1) : 0;

        return [
            Stat::make('Total de Eventos', \App\Models\Event::count())
                ->description('Eventos ativos e encerrados')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make('Total de Convidados', $totalGuests)
                ->description('Cadastrados via promoters')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Taxa de Presença', "{$presenceRate}%")
                ->description('Conversão (Presentes / Total)')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('warning'),
        ];
    }
}
