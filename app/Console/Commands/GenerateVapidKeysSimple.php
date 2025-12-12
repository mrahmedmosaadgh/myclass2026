<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeysSimple extends Command
{
    protected $signature = 'webpush:keys';
    protected $description = 'Generate VAPID keys for web push notifications';

    public function handle()
    {
        $this->info('Generating VAPID keys...');

        // Generate key pair using OpenSSL
        $keyPair = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1'
        ]);

        if (!$keyPair) {
            $this->error('Failed to generate key pair');
            return 1;
        }

        // Extract private key
        openssl_pkey_export($keyPair, $privateKey);

        // Extract public key
        $keyDetails = openssl_pkey_get_details($keyPair);
        $publicKey = $keyDetails['key'];

        // Base64URL encode the keys
        $publicKey = $this->base64UrlEncode($publicKey);
        $privateKey = $this->base64UrlEncode($privateKey);

        $this->info('VAPID keys generated successfully!');
        $this->line('');
        $this->line('Add these to your .env file:');
        $this->line('');
        $this->line("VAPID_PUBLIC_KEY=$publicKey");
        $this->line("VAPID_PRIVATE_KEY=$privateKey");
        $this->line('VAPID_SUBJECT=mailto:your-email@example.com');

        if ($this->confirm('Would you like to add these keys to your .env file now?')) {
            $this->updateEnvFile($publicKey, $privateKey);
            $this->info('Keys added to .env file successfully!');
        }

        return 0;
    }

    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function updateEnvFile($publicKey, $privateKey)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        // Replace or add VAPID keys
        $envContent = preg_replace('/VAPID_PUBLIC_KEY=.*/', '', $envContent);
        $envContent = preg_replace('/VAPID_PRIVATE_KEY=.*/', '', $envContent);
        $envContent = preg_replace('/VAPID_SUBJECT=.*/', '', $envContent);

        // Add new keys
        $envContent .= "\n\nVAPID_PUBLIC_KEY=" . $publicKey;
        $envContent .= "\nVAPID_PRIVATE_KEY=" . $privateKey;
        $envContent .= "\nVAPID_SUBJECT=mailto:your-email@example.com";

        file_put_contents($envPath, $envContent);
    }
}
