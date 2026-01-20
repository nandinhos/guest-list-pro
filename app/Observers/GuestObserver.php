<?php

namespace App\Observers;

use App\Models\Guest;
use Illuminate\Support\Str;

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
