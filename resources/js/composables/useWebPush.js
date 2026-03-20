import { ref } from 'vue';
// Reactive variables
const isSubscribed = ref(false);

// Check if push notifications are supported
const isPushSupported = () => {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.warn('Push notifications are not supported in this browser.');
        return false;
    }
    return true;
};

// Check current subscription status
const checkSubscription = async () => {
    if (!isPushSupported()) {
        return false;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();
        isSubscribed.value = !!subscription;
        return !!subscription;
    } catch (error) {
        console.error('Error checking push subscription:', error);
        isSubscribed.value = false;
        return false;
    }
};

// URL safe base64 encode
const urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
};

// Get VAPID public key from meta tag
const getVapidPublicKey = () => {
    const el = document.querySelector("meta[name='vapid-key']");
    if (!el) {
        throw new Error('VAPID key not found in meta tags');
    }
    return el.getAttribute('content');
};

// Setup push notifications
const setupPushNotifications = async () => {
    if (!isPushSupported()) {
        throw new Error('Push notifications are not supported in this browser');
    }

    try {
        // Wait for service worker to be ready
        const registration = await navigator.serviceWorker.ready;

        // Check current subscription
        let subscription = await registration.pushManager.getSubscription();
        
        if (subscription) {
            // Already subscribed
            isSubscribed.value = true;
            return true;
        }

        // Get the VAPID public key
        const vapidPublicKey = getVapidPublicKey();
        const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);

        // Subscribe to push notifications
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        });

        if (subscription) {
            // Send subscription to backend
            const response = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                },
                body: JSON.stringify(subscription),
            });

            if (!response.ok) {
                throw new Error(`Failed to register subscription: ${response.statusText}`);
            }

            const data = await response.json();
            if (!data || !data.success) {
                throw new Error('Server returned unexpected response format');
            }

            isSubscribed.value = true;
            return true;
        } else {
            throw new Error('Could not create subscription');
        }
    } catch (error) {
        console.error('Error setting up push notifications:', error);
        throw error;
    }
};

// Initialize subscription status on module load
if (isPushSupported()) {
    // Use setTimeout to not block module loading
    setTimeout(async () => {
        try {
            await checkSubscription();
        } catch (error) {
            console.error('Error during initial subscription check:', error);
        }
    }, 0);
}

export function useWebPush() {
    return {
        isSubscribed,
        setupPushNotifications,
        checkSubscription,
        isPushSupported
    };
}
