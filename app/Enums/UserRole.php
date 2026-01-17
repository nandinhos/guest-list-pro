<?php

namespace App\Enums;

/**
 * Define os papéis (roles) disponíveis para os usuários do sistema.
 * 
 * ADMIN: Acesso total.
 * PROMOTER: Cadastro de convidados dentro de limites.
 * VALIDATOR: Realização de check-ins nos eventos.
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case PROMOTER = 'promoter';
    case VALIDATOR = 'validator';

    /**
     * Retorna o rótulo amigável para exibição na interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::PROMOTER => 'Promoter',
            self::VALIDATOR => 'Validador',
        };
    }
}
