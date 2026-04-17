<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_sale_id',
        'payment_method',
        'value',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function ticketSale(): BelongsTo
    {
        return $this->belongsTo(TicketSale::class);
    }
}
