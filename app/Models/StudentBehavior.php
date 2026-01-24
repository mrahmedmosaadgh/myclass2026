<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentBehavior extends Model
{
    use HasFactory;

    protected $table = 'student_behaviors';

    protected $fillable = [
        'school_id',
        'student_behaviors_mains_id',
        'student_id',
        'attend',
        'academic_tracker',
        'behavior_tracker',
        'logistics_tracker',
        'notes',
    ];

    protected $casts = [
        'attend' => 'boolean',
        'academic_tracker' => 'array',
        'behavior_tracker' => 'array',
        'logistics_tracker' => 'array',
    ];

    protected $appends = [
        'points_plus',
        'points_minus',
        'total_points',
    ];

    // Relationships
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function behaviorMain(): BelongsTo
    {
        return $this->belongsTo(StudentBehaviorsMain::class, 'student_behaviors_mains_id');
    }

    public function pointActions(): HasMany
    {
        return $this->hasMany(StudentBehaviorsPointAction::class, 'student_behaviors_id');
    }

    // Dynamic Calculations (optimized to use loaded relationship if available)
    public function getPointsPlusAttribute()
    {
        // 1. Check if value was manually set (e.g., by "Overall" filter in Controller)
        if (array_key_exists('points_plus', $this->attributes)) {
             return (int) $this->attributes['points_plus'];
        }

        // 2. If pointActions are already loaded, calculate from collection
        if ($this->relationLoaded('pointActions')) {
            return $this->pointActions
                ->where('canceled', false)
                ->where('value', '>', 0)
                ->sum('value');
        }
        
        // 3. Otherwise query database
        return $this->pointActions()
            ->where('canceled', false)
            ->where('value', '>', 0)
            ->sum('value');
    }

    public function getPointsMinusAttribute()
    {
        // 1. Check if value was manually set
        if (array_key_exists('points_minus', $this->attributes)) {
             return (int) $this->attributes['points_minus'];
        }

        // 2. If pointActions are already loaded, calculate from collection
        if ($this->relationLoaded('pointActions')) {
            return abs(
                $this->pointActions
                    ->where('canceled', false)
                    ->where('value', '<', 0)
                    ->sum('value')
            );
        }
        
        // 3. Otherwise query database
        return abs(
            $this->pointActions()
                ->where('canceled', false)
                ->where('value', '<', 0)
                ->sum('value')
        );
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->points_plus - $this->points_minus;
    }
}
