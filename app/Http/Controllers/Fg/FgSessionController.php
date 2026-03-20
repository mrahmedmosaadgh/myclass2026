<?php

namespace App\Http\Controllers\Fg;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fg\FgSessionRequest;
use App\Models\Fg\FgSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FgSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = FgSession::where('user_id', $request->user()->id)->with('task');
        
        // Example filter: show only today's sessions for review
        if ($request->has('today')) {
            $query->whereDate('started_at', Carbon::today());
        }
        
        $sessions = $query->orderByDesc('started_at')->get();
        return response()->json($sessions);
    }

    public function store(FgSessionRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['started_at'] = now();
        $data['status'] = 'active'; // Always starts active
        
        // Ensure no other active session exists (frontend should also check this)
        FgSession::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->update([
                'status' => 'drifted',
                'ended_at' => now(), // Force close unhandled sessions
            ]);

        $session = FgSession::create($data);
        $session->load('task');
        
        return response()->json($session, 201);
    }

    public function show(Request $request, FgSession $session)
    {
        if ($session->user_id !== $request->user()->id) abort(403);
        $session->load('task');
        return response()->json($session);
    }

    public function update(FgSessionRequest $request, FgSession $session)
    {
        if ($session->user_id !== $request->user()->id) abort(403);
        
        $data = $request->validated();
        
        // If closing the session, calculate duration
        if (isset($data['status']) && $data['status'] !== 'active' && $session->status === 'active') {
            $data['ended_at'] = now();
            $data['duration_seconds'] = $data['ended_at']->diffInSeconds($session->started_at);
        }
        
        $data['version'] = $session->version + 1;
        $session->update($data);
        
        return response()->json($session);
    }
}
