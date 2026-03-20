<?php

namespace App\Http\Controllers\Fg;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Fg\FgSyncService;

class FgSyncController extends Controller
{
    protected $syncService;

    public function __construct(FgSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Pull all current active data for the user.
     * (Optimized version would take a timestamp 'since', but this returns the total state for v1.2)
     */
    public function pull(Request $request)
    {
        $userId = $request->user()->id;
        $state = $this->syncService->pullState($userId);
        
        return response()->json($state);
    }

    /**
     * Push localized pending changes to the server.
     */
    public function push(Request $request)
    {
        $userId = $request->user()->id;
        $payload = $request->all();
        
        $syncedIds = $this->syncService->pushChanges($userId, $payload);
        
        return response()->json([
            'message' => 'Sync processed successfully',
            'synced_ids' => $syncedIds
        ]);
    }
}
