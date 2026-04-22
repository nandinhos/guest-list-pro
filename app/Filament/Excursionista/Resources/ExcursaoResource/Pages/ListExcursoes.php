<?php

namespace App\Filament\Excursionista\Resources\ExcursaoResource\Pages;

use App\Filament\Excursionista\Resources\ExcursaoResource;
use Filament\Resources\Pages\ListRecords;

class ListExcursoes extends ListRecords
{
    protected static string $resource = ExcursaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
