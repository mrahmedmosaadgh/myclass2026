<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'moyasar_id',
        'amount',
        'currency',
        'status',
        'payment_type',
        'description',
        'metadata',
        'callback_url',
        'source_data',
        'error_message',
        'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'source_data' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeInitiated($query)
    {
        return $query->where('status', 'initiated');
    }

    public function scopeByMoyasarId($query, string $moyasarId)
    {
        return $query->where('moyasar_id', $moyasarId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ─── Helpers ─────────────────────────────────────────────

    /**
     * Get the amount in the main currency unit (e.g., SAR instead of halalas).
     */
    public function getAmountInMainUnit(): float
    {
        return $this->amount / 100;
    }

    /**
     * Check if this payment was successful.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if this payment has failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
