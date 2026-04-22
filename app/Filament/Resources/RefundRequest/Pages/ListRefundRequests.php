<?php

namespace App\Filament\Resources\RefundRequest\Pages;

use App\Filament\Resources\RefundRequest\RefundRequestResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListRefundRequests extends ListRecords
{
    protected static string $resource = RefundRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Atualizar')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->resetTable();
                    \Filament\Notifications\Notification::make()
                        ->title('Dados atualizados')
                        ->success()
                        ->send();
                }),
        ];
    }
}