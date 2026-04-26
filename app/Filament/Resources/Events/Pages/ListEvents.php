<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected string $view = 'filament.resources.events.pages.list-events';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importExcursoes')
                ->label('Importar Excursões')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->url(fn () => '/admin/import-excursoes'),
            CreateAction::make(),
        ];
    }
}
