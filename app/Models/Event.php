<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    use \Spatie\Activitylog\Traits\LogsActivity;

    protected $fillable = [
        'name',
        'banner_path',
        'banner_url',
        'location',
        'date',
        'start_time',
        'end_time',
        'status',
        'ticket_price',
        'bilheteria_enabled',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => EventStatus::class,
            'ticket_price' => 'decimal:2',
            'bilheteria_enabled' => 'boolean',
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
     * Permissões de promoters para este evento (alias para compatibilidade).
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(PromoterPermission::class);
    }

    /**
     * Atribuicoes de usuarios a este evento.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(EventAssignment::class);
    }

    /**
     * Vendas de bilheteria deste evento.
     */
    public function ticketSales(): HasMany
    {
        return $this->hasMany(TicketSale::class);
    }

    /**
     * Retorna a URL de exibicao do banner.
     * Prioriza banner_url externo, depois banner_path local, e por ultimo um placeholder.
     */
    protected function bannerDisplayUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->banner_url) {
                return $this->banner_url;
            }

            if ($this->banner_path) {
                return Storage::url($this->banner_path);
            }

            return null;
        });
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
            ->setDescriptionForEvent(fn (string $eventName) => "Evento foi {$eventName}");
    }
}
