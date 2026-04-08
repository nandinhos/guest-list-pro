<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Guest;

class DuplicateGuestValidator
{
    public function __construct(
        private GuestSearchService $searchService,
    ) {}

    /**
     * Verifica duplicatas para um convidado.
     *
     * @return array|null Array com 'type', 'level', 'message', 'existing' ou null
     */
    public function check(
        int $eventId,
        string $name,
        ?string $document = null,
        ?int $excludeGuestId = null
    ): ?array {
        $normalizedName = $this->searchService->normalize($name);

        if ($document) {
            $result = $this->checkDocument($eventId, $document, $excludeGuestId);
            if ($result) {
                return $result;
            }
        }

        return $this->checkName($eventId, $name, $excludeGuestId);
    }

    /**
     * Verifica duplicata por documento (bloqueante).
     */
    private function checkDocument(int $eventId, string $document, ?int $excludeGuestId): ?array
    {
        $normalizedDoc = $this->searchService->normalizeDocument($document);

        $existingByDocument = Guest::where('event_id', $eventId)
            ->when($excludeGuestId, fn ($q) => $q->where('id', '!=', $excludeGuestId))
            ->where(fn ($q) => $q->where('document', $document)->orWhere('document_normalized', $normalizedDoc))
            ->with(['promoter', 'sector'])
            ->first();

        if ($existingByDocument) {
            return [
                'type' => 'document',
                'level' => 'error',
                'message' => sprintf(
                    'Documento "%s" já cadastrado na lista de %s (Setor: %s)',
                    $document,
                    $existingByDocument->promoter?->name ?? 'N/A',
                    $existingByDocument->sector?->name ?? 'N/A'
                ),
                'existing' => $existingByDocument,
            ];
        }

        $pendingByDocument = ApprovalRequest::pending()
            ->forEvent($eventId)
            ->where('guest_document', $document)
            ->with('requester')
            ->first();

        if ($pendingByDocument) {
            return [
                'type' => 'document',
                'level' => 'error',
                'message' => sprintf(
                    'Documento "%s" já possui solicitação pendente por %s',
                    $document,
                    $pendingByDocument->requester?->name ?? 'N/A'
                ),
                'existing' => $pendingByDocument,
            ];
        }

        return null;
    }

    /**
     * Verifica duplicata por nome (apenas alerta).
     */
    private function checkName(int $eventId, string $name, ?int $excludeGuestId): ?array
    {
        $normalizedName = $this->searchService->normalize($name);

        $existingByName = Guest::where('event_id', $eventId)
            ->when($excludeGuestId, fn ($q) => $q->where('id', '!=', $excludeGuestId))
            ->where('name_normalized', $normalizedName)
            ->with(['promoter', 'sector'])
            ->first();

        if ($existingByName) {
            return [
                'type' => 'name',
                'level' => 'warning',
                'message' => sprintf(
                    'Possível homônimo: "%s" já existe na lista de %s (Setor: %s) com documento %s',
                    $existingByName->name,
                    $existingByName->promoter?->name ?? 'N/A',
                    $existingByName->sector?->name ?? 'N/A',
                    $existingByName->document ?? 'sem documento'
                ),
                'existing' => $existingByName,
            ];
        }

        $pendingByName = ApprovalRequest::pending()
            ->forEvent($eventId)
            ->whereRaw('LOWER(guest_name) = ?', [strtolower($name)])
            ->with('requester')
            ->first();

        if ($pendingByName) {
            return [
                'type' => 'name',
                'level' => 'warning',
                'message' => sprintf(
                    'Possível homônimo: Já existe solicitação pendente para "%s" por %s com documento %s',
                    $pendingByName->guest_name,
                    $pendingByName->requester?->name ?? 'N/A',
                    $pendingByName->guest_document ?? 'sem documento'
                ),
                'existing' => $pendingByName,
            ];
        }

        return null;
    }

    /**
     * Verifica duplicatas para uma ApprovalRequest existente.
     */
    public function checkForRequest(ApprovalRequest $request, ?int $excludeRequestId = null): ?array
    {
        return $this->check(
            $request->event_id,
            $request->guest_name,
            $request->guest_document,
            null
        );
    }
}
