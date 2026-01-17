<?php

namespace App\Filament\Promoter\Resources\Guests\Pages;

use App\Filament\Promoter\Resources\Guests\GuestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGuest extends EditRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
