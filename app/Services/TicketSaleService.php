<?php

namespace App\Services;

use App\Models\TicketType;
use App\Models\TicketTypeSector;

class TicketSaleService
{
    /**
     * Retorna o preço para um tipo de ingresso em um setor específico.
     * Se houver preço customizado na pivot, usa ele. Caso contrário, usa o price default.
     */
    public static function getPriceForSector(TicketType $ticketType, int $sectorId): float
    {
        $override = TicketTypeSector::where('ticket_type_id', $ticketType->id)
            ->where('sector_id', $sectorId)
            ->first();

        return (float) ($override?->price ?? $ticketType->price);
    }
}
