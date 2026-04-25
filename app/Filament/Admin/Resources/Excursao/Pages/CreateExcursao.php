<?php

namespace App\Filament\Admin\Resources\Excursao\Pages;

use App\Filament\Admin\Resources\Excursao\ExcursaoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExcursao extends CreateRecord
{
    protected static string $resource = ExcursaoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['criado_por'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
