<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    /** @use HasFactory<\Database\Factories\SectorFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'capacity',
    ];

    /**
     * Evento ao qual este setor pertence.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Convidados deste setor.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    /**
     * Permissões vinculadas a este setor.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(PromoterPermission::class);
    }
}
