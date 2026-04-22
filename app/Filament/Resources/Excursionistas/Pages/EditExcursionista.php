<?php

namespace App\Filament\Resources\Excursionistas\Pages;

use App\Filament\Resources\Excursionistas\ExcursionistaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExcursionista extends EditRecord
{
    protected static string $resource = ExcursionistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
