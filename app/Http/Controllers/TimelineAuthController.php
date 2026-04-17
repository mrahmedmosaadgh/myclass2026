<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\TimelineUser;
use Tymon\JWTAuth\Facades\JWTAuth;

class TimelineAuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:timeline', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only(['email', 'password']);

        try {
            if (!$token = auth('timeline')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent'
            ], 401);
        }

        // Get or create timeline user profile
        $user = auth('timeline')->user();
        $timelineUser = TimelineUser::firstOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->name,
                'preferences' => json_encode([
                    'theme' => 'light',
                    'language' => 'en',
                    'timezone' => 'UTC'
                ]),
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]
        );

        // Update last login
        $timelineUser->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        return $this->respondWithToken($token, $user, $timelineUser);
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create timeline user profile
        $timelineUser = TimelineUser::create([
            'user_id' => $user->id,
            'display_name' => $request->name,
            'preferences' => json_encode([
                'theme' => 'light',
                'language' => 'en',
                'timezone' => 'UTC'
            ]),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);

        // Create user data directory
        $userPath = storage_path("app/timeline_users/{$user->id}");
        if (!is_dir($userPath)) {
            mkdir($userPath, 0755, true);
        }

        // Initialize user data files
        $this->initializeUserDataFiles($user->id);

        $token = auth('timeline')->login($user);

        return $this->respondWithToken($token, $user, $timelineUser);
    }

    /**
     * Get the authenticated User.
     */
    public function me()
    {
        $user = auth('timeline')->user();
        $timelineUser = TimelineUser::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'user' => $user,
            'timeline_user' => $timelineUser
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        auth('timeline')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        $token = auth('timeline')->refresh();
        $user = auth('timeline')->user();
        $timelineUser = TimelineUser::where('user_id', $user->id)->first();

        return $this->respondWithToken($token, $user, $timelineUser);
    }

    /**
     * Get user profile.
     */
    public function profile()
    {
        $user = auth('timeline')->user();
        $timelineUser = TimelineUser::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'user' => $user,
            'timeline_user' => $timelineUser
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth('timeline')->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'display_name' => 'sometimes|string|max:255',
            'preferences' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user name if provided
        if ($request->has('name')) {
            $user->update(['name' => $request->name]);
        }

        // Update timeline user profile
        $timelineUser = TimelineUser::where('user_id', $user->id)->first();
        $updateData = [];

        if ($request->has('display_name')) {
            $updateData['display_name'] = $request->display_name;
        }

        if ($request->has('preferences')) {
            $updateData['preferences'] = json_encode($request->preferences);
        }

        if (!empty($updateData)) {
            $timelineUser->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user,
            'timeline_user' => $timelineUser->fresh()
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth('timeline')->user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token, $user, $timelineUser)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('timeline')->factory()->getTTL() * 60,
            'user' => $user,
            'timeline_user' => $timelineUser
        ]);
    }

    /**
     * Initialize user data files.
     */
    protected function initializeUserDataFiles($userId)
    {
        $userPath = storage_path("app/timeline_users/{$userId}");
        
        // Create initial timeline data
        $timelineData = [
            'version' => '1.0',
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
            'data' => [
                'periods' => [],
                'events' => [],
                'settings' => [
                    'theme' => 'light',
                    'language' => 'en',
                    'timezone' => 'UTC',
                    'notifications' => true
                ]
            ]
        ];

        file_put_contents("{$userPath}/timeline_data.json", json_encode($timelineData, JSON_PRETTY_PRINT));

        // Create devices file
        $devicesData = [
            'version' => '1.0',
            'created_at' => now()->toISOString(),
            'devices' => []
        ];

        file_put_contents("{$userPath}/devices.json", json_encode($devicesData, JSON_PRETTY_PRINT));

        // Create sync log
        $syncLog = [
            'version' => '1.0',
            'created_at' => now()->toISOString(),
            'sync_history' => []
        ];

        file_put_contents("{$userPath}/sync_log.json", json_encode($syncLog, JSON_PRETTY_PRINT));
    }
}
