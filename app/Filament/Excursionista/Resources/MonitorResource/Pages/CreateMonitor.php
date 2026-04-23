<?php

namespace App\Filament\Excursionista\Resources\MonitorResource\Pages;

use App\Filament\Excursionista\Resources\MonitorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMonitor extends CreateRecord
{
    protected static string $resource = MonitorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['criado_por'] = auth()->id();

        if ($selectedEventId = session('selected_event_id')) {
            $data['event_id'] = $selectedEventId;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
