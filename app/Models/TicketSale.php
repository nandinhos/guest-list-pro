<?php

namespace App\Models;

use App\Observers\TicketSaleObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ObservedBy([TicketSaleObserver::class])]
class TicketSale extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'event_id',
        'ticket_type_id',
        'sector_id',
        'guest_id',
        'sold_by',
        'value',
        'payment_method',
        'buyer_name',
        'buyer_document',
        'notes',
        'is_refunded',
        'refunded_at',
        'refunded_by',
        'refund_reason',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_refunded' => 'boolean',
            'refunded_at' => 'datetime',
        ];
    }

    public function scopeNotRefunded($query)
    {
        return $query->where('is_refunded', false);
    }

    public function scopeRefunded($query)
    {
        return $query->where('is_refunded', true);
    }

    public function refundRequest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RefundRequest::class);
    }

    /**
     * Evento da venda.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Convidado gerado pela venda.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Usuário que realizou a venda.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    /**
     * Tipo do ingresso.
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * Setor da venda.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    /**
     * Divisões de pagamento.
     */
    public function paymentSplits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentSplit::class);
    }

    /**
     * Retorna o documento do comprador mascarado (últimos 4 dígitos).
     */
    public function getBuyerDocumentMaskedAttribute(): ?string
    {
        if (! $this->buyer_document) {
            return null;
        }

        $doc = preg_replace('/[^0-9A-Za-z]/', '', $this->buyer_document);

        if (strlen($doc) <= 4) {
            return $doc;
        }

        $lastFour = substr($doc, -4);

        return '****'.$lastFour;
    }

    /**
     * Retorna o nome do comprador com iniciais mascaradas.
     */
    public function getBuyerNameMaskedAttribute(): ?string
    {
        if (! $this->buyer_name) {
            return null;
        }

        $parts = explode(' ', $this->buyer_name);

        if (count($parts) === 1) {
            return substr($parts[0], 0, 2).'***';
        }

        $masked = array_map(fn ($name) => substr($name, 0, 1).'***', array_slice($parts, 0, -1));
        $masked[] = end($parts);

        return implode(' ', $masked);
    }

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['event_id', 'guest_id', 'value', 'payment_method', 'buyer_name', 'buyer_document'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Venda de bilhete foi {$eventName}");
    }
}
