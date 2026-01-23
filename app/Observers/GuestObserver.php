<?php

namespace App\Observers;

use App\Models\Guest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GuestObserver
{
    /**
     * Handle the Guest "saving" event (before create or update).
     */
    public function saving(Guest $guest): void
    {
        $this->normalizeDocument($guest);
        $this->normalizeName($guest);
        $this->fillNormalizedColumns($guest);
        $this->validateUniqueDocumentInEvent($guest);
    }

    /**
     * Validate that the document is not already registered in another sector of the same event.
     * Provides a user-friendly error message with the existing sector name.
     *
     * @throws ValidationException
     */
    protected function validateUniqueDocumentInEvent(Guest $guest): void
    {
        if (! $guest->document || ! $guest->event_id) {
            return;
        }

        $query = Guest::query()
            ->where('event_id', $guest->event_id)
            ->where('document', $guest->document)
            ->with('sector:id,name');

        // Exclude current record when updating
        if ($guest->exists) {
            $query->where('id', '!=', $guest->id);
        }

        $existingGuest = $query->first();

        if ($existingGuest) {
            $sectorName = $existingGuest->sector?->name ?? 'outro setor';

            throw ValidationException::withMessages([
                'document' => "Este documento já está cadastrado no setor \"{$sectorName}\" para este evento. Nome: {$existingGuest->name}",
            ]);
        }
    }

    /**
     * Normalize the document field by removing all non-numeric characters.
     */
    protected function normalizeDocument(Guest $guest): void
    {
        if ($guest->document) {
            $guest->document = preg_replace('/\D/', '', $guest->document);
        }
    }

    /**
     * Normalize the name field by trimming extra spaces.
     */
    protected function normalizeName(Guest $guest): void
    {
        if ($guest->name) {
            $guest->name = preg_replace('/\s+/', ' ', trim($guest->name));
        }
    }

    /**
     * Preenche as colunas normalizadas para busca sem acentos.
     * Usa Str::ascii() para remover acentos e strtolower para lowercase.
     */
    protected function fillNormalizedColumns(Guest $guest): void
    {
        // Normaliza o nome: remove acentos e converte para lowercase
        if ($guest->name) {
            $guest->name_normalized = strtolower(Str::ascii($guest->name));
        }

        // Normaliza o documento: apenas números
        if ($guest->document) {
            $guest->document_normalized = preg_replace('/\D/', '', $guest->document);
        }
    }
}
