<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function index()
    {
        return ProjectTask::orderBy('updated_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $task = ProjectTask::create($request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]));
        return response()->json($task, 201);
    }

    public function update(Request $request, ProjectTask $projectTask)
    {
        $projectTask->update($request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]));
        return response()->json($projectTask);
    }

    public function destroy(ProjectTask $projectTask)
    {
        $projectTask->delete();
        return response()->noContent();
    }
}
