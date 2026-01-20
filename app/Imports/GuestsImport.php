<?php

namespace App\Imports;

use App\Models\Guest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuestsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected int $eventId;

    protected int $sectorId;

    protected int $promoterId;

    protected int $imported = 0;

    protected int $skipped = 0;

    protected array $errors = [];

    public function __construct(int $eventId, int $sectorId, int $promoterId)
    {
        $this->eventId = $eventId;
        $this->sectorId = $sectorId;
        $this->promoterId = $promoterId;
    }

    /**
     * Processa a coleção de linhas do arquivo.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $name = trim($row['nome'] ?? $row['name'] ?? '');
            $document = trim($row['documento'] ?? $row['document'] ?? $row['cpf'] ?? '');
            $email = trim($row['email'] ?? '');

            // Pula linhas vazias
            if (empty($name)) {
                continue;
            }

            // Normaliza documento para verificação
            $documentNormalized = preg_replace('/\D/', '', $document);

            // Verifica duplicidade no evento
            $exists = Guest::query()
                ->where('event_id', $this->eventId)
                ->where('document_normalized', $documentNormalized)
                ->exists();

            if ($exists && $documentNormalized) {
                $this->skipped++;
                $this->errors[] = 'Linha '.($index + 2).": Documento '{$document}' já cadastrado.";

                continue;
            }

            // Cria o convidado
            Guest::create([
                'event_id' => $this->eventId,
                'sector_id' => $this->sectorId,
                'promoter_id' => $this->promoterId,
                'name' => $name,
                'document' => $document,
                'email' => $email ?: null,
            ]);

            $this->imported++;
        }
    }

    /**
     * Regras de validação para cada linha.
     */
    public function rules(): array
    {
        return [
            'nome' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
            'documento' => 'nullable|string|max:50',
            'document' => 'nullable|string|max:50',
            'cpf' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ];
    }

    /**
     * Retorna contagem de importados.
     */
    public function getImportedCount(): int
    {
        return $this->imported;
    }

    /**
     * Retorna contagem de pulados.
     */
    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    /**
     * Retorna erros encontrados.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
