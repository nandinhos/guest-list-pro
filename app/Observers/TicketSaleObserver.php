<?php

namespace App\Observers;

use App\Models\TicketSale;
use App\Services\GuestService;
use Filament\Notifications\Notification;

class TicketSaleObserver
{
    public function __construct(
        private GuestService $guestService
    ) {}

    public function created(TicketSale $ticketSale): void
    {
        if (! $ticketSale->guest) {
            return;
        }

        $guest = $ticketSale->guest;

        if ($guest->is_checked_in) {
            return;
        }

        $guest->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
            'checked_in_by' => $ticketSale->sold_by,
        ]);

        if ($ticketSale->seller) {
            Notification::make()
                ->title('Check-in automático')
                ->body("Venda #{$ticketSale->id} gerada - {$guest->name} já está com entrada confirmada")
                ->success()
                ->sendToDatabase($ticketSale->seller);
        }
    }
}
