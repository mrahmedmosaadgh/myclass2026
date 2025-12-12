<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyProjectTask extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'myproject_tasks';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'parent_id',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'due_date' => 'date',
        'status' => 'string',
        'priority' => 'string'
    ];

    /**
     * Validation rules for the model.
     */
    public static $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,completed',
        'priority' => 'required|in:low,medium,high',
        'due_date' => 'nullable|date|after_or_equal:today',
        'parent_id' => 'nullable|exists:myproject_tasks,id',
        'sort_order' => 'nullable|integer|min:0'
    ];

    /**
     * Get validation rules for updates (due_date can be in the past for existing tasks).
     */
    public static function getUpdateRules()
    {
        $rules = self::$rules;
        $rules['due_date'] = 'nullable|date';
        return $rules;
    }

    /**
     * Get validation rules for partial updates (only validate provided fields).
     */
    public static function getPartialUpdateRules(array $fields)
    {
        $allRules = self::getUpdateRules();
        $partialRules = [];
        
        foreach ($fields as $field) {
            if (array_key_exists($field, $allRules)) {
                $rule = $allRules[$field];
                
                // For partial updates, make required fields optional unless they have a value
                if (strpos($rule, 'required') === 0) {
                    $rule = str_replace('required|', 'nullable|', $rule);
                }
                
                $partialRules[$field] = $rule;
            }
        }
        
        return $partialRules;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        if ($priority) {
            return $query->where('priority', $priority);
        }
        return $query;
    }

    /**
     * Scope to search by title and description.
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        return $query;
    }

    // ========================================
    // HIERARCHICAL RELATIONSHIPS
    // ========================================

    /**
     * Get the parent task.
     */
    public function parent()
    {
        return $this->belongsTo(MyProjectTask::class, 'parent_id');
    }

    /**
     * Get all subtasks (children).
     */
    public function subtasks()
    {
        return $this->hasMany(MyProjectTask::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants (recursive subtasks).
     */
    public function descendants()
    {
        return $this->subtasks()->with('descendants');
    }

    /**
     * Get all ancestors (parent chain).
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }

    /**
     * Check if this task is a root task (no parent).
     */
    public function isRoot()
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this task has subtasks.
     */
    public function hasSubtasks()
    {
        return $this->subtasks()->exists();
    }

    /**
     * Get the depth level of this task in the hierarchy.
     */
    public function getDepthLevel()
    {
        return $this->ancestors()->count();
    }

    /**
     * Scope to get only root tasks (no parent).
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get tasks with their subtasks loaded.
     */
    public function scopeWithSubtasks($query)
    {
        return $query->with(['subtasks' => function ($query) {
            $query->orderBy('sort_order');
        }]);
    }

    /**
     * Scope to get the full tree structure.
     */
    public function scopeTree($query)
    {
        return $query->roots()->withSubtasks()->orderBy('sort_order');
    }

    /**
     * Get flattened tree structure for display.
     */
    public static function getFlattenedTree($tasks = null)
    {
        if ($tasks === null) {
            $tasks = self::tree()->get();
        }

        $flattened = collect();
        
        foreach ($tasks as $task) {
            self::flattenTask($task, $flattened, 0);
        }
        
        return $flattened;
    }

    /**
     * Recursively flatten a task and its subtasks.
     */
    private static function flattenTask($task, &$flattened, $depth)
    {
        $task->depth = $depth;
        $flattened->push($task);
        
        foreach ($task->subtasks as $subtask) {
            self::flattenTask($subtask, $flattened, $depth + 1);
        }
    }

    /**
     * Move task to a new parent.
     */
    public function moveTo($newParentId = null, $sortOrder = null)
    {
        // Prevent circular references
        if ($newParentId && $this->isAncestorOf($newParentId)) {
            throw new \InvalidArgumentException('Cannot move task to its own descendant');
        }

        $this->parent_id = $newParentId;
        
        if ($sortOrder !== null) {
            $this->sort_order = $sortOrder;
        } else {
            // Auto-assign sort order
            $maxOrder = self::where('parent_id', $newParentId)->max('sort_order') ?? 0;
            $this->sort_order = $maxOrder + 1;
        }
        
        $this->save();
    }

    /**
     * Check if this task is an ancestor of the given task ID.
     */
    public function isAncestorOf($taskId)
    {
        $descendants = $this->getAllDescendantIds();
        return $descendants->contains($taskId);
    }

    /**
     * Get all descendant IDs recursively.
     */
    public function getAllDescendantIds()
    {
        $descendants = collect();
        
        foreach ($this->subtasks as $subtask) {
            $descendants->push($subtask->id);
            $descendants = $descendants->merge($subtask->getAllDescendantIds());
        }
        
        return $descendants;
    }

    /**
     * Reorder subtasks.
     */
    public function reorderSubtasks(array $taskIds)
    {
        foreach ($taskIds as $index => $taskId) {
            self::where('id', $taskId)
                ->where('parent_id', $this->id)
                ->update(['sort_order' => $index + 1]);
        }
    }
}
