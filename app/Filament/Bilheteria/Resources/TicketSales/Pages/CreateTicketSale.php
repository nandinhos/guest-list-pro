<?php

namespace App\Filament\Bilheteria\Resources\TicketSales\Pages;

use App\Filament\Bilheteria\Resources\TicketSales\TicketSaleResource;
use App\Models\Event;
use App\Models\Guest;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateTicketSale extends CreateRecord
{
    protected static string $resource = TicketSaleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $eventId = session('selected_event_id');
        $event = Event::find($eventId);

        if (! $event || ! $event->bilheteria_enabled) {
            Notification::make()
                ->title('Erro')
                ->body('A bilheteria não está habilitada para este evento.')
                ->danger()
                ->send();

            $this->halt();
        }

        $data['event_id'] = $eventId;
        $data['sold_by'] = auth()->id();

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $guest = Guest::create([
                'event_id' => $data['event_id'],
                'sector_id' => $data['sector_id'],
                'promoter_id' => auth()->id(),
                'name' => $data['buyer_name'],
                'document' => $data['buyer_document'] ?? null,
                'is_checked_in' => false,
            ]);

            unset($data['sector_id']);

            $ticketSale = static::getModel()::create([
                ...$data,
                'guest_id' => $guest->id,
            ]);

            return $ticketSale;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Venda registrada com sucesso!';
    }
}
