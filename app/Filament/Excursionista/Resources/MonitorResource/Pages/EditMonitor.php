<?php

namespace App\Filament\Excursionista\Resources\MonitorResource\Pages;

use App\Filament\Excursionista\Resources\MonitorResource;
use Filament\Resources\Pages\EditRecord;

class EditMonitor extends EditRecord
{
    protected static string $resource = MonitorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
