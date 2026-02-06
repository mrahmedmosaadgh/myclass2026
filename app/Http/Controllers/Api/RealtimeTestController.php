<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RealtimeNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Realtime System Test Controller
 * 
 * Provides endpoints for testing the realtime notification system.
 * All notifications are ephemeral - no database storage.
 */
class RealtimeTestController extends Controller
{
    protected RealtimeNotificationService $realtimeService;

    public function __construct(RealtimeNotificationService $realtimeService)
    {
        $this->realtimeService = $realtimeService;
    }

    /**
     * Send a public broadcast to all users
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $result = $this->realtimeService->notifyWithDetails('PUBLIC_BROADCAST', [
            'message' => $request->message,
            'sender' => auth()->user()->name ?? 'System',
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'details' => $result['details']
        ]);
    }

    /**
     * Send a private notification to a specific user
     */
    public function privateNotification(Request $request)
    {
        $request->validate([
            'userId' => 'required|integer',
            'message' => 'required|string|max:500'
        ]);

        // Use specific channel construction to test raw notifyWithDetails or add wrappers if needed
        // Here we do raw to get details easily without refactoring service wrappers yet
        $result = $this->realtimeService->notifyWithDetails(
            "user.{$request->userId}",
            [
                'event' => 'PRIVATE_MESSAGE',
                'message' => $request->message,
                'from' => auth()->user()->name ?? 'System',
                'timestamp' => now()->toIso8601String()
            ]
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] ? 'Private notification sent' : $result['message'],
            'details' => $result['details'],
            'target_user' => $request->userId
        ]);
    }

    /**
     * Send a chat message to a specific room
     */
    public function chatMessage(Request $request)
    {
        $request->validate([
            'roomId' => 'required|string|max:100',
            'message' => 'required|string|max:1000',
            'sender' => 'nullable|string|max:100'
        ]);

        $success = $this->realtimeService->notifyGroup(
            'chat',
            $request->roomId,
            'NEW_MESSAGE',
            [
                'message' => $request->message,
                'sender' => $request->sender ?? auth()->user()->name ?? 'Anonymous',
                'timestamp' => now()->toIso8601String()
            ]
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Chat message sent' : 'Failed to send message',
            'room' => $request->roomId
        ]);
    }

    /**
     * Submit a live question response
     */
    public function questionResponse(Request $request)
    {
        $request->validate([
            'questionId' => 'required|string|max:100',
            'answer' => 'required|string|max:1000',
            'userId' => 'required|integer'
        ]);

        $success = $this->realtimeService->notifyGroup(
            'question',
            $request->questionId,
            'NEW_RESPONSE',
            [
                'answer' => $request->answer,
                'userId' => $request->userId,
                'userName' => auth()->user()->name ?? "User {$request->userId}",
                'timestamp' => now()->toIso8601String()
            ]
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Response submitted' : 'Failed to submit response',
            'question' => $request->questionId
        ]);
    }

    /**
     * Get connection status
     */
    public function status()
    {
        $dbUrl = env('FIREBASE_DATABASE_URL') ?: env('VITE_FIREBASE_DATABASE_URL');
        $firebaseEnabled = !empty($dbUrl);
        
        return response()->json([
            'firebase_enabled' => $firebaseEnabled,
            'database_url' => $dbUrl,
            'timestamp' => now()->toIso8601String(),
            'server_time' => now()->timestamp
        ]);
    }

    /**
     * Test error handling
     */
    public function testError(Request $request)
    {
        $errorType = $request->input('type', 'general');

        switch ($errorType) {
            case 'invalid_channel':
                // Try to send to an invalid channel format
                $success = $this->realtimeService->notify('invalid..channel', [
                    'event' => 'TEST_ERROR'
                ]);
                break;

            case 'missing_data':
                // Try to send without required data
                $success = $this->realtimeService->notify('test.error', []);
                break;

            default:
                $success = false;
                break;
        }

        return response()->json([
            'success' => $success,
            'error_type' => $errorType,
            'message' => 'Error test completed'
        ]);
    }

    /**
     * Clear a specific channel (for testing)
     */
    public function clearChannel(Request $request)
    {
        $request->validate([
            'channel' => 'required|string|max:100'
        ]);

        $success = $this->realtimeService->clearNotification($request->channel);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Channel cleared' : 'Failed to clear channel',
            'channel' => $request->channel
        ]);
    }
}
