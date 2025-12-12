<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\TestPushNotification;

class SendTestPushNotification extends Command
{
    protected $signature = 'webpush:test {user?}';
    protected $description = 'Send a test web push notification';

    public function handle()
    {
        $userId = $this->argument('user');

        if ($userId) {
            $users = User::where('id', $userId)->get();
        } else {
            $users = User::whereHas('pushSubscriptions')->get();
        }

        $count = 0;
        foreach ($users as $user) {
            $user->notify(new TestPushNotification());
            $count++;
        }

        if ($count > 0) {
            $this->info("Sent test notifications to {$count} users.");
        } else {
            $this->warn('No users found with push subscriptions.');
            $this->line('Make sure users have subscribed to push notifications first.');
        }
    }
}
