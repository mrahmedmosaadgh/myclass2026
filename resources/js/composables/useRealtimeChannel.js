import { ref, onMounted, onUnmounted, watch, unref } from 'vue';
import { database } from '@/firebase/init';
import { ref as dbRef, onValue, off } from 'firebase/database';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';
import axios from 'axios';

/**
 * Unified Realtime Channel Listener
 * 
 * Handles both plain strings and reactive refs/computed names.
 */
export function useRealtimeChannel(channel, onSignal = null) {
    const data = ref(null);
    const isListening = ref(false);
    const error = ref(null);

    let firebaseRef = null;
    let unsubscribe = null;

    /**
     * Start listening to the channel
     */
    const listen = () => {
        // Stop any existing listener first
        stopListening();

        const channelName = unref(channel);

        if (!ToolsSwitcher.isFirebaseEnabled() || !database) {
            console.warn('🚫 Firebase disabled or database unavailable');
            return;
        }

        if (!channelName) {
            // It's okay to not have a channel yet (e.g. waiting for session code)
            return;
        }

        try {
            // Convert 'user.123' -> 'channels/user_123'
            const path = `channels/${String(channelName).replace(/\./g, '_')}`;
            firebaseRef = dbRef(database, path);

            unsubscribe = onValue(
                firebaseRef,
                (snapshot) => {
                    const signal = snapshot.val();

                    if (!signal) {
                        data.value = null;
                        return;
                    }

                    console.log(`🔔 Signal received on ${channelName}:`, signal);

                    // Update reactive data
                    data.value = signal;

                    // Call custom handler if provided
                    if (onSignal && typeof onSignal === 'function') {
                        onSignal(signal);
                    }
                },
                (err) => {
                    console.error(`❌ Error listening to ${channelName}:`, err);
                    error.value = err.message;
                }
            );

            isListening.value = true;
            console.log(`✅ Listening to channel: ${channelName}`);

        } catch (err) {
            console.error('Failed to start listener:', err);
            error.value = err.message;
        }
    };

    /**
     * Stop listening
     */
    const stopListening = () => {
        if (unsubscribe && firebaseRef) {
            off(firebaseRef, unsubscribe);
            unsubscribe = null;
            firebaseRef = null;
            isListening.value = false;
            // console.log('🛑 Stopped listening to previous channel');
        }
    };

    /**
     * Fetch data from Laravel API based on the signal
     */
    const fetchFromSignal = async (endpoint) => {
        try {
            const response = await axios.get(endpoint);
            return response.data;
        } catch (err) {
            console.error('Failed to fetch from signal:', err);
            throw err;
        }
    };

    // Watch for channel changes (if it's a ref/computed)
    watch(() => unref(channel), (newVal) => {
        if (newVal) {
            listen();
        } else {
            stopListening();
        }
    });

    // Auto-start listening on mount
    onMounted(() => {
        listen();
    });

    // Auto-stop on unmount
    onUnmounted(() => {
        stopListening();
    });

    return {
        data,
        isListening,
        error,
        listen,
        stopListening,
        fetchFromSignal
    };
}
