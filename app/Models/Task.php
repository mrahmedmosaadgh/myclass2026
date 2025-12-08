<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'parent_id',
        'classification',
        'due_date',
        'completed_at',
        'position',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent task.
     */
    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /**
     * Get the child tasks.
     */
    public function children()
    {
        return $this->hasMany(Task::class, 'parent_id')->orderBy('position');
    }

    /**
     * Get all pomodoro sessions for this task.
     */
    public function pomodoroSessions()
    {
        return $this->hasMany(PomodoroSession::class);
    }

    /**
     * Scope a query to only include root tasks (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Scope a query to only include incomplete tasks.
     */
    public function scopeIncomplete($query)
    {
        return $query->whereNull('completed_at');
    }

    /**
     * Scope a query to filter by classification.
     */
    public function scopeClassification($query, $classification)
    {
        return $query->where('classification', $classification);
    }

    /**
     * Check if the task is completed.
     */
    public function isCompleted()
    {
        return $this->completed_at !== null;
    }

    /**
     * Get all tasks with their children recursively.
     */
    public static function getTasksWithChildren($userId)
    {
        // First get all tasks for this user
        $allTasks = self::where('user_id', $userId)
            ->orderBy('position')
            ->get();

        // Then build the tree structure
        $rootTasks = $allTasks->whereNull('parent_id')->sortBy('position');

        // Add children to each root task
        foreach ($rootTasks as $rootTask) {
            self::addChildrenToTask($rootTask, $allTasks);
        }

        return $rootTasks;
    }

    /**
     * Recursively add children to a task
     */
    private static function addChildrenToTask($task, $allTasks)
    {
        $children = $allTasks->where('parent_id', $task->id)->sortBy('position');
        $task->setRelation('children', $children);

        foreach ($children as $child) {
            self::addChildrenToTask($child, $allTasks);
        }
    }
}
