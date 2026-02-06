import { ref, onMounted, onUnmounted } from 'vue';
import { database } from '@/firebase/init';
import { ref as dbRef, onValue, off } from 'firebase/database';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';
import axios from 'axios';

/**
 * Unified Realtime Channel Listener
 * 
 * This composable handles ALL realtime listening needs.
 * NO NEED to create separate listeners for each feature.
 * 
 * Usage Examples:
 * 
 * 1. Listen to your own notifications:
 *    const { data, isListening } = useRealtimeChannel(`user.${userId}`);
 * 
 * 2. Listen to class updates:
 *    const { data } = useRealtimeChannel('class.7A', (signal) => {
 *      if (signal.event === 'ANNOUNCEMENT') {
 *        fetchAnnouncements();
 *      }
 *    });
 * 
 * 3. Listen to system broadcasts:
 *    useRealtimeChannel('system.all', (signal) => {
 *      showAlert(signal.context.message);
 *    });
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
        if (!ToolsSwitcher.isFirebaseEnabled() || !database) {
            console.warn('🚫 Firebase disabled or database unavailable');
            return;
        }

        if (!channel) {
            console.error('❌ Channel name is required');
            return;
        }

        try {
            // Convert 'user.123' -> 'channels/user_123'
            const path = `channels/${channel.replace('.', '_')}`;
            firebaseRef = dbRef(database, path);

            unsubscribe = onValue(
                firebaseRef,
                (snapshot) => {
                    const signal = snapshot.val();

                    if (!signal) {
                        data.value = null;
                        return;
                    }

                    console.log(`🔔 Signal received on ${channel}:`, signal);

                    // Update reactive data
                    data.value = signal;

                    // Call custom handler if provided
                    if (onSignal && typeof onSignal === 'function') {
                        onSignal(signal);
                    }
                },
                (err) => {
                    console.error(`❌ Error listening to ${channel}:`, err);
                    error.value = err.message;
                }
            );

            isListening.value = true;
            console.log(`✅ Listening to channel: ${channel}`);

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
            isListening.value = false;
            console.log(`🛑 Stopped listening to ${channel}`);
        }
    };

    /**
     * Fetch data from Laravel API based on the signal
     * 
     * Example:
     *   if (signal.event === 'NEW_MESSAGE') {
     *     const messages = await fetchFromSignal('/api/conversations/${signal.context.conversation_id}/messages');
     *   }
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
