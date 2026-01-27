<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinAttempt extends Model
{
    /** @use HasFactory<\Database\Factories\CheckinAttemptFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'validator_id',
        'guest_id',
        'search_query',
        'result',
        'ip_address',
        'user_agent',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
