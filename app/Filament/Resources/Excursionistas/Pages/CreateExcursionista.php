<?php

namespace App\Filament\Resources\Excursionistas\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Excursionistas\ExcursionistaResource;
use App\Models\EventAssignment;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateExcursionista extends CreateRecord
{
    protected static string $resource = ExcursionistaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = UserRole::EXCURSIONISTA;
        $data['is_active'] = $data['is_active'] ?? true;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $user = static::getModel()::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'] ?? bcrypt(bin2hex(random_bytes(16))),
            'role' => UserRole::EXCURSIONISTA,
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (! empty($data['eventAssignments'])) {
            foreach ($data['eventAssignments'] as $eventId) {
                EventAssignment::create([
                    'user_id' => $user->id,
                    'event_id' => $eventId,
                    'role' => UserRole::EXCURSIONISTA->value,
                ]);
            }
        }

        return $user;
    }
}
