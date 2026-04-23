<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Observers\GuestObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([GuestObserver::class])]
class Guest extends Model
{
    /** @use HasFactory<\Database\Factories\GuestFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'event_id',
        'sector_id',
        'promoter_id',
        'parent_id',
        'name',
        'document',
        'document_type',
        'email',
    ];

    protected function casts(): array
    {
        return [
            'is_checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
            'document_type' => DocumentType::class,
        ];
    }

    /**
     * Evento deste convidado.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Setor deste convidado.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Promoter que cadastrou este convidado.
     */
    public function promoter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoter_id');
    }

    /**
     * Validador que confirmou o check-in.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Relacionamento para o validador que realizou o check-in.
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->validator();
    }

    /**
     * Convidado pai (para +1).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'parent_id');
    }

    /**
     * Convidados +1 (acompanhantes).
     */
    public function companions(): BelongsToMany
    {
        return $this->belongsToMany(Guest::class, 'parent_id')
            ->withTimestamps();
    }

    /**
     * Verifica se é um convidado +1.
     */
    public function isCompanion(): bool
    {
        return $this->parent_id !== null;
    }

    /**
     * Marca o convidado como check-in.
     */
    public function checkIn(int $validatedBy): void
    {
        $this->is_checked_in = true;
        $this->checked_in_at = now();
        $this->checked_in_by = $validatedBy;
        $this->save();
    }

    /**
     * Desfaz o check-in do convidado.
     */
    public function undoCheckIn(): void
    {
        $this->is_checked_in = false;
        $this->checked_in_at = null;
        $this->checked_in_by = null;
        $this->save();
    }

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'document', 'sector_id', 'is_checked_in', 'checked_in_at', 'checked_in_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Convidado foi {$eventName}");
    }
}
