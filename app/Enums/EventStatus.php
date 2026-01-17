<?php

namespace App\Enums;

/**
 * Define os possíveis estados de um evento.
 * 
 * DRAFT: Evento em criação, não visível.
 * ACTIVE: Evento ativo e recebendo cadastros/check-ins.
 * FINISHED: Evento encerrado.
 * CANCELLED: Evento cancelado.
 */
enum EventStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';

    /**
     * Retorna o rótulo amigável para exibição na interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::ACTIVE => 'Ativo',
            self::FINISHED => 'Finalizado',
            self::CANCELLED => 'Cancelado',
        };
    }
}
