<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Generic Realtime Event
 * 
 * Use this for ANY realtime notification. No need to create separate event classes.
 * 
 * Usage Examples:
 *   // Notify a user
 *   event(new RealtimeEvent('user.123', 'NEW_MESSAGE', ['conversation_id' => 55]));
 * 
 *   // Notify a class
 *   event(new RealtimeEvent('class.7A', 'ANNOUNCEMENT', ['title' => 'Test Tomorrow']));
 * 
 *   // Notify everyone
 *   event(new RealtimeEvent('system.all', 'MAINTENANCE', ['scheduled_at' => '2026-02-10']));
 */
class RealtimeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $channel;
    public string $event;
    public array $context;

    /**
     * Create a new event instance.
     *
     * @param string $channel Channel name (e.g., 'user.123', 'class.7A')
     * @param string $event Event type (e.g., 'NEW_MESSAGE', 'ANNOUNCEMENT')
     * @param array $context Additional context (NOT the message content itself)
     */
    public function __construct(string $channel, string $event, array $context = [])
    {
        $this->channel = $channel;
        $this->event = $event;
        $this->context = $context;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // This will be handled by the listener, not Laravel's broadcast system
        return [
            new PrivateChannel($this->channel),
        ];
    }
}
