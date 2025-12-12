import webPush from 'web-push';

const vapidKeys = webPush.generateVAPIDKeys();

console.log('VAPID Keys:');
console.log('Public Key:', vapidKeys.publicKey);
console.log('Private Key:', vapidKeys.privateKey);
