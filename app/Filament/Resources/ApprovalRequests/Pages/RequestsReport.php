<?php

namespace App\Filament\Resources\ApprovalRequests\Pages;

use App\Filament\Resources\ApprovalRequests\ApprovalRequestResource;
use Filament\Resources\Pages\Page;

class RequestsReport extends Page
{
    protected static string $resource = ApprovalRequestResource::class;

    protected string $view = 'filament.resources.approval-requests.pages.requests-report';

    protected static ?string $title = 'Relatório Analítico';

    protected static ?string $slug = 'report';

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return true;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ApprovalMetricsChart::class,
        ];
    }
}
