<?php

namespace App\Livewire;

use App\Services\GuestService;
use Filament\Notifications\Notification;
use Livewire\Component;

class QrScannerModal extends Component
{
    /**
     * Processa o check-in após o QR Code ser lido via JS.
     */
    public function processCheckin(string $token, GuestService $guestService): void
    {
        $result = $guestService->checkinByQrToken($token, auth()->user());

        if ($result['success']) {
            Notification::make()
                ->title($result['message'])
                ->success()
                ->send();

            // Emitir evento para atualizar tabelas se necessário
            $this->dispatch('guest-checked-in');
        } else {
            Notification::make()
                ->title($result['message'])
                ->danger()
                ->send();
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.qr-scanner-modal');
    }
}
