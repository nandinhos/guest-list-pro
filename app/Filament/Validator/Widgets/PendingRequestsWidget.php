<?php

namespace App\Filament\Validator\Widgets;

use App\Filament\Validator\Pages\MyRequests;
use App\Models\ApprovalRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingRequestsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $count = ApprovalRequest::pending()
            ->byRequester(auth()->id())
            ->forEvent(session('selected_event_id'))
            ->count();

        return [
            Stat::make('Minhas Solicitações Pendentes', $count)
                ->description('Aguardando aprovação do administrador')
                ->descriptionIcon('heroicon-m-clock')
                ->color($count > 0 ? 'warning' : 'success')
                ->url(MyRequests::getUrl()),
        ];
    }
}
