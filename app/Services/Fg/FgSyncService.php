<?php

namespace App\Services\Fg;

use App\Models\Fg\FgDomain;
use App\Models\Fg\FgTask;
use App\Models\Fg\FgSubTask;
use App\Models\Fg\FgNote;
use App\Models\Fg\FgSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FgSyncService
{
    /**
     * Returns the complete current state of focus grid for a user.
     */
    public function pullState($userId)
    {
        return [
            'domains' => FgDomain::where('user_id', $userId)->get(),
            'tasks' => FgTask::where('user_id', $userId)->get(),
            // Only pull subtasks for tasks this user owns
            'sub_tasks' => FgSubTask::whereHas('task', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->get(),
            'notes' => FgNote::where('user_id', $userId)->get(),
            'sessions' => FgSession::where('user_id', $userId)->get(),
        ];
    }

    /**
     * Processes pushed changes from IndexedDB.
     */
    public function pushChanges($userId, array $payload)
    {
        $syncedIds = [
            'domains' => [],
            'tasks' => [],
            'sub_tasks' => [],
            'notes' => [],
            'sessions' => []
        ];

        DB::transaction(function () use ($userId, $payload, &$syncedIds) {
            
            // 1. Process Domains
            if (isset($payload['domains']) && is_array($payload['domains'])) {
                foreach ($payload['domains'] as $item) {
                     $dbItem = FgDomain::withTrashed()->where('id', $item['id'])->where('user_id', $userId)->first();
                     if (!$dbItem && Str::startsWith($item['id'], 'local_')) {
                          // It's a brand new created offline
                          $newItem = FgDomain::create($item);
                          $syncedIds['domains'][] = $item['id']; // send back local ID to mark synced
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['domains'][] = $item['id'];
                     }
                }
            }
            
            // 2. Process Tasks
            if (isset($payload['tasks']) && is_array($payload['tasks'])) {
                foreach ($payload['tasks'] as $item) {
                     $dbItem = FgTask::withTrashed()->where('id', $item['id'])->where('user_id', $userId)->first();
                     if (!$dbItem && Str::startsWith($item['id'], 'local_')) {
                          $newItem = FgTask::create($item);
                          $syncedIds['tasks'][] = $item['id'];
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['tasks'][] = $item['id'];
                     }
                }
            }

            // 3. Process SubTasks
            if (isset($payload['sub_tasks']) && is_array($payload['sub_tasks'])) {
                foreach ($payload['sub_tasks'] as $item) {
                     $dbItem = FgSubTask::withTrashed()->where('id', $item['id'])->first(); // task_id check would be more secure
                     if (!$dbItem && Str::startsWith($item['id'], 'local_')) {
                          $newItem = FgSubTask::create($item);
                          $syncedIds['sub_tasks'][] = $item['id'];
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['sub_tasks'][] = $item['id'];
                     }
                }
            }
            
            // 4. Process Notes
            if (isset($payload['notes']) && is_array($payload['notes'])) {
                foreach ($payload['notes'] as $item) {
                     $dbItem = FgNote::withTrashed()->where('id', $item['id'])->where('user_id', $userId)->first();
                     if (!$dbItem && Str::startsWith($item['id'], 'local_')) {
                          $newItem = FgNote::create($item);
                          $syncedIds['notes'][] = $item['id'];
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['notes'][] = $item['id'];
                     }
                }
            }
            
            // 5. Process Sessions
            if (isset($payload['sessions']) && is_array($payload['sessions'])) {
                foreach ($payload['sessions'] as $item) {
                     $dbItem = FgSession::withTrashed()->where('id', $item['id'])->where('user_id', $userId)->first();
                     if (!$dbItem && Str::startsWith($item['id'], 'local_')) {
                          $newItem = FgSession::create($item);
                          $syncedIds['sessions'][] = $item['id'];
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['sessions'][] = $item['id'];
                     }
                }
            }

        });

        return $syncedIds;
    }
}
