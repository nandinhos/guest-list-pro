<?php

namespace App\Filament\Validator\Resources\Guests\Pages;

use App\Filament\Validator\Resources\Guests\GuestResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListGuests extends ListRecords
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scanQr')
                ->label('Ler QR Code')
                ->icon('heroicon-o-qr-code')
                ->color('success')
                ->modalHeading('Escaneie o QR Code')
                ->modalWidth('sm')
                ->modalSubmitAction(false) // Remove o botão padrão 'Salvar'
                ->modalCancelAction(false) // Remove o botão padrão 'Cancelar'
                ->modalContent(fn (): View => view('livewire.qr-scanner-modal-wrapper')),
        ];
    }
}
