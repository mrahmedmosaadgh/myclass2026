<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentBehaviorsPointAction extends Model
{
    protected $table = 'student_behaviors_point_actions';

    protected $fillable = [
        'student_behaviors_id',
        'reason_id',
        'value',
        'action_type',
        'note',
        'canceled',
        'canceled_by',
        'canceled_at',
        'cancel_reason',
        'created_by',
    ];

    protected $casts = [
        'value' => 'integer',
        'canceled' => 'boolean',
        'canceled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function studentBehavior(): BelongsTo
    {
        return $this->belongsTo(StudentBehavior::class, 'student_behaviors_id');
    }

    public function behavior(): BelongsTo
    {
        return $this->belongsTo(Behavior::class, 'reason_id');
    }

    public function canceledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
