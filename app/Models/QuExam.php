<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuExam extends Model
{
    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'exam_type',
        'custom_group',
        'duration_minutes',
        'total_marks',
        'passing_score',
        'max_attempts',
        'mark_calculation_method',
        'bloom_distribution',
        'created_by',
        'is_published',
        'start_date',
        'end_date',
        'publish_results_timing',
        'settings',
        'target_audience'
    ];

    protected $casts = [
        'bloom_distribution' => 'array',
        'is_published' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'passing_score' => 'decimal:2',
        'settings' => 'array',
        'target_audience' => 'array',
    ];

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->belongsToMany(QuQuestion::class, 'qu_exam_questions', 'qu_exam_id', 'qu_question_id')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('qu_exam_questions.order');
    }

    public function attempts()
    {
        return $this->hasMany(QuAttempt::class, 'qu_exam_id');
    }

    // Helper methods
    public function isAvailable()
    {
        $now = now();
        
        // If no scheduling, always available
        if (!$this->start_date && !$this->end_date) {
            return $this->is_published;
        }
        
        // Check if within scheduled time
        $afterStart = !$this->start_date || $now->gte($this->start_date);
        $beforeEnd = !$this->end_date || $now->lte($this->end_date);
        
        return $this->is_published && $afterStart && $beforeEnd;
    }

    public function hasUnlimitedAttempts()
    {
        return is_null($this->max_attempts);
    }

    public function getRemainingAttempts($userId)
    {
        if ($this->hasUnlimitedAttempts()) {
            return null; // Unlimited
        }
        
        $attemptCount = $this->attempts()->where('user_id', $userId)->count();
        return max(0, $this->max_attempts - $attemptCount);
    }

    public function getStatus()
    {
        if (!$this->is_published) {
            return 'draft';
        }
        
        $now = now();
        
        if ($this->start_date && $now->lt($this->start_date)) {
            return 'upcoming';
        }
        
        if ($this->end_date && $now->gt($this->end_date)) {
            return 'ended';
        }
        
        return 'active';
    }
    /**
     * Scope a query to only include exams visible to a given user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\User  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // Creator always sees their exams
            $q->where('created_by', $user->id)
            
            // Public exams (target_audience is NULL)
              ->orWhereNull('target_audience')
              
            // Explicit User ID
              ->orWhereRaw("JSON_CONTAINS(JSON_EXTRACT(target_audience, '$.user_ids'), ?)", [$user->id])
              
            // Role Logic
              ->orWhere(function ($subQ) use ($user) {
                  // Check if user has a role property or relation.
                  // Using 'role' column for now as per plan assumption, verifying next.
                  $userRole = $user->role ?? null;
                  
                  if ($userRole) {
                      $subQ->whereRaw("JSON_CONTAINS(JSON_EXTRACT(target_audience, '$.roles'), JSON_QUOTE(?))", [$userRole]);
                      
                      // Granular filters for students
                      if ($userRole === 'student') {
                          $student = $user->student ?? null;
                          if ($student) {
                              $gradeId = $student->grade_id;
                              $classroomId = $student->classroom_id;
                              
                              $subQ->where(function ($filterQ) use ($gradeId, $classroomId) {
                                 $filterQ->whereRaw("(JSON_EXTRACT(target_audience, '$.grade_ids') IS NULL OR JSON_CONTAINS(JSON_EXTRACT(target_audience, '$.grade_ids'), ?))", [$gradeId])
                                         ->whereRaw("(JSON_EXTRACT(target_audience, '$.classroom_ids') IS NULL OR JSON_CONTAINS(JSON_EXTRACT(target_audience, '$.classroom_ids'), ?))", [$classroomId]);
                              });
                          }
                      }
                  }
              });
        });
    }
}
