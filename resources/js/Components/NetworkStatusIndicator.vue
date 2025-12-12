<!--
  NetworkStatusIndicator.vue

  Global network status indicator that shows on every page.
  Features:
  - Animated blinking green dot when online
  - Static gray dot when offline
  - Sync status indicators
  - Tooltip with detailed information
  - Mobile-friendly positioning
-->

<template>
  <div class="network-status-indicator">
    <!-- Main Status Indicator -->
    <div
      class="status-container cursor-pointer"
      @click="showDetails = !showDetails"
    >
      <!-- Status Dot -->
      <div
        class="status-dot"
        :class="{
          'bg-green-500 animate-pulse': isOnline && syncStatus !== 'syncing',
          'bg-gray-400': !isOnline,
          'bg-blue-500 animate-spin': syncStatus === 'syncing',
          'bg-red-500': syncStatus === 'error',
          'bg-green-600': syncStatus === 'success'
        }"
      >
        <!-- Sync Status Icon -->
        <div v-if="syncStatus === 'syncing'" class="sync-icon">
          <q-icon name="sync" size="8px" color="white" />
        </div>
        <div v-else-if="syncStatus === 'error'" class="sync-icon">
          <q-icon name="error" size="8px" color="white" />
        </div>
        <div v-else-if="syncStatus === 'success'" class="sync-icon">
          <q-icon name="check" size="8px" color="white" />
        </div>
      </div>

      <!-- Status Text (Desktop) -->
      <span class="status-text hidden sm:inline-block">
        {{ statusText }}
      </span>
    </div>

    <!-- Detailed Tooltip/Panel -->
    <Transition name="fade">
      <div v-if="showDetails" class="details-panel">
        <div class="details-content">
          <div class="detail-row">
            <q-icon name="wifi" size="16px" />
            <span>Status: {{ statusText }}</span>
          </div>

          <div v-if="connectionType !== 'unknown'" class="detail-row">
            <q-icon name="network_check" size="16px" />
            <span>Connection: {{ connectionType.toUpperCase() }}</span>
          </div>

          <div v-if="lastOnlineTime" class="detail-row">
            <q-icon name="schedule" size="16px" />
            <span>Last Online: {{ formatTime(lastOnlineTime) }}</span>
          </div>

          <div class="detail-row">
            <q-icon
              :name="syncStatusIcon"
              size="16px"
              :color="syncStatusColor"
            />
            <span>Sync: {{ syncStatusText }}</span>
          </div>

          <!-- Action Buttons -->
          <div class="detail-row">
            <div class="flex flex-col space-y-2 w-full">
              <!-- Manual Sync Button -->
              <q-btn
                v-if="isOnline && syncStatus !== 'syncing'"
                size="sm"
                color="primary"
                icon="sync"
                label="Sync Now"
                @click="handleManualSync"
                :loading="syncStatus === 'syncing'"
                dense
                no-caps
                class="w-full"
              />

              <!-- Clear Offline Data Button -->
              <q-btn
                size="sm"
                color="negative"
                icon="delete_sweep"
                label="Clear Offline Data"
                @click="handleClearOfflineData"
                :loading="clearingData"
                dense
                no-caps
                class="w-full"
              />

              <!-- Reset Network State Button -->
              <q-btn
                size="sm"
                color="warning"
                icon="refresh"
                label="Reset Network State"
                @click="handleResetNetworkState"
                dense
                no-caps
                class="w-full"
              />

              <!-- Reset Service Worker Button -->
              <q-btn
                size="sm"
                color="orange"
                icon="build"
                label="Reset Service Worker"
                @click="handleResetServiceWorker"
                dense
                no-caps
                class="w-full"
              />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useNetworkStore } from '@/Stores/networkStore';

// Use the global network store
const networkStore = useNetworkStore();
const showDetails = ref(false);
const showSyncAnimation = ref(false);
const clearingData = ref(false);

// Get reactive state from store using storeToRefs
const {
  isOnline,
  syncStatus,
  lastOnlineTime,
  connectionType
} = storeToRefs(networkStore);

// Get computed properties from store
const { statusText } = networkStore;

// Sync status computed properties
const syncStatusText = computed(() => {
  switch (syncStatus.value) {
    case 'syncing': return 'Syncing...';
    case 'success': return 'Synced';
    case 'error': return 'Sync Failed';
    default: return 'Ready';
  }
});

const syncStatusIcon = computed(() => {
  switch (syncStatus.value) {
    case 'syncing': return 'sync';
    case 'success': return 'check_circle';
    case 'error': return 'error';
    default: return 'cloud_done';
  }
});

const syncStatusColor = computed(() => {
  switch (syncStatus.value) {
    case 'syncing': return 'blue';
    case 'success': return 'green';
    case 'error': return 'red';
    default: return 'grey';
  }
});

// Methods
const formatTime = (date) => {
  if (!date) return 'Never';
  return new Intl.DateTimeFormat('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  }).format(new Date(date));
};

const handleManualSync = async () => {
  showSyncAnimation.value = true;
  await networkStore.triggerSync();
  setTimeout(() => {
    showSyncAnimation.value = false;
  }, 1000);
};

const handleClearOfflineData = async () => {
  if (!confirm('⚠️ This will clear ALL offline data including:\n\n• Cached lessons, students, grades\n• Pending sync queue\n• Local storage data\n• IndexedDB data\n\nThis action cannot be undone. Continue?')) {
    return;
  }

  clearingData.value = true;
  console.log('🗑️ Starting offline data cleanup...');

  try {
    // Clear IndexedDB (Dexie database)
    try {
      const { db } = await import('@/offline/dexie.js');
      await db.delete();
      console.log('✅ IndexedDB cleared');
    } catch (error) {
      console.warn('⚠️ Could not clear IndexedDB:', error);
    }

    // Clear localStorage items related to offline functionality
    const offlineKeys = [];
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && (
        key.startsWith('offline_') ||
        key.startsWith('sync_') ||
        key.startsWith('network_') ||
        key.includes('queue') ||
        key.includes('cache') ||
        key.includes('lesson') ||
        key.includes('student') ||
        key.includes('grade')
      )) {
        offlineKeys.push(key);
      }
    }

    offlineKeys.forEach(key => {
      localStorage.removeItem(key);
      console.log(`🗑️ Removed localStorage: ${key}`);
    });

    // Clear sessionStorage
    sessionStorage.clear();
    console.log('✅ SessionStorage cleared');

    // Clear any cached service worker data
    if ('caches' in window) {
      const cacheNames = await caches.keys();
      await Promise.all(
        cacheNames.map(cacheName => {
          console.log(`🗑️ Clearing cache: ${cacheName}`);
          return caches.delete(cacheName);
        })
      );
      console.log('✅ Service Worker caches cleared');
    }

    // Reset sync queue
    try {
      const { clearQueue } = await import('@/offline/syncQueue.js');
      await clearQueue();
      console.log('✅ Sync queue cleared');
    } catch (error) {
      console.warn('⚠️ Could not clear sync queue:', error);
    }

    // Show success message
    alert('✅ All offline data has been cleared successfully!\n\nThe page will reload to reset the application state.');

    // Reload the page to reset everything
    window.location.reload();

  } catch (error) {
    console.error('❌ Error clearing offline data:', error);
    alert('❌ Error clearing offline data. Check console for details.');
  } finally {
    clearingData.value = false;
  }
};

const handleResetNetworkState = () => {
  console.log('🔄 Resetting network state...');

  // Reset network store to initial state
  networkStore.updateOnlineStatus(navigator.onLine);
  networkStore.setSyncStatus('idle');

  // Re-initialize network listeners
  networkStore.initializeNetworkListeners();

  console.log('✅ Network state reset complete');
  alert('✅ Network state has been reset!\n\nNetwork listeners have been re-initialized.');
};

const handleResetServiceWorker = async () => {
  if (!confirm('🔧 This will reset the Service Worker and clear all caches.\n\nThis may help resolve navigation and caching issues.\n\nThe page will reload after completion. Continue?')) {
    return;
  }

  console.log('🔧 Resetting Service Worker...');

  try {
    // Unregister all service workers
    if ('serviceWorker' in navigator) {
      const registrations = await navigator.serviceWorker.getRegistrations();

      for (const registration of registrations) {
        console.log('🗑️ Unregistering service worker:', registration.scope);
        await registration.unregister();
      }

      console.log('✅ All service workers unregistered');
    }

    // Clear all caches
    if ('caches' in window) {
      const cacheNames = await caches.keys();
      await Promise.all(
        cacheNames.map(cacheName => {
          console.log(`🗑️ Clearing cache: ${cacheName}`);
          return caches.delete(cacheName);
        })
      );
      console.log('✅ All caches cleared');
    }

    // Show success message
    alert('✅ Service Worker has been reset!\n\nAll caches have been cleared.\nThe page will now reload to re-register the service worker.');

    // Reload the page to re-register the service worker
    window.location.reload();

  } catch (error) {
    console.error('❌ Error resetting service worker:', error);
    alert('❌ Error resetting service worker. Check console for details.');
  }
};

// Close details when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.network-status-indicator')) {
    showDetails.value = false;
  }
};

onMounted(() => {
  // Initialize network listeners
  networkStore.initializeNetworkListeners();

  // Add click outside listener
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.network-status-indicator {
  @apply fixed top-24 left-4  z-50;

}

.status-container {
  @apply flex items-center   bg-white dark:bg-gray-800 rounded-full  shadow-lg border border-gray-200 dark:border-gray-700;
  transition: all 0.3s ease;


}

.status-container:hover {
  @apply shadow-xl transform scale-105;
}

.status-dot {
  @apply w-3 h-3 rounded-full relative flex items-center justify-center;
  transition: all 0.3s ease;


}

.sync-icon {
  @apply absolute inset-0 flex items-center justify-center;
}

.status-text {
  @apply text-sm font-medium text-gray-700 dark:text-gray-300;
}

.details-panel {
  @apply absolute top-full left-0 mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4 min-w-64;
}

.details-content {
  @apply space-y-3;
}

.detail-row {
  @apply flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400;
}

/* Animations */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Custom animations are handled by Tailwind classes */

/* Mobile responsiveness */
@media (max-width: 640px) {
  .network-status-indicator {
    @apply top-2 left-2;
  }

  .status-container {
    @apply px-2 py-1;
  }

  .details-panel {
    @apply min-w-56 left-0;
  }
}
</style>
