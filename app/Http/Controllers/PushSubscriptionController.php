<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\PushSubscription;
use App\Notifications\WebPushNotification;

class PushSubscriptionController extends Controller
{
    public function getVapidPublicKey()
    {
        return response(config('webpush.vapid.public_key'))
            ->header('Content-Type', 'text/plain');
    }

    public function store(Request $request)
    {
        try {
            // Log the request data for debugging
            Log::info('Push subscription request:', [
                'endpoint' => $request->endpoint,
                'keys' => $request->keys,
                'user' => Auth::check() ? Auth::id() : 'not authenticated'
            ]);

            $validated = $request->validate([
                'endpoint' => 'required|string|max:500',
                'keys.auth' => 'required|string|max:100',
                'keys.p256dh' => 'required|string|max:200',
            ]);

            // Make sure user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get the authenticated user
            $user = Auth::user();

            try {
                $subscription = PushSubscription::updateOrCreate(
                    [
                        'endpoint' => $request->endpoint,
                        'subscribable_type' => get_class($user),
                        'subscribable_id' => $user->id,
                    ],
                    [
                        'public_key' => $request->keys['p256dh'],
                        'auth_token' => $request->keys['auth'],
                    ]
                );

                Log::info('Push subscription created:', [
                    'id' => $subscription->id,
                    'endpoint' => $subscription->endpoint
                ]);

                // Send a test notification to confirm subscription
                $user->notify(new WebPushNotification(
                    'Push Notifications Enabled',
                    'You will now receive notifications from our application.',
                    route('dashboard')
                ));

                return response()->json([
                    'success' => true,
                    'message' => 'Push subscription saved successfully'
                ]);
            } catch (\Exception $e) {
                Log::error('Error creating push subscription:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Push subscription validation failed:', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Push subscription error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save push subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'endpoint' => 'required|string|max:500'
            ]);

            // Make sure user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $user = Auth::user();

            Log::info('Unsubscribing from push notifications:', [
                'endpoint' => $request->endpoint,
                'user_id' => $user->id
            ]);

            $deleted = PushSubscription::where([
                'endpoint' => $request->endpoint,
                'subscribable_type' => get_class($user),
                'subscribable_id' => $user->id,
            ])->delete();

            return response()->json([
                'success' => true,
                'message' => 'Push subscription removed successfully',
                'deleted' => (bool)$deleted
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Push subscription unsubscribe validation failed:', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Push subscription unsubscribe error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove push subscription: ' . $e->getMessage()
            ], 500);
        }
    }
}
