<?php

require __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\VAPID;

$vapid = VAPID::createVapidKeys();

echo "Public Key: " . $vapid['publicKey'] . "\n";
echo "Private Key: " . $vapid['privateKey'] . "\n";
