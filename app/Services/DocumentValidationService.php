<?php

namespace App\Services;

use App\Enums\DocumentType;

/**
 * Serviço de validação de documentos (CPF, RG, Passaporte).
 *
 * Implementa validação de dígitos verificadores para CPF
 * e validações básicas de formato para RG e Passaporte.
 */
class DocumentValidationService
{
    /**
     * Valida um documento baseado no tipo informado ou detectado automaticamente.
     *
     * @return array{valid: bool, type: ?DocumentType, message: ?string, formatted: ?string}
     */
    public function validate(string $value, ?DocumentType $type = null): array
    {
        $value = trim($value);

        if (empty($value)) {
            return [
                'valid' => false,
                'type' => null,
                'message' => 'Documento não informado.',
                'formatted' => null,
            ];
        }

        // Auto-detecta o tipo se não informado
        $type = $type ?? DocumentType::detectFromValue($value);

        if (! $type) {
            return [
                'valid' => false,
                'type' => null,
                'message' => 'Não foi possível identificar o tipo de documento.',
                'formatted' => null,
            ];
        }

        return match ($type) {
            DocumentType::CPF => $this->validateCpf($value),
            DocumentType::RG => $this->validateRg($value),
            DocumentType::PASSPORT => $this->validatePassport($value),
        };
    }

    /**
     * Valida CPF com verificação de dígitos verificadores.
     *
     * @return array{valid: bool, type: DocumentType, message: ?string, formatted: ?string}
     */
    public function validateCpf(string $value): array
    {
        $cpf = preg_replace('/\D/', '', $value);

        // Deve ter 11 dígitos
        if (strlen($cpf) !== 11) {
            return [
                'valid' => false,
                'type' => DocumentType::CPF,
                'message' => 'CPF deve ter 11 dígitos.',
                'formatted' => null,
            ];
        }

        // Verifica sequências inválidas conhecidas
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return [
                'valid' => false,
                'type' => DocumentType::CPF,
                'message' => 'CPF inválido (sequência repetida).',
                'formatted' => null,
            ];
        }

        // Validação do primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cpf[9] !== $digit1) {
            return [
                'valid' => false,
                'type' => DocumentType::CPF,
                'message' => 'CPF inválido (dígito verificador incorreto).',
                'formatted' => null,
            ];
        }

        // Validação do segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cpf[10] !== $digit2) {
            return [
                'valid' => false,
                'type' => DocumentType::CPF,
                'message' => 'CPF inválido (dígito verificador incorreto).',
                'formatted' => null,
            ];
        }

        return [
            'valid' => true,
            'type' => DocumentType::CPF,
            'message' => null,
            'formatted' => $this->formatCpf($cpf),
        ];
    }

    /**
     * Valida RG (validação básica de formato).
     *
     * O RG varia por estado, então fazemos apenas validação de comprimento.
     *
     * @return array{valid: bool, type: DocumentType, message: ?string, formatted: ?string}
     */
    public function validateRg(string $value): array
    {
        $rg = preg_replace('/\D/', '', $value);

        // RG geralmente tem entre 7 e 9 dígitos
        if (strlen($rg) < 5 || strlen($rg) > 14) {
            return [
                'valid' => false,
                'type' => DocumentType::RG,
                'message' => 'RG deve ter entre 5 e 14 caracteres.',
                'formatted' => null,
            ];
        }

        // Verifica sequências inválidas conhecidas
        if (preg_match('/^(\d)\1+$/', $rg)) {
            return [
                'valid' => false,
                'type' => DocumentType::RG,
                'message' => 'RG inválido (sequência repetida).',
                'formatted' => null,
            ];
        }

        return [
            'valid' => true,
            'type' => DocumentType::RG,
            'message' => null,
            'formatted' => $this->formatRg($value),
        ];
    }

    /**
     * Valida Passaporte (validação básica de formato).
     *
     * Passaporte brasileiro: 2 letras + 6 dígitos
     * Outros países: formatos variados, geralmente alfanumérico
     *
     * @return array{valid: bool, type: DocumentType, message: ?string, formatted: ?string}
     */
    public function validatePassport(string $value): array
    {
        $passport = strtoupper(trim($value));

        // Remove espaços e caracteres especiais
        $passport = preg_replace('/[^A-Z0-9]/', '', $passport);

        // Passaporte deve ter entre 6 e 9 caracteres alfanuméricos
        if (strlen($passport) < 6 || strlen($passport) > 9) {
            return [
                'valid' => false,
                'type' => DocumentType::PASSPORT,
                'message' => 'Passaporte deve ter entre 6 e 9 caracteres.',
                'formatted' => null,
            ];
        }

        // Deve conter pelo menos uma letra
        if (! preg_match('/[A-Z]/', $passport)) {
            return [
                'valid' => false,
                'type' => DocumentType::PASSPORT,
                'message' => 'Passaporte deve conter letras.',
                'formatted' => null,
            ];
        }

        // Deve conter pelo menos um número
        if (! preg_match('/[0-9]/', $passport)) {
            return [
                'valid' => false,
                'type' => DocumentType::PASSPORT,
                'message' => 'Passaporte deve conter números.',
                'formatted' => null,
            ];
        }

        return [
            'valid' => true,
            'type' => DocumentType::PASSPORT,
            'message' => null,
            'formatted' => $passport,
        ];
    }

    /**
     * Formata CPF no padrão brasileiro.
     */
    public function formatCpf(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        return sprintf(
            '%s.%s.%s-%s',
            substr($cpf, 0, 3),
            substr($cpf, 3, 3),
            substr($cpf, 6, 3),
            substr($cpf, 9, 2)
        );
    }

    /**
     * Formata RG (mantém formato original, apenas limpa).
     */
    public function formatRg(string $rg): string
    {
        // Mantém letras e números, remove outros caracteres
        return preg_replace('/[^a-zA-Z0-9\-\.]/', '', $rg);
    }

    /**
     * Normaliza documento para comparação (remove caracteres especiais).
     */
    public function normalize(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($value));
    }

    /**
     * Verifica se o documento é válido (qualquer tipo).
     */
    public function isValid(string $value, ?DocumentType $type = null): bool
    {
        return $this->validate($value, $type)['valid'];
    }

    /**
     * Retorna apenas a mensagem de erro (se houver).
     */
    public function getErrorMessage(string $value, ?DocumentType $type = null): ?string
    {
        $result = $this->validate($value, $type);

        return $result['valid'] ? null : $result['message'];
    }
}
