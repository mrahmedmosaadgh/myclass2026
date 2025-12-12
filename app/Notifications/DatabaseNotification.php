<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DatabaseNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $body, string $url = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url ?? url('/dashboard');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'timestamp' => now()->timestamp,
        ];
    }
}
