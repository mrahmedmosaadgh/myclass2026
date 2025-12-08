<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BehaviorIncident extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'school_id',
        'student_id',
        'classroom_id',
        'created_by',
        'reported_by',
        'reviewed_by',
        'student_name',
        'grade',
        'student_grade_snapshot',
        'student_section_snapshot',
        'occurred_at',
        'period_code',
        'incident_type',
        'location',
        'behavior',
        'description',
        'motivation',
        'others_involved',
        'teacher_action',
        'admin_action',
        'primary_behavior_code',
        'primary_location_code',
        'severity',
        'status',
        'follow_up_needed',
        'points_deducted',
        'points_awarded',
        'visible_to_parent',
        'parent_viewed_at',
        'parent_notified_at',
        'parent_notified_by',
        'critical_alert',
        'escalated_at',
        'attachments',
        'submitted_via',
        'device_ip',
        'school_year_id',
    ];

    protected $casts = [
        'incident_type' => 'array',
        'location' => 'array',
        'behavior' => 'array',
        'description' => 'array',
        'motivation' => 'array',
        'others_involved' => 'array',
        'teacher_action' => 'array',
        'admin_action' => 'array',
        'attachments' => 'array',
        'occurred_at' => 'datetime',
        'parent_viewed_at' => 'datetime',
        'parent_notified_at' => 'datetime',
        'escalated_at' => 'datetime',
        'follow_up_needed' => 'boolean',
        'visible_to_parent' => 'boolean',
        'critical_alert' => 'boolean',
        'points_deducted' => 'integer',
        'points_awarded' => 'integer',
        'grade' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID on creation
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function parentNotifiedBy()
    {
        return $this->belongsTo(User::class, 'parent_notified_by');
    }

    public function schoolYear()
    {
        return $this->belongsTo(AcademicYear::class, 'school_year_id');
    }

    // Scopes
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForClassroom($query, $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('occurred_at', [$startDate, $endDate]);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCriticalOnly($query)
    {
        return $query->where('critical_alert', true);
    }

    public function scopeNeedingFollowUp($query)
    {
        return $query->where('follow_up_needed', true);
    }

    public function scopeVisibleToParents($query)
    {
        return $query->where('visible_to_parent', true);
    }

    // Helper methods
    public function getNetPoints()
    {
        return $this->points_awarded - $this->points_deducted;
    }

    public function isCritical()
    {
        return $this->critical_alert === true;
    }

    public function isResolved()
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function markAsViewed()
    {
        if (!$this->parent_viewed_at) {
            $this->update(['parent_viewed_at' => now()]);
        }
    }

    public function escalate()
    {
        $this->update([
            'critical_alert' => true,
            'escalated_at' => now(),
        ]);
    }
}
