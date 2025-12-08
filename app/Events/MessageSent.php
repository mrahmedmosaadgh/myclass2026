<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message instance.
     *
     * @var \App\Models\Message
     */
    public $message;

    /**
     * The user who sent the message.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, User $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        // Add a private channel for the conversation
        $channels[] = new PresenceChannel('conversation.' . $this->message->conversation_id);
        
        // Add private channels for each participant (for notifications)
        foreach ($this->message->conversation->users as $user) {
            if ($user->id !== $this->user->id) {
                $channels[] = new PrivateChannel('user.' . $user->id);
            }
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id' => $this->message->user_id,
            'user_name' => $this->user->name,
            'user_photo' => $this->user->profile_photo_url,
            'body' => $this->message->body,
            'type' => $this->message->type,
            'attachment_url' => $this->message->attachment_url,
            'created_at' => $this->message->created_at,
            'is_seen' => $this->message->is_seen,
        ];
    }
}
