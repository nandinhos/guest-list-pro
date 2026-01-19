<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected string $view = 'filament.resources.events.pages.list-events';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
