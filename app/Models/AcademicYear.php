<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'start_date',
        'end_date',
        'active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean'
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    public function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class, 'year_id');
    }

    protected static function booted()
    {
        static::creating(function ($academicYear) {
            if ($academicYear->active) {
                // Deactivate all other years for this school
                static::where('school_id', $academicYear->school_id)
                    ->update(['active' => false]);
            }
        });

        static::created(function ($academicYear) {
            // Semesters are created separately by the user via AI Setup or manually.
            // No auto-creation here.
        });

        static::updating(function ($academicYear) {
            if ($academicYear->isDirty('active') && $academicYear->active) {
                // Deactivate all other years for this school
                static::where('school_id', $academicYear->school_id)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['active' => false]);
            }
        });
    }
}
