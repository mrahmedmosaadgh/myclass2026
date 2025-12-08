<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * User Context API Controller
 *
 * Handles API endpoints for segmented user context with offline-first caching.
 * Provides individual segment refresh endpoints and context updates.
 *
 * @author Education Management System
 * @version 1.0.0
 */
class UserContextController extends Controller
{
    protected $inertiaHandler;

    public function __construct()
    {
        $this->inertiaHandler = new HandleInertiaRequests();
    }

    /**
     * Get all user context segments
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllContext(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $context = $this->buildUserContext($user);

            return response()->json([
                'success' => true,
                'data' => $context,
                'meta' => [
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                    'expires_at' => now()->addDays(7)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user context: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user context',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get user profile segment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $profile = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'user_role' => $user->role,
            ];

            return response()->json([
                'success' => true,
                'data' => $profile,
                'meta' => [
                    'segment' => 'profile',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                    'expires_at' => now()->addDays(7)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user profile: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user profile',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get user permissions segment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPermissions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $permissions = [
                'roles' => $user->getRoleNames(),
            ];

            return response()->json([
                'success' => true,
                'data' => $permissions,
                'meta' => [
                    'segment' => 'permissions',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                    'expires_at' => now()->addDays(7)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user permissions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user permissions',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get user school segment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSchool(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $schools = $this->inertiaHandler->getUserSchool($user);

            $school = [
                'school' => $schools,
                'schools' => $schools,
            ];

            return response()->json([
                'success' => true,
                'data' => $school,
                'meta' => [
                    'segment' => 'school',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                    'expires_at' => now()->addDays(7)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user school: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user school',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get user classroom segment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getClassroom(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $classroom = [
                'teacher' => $this->inertiaHandler->getUserTeacher($user),
                'classroom' => $this->inertiaHandler->getUserClassroom($user),
            ];

            return response()->json([
                'success' => true,
                'data' => $classroom,
                'meta' => [
                    'segment' => 'classroom',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                    'expires_at' => now()->addDays(7)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user classroom: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user classroom',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get user schedule segment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSchedule(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $schedule = [
                'schedule' => $this->inertiaHandler->getUserSchedule($user),
            ];

            return response()->json([
                'success' => true,
                'data' => $schedule,
                'meta' => [
                    'segment' => 'schedule',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                    'expires_at' => now()->addDays(7)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user schedule: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user schedule',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get multiple segments at once
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSegments(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $segments = $request->input('segments', ['profile', 'permissions', 'school', 'classroom', 'schedule']);

            if (!is_array($segments)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Segments must be an array'
                ], 400);
            }

            $validSegments = ['profile', 'permissions', 'school', 'classroom', 'schedule'];
            $invalidSegments = array_diff($segments, $validSegments);

            if (!empty($invalidSegments)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid segments: ' . implode(', ', $invalidSegments),
                    'valid_segments' => $validSegments
                ], 400);
            }

            $context = $this->buildUserContext($user, $segments);

            return response()->json([
                'success' => true,
                'data' => $context,
                'meta' => [
                    'segments' => $segments,
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                    'expires_at' => now()->addDays(7)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user segments: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load user segments',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update user context (for when user data changes)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateContext(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Clear any cached user data to force refresh
            $cacheKeys = [
                "user_context_{$user->id}_profile",
                "user_context_{$user->id}_permissions",
                "user_context_{$user->id}_school",
                "user_context_{$user->id}_classroom",
                "user_context_{$user->id}_schedule",
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            // Get fresh context
            $context = $this->buildUserContext($user);

            return response()->json([
                'success' => true,
                'message' => 'User context updated successfully',
                'data' => $context,
                'meta' => [
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString(),
                    'expires_at' => now()->addDays(7)->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating user context: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user context',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Clear user context cache
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Clear all user context cache
            $cacheKeys = [
                "user_context_{$user->id}_profile",
                "user_context_{$user->id}_permissions",
                "user_context_{$user->id}_school",
                "user_context_{$user->id}_classroom",
                "user_context_{$user->id}_schedule",
            ];

            $clearedCount = 0;
            foreach ($cacheKeys as $key) {
                if (Cache::forget($key)) {
                    $clearedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'User context cache cleared successfully',
                'data' => [
                    'cleared_keys' => $clearedCount,
                    'total_keys' => count($cacheKeys)
                ],
                'meta' => [
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing user context cache: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear user context cache',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get cache status for user context
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCacheStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $segments = ['profile', 'permissions', 'school', 'classroom', 'schedule'];
            $cacheStatus = [];

            foreach ($segments as $segment) {
                $cacheKey = "user_context_{$user->id}_{$segment}";
                $cached = Cache::has($cacheKey);
                $ttl = $cached ? Cache::getStore()->getRedis()->ttl($cacheKey) : null;

                $cacheStatus[$segment] = [
                    'cached' => $cached,
                    'ttl_seconds' => $ttl,
                    'expires_at' => $ttl ? now()->addSeconds($ttl)->toISOString() : null
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'segments' => $cacheStatus,
                    'summary' => [
                        'total_segments' => count($segments),
                        'cached_segments' => count(array_filter($cacheStatus, fn($s) => $s['cached'])),
                        'cache_health' => round((count(array_filter($cacheStatus, fn($s) => $s['cached'])) / count($segments)) * 100, 2)
                    ]
                ],
                'meta' => [
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting cache status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Health check endpoint for user context system
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function healthCheck(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Test each segment
            $health = [];
            $segments = ['profile', 'permissions', 'school', 'classroom', 'schedule'];

            foreach ($segments as $segment) {
                try {
                    $startTime = microtime(true);

                    switch ($segment) {
                        case 'profile':
                            $data = [
                                'id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                                'user_role' => $user->role,
                            ];
                            break;
                        case 'permissions':
                            $data = ['roles' => $user->getRoleNames()];
                            break;
                        case 'school':
                            $data = $this->inertiaHandler->getUserSchool($user);
                            break;
                        case 'classroom':
                            $data = $this->inertiaHandler->getUserClassroom($user);
                            break;
                        case 'schedule':
                            $data = $this->inertiaHandler->getUserSchedule($user);
                            break;
                    }

                    $endTime = microtime(true);
                    $responseTime = round(($endTime - $startTime) * 1000, 2); // ms

                    $health[$segment] = [
                        'status' => 'healthy',
                        'response_time_ms' => $responseTime,
                        'data_available' => !empty($data),
                        'error' => null
                    ];

                } catch (\Exception $e) {
                    $health[$segment] = [
                        'status' => 'error',
                        'response_time_ms' => null,
                        'data_available' => false,
                        'error' => $e->getMessage()
                    ];
                }
            }

            $healthySegments = count(array_filter($health, fn($h) => $h['status'] === 'healthy'));
            $overallHealth = $healthySegments === count($segments) ? 'healthy' : 'degraded';

            return response()->json([
                'success' => true,
                'data' => [
                    'overall_status' => $overallHealth,
                    'segments' => $health,
                    'summary' => [
                        'total_segments' => count($segments),
                        'healthy_segments' => $healthySegments,
                        'health_percentage' => round(($healthySegments / count($segments)) * 100, 2)
                    ]
                ],
                'meta' => [
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in health check: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Health check failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Build user context data
     *
     * @param \App\Models\User $user
     * @param array $segments
     * @return array
     */
    private function buildUserContext($user, array $segments = null): array
    {
        $segments = $segments ?? ['profile', 'permissions', 'school', 'classroom', 'schedule'];
        $context = [];

        foreach ($segments as $segment) {
            switch ($segment) {
                case 'profile':
                    $context['user_profile'] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'user_role' => $user->role,
                    ];
                    break;

                case 'permissions':
                    $context['user_permissions'] = [
                        'roles' => $user->getRoleNames(),
                    ];
                    break;

                case 'school':
                    $schools = $this->inertiaHandler->getUserSchool($user);
                    $context['user_school'] = [
                        'school' => $schools,
                        'schools' => $schools,
                    ];
                    break;

                case 'classroom':
                    $context['user_classroom'] = [
                        'teacher' => $this->inertiaHandler->getUserTeacher($user),
                        'classroom' => $this->inertiaHandler->getUserClassroom($user),
                    ];
                    break;

                case 'schedule':
                    $context['user_schedule'] = [
                        'schedule' => $this->inertiaHandler->getUserSchedule($user),
                    ];
                    break;
            }
        }

        return $context;
    }
}
