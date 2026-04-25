<?php

namespace App\Filament\Admin\Resources\Excursao\Pages;

use App\Filament\Admin\Resources\Excursao\ExcursaoResource;
use Filament\Actions\CreateAction;
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
