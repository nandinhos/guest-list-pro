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
    case CNH = 'cnh';
    case PASSPORT = 'passport';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CPF => 'CPF',
            self::RG => 'RG',
            self::CNH => 'CNH',
            self::PASSPORT => 'Passaporte',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CPF => 'success',
            self::RG => 'info',
            self::CNH => 'warning',
            self::PASSPORT => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CPF => 'heroicon-m-identification',
            self::RG => 'heroicon-m-credit-card',
            self::CNH => 'heroicon-m-truck',
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
            self::RG => '##.###.###-#',
            self::CNH => '###########',
            self::PASSPORT => null,
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
            self::CNH => '00000000000',
            self::PASSPORT => 'Ex: AB123456',
        };
    }

    /**
     * Tenta detectar o tipo de documento pelo valor.
     */
    public static function detectFromValue(string $value): ?self
    {
        $clean = trim($value);

        if (preg_match('/^Passaporte\s+/i', $clean)) {
            return self::PASSPORT;
        }

        if (preg_match('/^RG\s*:?\s*/i', $clean)) {
            return self::RG;
        }

        // Letras que não são X (dígito verificador do RG)
        if (preg_match('/[a-wyzA-WYZ]/', $clean)) {
            return self::PASSPORT;
        }

        $digits = preg_replace('/\D/', '', $clean);

        if (strlen($digits) === 11) {
            return self::CPF;
        }

        if (strlen($digits) >= 1) {
            return self::RG;
        }

        return null;
    }

    /**
     * Normaliza o valor do documento de acordo com o tipo detectado.
     */
    public static function normalizeValue(string $value, self $type): string
    {
        $clean = trim($value);

        return match ($type) {
            self::PASSPORT => strtoupper(preg_replace(
                '/[^A-Z0-9]/i', '',
                preg_replace('/^Passaporte\s+/i', '', $clean)
            )),
            self::RG => strtoupper(preg_replace(
                '/[^0-9X]/i', '',
                preg_replace('/^RG\s*:?\s*/i', '', $clean)
            )),
            self::CPF, self::CNH => preg_replace('/\D/', '', $clean),
        };
    }
}
