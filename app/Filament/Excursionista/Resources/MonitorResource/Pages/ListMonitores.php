<?php

namespace App\Filament\Excursionista\Resources\MonitorResource\Pages;

use App\Filament\Excursionista\Resources\MonitorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitores extends ListRecords
{
    protected static string $resource = MonitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
