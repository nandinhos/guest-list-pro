<?php

namespace App\Filament\Validator\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ValidatorOverview extends StatsOverviewWidget
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

        $total = \App\Models\Guest::where('event_id', $selectedEventId)->count();
        $confirmed = \App\Models\Guest::where('event_id', $selectedEventId)
            ->where('is_checked_in', true)
            ->count();
        $pending = $total - $confirmed;

        return [
            Stat::make('Total de Convidados', $total)
                ->description('Cadastrados neste evento')
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
