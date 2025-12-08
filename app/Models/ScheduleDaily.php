<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleDaily extends Model
{
    protected $fillable = [
        'schedule_id',
        'teacher_substitute_id',
        'date',
        'data'
    ];

    protected $casts = [
        'data' => 'json',
        'date' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($scheduleDaily) {
            if ($scheduleDaily->schedule_id) {
                $schedule = Schedule::find($scheduleDaily->schedule_id);

                // Auto-populate from related Schedule
                $scheduleDaily->schedule_copy_id = $schedule->copy_id;
                $scheduleDaily->day = $schedule->day;
                $scheduleDaily->week = $schedule->week;
                $scheduleDaily->semester = $schedule->semester;
            }
        });
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function scheduleCopy()
    {
        return $this->belongsTo(ScheduleCopy::class, 'schedule_copy_id');
    }

    public function teacherSubstitute()
    {
        return $this->belongsTo(User::class, 'teacher_substitute_id');
    }
}


