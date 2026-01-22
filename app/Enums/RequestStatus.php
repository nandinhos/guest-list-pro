<?php

namespace App\Enums;

/**
 * Define os status possíveis para solicitações de aprovação.
 *
 * PENDING: Aguardando análise do administrador.
 * APPROVED: Aprovado pelo administrador.
 * REJECTED: Rejeitado pelo administrador.
 * CANCELLED: Cancelado pelo solicitante.
 * EXPIRED: Expirado automaticamente (evento finalizado).
 */
enum RequestStatus: string implements \Filament\Support\Contracts\HasColor, \Filament\Support\Contracts\HasIcon, \Filament\Support\Contracts\HasLabel
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::APPROVED => 'Aprovado',
            self::REJECTED => 'Rejeitado',
            self::CANCELLED => 'Cancelado',
            self::EXPIRED => 'Expirado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'gray',
            self::EXPIRED => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-m-clock',
            self::APPROVED => 'heroicon-m-check-circle',
            self::REJECTED => 'heroicon-m-x-circle',
            self::CANCELLED => 'heroicon-m-minus-circle',
            self::EXPIRED => 'heroicon-m-exclamation-circle',
        };
    }

    /**
     * Verifica se o status permite revisão (aprovação/rejeição).
     */
    public function canBeReviewed(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Verifica se o status permite cancelamento pelo solicitante.
     */
    public function canBeCancelled(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Verifica se o status permite reconsideração.
     * Apenas REJECTED e CANCELLED podem ser reconsiderados.
     */
    public function canBeReconsidered(): bool
    {
        return in_array($this, [self::REJECTED, self::CANCELLED]);
    }

    /**
     * Verifica se o status permite reversão (desfazer aprovação).
     * Apenas APPROVED pode ser revertido.
     */
    public function canBeReverted(): bool
    {
        return $this === self::APPROVED;
    }
}
