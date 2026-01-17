<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoterPermission extends Model
{
    /** @use HasFactory<\Database\Factories\PromoterPermissionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'sector_id',
        'guest_limit',
        'start_time',
        'end_time',
    ];

    /**
     * Usuário (Promoter) desta permissão.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Evento desta permissão.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Setor desta permissão.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
}
