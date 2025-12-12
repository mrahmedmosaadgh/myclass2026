<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeAnswerReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'answer_id',
        'report_type',
        'reason',
        'status',
        'reviewed_by',
        'admin_notes',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime'
    ];

    /**
     * Get the user who reported the answer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the answer being reported
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(ResumeAnswer::class, 'answer_id');
    }

    /**
     * Get the admin who reviewed the report
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope to get pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get reviewed reports
     */
    public function scopeReviewed($query)
    {
        return $query->whereIn('status', ['reviewed', 'resolved', 'dismissed']);
    }

    /**
     * Scope to get reports by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Mark report as reviewed
     */
    public function markAsReviewed($reviewerId, $adminNotes = null)
    {
        $this->update([
            'status' => 'reviewed',
            'reviewed_by' => $reviewerId,
            'admin_notes' => $adminNotes,
            'reviewed_at' => now()
        ]);
    }

    /**
     * Mark report as resolved
     */
    public function markAsResolved($reviewerId, $adminNotes = null)
    {
        $this->update([
            'status' => 'resolved',
            'reviewed_by' => $reviewerId,
            'admin_notes' => $adminNotes,
            'reviewed_at' => now()
        ]);
    }

    /**
     * Mark report as dismissed
     */
    public function markAsDismissed($reviewerId, $adminNotes = null)
    {
        $this->update([
            'status' => 'dismissed',
            'reviewed_by' => $reviewerId,
            'admin_notes' => $adminNotes,
            'reviewed_at' => now()
        ]);
    }

    /**
     * Get report types
     */
    public static function getReportTypes()
    {
        return [
            'spam' => 'Spam',
            'inappropriate' => 'Inappropriate Content',
            'copyright' => 'Copyright Violation',
            'harassment' => 'Harassment',
            'misinformation' => 'Misinformation',
            'other' => 'Other'
        ];
    }
}
