<template>
  <div>
    <!-- Trigger Button -->
    <q-btn
      flat
      dense
      no-caps
      color="grey-8"
      icon="settings"
      label="Tools Switcher"
      class="full-width text-left"
      align="left"
      @click="showDialog = true"
    />

    <!-- Dialog -->
    <q-dialog v-model="showDialog" persistent>
      <q-card class="tools-switcher-panel" style="min-width: 450px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">🛠️ Tools Switcher</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        
        <q-card-section class="text-caption q-pt-none">
           Control system features and network status
        </q-card-section>

        <q-card-section class="q-pt-none">
          
          <!-- Network Status -->
          <q-expansion-item label="Network Status" icon="wifi" default-opened header-class="bg-grey-1 rounded-borders font-bold mb-2">
             <div class="q-pa-md">
                <!-- Status Indicators -->
                <div class="row items-center q-gutter-x-md q-mb-md">
                   <div class="row items-center q-gutter-x-sm">
                      <div class="status-dot-indicator" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></div>
                      <span class="text-subtitle2">{{ statusTextComputed }}</span>
                   </div>
                   
                   <q-separator vertical spaced />
                   
                   <div class="row items-center q-gutter-x-sm text-grey-7" v-if="connectionType !== 'unknown'">
                      <q-icon name="network_check" />
                      <span>{{ connectionType.toUpperCase() }}</span>
                   </div>
                </div>
                
                <div class="bg-blue-50 text-blue-900 q-pa-sm rounded-borders q-mb-md row items-center justify-between">
                   <div class="row items-center q-gutter-x-sm">
                      <q-icon :name="syncStatusIcon" size="sm" :color="syncStatusColor" />
                      <div>
                         <div class="text-weight-bold">Sync Status</div>
                         <div class="text-caption">{{ syncStatusText }}</div>
                      </div>
                   </div>
                   <q-btn v-if="isOnline" icon="sync" round flat dense color="primary" :loading="syncStatus === 'syncing'" @click="handleManualSync" />
                </div>

                <!-- Actions -->
                <div class="column q-gutter-y-sm">
                   <q-btn  outline color="negative" icon="delete_sweep" label="Clear Offline Data" no-caps @click="handleClearOfflineData" :loading="clearingData" />
                   <q-btn outline color="warning text-dark" icon="refresh" label="Reset Network State" no-caps @click="handleResetNetworkState" />
                   <q-btn outline color="orange text-dark" icon="build" label="Reset Service Worker" no-caps @click="handleResetServiceWorker" />
                </div>
             </div>
          </q-expansion-item>
        
          <q-separator spaced />

          <!-- Firebase Controls -->
          <q-expansion-item label="Firebase" icon="local_fire_department" header-class="bg-grey-1 rounded-borders font-bold mb-2">
            <q-toggle v-model="config.firebase.enabled" label="Enable Firebase" @update:model-value="saveConfig" />
            <q-toggle v-model="config.firebase.auth" label="Authentication" :disable="!config.firebase.enabled" @update:model-value="saveConfig" />
            <q-toggle v-model="config.firebase.database" label="Realtime Database" :disable="!config.firebase.enabled" @update:model-value="saveConfig" />
            <q-toggle v-model="config.firebase.emulators" label="Use Emulators (Dev)" :disable="!config.firebase.enabled" @update:model-value="saveConfig" />
            <q-toggle v-model="config.firebase.notifications" label="Firebase Notifications" :disable="!config.firebase.enabled" @update:model-value="saveConfig" />
          </q-expansion-item>

          <q-separator spaced />

          <!-- Background Services -->
          <q-expansion-item label="Background Services" icon="settings_applications" header-class="bg-grey-1 rounded-borders font-bold mb-2">
            <q-toggle v-model="config.backgroundServices.notifications" label="Background Notifications" @update:model-value="saveConfig" />
            <q-toggle v-model="config.backgroundServices.sync" label="Background Sync" @update:model-value="saveConfig" />
            <q-toggle v-model="config.backgroundServices.realtime" label="Realtime Updates" @update:model-value="saveConfig" />
          </q-expansion-item>
        </q-card-section>

        <q-card-actions align="right" class="bg-grey-1">
          <q-btn flat color="warning text-dark" @click="resetToDefaults" label="Reset Defaults" no-caps />
          <q-btn unelevated color="primary" @click="reloadPage" label="Reload Page" no-caps />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { ToolsSwitcher } from '@/Utils/toolsSwitcher';
import { useQuasar } from 'quasar';
import { storeToRefs } from 'pinia';
import { useNetworkStore } from '@/Stores/networkStore';

const $q = useQuasar();
const showDialog = ref(false);

// Config State
const config = ref({
  firebase: { enabled: true, auth: true, database: true, emulators: true, notifications: true },
  backgroundServices: { notifications: true, sync: true, realtime: true }
});

// Network Store
const networkStore = useNetworkStore();
const { isOnline, syncStatus, lastOnlineTime, connectionType } = storeToRefs(networkStore);
const clearingData = ref(false);

// Check if running in local environment
const isLocalEnvironment = computed(() => {
  const hostname = window.location.hostname;
  return hostname === 'localhost' || hostname === '127.0.0.1' || hostname.includes('.local');
});

// Computed
const statusTextComputed = computed(() => networkStore.statusText);

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
const saveConfig = () => {
  ToolsSwitcher.saveConfig(config.value);
  $q.notify({ message: 'Configuration saved', color: 'positive', timeout: 1000 });
};

const resetToDefaults = () => {
  localStorage.removeItem('toolsSwitcher');
  config.value = ToolsSwitcher.getConfig();
  $q.notify({ message: 'Reset to defaults', color: 'info' });
};

const reloadPage = () => window.location.reload();

const handleManualSync = async () => {
  await networkStore.triggerSync();
};

const handleClearOfflineData = async () => {
  if (!confirm('⚠️ Clear ALL offline data? This will remove cached lessons, students, and grades. Continue?')) return;

  clearingData.value = true;
  try {
    try { const { db } = await import('@/offline/dexie.js'); await db.delete(); } catch (e) {}
    
    // Clear LocalStorage
    const offlineKeys = [];
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && (key.startsWith('offline_') || key.startsWith('sync_') || key.startsWith('network_') || key.includes('queue') || key.includes('cache'))) {
            offlineKeys.push(key);
        }
    }
    offlineKeys.forEach(key => localStorage.removeItem(key));
    
    sessionStorage.clear();
    
    if ('caches' in window) {
      const keys = await caches.keys();
      await Promise.all(keys.map(k => caches.delete(k)));
    }
    
    if ('serviceWorker' in navigator) {
      const regs = await navigator.serviceWorker.getRegistrations();
      for (const r of regs) await r.unregister();
    }
    
    $q.notify({ message: 'Offline data cleared', color: 'positive' });
    setTimeout(() => window.location.reload(), 1000);
  } catch (error) {
    $q.notify({ message: 'Error clearing data', color: 'negative' });
  } finally {
    clearingData.value = false;
  }
};

const handleResetNetworkState = () => {
  networkStore.updateOnlineStatus(navigator.onLine);
  networkStore.setSyncStatus('idle');
  networkStore.initializeNetworkListeners();
  $q.notify({ message: 'Network state reset', color: 'info' });
};

const handleResetServiceWorker = async () => {
  if (!confirm('Reset Service Worker?')) return;
  try {
    if ('serviceWorker' in navigator) {
      const regs = await navigator.serviceWorker.getRegistrations();
      for (const r of regs) await r.unregister();
    }
    if ('caches' in window) {
      const keys = await caches.keys();
      await Promise.all(keys.map(k => caches.delete(k)));
    }
    window.location.reload();
  } catch (e) {
    console.error(e);
  }
};

onMounted(() => {
  config.value = ToolsSwitcher.getConfig();
  networkStore.initializeNetworkListeners();
});
</script>

<style scoped>
.status-dot-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
}
</style>




