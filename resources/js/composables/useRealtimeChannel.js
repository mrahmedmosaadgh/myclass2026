import { ref, onMounted, onUnmounted, watch, unref } from 'vue';
import { database } from '@/firebase/init';
import { ref as dbRef, onValue, off } from 'firebase/database';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';
import axios from 'axios';

// Global cache for active Firebase listeners to prevent duplicate connections across components
const channelCache = new Map();

/**
 * Unified Realtime Channel Listener (Singleton Pattern)
 * 
 * Ensures only ONE Firebase listener is created per channel, 
 * distributing the signal to all components that requested it.
 */
export function useRealtimeChannel(channel, onSignal = null) {
    const data = ref(null);
    const isListening = ref(false);
    const error = ref(null);

    // This specific component's local callback reference
    let localCallback = null;
    let currentChannelName = null;

    const stopListening = () => {
        if (!currentChannelName || !localCallback) return;

        const cacheEntry = channelCache.get(currentChannelName);
        if (cacheEntry) {
            // Remove this component's listener
            cacheEntry.subscribers.delete(localCallback);

            // If no more components are listening to this channel, sever the Firebase connection
            if (cacheEntry.subscribers.size === 0) {
                if (cacheEntry.unsubscribe && cacheEntry.ref) {
                    off(cacheEntry.ref, cacheEntry.unsubscribe);
                }
                channelCache.delete(currentChannelName);
                // console.log(`🛑 Completely stopped Firebase listener for ${currentChannelName}`);
            }
        }

        localCallback = null;
        currentChannelName = null;
        isListening.value = false;
    };

    const listen = () => {
        // Stop any existing listener first if channel changed
        stopListening();

        const channelName = unref(channel);

        if (!ToolsSwitcher.isFirebaseEnabled() || !database) {
            console.warn('🚫 Firebase disabled or database unavailable');
            return;
        }

        if (!channelName) return;
        currentChannelName = channelName;

        // Create the unique callback for this exact component instance
        localCallback = (signal) => {
            data.value = signal;
            if (onSignal && typeof onSignal === 'function') {
                onSignal(signal);
            }
        };

        // If the channel doesn't exist in cache, create the actual Firebase connection
        if (!channelCache.has(channelName)) {
            try {
                const path = `channels/${String(channelName).replace(/\./g, '_')}`;
                const firebaseRef = dbRef(database, path);

                const state = {
                    ref: firebaseRef,
                    unsubscribe: null,
                    subscribers: new Set()
                };

                state.unsubscribe = onValue(
                    firebaseRef,
                    (snapshot) => {
                        const signal = snapshot.val();
                        if (!signal) {
                            return;
                        }

                        console.log(`🔔 Signal received on ${channelName}:`, signal);

                        // Notify ALL subscribed Vue components
                        for (const cb of state.subscribers) {
                            cb(signal);
                        }
                    },
                    (err) => {
                        console.error(`❌ Error listening to ${channelName}:`, err);
                        error.value = err.message;
                    }
                );

                channelCache.set(channelName, state);
                console.log(`✅ Started primary Firebase listener for: ${channelName}`);
            } catch (err) {
                console.error('Failed to start listener:', err);
                error.value = err.message;
                return;
            }
        }

        // Add this component to the active subscribers
        channelCache.get(channelName).subscribers.add(localCallback);
        isListening.value = true;
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
