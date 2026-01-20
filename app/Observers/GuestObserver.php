<?php

namespace App\Observers;

use App\Models\Guest;

class GuestObserver
{
    /**
     * Handle the Guest "saving" event (before create or update).
     */
    public function saving(Guest $guest): void
    {
        $this->normalizeDocument($guest);
        $this->normalizeName($guest);
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
}
