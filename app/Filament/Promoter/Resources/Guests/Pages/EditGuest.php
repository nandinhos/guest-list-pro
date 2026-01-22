<?php

namespace App\Filament\Promoter\Resources\Guests\Pages;

use App\Filament\Promoter\Resources\Guests\GuestResource;
use App\Services\ApprovalRequestService;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGuest extends EditRecord
{
    protected static string $resource = GuestResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(ApprovalRequestService::class);

        // Só validar duplicidade se houver alteração no nome ou documento
        $nameChanged = $data['name'] !== $this->record->name;
        $documentChanged = ($data['document'] ?? null) !== $this->record->document;

        if ($nameChanged || $documentChanged) {
            $duplicate = $service->checkForDuplicates(
                (int) $data['event_id'],
                $data['name'],
                $data['document'] ?? null,
                $this->record->id
            );

            if ($duplicate) {
                if ($duplicate['level'] === 'error') {
                    Notification::make()
                        ->title('Edição Bloqueada')
                        ->body($duplicate['message'])
                        ->danger()
                        ->persistent()
                        ->send();

                    $this->halt();
                }

                // Aviso de homônimo
                Notification::make()
                    ->title('Atenção: Possível Duplicidade')
                    ->body($duplicate['message'])
                    ->warning()
                    ->send();
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
