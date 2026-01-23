<?php

namespace App\Filament\Bilheteria\Resources\TicketSales\Pages;

use App\Filament\Bilheteria\Resources\TicketSales\TicketSaleResource;
use App\Models\Event;
use App\Models\Guest;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class CreateTicketSale extends CreateRecord
{
    protected static string $resource = TicketSaleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check rate limit for sales
        $this->checkRateLimit();

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

    /**
     * Check if the user has exceeded the rate limit for creating sales.
     */
    protected function checkRateLimit(): void
    {
        $key = 'bilheteria-sales:'.(auth()->id() ?: request()->ip());

        if (RateLimiter::tooManyAttempts($key, 15)) {
            $seconds = RateLimiter::availableIn($key);

            Notification::make()
                ->title('Limite de vendas atingido')
                ->body("Muitas vendas em pouco tempo. Aguarde {$seconds} segundos.")
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        RateLimiter::hit($key, 60); // Decay in 60 seconds
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
