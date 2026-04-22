<?php

namespace App\Models;

use App\Enums\TipoVeiculo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Veiculo extends Model
{
    /** @use HasFactory<\Database\Factories\VeiculoFactory> */
    use HasFactory;

    protected $table = 'veiculos';

    protected $fillable = [
        'excursao_id',
        'tipo',
        'placa',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoVeiculo::class,
        ];
    }

    public function excursao(): BelongsTo
    {
        return $this->belongsTo(Excursao::class, 'excursao_id');
    }

    public function monitores(): HasMany
    {
        return $this->hasMany(Monitor::class, 'veiculo_id');
    }
}
