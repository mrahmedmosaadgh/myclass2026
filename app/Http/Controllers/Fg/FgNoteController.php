<?php

namespace App\Http\Controllers\Fg;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fg\FgNoteRequest;
use App\Models\Fg\FgNote;
use Illuminate\Http\Request;

class FgNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = FgNote::where('user_id', $request->user()->id);
        
        if ($request->has('domain_id')) {
            $query->where('domain_id', $request->input('domain_id'));
        }
        
        $notes = $query->orderByDesc('created_at')->get();
        return response()->json($notes);
    }

    public function store(FgNoteRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        
        $note = FgNote::create($data);
        return response()->json($note, 201);
    }

    public function show(Request $request, FgNote $note)
    {
        if ($note->user_id !== $request->user()->id) abort(403);
        return response()->json($note);
    }

    public function update(FgNoteRequest $request, FgNote $note)
    {
        if ($note->user_id !== $request->user()->id) abort(403);
        
        $data = $request->validated();
        $data['version'] = $note->version + 1;
        
        $note->update($data);
        return response()->json($note);
    }

    public function destroy(Request $request, FgNote $note)
    {
        if ($note->user_id !== $request->user()->id) abort(403);
        $note->delete();
        return response()->json(null, 204);
    }

    public function sync(Request $request)
    {
        // For Phase 8 offline sync placeholder
        return response()->json(['message' => 'Notes sync endpoint (v1.2) placeholder']);
    }
}
