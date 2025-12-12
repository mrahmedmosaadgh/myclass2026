<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MyProjectTask;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MyProjectTaskController extends Controller
{
    /**
     * Display a listing of the resource with filtering, sorting, and pagination.
     * Supports both tree view and flat view.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $viewType = $request->get('view', 'tree'); // 'tree' or 'flat'
            
            if ($viewType === 'tree') {
                return $this->getTreeView($request);
            } else {
                return $this->getFlatView($request);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tasks in tree structure.
     */
    private function getTreeView(Request $request): JsonResponse
    {
        $query = MyProjectTask::query();

        // Apply filters
        $query->byStatus($request->get('status'))
              ->byPriority($request->get('priority'))
              ->search($request->get('search'));

        // For tree view, get root tasks with their subtasks
        $tasks = $query->roots()
                      ->with(['subtasks' => function ($query) use ($request) {
                          $this->applyFiltersToSubtasks($query, $request);
                      }])
                      ->orderBy('sort_order')
                      ->get();

        // Convert to flattened structure for easier frontend handling
        $flattenedTasks = MyProjectTask::getFlattenedTree($tasks);

        return response()->json([
            'success' => true,
            'data' => $flattenedTasks,
            'view_type' => 'tree',
            'total_tasks' => $flattenedTasks->count(),
            'filters' => [
                'status' => $request->get('status'),
                'priority' => $request->get('priority'),
                'search' => $request->get('search'),
            ]
        ]);
    }

    /**
     * Get tasks in flat paginated structure.
     */
    private function getFlatView(Request $request): JsonResponse
    {
        $query = MyProjectTask::query();

        // Apply filters
        $query->byStatus($request->get('status'))
              ->byPriority($request->get('priority'))
              ->search($request->get('search'));

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        
        // Validate sort column
        $allowedSortColumns = ['title', 'status', 'priority', 'due_date', 'created_at', 'updated_at', 'sort_order'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Apply pagination
        $perPage = min($request->get('per_page', 10), 50); // Max 50 items per page
        $tasks = $query->with('parent')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tasks->items(),
            'view_type' => 'flat',
            'pagination' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'from' => $tasks->firstItem(),
                'to' => $tasks->lastItem(),
            ],
            'filters' => [
                'status' => $request->get('status'),
                'priority' => $request->get('priority'),
                'search' => $request->get('search'),
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ]
        ]);
    }

    /**
     * Apply filters to subtasks recursively.
     */
    private function applyFiltersToSubtasks($query, $request)
    {
        $query->byStatus($request->get('status'))
              ->byPriority($request->get('priority'))
              ->search($request->get('search'))
              ->orderBy('sort_order')
              ->with(['subtasks' => function ($subQuery) use ($request) {
                  $this->applyFiltersToSubtasks($subQuery, $request);
              }]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(MyProjectTask::$rules);

            $task = MyProjectTask::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $task = MyProjectTask::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $task
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $task = MyProjectTask::findOrFail($id);
            
            // Get only the fields that are present in the request and not null
            $updateData = [];
            $allowedFields = ['title', 'description', 'status', 'priority', 'due_date', 'parent_id', 'sort_order'];
            
            foreach ($allowedFields as $field) {
                if ($request->has($field)) {
                    $updateData[$field] = $request->input($field);
                }
            }
            
            // Build validation rules dynamically based on what's being updated
            $validationRules = [];
            
            if (array_key_exists('title', $updateData)) {
                $validationRules['title'] = 'required|string|max:255';
            }
            
            if (array_key_exists('description', $updateData)) {
                $validationRules['description'] = 'nullable|string';
            }
            
            if (array_key_exists('status', $updateData)) {
                $validationRules['status'] = 'required|in:pending,in_progress,completed';
            }
            
            if (array_key_exists('priority', $updateData)) {
                $validationRules['priority'] = 'required|in:low,medium,high';
            }
            
            if (array_key_exists('due_date', $updateData)) {
                $validationRules['due_date'] = 'nullable|date';
            }
            
            if (array_key_exists('parent_id', $updateData)) {
                $validationRules['parent_id'] = 'nullable|exists:myproject_tasks,id';
            }
            
            if (array_key_exists('sort_order', $updateData)) {
                $validationRules['sort_order'] = 'nullable|integer|min:0';
            }
            
            // Validate only the fields being updated
            $validated = $request->validate($validationRules);

            $task->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => $task->fresh()
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $task = MyProjectTask::findOrFail($id);
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // HIERARCHICAL OPERATIONS
    // ========================================

    /**
     * Create a subtask under a parent task.
     */
    public function createSubtask(Request $request, string $parentId): JsonResponse
    {
        try {
            $parent = MyProjectTask::findOrFail($parentId);
            
            $rules = MyProjectTask::$rules;
            $rules['parent_id'] = 'nullable'; // Override parent_id validation
            
            $validated = $request->validate($rules);
            $validated['parent_id'] = $parent->id;
            
            // Auto-assign sort order
            $maxOrder = MyProjectTask::where('parent_id', $parent->id)->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;

            $subtask = MyProjectTask::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Subtask created successfully',
                'data' => $subtask->load('parent')
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parent task not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subtask',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move a task to a new parent or make it a root task.
     */
    public function moveTask(Request $request, string $id): JsonResponse
    {
        try {
            $task = MyProjectTask::findOrFail($id);
            
            $validated = $request->validate([
                'parent_id' => 'nullable|exists:myproject_tasks,id',
                'sort_order' => 'nullable|integer|min:0'
            ]);

            // Prevent circular references
            if ($validated['parent_id'] && $task->isAncestorOf($validated['parent_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot move task to its own descendant'
                ], 422);
            }

            $task->moveTo($validated['parent_id'], $validated['sort_order'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Task moved successfully',
                'data' => $task->fresh()->load('parent')
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move task',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder tasks within the same parent.
     */
    public function reorderTasks(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'nullable|exists:myproject_tasks,id',
                'task_ids' => 'required|array',
                'task_ids.*' => 'exists:myproject_tasks,id'
            ]);

            // Verify all tasks belong to the same parent
            $tasks = MyProjectTask::whereIn('id', $validated['task_ids'])
                                 ->where('parent_id', $validated['parent_id'])
                                 ->get();

            if ($tasks->count() !== count($validated['task_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some tasks do not belong to the specified parent'
                ], 422);
            }

            // Update sort orders
            foreach ($validated['task_ids'] as $index => $taskId) {
                MyProjectTask::where('id', $taskId)->update(['sort_order' => $index + 1]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Tasks reordered successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder tasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subtasks of a specific task.
     */
    public function getSubtasks(string $id): JsonResponse
    {
        try {
            $task = MyProjectTask::findOrFail($id);
            $subtasks = $task->subtasks()->orderBy('sort_order')->get();

            return response()->json([
                'success' => true,
                'data' => $subtasks,
                'parent' => $task
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subtasks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
