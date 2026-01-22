<?php

namespace App\Filament\Widgets;

use App\Enums\RequestType;
use App\Filament\Resources\ApprovalRequests\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingApprovalsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCount = ApprovalRequest::pending()->count();
        $inclusionCount = ApprovalRequest::pending()->byType(RequestType::GUEST_INCLUSION)->count();
        $emergencyCount = ApprovalRequest::pending()->byType(RequestType::EMERGENCY_CHECKIN)->count();

        return [
            Stat::make('Total Pendentes', $totalCount)
                ->description('Solicitações aguardando revisão')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($totalCount > 0 ? 'warning' : 'success')
                ->url(ApprovalRequestResource::getUrl('index')),

            Stat::make('Inclusões (Promoters)', $inclusionCount)
                ->color('primary'),

            Stat::make('Check-ins (Validadores)', $emergencyCount)
                ->color('warning'),
        ];
    }
}
