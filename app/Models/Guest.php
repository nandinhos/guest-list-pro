<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Guest extends Model
{
    /** @use HasFactory<\Database\Factories\GuestFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'sector_id',
        'promoter_id',
        'name',
        'document',
        'email',
        'is_checked_in',
        'checked_in_at',
        'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'is_checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
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
}
