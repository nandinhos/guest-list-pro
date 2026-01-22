<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ApprovalRequest extends Model
{
    /** @use HasFactory<\Database\Factories\ApprovalRequestFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'event_id',
        'sector_id',
        'type',
        'status',
        'requester_id',
        'guest_name',
        'guest_document',
        'guest_document_type',
        'guest_email',
        'guest_id',
        'requester_notes',
        'reviewer_id',
        'reviewed_at',
        'reviewer_notes',
        'ip_address',
        'user_agent',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => RequestType::class,
            'status' => RequestStatus::class,
            'guest_document_type' => DocumentType::class,
            'reviewed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Evento relacionado à solicitação.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Setor relacionado à solicitação.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Usuário que criou a solicitação.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * Administrador que revisou a solicitação.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Guest associado (se existente ou criado após aprovação).
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Scope para solicitações pendentes.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', RequestStatus::PENDING);
    }

    /**
     * Scope para solicitações de um evento específico.
     */
    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope para solicitações por tipo.
     */
    public function scopeByType(Builder $query, RequestType $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para solicitações expiradas.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', RequestStatus::PENDING)
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope para solicitações de um solicitante específico.
     */
    public function scopeByRequester(Builder $query, int $requesterId): Builder
    {
        return $query->where('requester_id', $requesterId);
    }

    /**
     * Verifica se a solicitação está pendente.
     */
    public function isPending(): bool
    {
        return $this->status === RequestStatus::PENDING;
    }

    /**
     * Verifica se a solicitação foi aprovada.
     */
    public function isApproved(): bool
    {
        return $this->status === RequestStatus::APPROVED;
    }

    /**
     * Verifica se a solicitação foi rejeitada.
     */
    public function isRejected(): bool
    {
        return $this->status === RequestStatus::REJECTED;
    }

    /**
     * Verifica se a solicitação pode ser revisada.
     */
    public function canBeReviewed(): bool
    {
        return $this->status->canBeReviewed();
    }

    /**
     * Verifica se a solicitação pode ser cancelada.
     */
    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }

    /**
     * Verifica se a solicitação expirou.
     */
    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Verifica se a solicitação pode ser reconsiderada.
     */
    public function canBeReconsidered(): bool
    {
        return $this->status->canBeReconsidered();
    }

    /**
     * Verifica se a solicitação aprovada pode ser revertida.
     * Só pode reverter se o guest associado não foi modificado.
     */
    public function canBeReverted(): bool
    {
        if (! $this->status->canBeReverted()) {
            return false;
        }

        // Se não tem guest associado, pode reverter
        if (! $this->guest_id) {
            return true;
        }

        // Verificar se o guest ainda existe e não foi modificado
        $guest = $this->guest;
        if (! $guest) {
            return true;
        }

        // Permitir reverter apenas se guest não teve check-in manual
        // (se foi emergency_checkin, o check-in faz parte da aprovação)
        if ($this->type === RequestType::GUEST_INCLUSION && $guest->is_checked_in) {
            return false; // Guest já fez check-in, não pode reverter
        }

        return true;
    }

    /**
     * Encontra um Guest existente com o mesmo documento no evento.
     */
    public function findExistingGuest(): ?Guest
    {
        if (empty($this->guest_document)) {
            return null;
        }

        return Guest::where('event_id', $this->event_id)
            ->where('document', $this->guest_document)
            ->with(['promoter', 'sector'])
            ->first();
    }

    /**
     * Verifica se existe um Guest com o mesmo documento no evento.
     */
    public function hasExistingGuest(): bool
    {
        return $this->findExistingGuest() !== null;
    }

    /**
     * Verifica se o Guest existente está no mesmo setor da solicitação.
     */
    public function existingGuestInSameSector(): bool
    {
        $existing = $this->findExistingGuest();

        if (! $existing) {
            return false;
        }

        return $existing->sector_id === $this->sector_id;
    }

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'reviewer_id',
                'reviewed_at',
                'reviewer_notes',
                'guest_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Solicitação de aprovação foi {$eventName}");
    }
}
