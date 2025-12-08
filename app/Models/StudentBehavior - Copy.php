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
        'points_plus',
        'points_minus',
        'notes',
    ];

    protected $casts = [
        'points_plus' => 'integer',
        'points_minus' => 'integer',
        'attend' => 'boolean',
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

    // Accessors for dynamic point calculations
    public function getPointsPlusAttribute()
    {
        return $this->pointActions()
            ->where('canceled', false)
            ->where('value', '>', 0)
            ->sum('value');
    }

    public function getPointsMinusAttribute()
    {
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
