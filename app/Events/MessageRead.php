<?php

namespace App\Events;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The conversation instance.
     *
     * @var \App\Models\Conversation
     */
    public $conversation;

    /**
     * The user who read the message.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The timestamp when the message was read.
     *
     * @var string
     */
    public $readAt;

    /**
     * Create a new event instance.
     */
    public function __construct(Conversation $conversation, User $user, string $readAt)
    {
        $this->conversation = $conversation;
        $this->user = $user;
        $this->readAt = $readAt;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('conversation.' . $this->conversation->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.read';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->user->id,
            'read_at' => $this->readAt,
        ];
    }
}
