<?php

namespace App\Filament\Promoter\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PromoterQuotaOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $selectedEventId = session('selected_event_id');

        if (! $selectedEventId) {
            return [];
        }

        $permissions = \App\Models\PromoterPermission::with(['event', 'sector'])
            ->where('user_id', auth()->id())
            ->where('event_id', $selectedEventId)
            ->get();

        $stats = [];

        foreach ($permissions as $permission) {
            $used = \App\Models\Guest::where('promoter_id', auth()->id())
                ->where('event_id', $permission->event_id)
                ->where('sector_id', $permission->sector_id)
                ->count();

            $remaining = $permission->guest_limit - $used;

            $stats[] = Stat::make(
                "{$permission->event->name} - {$permission->sector->name}",
                "{$remaining} restantes"
            )
                ->description("Total: {$permission->guest_limit} | Usados: {$used}")
                ->descriptionIcon($remaining > 0 ? 'heroicon-m-ticket' : 'heroicon-m-no-symbol')
                ->color($remaining > 10 ? 'success' : ($remaining > 0 ? 'warning' : 'danger'));
        }

        if (empty($stats)) {
            $stats[] = Stat::make('Aviso', 'Nenhuma permissão vinculada')
                ->description('Contate o administrador para receber suas cotas.')
                ->color('gray');
        }

        return $stats;
    }
}
