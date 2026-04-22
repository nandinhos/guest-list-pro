<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RefundRequest extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'ticket_sale_id',
        'requester_id',
        'reason',
        'status',
        'reviewer_id',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RefundStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function ticketSale(): BelongsTo
    {
        return $this->belongsTo(TicketSale::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', RefundStatus::PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', RefundStatus::APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', RefundStatus::REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === RefundStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === RefundStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === RefundStatus::REJECTED;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'review_notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "RefundRequest foi {$eventName}");
    }
}
