<?php

namespace App\Rules;

use App\Enums\DocumentType;
use App\Services\DocumentValidationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Regra de validação de documentos (CPF, RG, Passaporte).
 *
 * Uso:
 *   - new DocumentValidation() // Auto-detecta o tipo
 *   - new DocumentValidation(DocumentType::CPF) // Força tipo CPF
 *   - new DocumentValidation(allowEmpty: true) // Permite vazio
 */
class DocumentValidation implements ValidationRule
{
    public function __construct(
        private ?DocumentType $type = null,
        private bool $allowEmpty = false
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Permite vazio se configurado
        if ($this->allowEmpty && empty($value)) {
            return;
        }

        $service = app(DocumentValidationService::class);
        $result = $service->validate((string) $value, $this->type);

        if (! $result['valid']) {
            $fail($result['message'] ?? 'Documento inválido.');
        }
    }

    /**
     * Cria uma rule para validação de CPF.
     */
    public static function cpf(bool $allowEmpty = false): self
    {
        return new self(DocumentType::CPF, $allowEmpty);
    }

    /**
     * Cria uma rule para validação de RG.
     */
    public static function rg(bool $allowEmpty = false): self
    {
        return new self(DocumentType::RG, $allowEmpty);
    }

    /**
     * Cria uma rule para validação de Passaporte.
     */
    public static function passport(bool $allowEmpty = false): self
    {
        return new self(DocumentType::PASSPORT, $allowEmpty);
    }

    /**
     * Cria uma rule com auto-detecção de tipo.
     */
    public static function any(bool $allowEmpty = false): self
    {
        return new self(null, $allowEmpty);
    }
}
