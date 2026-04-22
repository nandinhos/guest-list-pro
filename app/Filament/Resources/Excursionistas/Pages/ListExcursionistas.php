<?php

namespace App\Filament\Resources\Excursionistas\Pages;

use App\Filament\Resources\Excursionistas\ExcursionistaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExcursionistas extends ListRecords
{
    protected static string $resource = ExcursionistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
