<?php

namespace App\Filament\Excursionista\Resources\ExcursaoResource\Pages;

use App\Filament\Excursionista\Resources\ExcursaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExcursao extends CreateRecord
{
    protected static string $resource = ExcursaoResource::class;

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
