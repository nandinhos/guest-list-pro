<?php

namespace App\Filament\Admin\Resources\Monitor\Pages;

use App\Filament\Admin\Resources\Monitor\MonitorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitores extends ListRecords
{
    protected static string $resource = MonitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
