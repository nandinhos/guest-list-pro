<?php

namespace App\Enums;

enum TipoVeiculo: string
{
    case ONIBUS = 'onibus';
    case MICROONIBUS = 'microonibus';
    case VAN = 'van';

    public function label(): string
    {
        return match ($this) {
            self::ONIBUS => 'Ônibus',
            self::MICROONIBUS => 'Microônibus',
            self::VAN => 'Van',
        };
    }
}
