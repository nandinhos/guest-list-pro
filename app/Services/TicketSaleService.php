<?php

namespace App\Services;

use App\Models\TicketType;
use App\Models\TicketTypeSector;

class TicketSaleService
{
    public static function getPriceForSector(TicketType $ticketType, int $sectorId): float
    {
        $sectorPrice = TicketTypeSector::where('ticket_type_id', $ticketType->id)
            ->where('sector_id', $sectorId)
            ->first();

        if (! $sectorPrice) {
            throw new \RuntimeException(
                "Preço não configurado para o tipo '{$ticketType->name}' no setor #{$sectorId}"
            );
        }

        return (float) $sectorPrice->price;
    }
}
