<?php

namespace App\Filament\Promoter\Widgets;

use App\Models\Guest;
use App\Models\Sector;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PromoterQuotaOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '10s';

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
            if (is_null($permission->sector_id)) {
                $sectors = Sector::where('event_id', $selectedEventId)->get();

                foreach ($sectors as $sector) {
                    $used = Guest::where('promoter_id', auth()->id())
                        ->where('event_id', $selectedEventId)
                        ->where('sector_id', $sector->id)
                        ->count();

                    $totalLimit = $this->getSectorLimit($sector->id, $selectedEventId);
                    $remaining = $totalLimit - $used;

                    $stats[] = Stat::make(
                        $sector->name,
                        "{$remaining} restantes"
                    )
                        ->description("Total: {$totalLimit} | Usados: {$used}")
                        ->descriptionIcon($remaining > 0 ? 'heroicon-m-ticket' : 'heroicon-m-no-symbol')
                        ->color($remaining > 10 ? 'success' : ($remaining > 0 ? 'warning' : 'danger'));
                }
            } else {
                $used = Guest::where('promoter_id', auth()->id())
                    ->where('event_id', $permission->event_id)
                    ->where('sector_id', $permission->sector_id)
                    ->count();

                $remaining = $permission->guest_limit - $used;

                $sectorName = $permission->sector?->name ?? 'Setor';

                $stats[] = Stat::make(
                    $sectorName,
                    "{$remaining} restantes"
                )
                    ->description("Total: {$permission->guest_limit} | Usados: {$used}")
                    ->descriptionIcon($remaining > 0 ? 'heroicon-m-ticket' : 'heroicon-m-no-symbol')
                    ->color($remaining > 10 ? 'success' : ($remaining > 0 ? 'warning' : 'danger'));
            }
        }

        if (empty($stats)) {
            $stats[] = Stat::make('Aviso', 'Nenhuma permissão vinculada')
                ->description('Contate o administrador para receber suas cotas.')
                ->color('gray');
        }

        return $stats;
    }

    private function getSectorLimit(int $sectorId, int $eventId): int
    {
        $permission = \App\Models\PromoterPermission::where('user_id', auth()->id())
            ->where('event_id', $eventId)
            ->where('sector_id', $sectorId)
            ->first();

        if ($permission) {
            return $permission->guest_limit;
        }

        $globalPermission = \App\Models\PromoterPermission::where('user_id', auth()->id())
            ->where('event_id', $eventId)
            ->whereNull('sector_id')
            ->first();

        return $globalPermission?->guest_limit ?? 0;
    }
}
