import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useWebPush() {
    const isSubscribed = ref(false);
    const subscription = ref(null);

    // Convert VAPID key from base64 to Uint8Array
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

    // Register service worker
    const registerServiceWorker = async () => {
        try {
            // First check if there's an active service worker
            const existingRegistration = await navigator.serviceWorker.getRegistration();
            if (existingRegistration) {
                return existingRegistration;
            }

            // If no active service worker, register a new one
            const registration = await navigator.serviceWorker.register('/sw.js', {
                scope: '/'
            });

            // Wait for the service worker to be ready
            await navigator.serviceWorker.ready;
            return registration;
        } catch (error) {
            console.error('Service Worker registration failed:', error);
            throw error;
        }
    };

    // Subscribe to push notifications
    const subscribeUserToPush = async (registration) => {
        try {
            // Get VAPID public key
            const response = await fetch('/vapid/public-key', {
                headers: {
                    'Accept': 'text/plain'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to get VAPID public key');
            }

            const vapidPublicKey = await response.text();
            if (!vapidPublicKey || vapidPublicKey.length === 0) {
                throw new Error('Invalid VAPID public key');
            }

            console.log('Using VAPID public key:', vapidPublicKey);

            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
            };

            const pushSubscription = await registration.pushManager.subscribe(subscribeOptions);
            subscription.value = pushSubscription;

            // Send subscription to backend
            await saveSubscription(pushSubscription);
            isSubscribed.value = true;

            return pushSubscription;
        } catch (error) {
            console.error('Failed to subscribe to push:', error);
            if (error.name === 'NotAllowedError') {
                throw new Error('Please allow notifications in your browser settings');
            }
            throw error;
        }
    };

    // Save subscription to backend
    const saveSubscription = async (pushSubscription) => {
        try {
            console.log('Saving subscription to backend:', pushSubscription);

            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(pushSubscription),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error('Server error:', errorData);
                throw new Error(errorData.message || 'Failed to save subscription');
            }

            const data = await response.json();
            console.log('Subscription saved successfully:', data);
            return data;
        } catch (error) {
            console.error('Failed to save subscription:', error);
            throw error;
        }
    };

    // Request notification permission and setup push
    const setupPushNotifications = async () => {
        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                const registration = await registerServiceWorker();
                await subscribeUserToPush(registration);
                return true;
            }
            return false;
        } catch (error) {
            console.error('Failed to setup push notifications:', error);
            return false;
        }
    };

    // Check if already subscribed
    const checkSubscription = async () => {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            isSubscribed.value = !!subscription;
            subscription.value = subscription;
            return subscription;
        } catch (error) {
            console.error('Failed to check subscription:', error);
            return null;
        }
    };

    return {
        isSubscribed,
        subscription,
        setupPushNotifications,
        checkSubscription
    };
}
