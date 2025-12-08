<template>
  <Head title="Network Test" />

  <div class="p-8">
    <h1 class="text-2xl font-bold mb-4">Network Status Test</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Local Component Status -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-4">Local Component Status</h2>
        <div class="space-y-2">
          <p>Navigator Online: <strong>{{ navigatorOnline ? 'Yes' : 'No' }}</strong></p>
          <p>Local isOnline: <strong>{{ localIsOnline ? 'Yes' : 'No' }}</strong></p>
          <p>Last Update: <strong>{{ lastUpdate }}</strong></p>
        </div>

        <div class="mt-4">
          <button @click="testNetworkChange" class="bg-blue-500 text-white px-4 py-2 rounded mr-2">
            Test Network Change
          </button>
          <button @click="checkNavigatorStatus" class="bg-green-500 text-white px-4 py-2 rounded">
            Check Navigator
          </button>
        </div>
      </div>

      <!-- Global Store Status -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-4">Global Store Status</h2>
        <div class="space-y-2">
          <p>Store isOnline: <strong>{{ storeIsOnline ? 'Yes' : 'No' }}</strong></p>
          <p>Store Status Text: <strong>{{ storeStatusText }}</strong></p>
          <p>Store Sync Status: <strong>{{ storeSyncStatus }}</strong></p>
          <p>Store Last Online: <strong>{{ storeLastOnline }}</strong></p>
        </div>

        <div class="mt-4">
          <button @click="initializeStore" class="bg-purple-500 text-white px-4 py-2 rounded mr-2">
            Initialize Store
          </button>
          <button @click="triggerManualSync" class="bg-orange-500 text-white px-4 py-2 rounded">
            Manual Sync
          </button>
        </div>
      </div>
    </div>

    <!-- Instructions -->
    <div class="mt-8 bg-blue-50 p-6 rounded">
      <h3 class="text-lg font-semibold mb-2">🧪 How to Test Real-time Network Detection</h3>
      <ol class="list-decimal list-inside space-y-2">
        <li>Open DevTools (F12) and go to the Console tab to see debug logs</li>
        <li>Go to the Network tab in DevTools</li>
        <li>Check the "Offline" checkbox to simulate going offline</li>
        <li>Watch both the indicator in the top-right AND the values above update in real-time</li>
        <li>Uncheck "Offline" to go back online</li>
        <li>The green dot should pulse when online and turn gray when offline</li>
      </ol>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { storeToRefs } from 'pinia';
import { Head } from '@inertiajs/vue3';
import { useNetworkStore } from '@/Stores/networkStore';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';

// Define layout
defineOptions({
  layout: AppLayoutDefault
});

// Local component state
const navigatorOnline = ref(navigator.onLine);
const localIsOnline = ref(navigator.onLine);
const lastUpdate = ref(new Date().toLocaleTimeString());

// Global store
const networkStore = useNetworkStore();
const {
  isOnline: storeIsOnline,
  syncStatus: storeSyncStatus,
  lastOnlineTime: storeLastOnline
} = storeToRefs(networkStore);

const { statusText: storeStatusText } = networkStore;

// Methods
const updateLocalStatus = () => {
  navigatorOnline.value = navigator.onLine;
  localIsOnline.value = navigator.onLine;
  lastUpdate.value = new Date().toLocaleTimeString();
  console.log('📱 Local status updated:', localIsOnline.value ? 'ONLINE' : 'OFFLINE');
};

const testNetworkChange = () => {
  console.log('🧪 Testing network change...');
  updateLocalStatus();
  // Also trigger store update
  networkStore.updateOnlineStatus(navigator.onLine);
};

const checkNavigatorStatus = () => {
  console.log('🔍 Navigator.onLine:', navigator.onLine);
  console.log('🔍 Local isOnline:', localIsOnline.value);
  console.log('🔍 Store isOnline:', storeIsOnline.value);
  updateLocalStatus();
};

const initializeStore = () => {
  console.log('🚀 Manually initializing store...');
  networkStore.initializeNetworkListeners();
};

const triggerManualSync = async () => {
  console.log('🔄 Triggering manual sync...');
  await networkStore.triggerSync();
};

onMounted(() => {
  console.log('🎯 NetworkTest component mounted');

  // Add local listeners
  window.addEventListener('online', updateLocalStatus);
  window.addEventListener('offline', updateLocalStatus);

  // Initialize store
  networkStore.initializeNetworkListeners();

  console.log('✅ NetworkTest setup complete');
});

onUnmounted(() => {
  window.removeEventListener('online', updateLocalStatus);
  window.removeEventListener('offline', updateLocalStatus);
});
</script>
