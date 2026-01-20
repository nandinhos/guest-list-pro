<?php

namespace App\Filament\Bilheteria\Resources\TicketSales\Pages;

use App\Filament\Bilheteria\Resources\TicketSales\TicketSaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketSales extends ListRecords
{
    protected static string $resource = TicketSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova Venda'),
        ];
    }
}
