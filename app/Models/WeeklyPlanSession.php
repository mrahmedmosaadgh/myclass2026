<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyPlanSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'weekly_plan_id',
        'session_index',
        'period_code',
        'type',
        'title',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'session_index' => 'integer',
        'type' => 'string',
        'data' => 'array',
    ];

    /**
     * Get the weekly plan that owns the session.
     */
    public function weeklyPlan()
    {
        return $this->belongsTo(WeeklyPlan::class);
    }

    /**
     * Get the period code components.
     *
     * @return array
     */
    public function getPeriodCodeComponents(): array
    {
        $parts = explode('.', $this->period_code);
        return [
            'academic_year' => $parts[0] ?? null,
            'semester' => $parts[1] ?? null,
            'week' => $parts[2] ?? null,
            'day' => $parts[3] ?? null,
        ];
    }

    /**
     * Check if session is of a specific type.
     */
    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    /**
     * Get data value by key.
     */
    public function getDataValue(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set data value by key.
     */
    public function setDataValue(string $key, $value): void
    {
        $data = $this->data ?? [];
        $data[$key] = $value;
        $this->data = $data;
    }
}