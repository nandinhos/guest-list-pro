<?php

namespace App\Enums;

enum TipoVeiculo: string
{
    case ONIBUS = 'onibus';
    case VAN = 'van';

    public function label(): string
    {
        return match ($this) {
            self::ONIBUS => 'Ônibus',
            self::VAN => 'Van',
        };
    }
}
