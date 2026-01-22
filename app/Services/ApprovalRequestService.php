<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\UserRole;
use App\Models\ApprovalRequest;
use App\Models\Guest;
use App\Models\User;
use App\Notifications\ApprovalRequestStatusNotification;
use App\Notifications\NewApprovalRequestNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApprovalRequestService
{
    /**
     * Verifica se existem duplicidades para um convidado no evento.
     *
     * @return array|null Array com 'type' ('document'|'name'), 'level' ('error'|'warning'), 'message', 'existing' ou null
     */
    public function checkForDuplicates(
        int $eventId,
        string $name,
        ?string $document = null,
        ?int $excludeGuestId = null
    ): ?array {
        $searchService = app(GuestSearchService::class);
        $normalizedName = $searchService->normalize($name);

        // 1. Verificar Guest existente por DOCUMENTO (bloqueante)
        if ($document) {
            $normalizedDoc = $searchService->normalizeDocument($document);
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

            // 1.1 Verificar Solicitação pendente com mesmo documento
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
        }

        // 2. Verificar Guest existente por NOME (apenas alerta)
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

        // 2.1 Verificar Solicitação pendente com mesmo nome
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

        return null; // Sem duplicidade
    }

    /**
     * Cria uma solicitação de inclusão de convidado (Promoter).
     *
     * @param  array{name: string, document?: string, document_type?: string, email?: string}  $guestData
     */
    public function createGuestInclusionRequest(
        User $promoter,
        int $eventId,
        int $sectorId,
        array $guestData,
        ?string $notes = null
    ): ApprovalRequest {
        $request = ApprovalRequest::create([
            'event_id' => $eventId,
            'sector_id' => $sectorId,
            'type' => RequestType::GUEST_INCLUSION,
            'status' => RequestStatus::PENDING,
            'requester_id' => $promoter->id,
            'guest_name' => $guestData['name'],
            'guest_document' => $guestData['document'] ?? null,
            'guest_document_type' => $guestData['document_type'] ?? null,
            'guest_email' => $guestData['email'] ?? null,
            'requester_notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->notifyAdmins($request);

        return $request;
    }

    /**
     * Cria uma solicitação de check-in emergencial (Validator).
     *
     * @param  array{name: string, document?: string, document_type?: string, email?: string}  $guestData
     */
    public function createEmergencyCheckinRequest(
        User $validator,
        int $eventId,
        ?int $sectorId,
        array $guestData,
        ?string $notes = null
    ): ApprovalRequest {
        $request = ApprovalRequest::create([
            'event_id' => $eventId,
            'sector_id' => $sectorId,
            'type' => RequestType::EMERGENCY_CHECKIN,
            'status' => RequestStatus::PENDING,
            'requester_id' => $validator->id,
            'guest_name' => $guestData['name'],
            'guest_document' => $guestData['document'] ?? null,
            'guest_document_type' => $guestData['document_type'] ?? null,
            'guest_email' => $guestData['email'] ?? null,
            'requester_notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->notifyAdmins($request);

        return $request;
    }

    /**
     * Aprova uma solicitação (Admin).
     * Para GUEST_INCLUSION: cria o Guest.
     * Para EMERGENCY_CHECKIN: cria o Guest E realiza o check-in.
     */
    public function approve(
        ApprovalRequest $request,
        User $admin,
        ?string $notes = null
    ): ApprovalRequest {
        if (! $request->canBeReviewed()) {
            throw new \RuntimeException('Esta solicitação não pode ser aprovada.');
        }

        if (! $this->canReview($admin)) {
            throw new \RuntimeException('Você não tem permissão para aprovar solicitações.');
        }

        if ($request->requester_id === $admin->id) {
            throw new \RuntimeException('Você não pode aprovar sua própria solicitação.');
        }

        // Verificar se já existe um Guest com o mesmo documento no evento
        $existingGuest = $request->findExistingGuest();
        if ($existingGuest) {
            $promoterName = $existingGuest->promoter?->name ?? 'Desconhecido';
            $sectorName = $existingGuest->sector?->name ?? 'Desconhecido';

            // Se mesmo setor, não pode aprovar
            if ($existingGuest->sector_id === $request->sector_id) {
                throw new \RuntimeException(sprintf(
                    'Convidado já existe na lista de "%s" (Setor: %s). Rejeite esta solicitação.',
                    $promoterName,
                    $sectorName
                ));
            }

            // Se setor diferente, também lançar erro (será tratado por action específica)
            throw new \RuntimeException(sprintf(
                'Convidado já existe na lista de "%s" no Setor "%s". Use a ação "Aprovar em Outro Setor" para atualizar o setor.',
                $promoterName,
                $sectorName
            ));
        }

        return DB::transaction(function () use ($request, $admin, $notes) {
            // Criar o Guest
            // O solicitante sempre é o "responsável" pelo convidado
            $guest = Guest::create([
                'event_id' => $request->event_id,
                'sector_id' => $request->sector_id,
                'promoter_id' => $request->requester_id,
                'name' => $request->guest_name,
                'document' => $request->guest_document,
                'document_type' => $request->guest_document_type,
                'email' => $request->guest_email,
                'is_checked_in' => $request->type === RequestType::EMERGENCY_CHECKIN,
                'checked_in_at' => $request->type === RequestType::EMERGENCY_CHECKIN ? now() : null,
                'checked_in_by' => $request->type === RequestType::EMERGENCY_CHECKIN
                    ? $request->requester_id
                    : null,
            ]);

            // Atualizar a solicitação
            $request->update([
                'status' => RequestStatus::APPROVED,
                'reviewer_id' => $admin->id,
                'reviewed_at' => now(),
                'reviewer_notes' => $notes,
                'guest_id' => $guest->id,
            ]);

            $request = $request->fresh();

            // Notificar o solicitante
            $request->requester->notify(new ApprovalRequestStatusNotification($request));

            return $request;
        });
    }

    /**
     * Aprova uma solicitação atualizando o setor do Guest existente (Admin).
     * Usado quando o convidado já existe em outro setor.
     */
    public function approveWithSectorUpdate(
        ApprovalRequest $request,
        User $admin,
        ?string $notes = null
    ): ApprovalRequest {
        if (! $request->canBeReviewed()) {
            throw new \RuntimeException('Esta solicitação não pode ser aprovada.');
        }

        if (! $this->canReview($admin)) {
            throw new \RuntimeException('Você não tem permissão para aprovar solicitações.');
        }

        if ($request->requester_id === $admin->id) {
            throw new \RuntimeException('Você não pode aprovar sua própria solicitação.');
        }

        $existingGuest = $request->findExistingGuest();
        if (! $existingGuest) {
            throw new \RuntimeException('Nenhum convidado existente encontrado. Use a aprovação normal.');
        }

        if ($existingGuest->sector_id === $request->sector_id) {
            throw new \RuntimeException('O convidado já está no mesmo setor. Não é possível aprovar.');
        }

        return DB::transaction(function () use ($request, $admin, $notes, $existingGuest) {
            // Atualizar o setor do Guest existente
            $existingGuest->update([
                'sector_id' => $request->sector_id,
            ]);

            // Se for emergency_checkin e não estava checked_in, fazer check-in
            if ($request->type === RequestType::EMERGENCY_CHECKIN && ! $existingGuest->is_checked_in) {
                $existingGuest->update([
                    'is_checked_in' => true,
                    'checked_in_at' => now(),
                    'checked_in_by' => $request->requester_id,
                ]);
            }

            // Atualizar a solicitação
            $request->update([
                'status' => RequestStatus::APPROVED,
                'reviewer_id' => $admin->id,
                'reviewed_at' => now(),
                'reviewer_notes' => $notes ?? 'Aprovado com atualização de setor (convidado já existia).',
                'guest_id' => $existingGuest->id,
            ]);

            $request = $request->fresh();

            // Notificar o solicitante
            $request->requester->notify(new ApprovalRequestStatusNotification($request));

            return $request;
        });
    }

    /**
     * Rejeita uma solicitação (Admin).
     */
    public function reject(
        ApprovalRequest $request,
        User $admin,
        string $reason
    ): ApprovalRequest {
        if (! $request->canBeReviewed()) {
            throw new \RuntimeException('Esta solicitação não pode ser rejeitada.');
        }

        if (! $this->canReview($admin)) {
            throw new \RuntimeException('Você não tem permissão para rejeitar solicitações.');
        }

        if ($request->requester_id === $admin->id) {
            throw new \RuntimeException('Você não pode rejeitar sua própria solicitação.');
        }

        $request->update([
            'status' => RequestStatus::REJECTED,
            'reviewer_id' => $admin->id,
            'reviewed_at' => now(),
            'reviewer_notes' => $reason,
        ]);

        $request = $request->fresh();

        // Notificar o solicitante
        $request->requester->notify(new ApprovalRequestStatusNotification($request));

        return $request;
    }

    /**
     * Cancela uma solicitação pendente (Solicitante).
     */
    public function cancel(ApprovalRequest $request, User $user): ApprovalRequest
    {
        if (! $request->canBeCancelled()) {
            throw new \RuntimeException('Esta solicitação não pode ser cancelada.');
        }

        if ($request->requester_id !== $user->id) {
            throw new \RuntimeException('Você não pode cancelar esta solicitação.');
        }

        $request->update([
            'status' => RequestStatus::CANCELLED,
        ]);

        return $request->fresh();
    }

    /**
     * Reconsidera uma solicitação rejeitada ou cancelada (Admin).
     * Retorna o status para PENDING.
     */
    public function reconsider(
        ApprovalRequest $request,
        User $admin,
        ?string $notes = null
    ): ApprovalRequest {
        if (! $request->canBeReconsidered()) {
            throw new \RuntimeException('Esta solicitação não pode ser reconsiderada.');
        }

        if (! $this->canReview($admin)) {
            throw new \RuntimeException('Você não tem permissão para reconsiderar solicitações.');
        }

        if ($request->requester_id === $admin->id) {
            throw new \RuntimeException('Você não pode reconsiderar sua própria solicitação.');
        }

        $request->update([
            'status' => RequestStatus::PENDING,
            'reviewer_id' => null,
            'reviewed_at' => null,
            'reviewer_notes' => $notes ? "Reconsiderado: {$notes}" : 'Reconsiderado pelo administrador.',
        ]);

        $request = $request->fresh();

        // Notificar o solicitante
        $request->requester->notify(new ApprovalRequestStatusNotification($request));

        return $request;
    }

    /**
     * Reverte uma aprovação feita por engano (Admin).
     * Exclui o Guest criado e retorna o status para PENDING.
     */
    public function revert(
        ApprovalRequest $request,
        User $admin,
        ?string $reason = null
    ): ApprovalRequest {
        if (! $request->canBeReverted()) {
            throw new \RuntimeException('Esta aprovação não pode ser revertida.');
        }

        if (! $this->canReview($admin)) {
            throw new \RuntimeException('Você não tem permissão para reverter aprovações.');
        }

        if ($request->requester_id === $admin->id) {
            throw new \RuntimeException('Você não pode reverter sua própria solicitação.');
        }

        return DB::transaction(function () use ($request, $reason) {
            // Excluir o Guest se existir
            if ($request->guest_id && $request->guest) {
                $request->guest->delete();
            }

            // Reverter a solicitação para PENDING
            $request->update([
                'status' => RequestStatus::PENDING,
                'reviewer_id' => null,
                'reviewed_at' => null,
                'reviewer_notes' => $reason
                    ? "Aprovação revertida: {$reason}"
                    : 'Aprovação revertida pelo administrador.',
                'guest_id' => null,
            ]);

            $request = $request->fresh();

            // Notificar o solicitante
            $request->requester->notify(new ApprovalRequestStatusNotification($request));

            return $request;
        });
    }

    /**
     * Expira solicitações pendentes de eventos finalizados.
     *
     * @return int Quantidade de solicitações expiradas
     */
    public function expireRequestsForFinishedEvents(): int
    {
        return ApprovalRequest::pending()
            ->whereHas('event', function ($query) {
                $query->where('status', 'finished');
            })
            ->update(['status' => RequestStatus::EXPIRED]);
    }

    /**
     * Expira solicitações pendentes antigas.
     *
     * @return int Quantidade de solicitações expiradas
     */
    public function expireOldRequests(int $hoursOld = 24): int
    {
        return ApprovalRequest::pending()
            ->where('created_at', '<=', now()->subHours($hoursOld))
            ->update(['status' => RequestStatus::EXPIRED]);
    }

    /**
     * Retorna contagem de solicitações pendentes.
     */
    public function getPendingCount(?int $eventId = null): int
    {
        $query = ApprovalRequest::pending();

        if ($eventId !== null) {
            $query->forEvent($eventId);
        }

        return $query->count();
    }

    /**
     * Retorna solicitações pendentes para um evento.
     */
    public function getPendingForEvent(int $eventId): Collection
    {
        return ApprovalRequest::pending()
            ->forEvent($eventId)
            ->with(['requester', 'sector'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Retorna solicitações de um usuário.
     */
    public function getRequestsByUser(int $userId, ?int $eventId = null): Collection
    {
        $query = ApprovalRequest::byRequester($userId)
            ->with(['event', 'sector', 'reviewer'])
            ->orderByDesc('created_at');

        if ($eventId !== null) {
            $query->forEvent($eventId);
        }

        return $query->get();
    }

    /**
     * Verifica se um usuário pode revisar (aprovar/rejeitar) solicitações.
     */
    public function canReview(User $user): bool
    {
        return $user->role === UserRole::ADMIN && $user->is_active;
    }

    /**
     * Verifica se já existe uma solicitação pendente similar.
     */
    public function hasPendingSimilarRequest(
        int $eventId,
        string $guestDocument,
        ?int $excludeRequestId = null
    ): bool {
        $query = ApprovalRequest::pending()
            ->forEvent($eventId)
            ->where('guest_document', $guestDocument);

        if ($excludeRequestId !== null) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->exists();
    }

    /**
     * Notifica todos os admins ativos sobre uma nova solicitação.
     */
    protected function notifyAdmins(ApprovalRequest $request): void
    {
        $admins = User::where('role', UserRole::ADMIN)
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewApprovalRequestNotification($request));
        }
    }
}
