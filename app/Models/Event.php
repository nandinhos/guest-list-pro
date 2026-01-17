<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'banner_path',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => EventStatus::class,
        ];
    }

    /**
     * Setores deste evento.
     */
    public function sectors(): HasMany
    {
        return $this->hasMany(Sector::class);
    }

    /**
     * Convidados vinculados a este evento.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    /**
     * Permissões de promoters para este evento.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(PromoterPermission::class);
    }
}
