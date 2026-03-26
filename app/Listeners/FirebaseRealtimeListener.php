<?php

namespace App\Listeners;

use App\Events\RealtimeEvent;
use App\Services\RealtimeNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Firebase Realtime Listener
 * 
 * Automatically sends Firebase signals when RealtimeEvent is fired.
 * You NEVER need to touch this file after initial setup.
 */
class FirebaseRealtimeListener
{

    protected RealtimeNotificationService $firebaseService;

    public function __construct(RealtimeNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Handle the event.
     */
    public function handle(RealtimeEvent $event): void
    {
        $this->firebaseService->notify(
            $event->channel,
            [
                'event' => $event->event,
                'context' => $event->context
            ]
        );
    }
}
