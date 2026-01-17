<?php

namespace App\Filament\Validator\Resources\Guests\Pages;

use App\Filament\Validator\Resources\Guests\GuestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuest extends CreateRecord
{
    protected static string $resource = GuestResource::class;
}
