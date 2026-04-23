<?php

namespace App\Filament\Excursionista\Widgets;

use App\Models\Excursao;
use App\Models\Monitor;
use App\Models\Veiculo;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ExcursionistaStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    private const CACHE_TTL = 15;

    protected function getStats(): array
    {
        $eventId = session('selected_event_id');
        $userId = auth()->id();

        $data = Cache::remember(
            "excursionista_stats_{$eventId}_{$userId}",
            self::CACHE_TTL,
            fn () => [
                'excursoes' => Excursao::where('event_id', $eventId)
                    ->where('criado_por', $userId)->count(),
                'veiculos' => Veiculo::whereHas('excursao', fn ($q) => $q
                    ->where('event_id', $eventId)
                    ->where('criado_por', $userId))->count(),
                'monitores' => Monitor::where('event_id', $eventId)
                    ->where('criado_por', $userId)->count(),
            ]
        );

        return [
            Stat::make('Excursões', $data['excursoes'])
                ->description('Cadastradas neste evento')
                ->descriptionIcon('heroicon-m-map')
                ->color('teal'),

            Stat::make('Veículos', $data['veiculos'])
                ->description('Ônibus e Vans')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Monitores', $data['monitores'])
                ->description('Cadastrados')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
