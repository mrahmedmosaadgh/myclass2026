<template>
    <div>
        <div v-if="error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ error }}
        </div>
        <button
            @click="handlePushNotifications"
            :class="[
                'inline-flex items-center px-4 py-2 rounded-md font-semibold text-sm',
                isSubscribed
                    ? 'bg-red-600 text-white hover:bg-red-500'
                    : 'bg-blue-600 text-white hover:bg-primary-500',
                !serviceWorkerSupported ? 'opacity-50 cursor-not-allowed' : ''
            ]"
            :disabled="!serviceWorkerSupported"
        >
            <template v-if="isSubscribed">
                Disable Push Notifications
            </template>
            <template v-else>
                Enable Push Notifications
            </template>
        </button>
        <div v-if="!serviceWorkerSupported" class="mt-2 text-sm text-red-600">
            Your browser doesn't support push notifications
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useWebPush } from '@/composables/useWebPush';

const { isSubscribed, setupPushNotifications, checkSubscription } = useWebPush();
const error = ref(null);
const serviceWorkerSupported = ref('serviceWorker' in navigator);

const handlePushNotifications = async () => {
    console.log('handlePushNotifications');
    error.value = null;

    // Check if service worker is registered
    if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.getRegistration();
        console.log('Service Worker Registration:', registration);
        if (!registration) {
            error.value = 'Service Worker not registered. Try refreshing the page.';
            return;
        }
    }

    try {
        if (!serviceWorkerSupported.value) {
            throw new Error('Push notifications are not supported in your browser');
        }

        if (!isSubscribed.value) {
            const result = await setupPushNotifications();
            if (result) {
                // Success notification is handled by the server
            }
        } else {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            if (subscription) {
                await subscription.unsubscribe();
                await fetch('/push/unsubscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    },
                    body: JSON.stringify({ endpoint: subscription.endpoint }),
                });
                isSubscribed.value = false;
            }
        }
    } catch (err) {
        console.error('Push notification error:', err);
        error.value = err.message || 'Failed to handle push notifications';
    }
};

onMounted(async () => {
    if (serviceWorkerSupported.value) {
        try {
            await checkSubscription();
        } catch (err) {
            console.error('Failed to check subscription status:', err);
            error.value = 'Failed to check notification status';
        }
    }
});
</script>
