<?php

namespace App\Enums;

/**
 * Define os tipos de documentos aceitos para identificação de convidados.
 *
 * CPF: Cadastro de Pessoa Física (Brasil) - 11 dígitos
 * RG: Registro Geral (Brasil) - formato varia por estado
 * PASSPORT: Passaporte (Internacional) - alfanumérico
 */
enum DocumentType: string implements \Filament\Support\Contracts\HasColor, \Filament\Support\Contracts\HasIcon, \Filament\Support\Contracts\HasLabel
{
    case CPF = 'cpf';
    case RG = 'rg';
    case PASSPORT = 'passport';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CPF => 'CPF',
            self::RG => 'RG',
            self::PASSPORT => 'Passaporte',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CPF => 'success',
            self::RG => 'info',
            self::PASSPORT => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CPF => 'heroicon-m-identification',
            self::RG => 'heroicon-m-credit-card',
            self::PASSPORT => 'heroicon-m-globe-alt',
        };
    }

    /**
     * Retorna a máscara de exibição para o tipo de documento.
     */
    public function getMask(): ?string
    {
        return match ($this) {
            self::CPF => '###.###.###-##',
            self::RG => null, // Formato varia por estado
            self::PASSPORT => null, // Formato alfanumérico livre
        };
    }

    /**
     * Retorna o placeholder para input do documento.
     */
    public function getPlaceholder(): string
    {
        return match ($this) {
            self::CPF => '000.000.000-00',
            self::RG => 'Ex: 12.345.678-9',
            self::PASSPORT => 'Ex: AB123456',
        };
    }

    /**
     * Tenta detectar o tipo de documento pelo valor.
     */
    public static function detectFromValue(string $value): ?self
    {
        $normalized = preg_replace('/\D/', '', $value);

        // CPF tem exatamente 11 dígitos numéricos
        if (strlen($normalized) === 11) {
            return self::CPF;
        }

        // RG geralmente tem entre 7 e 9 dígitos
        if (strlen($normalized) >= 7 && strlen($normalized) <= 9) {
            return self::RG;
        }

        // Se contém letras, provavelmente é passaporte
        if (preg_match('/[a-zA-Z]/', $value)) {
            return self::PASSPORT;
        }

        return null;
    }
}
