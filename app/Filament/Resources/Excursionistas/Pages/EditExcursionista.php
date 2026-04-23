<?php

namespace App\Filament\Resources\Excursionistas\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Excursionistas\ExcursionistaResource;
use App\Models\EventAssignment;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditExcursionista extends EditRecord
{
    protected static string $resource = ExcursionistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['role'] = UserRole::EXCURSIONISTA;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        if (! empty($data['password'])) {
            $record->password = $data['password'];
        }

        $record->save();

        if (isset($data['eventAssignments'])) {
            $record->eventAssignments()->delete();

            foreach ($data['eventAssignments'] as $eventId) {
                EventAssignment::create([
                    'user_id' => $record->id,
                    'event_id' => $eventId,
                    'role' => UserRole::EXCURSIONISTA->value,
                ]);
            }
        }

        return $record;
    }
}
