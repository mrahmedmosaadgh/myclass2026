<?php

namespace App\Events;

use App\Models\StudentAnswer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentAnswerScanned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $answer;

    /**
     * Create a new event instance.
     */
    public function __construct(StudentAnswer $answer)
    {
        $this->answer = $answer;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // For the MVP, we broadcast on a public channel to test.
        // In reality, this should be a PrivateChannel for the specific classroom/session.
        return [
            new Channel('smart-scanner'),
        ];
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'scan.submitted';
    }
}
