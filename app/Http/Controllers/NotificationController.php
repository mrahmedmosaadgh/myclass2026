<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Notifications\WebPushNotification;
use App\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification as DbNotification;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get all notifications (read and unread)
        $notifications = $user->notifications()->paginate(15);

        // Check if this is an AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($notifications);
        }

        // Otherwise, return the Inertia view
        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Send a test notification to the authenticated user.
     */
    public function sendTestNotification(Request $request)
    {
        $user = Auth::user();

        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|in:database,push,both',
        ]);

        $title = $validated['title'];
        $body = $validated['body'];
        $type = $validated['type'];

        try {
            if ($type === 'database' || $type === 'both') {
                $user->notify(new DatabaseNotification($title, $body));
            }

            if ($type === 'push' || $type === 'both') {
                // Only send push notification if user has subscriptions
                if ($user->pushSubscriptions()->exists()) {
                    $user->notify(new WebPushNotification($title, $body));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a notification to multiple users.
     */
    public function sendToUsers(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'type' => 'required|in:database,push,both',
        ]);

        $title = $validated['title'];
        $body = $validated['body'];
        $userIds = $validated['user_ids'];
        $type = $validated['type'];

        try {
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                if ($type === 'database' || $type === 'both') {
                    $user->notify(new DatabaseNotification($title, $body));
                }

                if ($type === 'push' || $type === 'both') {
                    // Only send push notification if user has subscriptions
                    if ($user->pushSubscriptions()->exists()) {
                        $user->notify(new WebPushNotification($title, $body));
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifications sent to ' . count($users) . ' users'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications: ' . $e->getMessage()
            ], 500);
        }
    }
}
