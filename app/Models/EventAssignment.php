<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\EventAssignmentFactory> */
    use HasFactory;
    use \Spatie\Activitylog\Traits\LogsActivity;

    protected $fillable = [
        'user_id',
        'role',
        'event_id',
        'sector_id',
        'guest_limit',
        'start_time',
        'end_time',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    /**
     * Usuario atribuido a este evento.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Evento desta atribuicao.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Setor desta atribuicao (apenas para promoters).
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Atribuição de evento foi {$eventName}");
    }
}
