<?php

namespace App\Enums;

/**
 * Define os tipos de solicitações de aprovação.
 *
 * GUEST_INCLUSION: Promoter solicitando inclusão de convidado
 *                  (fora do prazo ou acima da cota).
 * EMERGENCY_CHECKIN: Validador solicitando check-in emergencial
 *                    de pessoa não cadastrada na lista.
 */
enum RequestType: string implements \Filament\Support\Contracts\HasColor, \Filament\Support\Contracts\HasIcon, \Filament\Support\Contracts\HasLabel
{
    case GUEST_INCLUSION = 'guest_inclusion';
    case EMERGENCY_CHECKIN = 'emergency_checkin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GUEST_INCLUSION => 'Inclusão de Convidado',
            self::EMERGENCY_CHECKIN => 'Check-in Emergencial',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::GUEST_INCLUSION => 'primary',
            self::EMERGENCY_CHECKIN => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::GUEST_INCLUSION => 'heroicon-m-user-plus',
            self::EMERGENCY_CHECKIN => 'heroicon-m-bolt',
        };
    }

    /**
     * Retorna a descrição detalhada do tipo de solicitação.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::GUEST_INCLUSION => 'Solicitação de inclusão de convidado pelo promoter (fora do prazo ou acima da cota)',
            self::EMERGENCY_CHECKIN => 'Solicitação de check-in emergencial pelo validador para pessoa não cadastrada',
        };
    }

    /**
     * Retorna o papel do usuário que pode criar este tipo de solicitação.
     */
    public function getAllowedRole(): UserRole
    {
        return match ($this) {
            self::GUEST_INCLUSION => UserRole::PROMOTER,
            self::EMERGENCY_CHECKIN => UserRole::VALIDATOR,
        };
    }
}
