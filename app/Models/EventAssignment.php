<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\EventAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'event_id',
        'sector_id',
        'guest_limit',
        'start_time',
        'end_time',
    ];

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
}
