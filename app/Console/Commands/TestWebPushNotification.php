<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\WebPushNotification;

class TestWebPushNotification extends Command
{
    protected $signature = 'webpush:test {user? : The ID of the user to send the notification to}';
    protected $description = 'Send a test web push notification';

    public function handle()
    {
        $userId = $this->argument('user');

        try {
            if ($userId) {
                $user = User::findOrFail($userId);
                $this->sendNotification($user);
            } else {
                $users = User::whereHas('pushSubscriptions')->get();
                if ($users->isEmpty()) {
                    $this->warn('No users found with push subscriptions.');
                    return 1;
                }

                foreach ($users as $user) {
                    $this->sendNotification($user);
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to send notification: {$e->getMessage()}");
            return 1;
        }
    }

    protected function sendNotification($user)
    {
        $this->info("Sending test notification to user {$user->id}...");

        try {
            $user->notify(new WebPushNotification(
                'Test Notification',
                'This is a test push notification from your application.',
                route('dashboard')
            ));

            $this->info("✓ Notification sent successfully to user {$user->id}");
        } catch (\Exception $e) {
            $this->error("✗ Failed to send notification to user {$user->id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
