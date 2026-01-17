<?php

namespace App\Filament\Promoter\Resources\Guests\Pages;

use App\Filament\Promoter\Resources\Guests\GuestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuest extends CreateRecord
{
    protected static string $resource = GuestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['promoter_id'] = auth()->id();
        $data['is_checked_in'] = false;

        $service = new \App\Services\GuestService();
        $validation = $service->canRegisterGuest(
            auth()->user(), 
            (int)$data['event_id'], 
            (int)$data['sector_id']
        );

        if (!$validation['allowed']) {
            \Filament\Notifications\Notification::make()
                ->title('Erro de Validação')
                ->body($validation['message'])
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
