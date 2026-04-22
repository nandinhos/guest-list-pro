<?php

namespace App\Filament\Excursionista\Resources\ExcursaoResource\Pages;

use App\Filament\Excursionista\Resources\ExcursaoResource;
use Filament\Resources\Pages\EditRecord;

class EditExcursao extends EditRecord
{
    protected static string $resource = ExcursaoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
