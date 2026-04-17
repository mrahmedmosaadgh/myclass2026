<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TimelineDataController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:timeline');
    }

    /**
     * Get user's timeline data.
     */
    public function getUserData(Request $request)
    {
        $user = auth('timeline')->user();
        $userId = $user->id;
        
        $userPath = "timeline_users/{$userId}";
        
        try {
            // Check if file exists
            if (!Storage::disk('local')->exists("{$userPath}/timeline_data.json")) {
                return response()->json([
                    'success' => false,
                    'message' => 'User data not found'
                ], 404);
            }

            $data = json_decode(Storage::disk('local')->get("{$userPath}/timeline_data.json"), true);
            
            // Add sync metadata
            $data['sync_metadata'] = [
                'last_sync_at' => Carbon::now()->toISOString(),
                'device_id' => $request->header('X-Device-ID', 'unknown'),
                'sync_version' => $this->getSyncVersion($userId)
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load user data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save user's timeline data.
     */
    public function saveUserData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'version' => 'sometimes|string',
            'client_timestamp' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('timeline')->user();
        $userId = $user->id;
        $userPath = "timeline_users/{$userId}";

        try {
            // Get current data for version comparison
            $currentData = [];
            if (Storage::disk('local')->exists("{$userPath}/timeline_data.json")) {
                $currentData = json_decode(Storage::disk('local')->get("{$userPath}/timeline_data.json"), true);
            }

            // Prepare new data
            $newData = [
                'version' => $request->input('version', '1.0'),
                'created_at' => $currentData['created_at'] ?? Carbon::now()->toISOString(),
                'updated_at' => Carbon::now()->toISOString(),
                'client_timestamp' => $request->input('client_timestamp', Carbon::now()->toISOString()),
                'data' => $request->input('data')
            ];

            // Save to file
            Storage::disk('local')->put("{$userPath}/timeline_data.json", json_encode($newData, JSON_PRETTY_PRINT));

            // Log sync
            $this->logSync($userId, 'save', $request->header('X-Device-ID', 'unknown'));

            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully',
                'version' => $newData['version'],
                'updated_at' => $newData['updated_at']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize data between devices.
     */
    public function syncData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_version' => 'required|string',
            'client_timestamp' => 'required|string',
            'device_id' => 'required|string',
            'sync_data' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('timeline')->user();
        $userId = $user->id;
        $userPath = "timeline_users/{$userId}";
        $deviceId = $request->input('device_id');

        try {
            // Register/update device
            $this->registerOrUpdateDevice($userId, $deviceId, $request);

            // Get server data
            $serverData = [];
            if (Storage::disk('local')->exists("{$userPath}/timeline_data.json")) {
                $serverData = json_decode(Storage::disk('local')->get("{$userPath}/timeline_data.json"), true);
            }

            $serverVersion = $serverData['version'] ?? '0.0';
            $clientVersion = $request->input('client_version');

            // Determine sync direction
            $syncResult = [
                'action' => 'no_sync_needed',
                'server_data' => null,
                'client_data_accepted' => false
            ];

            // Simple version comparison (in production, use more sophisticated conflict resolution)
            if (version_compare($clientVersion, $serverVersion, '>')) {
                // Client has newer data
                if ($request->has('sync_data')) {
                    $newData = [
                        'version' => $clientVersion,
                        'created_at' => $serverData['created_at'] ?? Carbon::now()->toISOString(),
                        'updated_at' => Carbon::now()->toISOString(),
                        'client_timestamp' => $request->input('client_timestamp'),
                        'data' => $request->input('sync_data')
                    ];

                    Storage::disk('local')->put("{$userPath}/timeline_data.json", json_encode($newData, JSON_PRETTY_PRINT));
                    
                    $syncResult['action'] = 'client_to_server';
                    $syncResult['client_data_accepted'] = true;
                }
            } elseif (version_compare($clientVersion, $serverVersion, '<')) {
                // Server has newer data
                $syncResult['action'] = 'server_to_client';
                $syncResult['server_data'] = $serverData;
            }

            // Log sync
            $this->logSync($userId, $syncResult['action'], $deviceId);

            return response()->json([
                'success' => true,
                'sync_result' => $syncResult,
                'server_version' => $serverVersion,
                'sync_timestamp' => Carbon::now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user settings.
     */
    public function getUserSettings()
    {
        $user = auth('timeline')->user();
        $timelineUser = \App\Models\TimelineUser::where('user_id', $user->id)->first();

        $preferences = [];
        if ($timelineUser && $timelineUser->preferences) {
            $preferences = json_decode($timelineUser->preferences, true);
        }

        return response()->json([
            'success' => true,
            'settings' => $preferences
        ]);
    }

    /**
     * Save user settings.
     */
    public function saveUserSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('timeline')->user();
        $timelineUser = \App\Models\TimelineUser::where('user_id', $user->id)->first();

        if ($timelineUser) {
            $timelineUser->update([
                'preferences' => json_encode($request->input('settings'))
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully'
        ]);
    }

    /**
     * Register a new device for sync.
     */
    public function registerDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'device_name' => 'required|string',
            'device_type' => 'required|string|in:desktop,mobile,tablet'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('timeline')->user();
        $this->registerOrUpdateDevice($user->id, $request->input('device_id'), $request);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully'
        ]);
    }

    /**
     * Get user's registered devices.
     */
    public function getUserDevices()
    {
        $user = auth('timeline')->user();
        $userId = $user->id;
        $userPath = "timeline_users/{$userId}";

        try {
            if (!Storage::disk('local')->exists("{$userPath}/devices.json")) {
                return response()->json([
                    'success' => true,
                    'devices' => []
                ]);
            }

            $devicesData = json_decode(Storage::disk('local')->get("{$userPath}/devices.json"), true);

            return response()->json([
                'success' => true,
                'devices' => $devicesData['devices'] ?? []
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load devices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a device.
     */
    public function removeDevice($deviceId)
    {
        $user = auth('timeline')->user();
        $userId = $user->id;
        $userPath = "timeline_users/{$userId}";

        try {
            if (!Storage::disk('local')->exists("{$userPath}/devices.json")) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found'
                ], 404);
            }

            $devicesData = json_decode(Storage::disk('local')->get("{$userPath}/devices.json"), true);
            
            // Remove device
            $devicesData['devices'] = array_filter($devicesData['devices'], function($device) use ($deviceId) {
                return $device['id'] !== $deviceId;
            });

            // Re-index array
            $devicesData['devices'] = array_values($devicesData['devices']);

            Storage::disk('local')->put("{$userPath}/devices.json", json_encode($devicesData, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'Device removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove device',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shared timeline.
     */
    public function getSharedTimeline($shareToken)
    {
        // This would implement sharing functionality
        return response()->json([
            'success' => false,
            'message' => 'Sharing not implemented yet'
        ], 501);
    }

    /**
     * Register or update a device.
     */
    protected function registerOrUpdateDevice($userId, $deviceId, $request)
    {
        $userPath = "timeline_users/{$userId}";
        
        // Create devices file if it doesn't exist
        if (!Storage::disk('local')->exists("{$userPath}/devices.json")) {
            $devicesData = [
                'version' => '1.0',
                'created_at' => Carbon::now()->toISOString(),
                'devices' => []
            ];
            Storage::disk('local')->put("{$userPath}/devices.json", json_encode($devicesData, JSON_PRETTY_PRINT));
        }

        $devicesData = json_decode(Storage::disk('local')->get("{$userPath}/devices.json"), true);
        
        // Check if device exists
        $deviceExists = false;
        foreach ($devicesData['devices'] as &$device) {
            if ($device['id'] === $deviceId) {
                $device['last_seen'] = Carbon::now()->toISOString();
                $device['ip_address'] = $request->ip();
                $device['user_agent'] = $request->userAgent();
                $deviceExists = true;
                break;
            }
        }

        // Add new device if it doesn't exist
        if (!$deviceExists) {
            $devicesData['devices'][] = [
                'id' => $deviceId,
                'name' => $request->input('device_name', 'Unknown Device'),
                'type' => $request->input('device_type', 'desktop'),
                'first_seen' => Carbon::now()->toISOString(),
                'last_seen' => Carbon::now()->toISOString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ];
        }

        Storage::disk('local')->put("{$userPath}/devices.json", json_encode($devicesData, JSON_PRETTY_PRINT));
    }

    /**
     * Log sync activity.
     */
    protected function logSync($userId, $action, $deviceId)
    {
        $userPath = "timeline_users/{$userId}";
        
        // Create sync log if it doesn't exist
        if (!Storage::disk('local')->exists("{$userPath}/sync_log.json")) {
            $syncLog = [
                'version' => '1.0',
                'created_at' => Carbon::now()->toISOString(),
                'sync_history' => []
            ];
            Storage::disk('local')->put("{$userPath}/sync_log.json", json_encode($syncLog, JSON_PRETTY_PRINT));
        }

        $syncLog = json_decode(Storage::disk('local')->get("{$userPath}/sync_log.json"), true);
        
        // Add new sync entry
        $syncLog['sync_history'][] = [
            'timestamp' => Carbon::now()->toISOString(),
            'action' => $action,
            'device_id' => $deviceId,
            'ip_address' => request()->ip()
        ];

        // Keep only last 100 entries
        if (count($syncLog['sync_history']) > 100) {
            $syncLog['sync_history'] = array_slice($syncLog['sync_history'], -100);
        }

        Storage::disk('local')->put("{$userPath}/sync_log.json", json_encode($syncLog, JSON_PRETTY_PRINT));
    }

    /**
     * Get sync version.
     */
    protected function getSyncVersion($userId)
    {
        $userPath = "timeline_users/{$userId}";
        
        if (Storage::disk('local')->exists("{$userPath}/timeline_data.json")) {
            $data = json_decode(Storage::disk('local')->get("{$userPath}/timeline_data.json"), true);
            return $data['version'] ?? '1.0';
        }

        return '1.0';
    }
}
