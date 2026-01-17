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
enum EventStatus: string implements \Filament\Support\Contracts\HasLabel, \Filament\Support\Contracts\HasColor
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::ACTIVE => 'Ativo',
            self::FINISHED => 'Finalizado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'success',
            self::FINISHED => 'info',
            self::CANCELLED => 'danger',
        };
    }
}
