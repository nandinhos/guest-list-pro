<?php

namespace App\Filament\Admin\Resources\Excursao\Pages;

use App\Filament\Admin\Resources\Excursao\ExcursaoResource;
use Filament\Resources\Pages\EditRecord;

class EditExcursao extends EditRecord
{
    protected static string $resource = ExcursaoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
