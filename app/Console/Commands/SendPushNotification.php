<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\WebPushNotification;

class SendPushNotification extends Command
{
    protected $signature = 'push:send {user?} {--title=} {--body=} {--url=}';
    protected $description = 'Send a push notification to a user or all subscribed users';

    public function handle()
    {
        $userId = $this->argument('user');
        $title = $this->option('title') ?? 'Test Notification';
        $body = $this->option('body') ?? 'This is a test notification';
        $url = $this->option('url') ?? route('dashboard');

        try {
            if ($userId) {
                $users = User::where('id', $userId)->get();
            } else {
                $users = User::whereHas('pushSubscriptions')->get();
            }

            if ($users->isEmpty()) {
                $this->warn('No users found with push subscriptions.');
                return 1;
            }

            foreach ($users as $user) {
                $this->info("Sending notification to user {$user->id}...");
                $user->notify(new WebPushNotification($title, $body, $url));
                $this->info('✓ Notification sent successfully');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send notification: {$e->getMessage()}");
            return 1;
        }
    }
}
