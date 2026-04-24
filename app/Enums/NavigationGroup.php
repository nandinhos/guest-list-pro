<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case RELATORIOS = 'Relatórios';
    case CONFIGURACOES = 'Configurações';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::RELATORIOS => 'Relatórios',
            self::CONFIGURACOES => 'Configurações',
        };
    }
}