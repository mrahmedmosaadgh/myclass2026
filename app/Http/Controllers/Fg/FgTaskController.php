<?php

namespace App\Http\Controllers\Fg;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fg\FgTaskRequest;
use App\Models\Fg\FgTask;
use Illuminate\Http\Request;

class FgTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = FgTask::where('user_id', $request->user()->id)
            ->with('subTasks');

        // Optional filtering
        if ($request->has('status')) {
            $query->where('status', $request->input('status')); // Active/Inbox etc.
        }
        
        if ($request->has('domain_id')) {
            $query->where('domain_id', $request->input('domain_id'));
        }
        
        if ($request->has('is_today')) {
            $query->where('is_today', $request->boolean('is_today'));
        }

        $tasks = $query->orderBy('sort_order')->orderByDesc('created_at')->get();
            
        return response()->json($tasks);
    }

    public function store(FgTaskRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        
        // Max 5 active tasks logic
        if (isset($data['status']) && $data['status'] === 'active') {
            $activeCount = FgTask::where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->count();
                
            if ($activeCount >= 5) {
                return response()->json([
                    'message' => 'You can only have up to 5 active tasks at a time.'
                ], 422);
            }
        }
        
        if (isset($data['source']) && $data['source'] === 'quick_capture') {
            $data['status'] = 'inbox';
            $data['importance'] = 1;
        }
        
        $task = FgTask::create($data);
        $task->load('subTasks');
        
        return response()->json($task, 201);
    }

    public function show(Request $request, FgTask $task)
    {
        if ($task->user_id !== $request->user()->id) {
            abort(403);
        }
        $task->load('subTasks');
        return response()->json($task);
    }

    public function update(FgTaskRequest $request, FgTask $task)
    {
        if ($task->user_id !== $request->user()->id) {
            abort(403);
        }
        
        $data = $request->validated();
        
        // Enforce max 5 rule if transition to active
        if (isset($data['status']) && $data['status'] === 'active' && $task->status !== 'active') {
            $activeCount = FgTask::where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->count();
                
            if ($activeCount >= 5) {
                return response()->json([
                    'message' => 'You can only have up to 5 active tasks at a time.'
                ], 422);
            }
        }

        if (isset($data['status']) && $data['status'] === 'done' && $task->status !== 'done') {
            $data['completed_at'] = now();
        }
        
        $data['version'] = $task->version + 1;
        $task->update($data);
        
        $task->load('subTasks');
        return response()->json($task);
    }

    public function destroy(Request $request, FgTask $task)
    {
        if ($task->user_id !== $request->user()->id) abort(403);
        $task->delete();
        return response()->json(null, 204);
    }

    public function sync(Request $request)
    {
        // For Phase 8 offline sync placeholder
        return response()->json(['message' => 'Sync endpoint (v1.2) placeholder']);
    }
}
