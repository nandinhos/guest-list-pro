<?php

namespace App\Filament\Resources\Excursionistas\Pages;

use App\Filament\Resources\Excursionistas\ExcursionistaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExcursionista extends CreateRecord
{
    protected static string $resource = ExcursionistaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = \App\Enums\UserRole::EXCURSIONISTA;
        $data['is_active'] = $data['is_active'] ?? true;

        return $data;
    }
}
