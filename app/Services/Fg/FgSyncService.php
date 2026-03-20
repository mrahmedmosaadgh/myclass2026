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
                     $id = $item['id'];
                     $isLocal = Str::startsWith($id, 'local_');
                     
                     $dbItem = $isLocal ? null : FgDomain::withTrashed()->where('id', $id)->where('user_id', $userId)->first();
                     
                     if (!$dbItem && $isLocal) {
                          unset($item['id']);
                          $item['user_id'] = $userId;
                          $newItem = FgDomain::create($item);
                          $syncedIds['domains'][$id] = $newItem->id; 
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['domains'][$id] = $dbItem->id;
                     }
                }
            }
            
            // 2. Process Tasks
            if (isset($payload['tasks']) && is_array($payload['tasks'])) {
                foreach ($payload['tasks'] as $item) {
                     $id = $item['id'];
                     $isLocal = Str::startsWith($id, 'local_');
                     
                     // If domain_id is local, we need to map it (but simple v1.2 assumes domains sync first or are already mapped)
                     // Actually, we should check if domain_id exists in our syncedIds map if it's local
                     if (isset($item['domain_id']) && isset($syncedIds['domains'][$item['domain_id']])) {
                         $item['domain_id'] = $syncedIds['domains'][$item['domain_id']];
                     }

                     $dbItem = $isLocal ? null : FgTask::withTrashed()->where('id', $id)->where('user_id', $userId)->first();
                     if (!$dbItem && $isLocal) {
                          unset($item['id']);
                          $item['user_id'] = $userId;
                          $newItem = FgTask::create($item);
                          $syncedIds['tasks'][$id] = $newItem->id;
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['tasks'][$id] = $dbItem->id;
                     }
                }
            }

            // 3. Process SubTasks
            if (isset($payload['sub_tasks']) && is_array($payload['sub_tasks'])) {
                foreach ($payload['sub_tasks'] as $item) {
                     $id = $item['id'];
                     $isLocal = Str::startsWith($id, 'local_');
                     
                     if (isset($item['task_id']) && isset($syncedIds['tasks'][$item['task_id']])) {
                         $item['task_id'] = $syncedIds['tasks'][$item['task_id']];
                     }

                     $dbItem = $isLocal ? null : FgSubTask::withTrashed()->where('id', $id)->first(); 
                     if (!$dbItem && $isLocal) {
                          unset($item['id']);
                          $newItem = FgSubTask::create($item);
                          $syncedIds['sub_tasks'][$id] = $newItem->id;
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['sub_tasks'][$id] = $dbItem->id;
                     }
                }
            }
            
            // 4. Process Notes
            if (isset($payload['notes']) && is_array($payload['notes'])) {
                foreach ($payload['notes'] as $item) {
                     $id = $item['id'];
                     $isLocal = Str::startsWith($id, 'local_');

                     if (isset($item['domain_id']) && isset($syncedIds['domains'][$item['domain_id']])) {
                         $item['domain_id'] = $syncedIds['domains'][$item['domain_id']];
                     }

                     $dbItem = $isLocal ? null : FgNote::withTrashed()->where('id', $id)->where('user_id', $userId)->first();
                     if (!$dbItem && $isLocal) {
                          unset($item['id']);
                          $item['user_id'] = $userId;
                          $newItem = FgNote::create($item);
                          $syncedIds['notes'][$id] = $newItem->id;
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['notes'][$id] = $dbItem->id;
                     }
                }
            }
            
            // 5. Process Sessions
            if (isset($payload['sessions']) && is_array($payload['sessions'])) {
                foreach ($payload['sessions'] as $item) {
                     $id = $item['id'];
                     $isLocal = Str::startsWith($id, 'local_');

                     if (isset($item['task_id']) && isset($syncedIds['tasks'][$item['task_id']])) {
                         $item['task_id'] = $syncedIds['tasks'][$item['task_id']];
                     }

                     $dbItem = $isLocal ? null : FgSession::withTrashed()->where('id', $id)->where('user_id', $userId)->first();
                     if (!$dbItem && $isLocal) {
                          unset($item['id']);
                          $item['user_id'] = $userId;
                          $newItem = FgSession::create($item);
                          $syncedIds['sessions'][$id] = $newItem->id;
                     } else if ($dbItem) {
                          $dbItem->update($item);
                          if (isset($item['deleted_at'])) $dbItem->delete();
                          $syncedIds['sessions'][$id] = $dbItem->id;
                     }
                }
            }

        });

        return $syncedIds;
    }
}
