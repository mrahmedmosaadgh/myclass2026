<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'calendar_id',
        'title',
        'description',
        'type',
        'is_full_day',
        'start_time',
        'end_time',
        'location',
        'affected_schedules',
        'affects_all_schedules',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_full_day' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'affected_schedules' => 'array',
        'affects_all_schedules' => 'boolean'
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'updated_by');
    }

    public function periodActivities(): HasMany
    {
        return $this->hasMany(PeriodActivity::class, 'event_id');
    }
}
