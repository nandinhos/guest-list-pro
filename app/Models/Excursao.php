<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Excursao extends Model
{
    use HasFactory;

    protected $table = 'excursoes';

    protected $fillable = [
        'event_id',
        'nome',
        'criado_por',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'excursao_id');
    }

    public function monitores(): HasManyThrough
    {
        return $this->hasManyThrough(Monitor::class, Veiculo::class, 'excursao_id', 'veiculo_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }
}
