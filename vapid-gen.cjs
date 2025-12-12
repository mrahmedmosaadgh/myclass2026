const webpush = require('web-push');

const generateVAPIDKeys = () => {
    const vapidKeys = webpush.generateVAPIDKeys();
    console.log('\nVAPID Keys generated successfully!\n');
    console.log('Public Key:', vapidKeys.publicKey);
    console.log('Private Key:', vapidKeys.privateKey);
    console.log('\nAdd these to your .env file:\n');
    console.log(`VAPID_PUBLIC_KEY=${vapidKeys.publicKey}`);
    console.log(`VAPID_PRIVATE_KEY=${vapidKeys.privateKey}`);
    console.log('VAPID_SUBJECT=mailto:your-email@example.com');
};

generateVAPIDKeys();
