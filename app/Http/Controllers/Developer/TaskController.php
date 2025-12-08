<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display the task management page.
     */
    public function index()
    {
        try {
            $tasks = Task::getTasksWithChildren(Auth::id());

            // Ensure $tasks is a collection, not null
            if (!$tasks) {
                $tasks = collect([]);
            }

            $classifications = Task::where('user_id', Auth::id())
                ->whereNotNull('classification')
                ->distinct()
                ->pluck('classification')
                ->toArray();

            // Convert tasks to array while preserving the nested structure
            $tasksArray = $this->convertTasksToArray($tasks);

            return Inertia::render('developer/ticktick/Index', [
                'tasks' => $tasksArray,
                'classifications' => $classifications,
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in TaskController@index: ' . $e->getMessage());

            // Return a fallback response
            return Inertia::render('developer/ticktick/Index', [
                'tasks' => [],
                'classifications' => [],
                'error' => 'An error occurred while loading tasks. Please try again later.'
            ]);
        }
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:tasks,id',
            'classification' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();

        // Set position to be the last in its parent group
        $position = Task::where('user_id', Auth::id())
            ->where('parent_id', $validated['parent_id'])
            ->max('position') + 1;
        $validated['position'] = $position;

        $task = Task::create($validated);

        // Load the parent task if it exists
        if ($task->parent_id) {
            $task->load('parent');
        }

        // Get all tasks with their children to return an updated tree
        $tasks = Task::getTasksWithChildren(Auth::id());

        // Convert tasks to array while preserving the nested structure
        $tasksArray = $this->convertTasksToArray($tasks);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
            'tasks' => $tasksArray,
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:tasks,id',
            'classification' => 'nullable|string|max:50',
            'due_date' => 'nullable|date',
            'position' => 'sometimes|integer',
            'completed_at' => 'nullable|date',
        ]);

        // Prevent circular references
        if (isset($validated['parent_id']) && $validated['parent_id'] == $task->id) {
            return response()->json(['message' => 'A task cannot be its own parent'], 422);
        }

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Toggle the completion status of a task.
     */
    public function toggleComplete(Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->completed_at = $task->completed_at ? null : now();
        $task->save();

        return response()->json([
            'message' => $task->completed_at ? 'Task marked as completed' : 'Task marked as incomplete',
            'task' => $task,
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task)
    {
        // Check if the task belongs to the authenticated user
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Reorder tasks.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.position' => 'required|integer',
            'tasks.*.parent_id' => 'nullable|exists:tasks,id',
        ]);

        foreach ($validated['tasks'] as $taskData) {
            $task = Task::find($taskData['id']);

            // Check if the task belongs to the authenticated user
            if ($task->user_id !== Auth::id()) {
                continue;
            }

            $task->update([
                'position' => $taskData['position'],
                'parent_id' => $taskData['parent_id'],
            ]);
        }

        // Get the updated tasks with their children
        $tasks = Task::getTasksWithChildren(Auth::id());

        // Convert tasks to array while preserving the nested structure
        $tasksArray = $this->convertTasksToArray($tasks);

        return response()->json([
            'message' => 'Tasks reordered successfully',
            'tasks' => $tasksArray
        ]);
    }

    /**
     * Get all tasks for the authenticated user (API endpoint for refreshing tasks)
     */
    public function getTasks()
    {
        try {
            $tasks = Task::getTasksWithChildren(Auth::id());

            // Ensure $tasks is a collection, not null
            if (!$tasks) {
                $tasks = collect([]);
            }

            // Convert tasks to array while preserving the nested structure
            $tasksArray = $this->convertTasksToArray($tasks);

            return response()->json([
                'tasks' => $tasksArray
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in TaskController@getTasks: ' . $e->getMessage());

            // Return an error response
            return response()->json([
                'error' => 'An error occurred while loading tasks. Please try again later.'
            ], 500);
        }
    }

    /**
     * Create multiple tasks at once
     */
    public function batchStore(Request $request)
    {
        try {
            $validated = $request->validate([
                'tasks' => 'required|array',
                'tasks.*.title' => 'required|string|max:255',
                'tasks.*.description' => 'nullable|string',
                'tasks.*.parent_id' => 'nullable|exists:tasks,id',
                'tasks.*.classification' => 'nullable|string|max:50',
                'tasks.*.due_date' => 'nullable|date',
            ]);

            $createdTasks = [];

            foreach ($validated['tasks'] as $taskData) {
                $taskData['user_id'] = Auth::id();

                // Set position to be the last in its parent group
                $position = Task::where('user_id', Auth::id())
                    ->where('parent_id', $taskData['parent_id'] ?? null)
                    ->max('position') + 1;
                $taskData['position'] = $position;

                $createdTasks[] = Task::create($taskData);
            }

            // Get all tasks with their children to return an updated tree
            $tasks = Task::getTasksWithChildren(Auth::id());

            // Convert tasks to array while preserving the nested structure
            $tasksArray = $this->convertTasksToArray($tasks);

            return response()->json([
                'message' => count($createdTasks) . ' tasks created successfully',
                'tasks' => $tasksArray,
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in TaskController@batchStore: ' . $e->getMessage());

            // Return an error response
            return response()->json([
                'error' => 'An error occurred while creating tasks. Please try again later.'
            ], 500);
        }
    }

    /**
     * Delete multiple tasks at once
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validated = $request->validate([
                'task_ids' => 'required|array',
                'task_ids.*' => 'required|exists:tasks,id',
            ]);

            $userId = Auth::id();
            $deletedCount = 0;

            foreach ($validated['task_ids'] as $taskId) {
                $task = Task::find($taskId);

                // Check if the task belongs to the authenticated user
                if ($task && $task->user_id === $userId) {
                    $task->delete();
                    $deletedCount++;
                }
            }

            // Get all tasks with their children to return an updated tree
            $tasks = Task::getTasksWithChildren($userId);

            // Convert tasks to array while preserving the nested structure
            $tasksArray = $this->convertTasksToArray($tasks);

            return response()->json([
                'message' => $deletedCount . ' tasks deleted successfully',
                'tasks' => $tasksArray,
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in TaskController@bulkDelete: ' . $e->getMessage());

            // Return an error response
            return response()->json([
                'error' => 'An error occurred while deleting tasks. Please try again later.'
            ], 500);
        }
    }

    /**
     * Convert tasks collection to array while preserving the nested structure
     */
    private function convertTasksToArray($tasks)
    {
        $result = [];

        // Sort tasks by position to ensure correct order
        $sortedTasks = $tasks->sortBy('position');

        foreach ($sortedTasks as $task) {
            $taskArray = $task->toArray();

            // If the task has children, recursively convert them too
            if ($task->children && $task->children->count() > 0) {
                // Sort children by position
                $sortedChildren = $task->children->sortBy('position');
                $taskArray['children'] = $this->convertTasksToArray($sortedChildren);
            }

            $result[] = $taskArray;
        }

        return $result;
    }
}
