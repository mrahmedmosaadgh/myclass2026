<?php

namespace App\Http\Controllers\Fg;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fg\FgSubTaskRequest;
use App\Models\Fg\FgSubTask;
use App\Models\Fg\FgTask;
use Illuminate\Http\Request;

class FgSubTaskController extends Controller
{
    public function index(Request $request)
    {
        $taskId = $request->input('task_id');
        $query = FgSubTask::query();
        
        if ($taskId) {
            $task = FgTask::findOrFail($taskId);
            if ($task->user_id !== $request->user()->id) abort(403);
            $query->where('task_id', $taskId);
        } else {
            // Need to join to enforce user isolation if querying all
            $query->whereHas('task', function($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }
        
        return response()->json($query->orderBy('sort_order')->get());
    }

    public function store(FgSubTaskRequest $request)
    {
        $data = $request->validated();
        
        $task = FgTask::findOrFail($data['task_id']);
        if ($task->user_id !== $request->user()->id) abort(403);
        
        $subTask = FgSubTask::create($data);
        
        return response()->json($subTask, 201);
    }

    public function show(Request $request, FgSubTask $subtask)
    {
        if ($subtask->task->user_id !== $request->user()->id) abort(403);
        return response()->json($subtask);
    }

    public function update(FgSubTaskRequest $request, FgSubTask $subtask)
    {
        if ($subtask->task->user_id !== $request->user()->id) abort(403);
        
        $data = $request->validated();
        $data['version'] = $subtask->version + 1;
        
        $subtask->update($data);
        return response()->json($subtask);
    }

    public function destroy(Request $request, FgSubTask $subtask)
    {
        if ($subtask->task->user_id !== $request->user()->id) abort(403);
        $subtask->delete();
        return response()->json(null, 204);
    }
}
