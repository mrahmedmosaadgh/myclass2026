<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Unified Firebase Realtime Notification Service
 * 
 * This service handles ALL Firebase realtime notifications.
 * Controllers/Events should ONLY call this service, never Firebase directly.
 * 
 * Usage:
 *   app(RealtimeNotificationService::class)->notify('user.123', ['event' => 'NEW_MESSAGE']);
 */
class RealtimeNotificationService
{
    protected string $databaseUrl;

    public function __construct()
    {
        $url = env('FIREBASE_DATABASE_URL');
        
        // Fallback to VITE_ var if main one is missing (common config issue)
        if (empty($url)) {
            $url = env('VITE_FIREBASE_DATABASE_URL');
        }
        
        $this->databaseUrl = rtrim($url ?? '', '/');
    }

    /**
     * Send a signal to a specific channel (topic)
     * 
     * @param string $channel Format: 'user.{id}', 'class.{id}', 'system.all'
     * @param array $payload Signal data (NOT the actual content)
     * @return bool
     */
    /**
     * Send a signal with detailed response
     * 
     * @param string $channel
     * @param array $payload
     * @return array ['success' => bool, 'message' => string, 'details' => mixed]
     */
    public function notifyWithDetails(string $channel, array $payload): array
    {
        if (!$this->isEnabled()) {
            Log::info('Firebase notifications disabled', ['channel' => $channel]);
            return [
                'success' => false, 
                'message' => 'Firebase notifications disabled (FIREBASE_DATABASE_URL not set)',
                'details' => null
            ];
        }

        try {
            $path = $this->buildPath($channel);
            $signal = $this->buildSignal($payload);

            $response = Http::put("{$this->databaseUrl}/{$path}.json", $signal);

            if ($response->successful()) {
                Log::info('Firebase signal sent', [
                    'channel' => $channel,
                    'event' => $payload['event'] ?? 'unknown'
                ]);
                return [
                    'success' => true,
                    'message' => 'Signal sent successfully',
                    'details' => $response->json()
                ];
            }

            Log::error('Firebase signal failed', [
                'channel' => $channel,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return [
                'success' => false,
                'message' => "Firebase HTTP Error: " . $response->status(),
                'details' => $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Firebase notification error', [
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => "Exception: " . $e->getMessage(),
                'details' => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Send a signal to a specific channel (topic)
     * 
     * @param string $channel Format: 'user.{id}', 'class.{id}', 'system.all'
     * @param array $payload Signal data (NOT the actual content)
     * @return bool
     */
    public function notify(string $channel, array $payload): bool
    {
        $result = $this->notifyWithDetails($channel, $payload);
        return $result['success'];
    }

    /**
     * Notify a specific user
     */
    public function notifyUser(int $userId, string $event, array $context = []): bool
    {
        return $this->notify("user.{$userId}", array_merge(['event' => $event], $context));
    }

    /**
     * Notify a class/group
     */
    public function notifyGroup(string $groupType, string|int $groupId, string $event, array $context = []): bool
    {
        return $this->notify("{$groupType}.{$groupId}", array_merge(['event' => $event], $context));
    }

    /**
     * Notify all users (system-wide broadcast)
     */
    public function notifyAll(string $event, array $context = []): bool
    {
        return $this->notify('system.all', array_merge(['event' => $event], $context));
    }

    /**
     * Build the Firebase path from channel name
     */
    protected function buildPath(string $channel): string
    {
        // Convert 'user.123' -> 'channels/user_123'
        $sanitized = str_replace('.', '_', $channel);
        return "channels/{$sanitized}";
    }

    /**
     * Build the signal payload (NEVER includes actual message content)
     */
    protected function buildSignal(array $payload): array
    {
        return [
            'event' => $payload['event'] ?? 'UNKNOWN',
            'context' => $payload['context'] ?? [],
            'timestamp' => now()->timestamp,
            'trigger_id' => uniqid('sig_', true) // Forces update even if event is same
        ];
    }

    /**
     * Check if Firebase is enabled
     */
    protected function isEnabled(): bool
    {
        return !empty($this->databaseUrl);
    }

    /**
     * Remove a notification (when read, for example)
     */
    public function clearNotification(string $channel): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $path = $this->buildPath($channel);
            $response = Http::delete("{$this->databaseUrl}/{$path}.json");
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to clear notification', [
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
