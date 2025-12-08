import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useNetworkStore = defineStore('network', () => {
    const isOnline = ref(navigator.onLine);
    const lastOnlineTime = ref(null);
    const connectionType = ref('unknown');
    const syncStatus = ref('idle'); // idle, syncing, error, success

    // Computed properties
    const statusText = computed(() => {
        if (isOnline.value) {
            return 'Online';
        } else {
            return 'Offline';
        }
    });

    const statusColor = computed(() => {
        if (isOnline.value) {
            return 'success'; // Green
        } else {
            return 'grey-6'; // Gray
        }
    });

    const shouldBlink = computed(() => {
        return isOnline.value && syncStatus.value !== 'syncing';
    });

    // Actions
    const updateOnlineStatus = (online) => {
        const wasOffline = !isOnline.value;
        console.log('🌐 Network status changed:', online ? 'ONLINE' : 'OFFLINE');
        isOnline.value = online;

        if (online) {
            lastOnlineTime.value = new Date();
            console.log('✅ Updated last online time:', lastOnlineTime.value);

            // If we just came back online, trigger sync
            if (wasOffline) {
                console.log('🔄 Coming back online, triggering sync...');
                triggerSync();
            }
        }
    };

    const updateConnectionType = () => {
        if ('connection' in navigator) {
            connectionType.value = navigator.connection.effectiveType || 'unknown';
        }
    };

    const setSyncStatus = (status) => {
        syncStatus.value = status;
    };

    const triggerSync = async () => {
        if (!isOnline.value) return;

        setSyncStatus('syncing');

        try {
            // Import sync queue dynamically to avoid circular dependencies
            const { processQueue } = await import('@/offline/syncQueue.js');
            await processQueue();
            setSyncStatus('success');

            // Reset to idle after 2 seconds
            setTimeout(() => {
                setSyncStatus('idle');
            }, 2000);
        } catch (error) {
            console.error('Sync failed:', error);
            setSyncStatus('error');

            // Reset to idle after 5 seconds
            setTimeout(() => {
                setSyncStatus('idle');
            }, 5000);
        }
    };

    // Initialize network listeners
    const initializeNetworkListeners = () => {
        console.log('🚀 Initializing network listeners...');
        console.log('📡 Initial network status:', navigator.onLine ? 'ONLINE' : 'OFFLINE');

        // Listen for online/offline events
        window.addEventListener('online', () => {
            console.log('🟢 Browser online event fired');
            updateOnlineStatus(true);
        });
        window.addEventListener('offline', () => {
            console.log('🔴 Browser offline event fired');
            updateOnlineStatus(false);
        });

        // Listen for connection changes
        if ('connection' in navigator) {
            navigator.connection.addEventListener('change', updateConnectionType);
            console.log('📶 Connection change listener added');
        }

        // Initial connection type
        updateConnectionType();

        // Set initial online time if online
        if (isOnline.value) {
            lastOnlineTime.value = new Date();
            console.log('⏰ Set initial online time:', lastOnlineTime.value);
        }

        console.log('✅ Network listeners initialized successfully');
    };

    return {
        // State
        isOnline,
        lastOnlineTime,
        connectionType,
        syncStatus,

        // Computed
        statusText,
        statusColor,
        shouldBlink,

        // Actions
        updateOnlineStatus,
        updateConnectionType,
        setSyncStatus,
        triggerSync,
        initializeNetworkListeners
    };
});
