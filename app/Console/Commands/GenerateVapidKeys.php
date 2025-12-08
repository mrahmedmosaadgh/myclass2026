<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid';
    protected $description = 'Generate VAPID keys for web push notifications';

    public function handle()
    {
        $vapid = VAPID::createVapidKeys();

        $this->info('VAPID keys generated successfully!');
        $this->line('');
        $this->line('Please add these keys to your .env file:');
        $this->line('');
        $this->line('VAPID_PUBLIC_KEY=' . $vapid['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $vapid['privateKey']);
        $this->line('VAPID_SUBJECT=mailto:your-email@example.com'); // Replace with your email
        $this->line('');

        if ($this->confirm('Would you like to add these keys to your .env file now?')) {
            $this->updateEnvFile($vapid);
            $this->info('Keys added to .env file successfully!');
        }
    }

    protected function updateEnvFile($vapid)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Replace or add VAPID keys
        $envContent = preg_replace('/VAPID_PUBLIC_KEY=.*/', '', $envContent);
        $envContent = preg_replace('/VAPID_PRIVATE_KEY=.*/', '', $envContent);
        $envContent = preg_replace('/VAPID_SUBJECT=.*/', '', $envContent);

        // Add new keys
        $envContent .= "\n\nVAPID_PUBLIC_KEY=" . $vapid['publicKey'];
        $envContent .= "\nVAPID_PRIVATE_KEY=" . $vapid['privateKey'];
        $envContent .= "\nVAPID_SUBJECT=mailto:your-email@example.com"; // Replace with your email

        file_put_contents($envPath, $envContent);
    }
}
