<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasColor, HasIcon, HasLabel
{
    case Cash = 'cash';
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case Pix = 'pix';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Cash => 'Dinheiro',
            self::CreditCard => 'Cartão de Crédito',
            self::DebitCard => 'Cartão de Débito',
            self::Pix => 'PIX',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Cash => 'success',
            self::CreditCard => 'warning',
            self::DebitCard => 'info',
            self::Pix => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Cash => 'heroicon-m-banknotes',
            self::CreditCard => 'heroicon-m-credit-card',
            self::DebitCard => 'heroicon-m-credit-card',
            self::Pix => 'heroicon-m-qr-code',
        };
    }
}
