<?php

namespace App\Filament\Validator\Widgets;

use App\Models\ApprovalRequest;
use App\Models\Guest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ValidatorOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $selectedEventId = session('selected_event_id');
        $userId = auth()->id();

        if (! $selectedEventId) {
            return [
                Stat::make('Aviso', 'Selecione um evento')
                    ->description('Selecione um evento para visualizar as estatísticas')
                    ->color('gray'),
            ];
        }

        $total = Guest::where('event_id', $selectedEventId)->count();
        $confirmed = Guest::where('event_id', $selectedEventId)
            ->where('is_checked_in', true)
            ->count();
        $pending = $total - $confirmed;

        // Solicitações do validador
        $myPendingRequests = ApprovalRequest::where('requester_id', $userId)
            ->where('event_id', $selectedEventId)
            ->pending()
            ->count();

        $myApprovedToday = ApprovalRequest::where('requester_id', $userId)
            ->where('event_id', $selectedEventId)
            ->where('status', 'approved')
            ->whereDate('reviewed_at', today())
            ->count();

        return [
            Stat::make('Check-ins Realizados', $confirmed)
                ->description("{$pending} aguardando entrada")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total na Lista', $total)
                ->description('Convidados cadastrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Minhas Solicitações', $myPendingRequests)
                ->description($myApprovedToday > 0 ? "{$myApprovedToday} aprovadas hoje" : 'Pendentes de aprovação')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($myPendingRequests > 0 ? 'warning' : 'gray')
                ->url(route('filament.validator.pages.my-requests')),
        ];
    }
}
