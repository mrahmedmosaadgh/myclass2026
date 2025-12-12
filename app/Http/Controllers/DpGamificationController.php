<?php

namespace App\Http\Controllers;

use App\Models\DpReward;
use Illuminate\Http\Request;

class DpGamificationController extends Controller
{
    public function index()
    {
        $points = DpReward::where('user_id', auth()->id())->sum('points');
        $badges = DpReward::where('user_id', auth()->id())->whereNotNull('badge')->pluck('badge');
        
        return response()->json([
            'points' => $points,
            'badges' => $badges,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'points' => 'required|integer',
            'badge' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $request->user()->dpRewards()->create($validated);

        return response()->json(['success' => true]);
    }
}
