<?php

namespace App\Enums;

/**
 * Define os papéis (roles) disponíveis para os usuários do sistema.
 * 
 * ADMIN: Acesso total.
 * PROMOTER: Cadastro de convidados dentro de limites.
 * VALIDATOR: Realização de check-ins nos eventos.
 */
enum UserRole: string implements \Filament\Support\Contracts\HasLabel, \Filament\Support\Contracts\HasColor, \Filament\Support\Contracts\HasIcon
{
    case ADMIN = 'admin';
    case PROMOTER = 'promoter';
    case VALIDATOR = 'validator';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::PROMOTER => 'Promoter',
            self::VALIDATOR => 'Validador',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ADMIN => 'danger',
            self::PROMOTER => 'warning',
            self::VALIDATOR => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ADMIN => 'heroicon-m-shield-check',
            self::PROMOTER => 'heroicon-m-user-group',
            self::VALIDATOR => 'heroicon-m-check-badge',
        };
    }
}
