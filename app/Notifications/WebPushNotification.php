<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class WebPushNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $body;
    protected $url;

    public function __construct(string $title, string $body, string $url = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->url = $url ?? url('/dashboard');
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->body($this->body)
            ->data(['url' => $this->url])
            ->icon('/icon.png')
            ->badge('/badge.png')
            ->vibrate([100, 50, 100]);
    }
}
