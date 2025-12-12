<?php

// Generate VAPID keys for Web Push
function generateVAPIDKeys() {
    $publicKey = base64_encode(random_bytes(32));
    $privateKey = base64_encode(random_bytes(32));
    
    return [
        'publicKey' => $publicKey,
        'privateKey' => $privateKey
    ];
}

$keys = generateVAPIDKeys();

echo "VAPID_PUBLIC_KEY=" . $keys['publicKey'] . "\n";
echo "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "\n";
echo "VAPID_SUBJECT=mailto:admin@example.com\n";
