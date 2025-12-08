<!--
  Example Vue Component showing how to use the User Context API endpoints
  
  This component demonstrates:
  - Direct API calls to user context endpoints
  - Individual segment refresh
  - Cache management via API
  - Health monitoring
  - Error handling
-->

<template>
  <div class="user-context-api-example p-6 space-y-8">
    <h1 class="text-3xl font-bold mb-8">User Context API Examples</h1>

    <!-- API Status -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">API Status</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center">
          <div class="text-2xl font-bold" :class="isOnline ? 'text-green-600' : 'text-red-600'">
            {{ isOnline ? 'Online' : 'Offline' }}
          </div>
          <div class="text-sm text-gray-600">Network Status</div>
        </div>
        
        <div class="text-center">
          <div class="text-2xl font-bold" :class="apiHealthy ? 'text-green-600' : 'text-red-600'">
            {{ apiHealthy ? 'Healthy' : 'Degraded' }}
          </div>
          <div class="text-sm text-gray-600">API Health</div>
        </div>
        
        <div class="text-center">
          <div class="text-2xl font-bold text-blue-600">
            {{ cacheHealthPercentage }}%
          </div>
          <div class="text-sm text-gray-600">Cache Health</div>
        </div>
      </div>
      
      <div class="mt-4 flex space-x-2">
        <button @click="checkHealth" :disabled="loading.health" 
                class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50">
          {{ loading.health ? 'Checking...' : 'Check Health' }}
        </button>
        <button @click="getCacheStatus" :disabled="loading.cache" 
                class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:opacity-50">
          {{ loading.cache ? 'Loading...' : 'Get Cache Status' }}
        </button>
      </div>
    </section>

    <!-- Individual Segment API Calls -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Individual Segment API Calls</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="segment in segments" :key="segment" class="border rounded-lg p-4">
          <h3 class="font-medium mb-2 capitalize">{{ segment }}</h3>
          
          <div v-if="segmentData[segment]" class="mb-2">
            <div class="text-xs text-green-600 mb-1">✓ Data loaded</div>
            <div class="text-xs text-gray-600">
              Last updated: {{ formatTime(segmentData[segment].meta?.timestamp) }}
            </div>
          </div>
          
          <div v-else class="mb-2">
            <div class="text-xs text-gray-500">No data loaded</div>
          </div>
          
          <button @click="loadSegment(segment)" 
                  :disabled="loading[segment]"
                  class="w-full px-2 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 disabled:opacity-50">
            {{ loading[segment] ? 'Loading...' : 'Load Segment' }}
          </button>
        </div>
      </div>
    </section>

    <!-- Bulk Operations -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">Bulk Operations</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
          <h3 class="font-medium">Load Multiple Segments</h3>
          <div class="space-y-1">
            <label v-for="segment in segments" :key="segment" class="flex items-center">
              <input type="checkbox" v-model="selectedSegments" :value="segment" class="mr-2">
              <span class="capitalize">{{ segment }}</span>
            </label>
          </div>
          <button @click="loadSelectedSegments" 
                  :disabled="loading.bulk || selectedSegments.length === 0"
                  class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700 disabled:opacity-50">
            {{ loading.bulk ? 'Loading...' : `Load ${selectedSegments.length} Segments` }}
          </button>
        </div>
        
        <div class="space-y-2">
          <h3 class="font-medium">Context Management</h3>
          <div class="space-y-2">
            <button @click="loadAllContext" 
                    :disabled="loading.all"
                    class="w-full px-3 py-1 bg-purple-600 text-white rounded text-sm hover:bg-purple-700 disabled:opacity-50">
              {{ loading.all ? 'Loading...' : 'Load All Context' }}
            </button>
            
            <button @click="updateContext" 
                    :disabled="loading.update"
                    class="w-full px-3 py-1 bg-orange-600 text-white rounded text-sm hover:bg-orange-700 disabled:opacity-50">
              {{ loading.update ? 'Updating...' : 'Update Context' }}
            </button>
            
            <button @click="clearCache" 
                    :disabled="loading.clear"
                    class="w-full px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700 disabled:opacity-50">
              {{ loading.clear ? 'Clearing...' : 'Clear Cache' }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- API Response Display -->
    <section class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold mb-4">API Responses</h2>
      
      <div class="space-y-4">
        <!-- Health Check Response -->
        <div v-if="healthData">
          <h3 class="font-medium mb-2">Health Check</h3>
          <pre class="bg-gray-50 p-3 rounded text-xs overflow-auto">{{ JSON.stringify(healthData, null, 2) }}</pre>
        </div>
        
        <!-- Cache Status Response -->
        <div v-if="cacheData">
          <h3 class="font-medium mb-2">Cache Status</h3>
          <pre class="bg-gray-50 p-3 rounded text-xs overflow-auto">{{ JSON.stringify(cacheData, null, 2) }}</pre>
        </div>
        
        <!-- Last API Response -->
        <div v-if="lastResponse">
          <h3 class="font-medium mb-2">Last API Response</h3>
          <pre class="bg-gray-50 p-3 rounded text-xs overflow-auto">{{ JSON.stringify(lastResponse, null, 2) }}</pre>
        </div>
      </div>
    </section>

    <!-- Error Display -->
    <section v-if="errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-6">
      <h2 class="text-xl font-semibold text-red-800 mb-4">Errors</h2>
      <ul class="space-y-2">
        <li v-for="(error, index) in errors" :key="index" class="text-red-600 text-sm">
          <strong>{{ error.operation }}:</strong> {{ error.message }}
          <span class="text-xs text-red-500 ml-2">{{ formatTime(error.timestamp) }}</span>
        </li>
      </ul>
      <button @click="clearErrors" class="mt-2 px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
        Clear Errors
      </button>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { userContextApi, userContextHelpers } from '@/api/userContextApi.js';
import { useNetworkStore } from '@/Stores/networkStore.js';

// Network status
const networkStore = useNetworkStore();
const isOnline = computed(() => networkStore.isOnline);

// Available segments
const segments = ['profile', 'permissions', 'school', 'classroom', 'schedule'];

// Loading states
const loading = ref({
  health: false,
  cache: false,
  all: false,
  update: false,
  clear: false,
  bulk: false,
  profile: false,
  permissions: false,
  school: false,
  classroom: false,
  schedule: false
});

// Data storage
const segmentData = ref({});
const healthData = ref(null);
const cacheData = ref(null);
const lastResponse = ref(null);
const errors = ref([]);

// UI state
const selectedSegments = ref(['profile', 'permissions']);
const apiHealthy = ref(true);
const cacheHealthPercentage = ref(0);

// Helper functions
const formatTime = (timestamp) => {
  if (!timestamp) return 'Unknown';
  return new Date(timestamp).toLocaleTimeString();
};

const addError = (operation, message) => {
  errors.value.unshift({
    operation,
    message,
    timestamp: new Date().toISOString()
  });
  
  // Keep only last 10 errors
  if (errors.value.length > 10) {
    errors.value = errors.value.slice(0, 10);
  }
};

const clearErrors = () => {
  errors.value = [];
};

// API methods
const checkHealth = async () => {
  loading.value.health = true;
  try {
    const response = await userContextApi.healthCheck();
    healthData.value = response;
    lastResponse.value = response;
    apiHealthy.value = response.data?.overall_status === 'healthy';
  } catch (error) {
    addError('Health Check', error.message);
    apiHealthy.value = false;
  } finally {
    loading.value.health = false;
  }
};

const getCacheStatus = async () => {
  loading.value.cache = true;
  try {
    const response = await userContextApi.getCacheStatus();
    cacheData.value = response;
    lastResponse.value = response;
    cacheHealthPercentage.value = response.data?.summary?.cache_health || 0;
  } catch (error) {
    addError('Cache Status', error.message);
  } finally {
    loading.value.cache = false;
  }
};

const loadSegment = async (segment) => {
  loading.value[segment] = true;
  try {
    const response = await userContextHelpers.refreshSegment(segment);
    segmentData.value[segment] = response;
    lastResponse.value = response;
  } catch (error) {
    addError(`Load ${segment}`, error.message);
  } finally {
    loading.value[segment] = false;
  }
};

const loadSelectedSegments = async () => {
  loading.value.bulk = true;
  try {
    const response = await userContextApi.getSegments(selectedSegments.value);
    
    // Update individual segment data
    selectedSegments.value.forEach(segment => {
      if (response.data[`user_${segment}`]) {
        segmentData.value[segment] = {
          data: response.data[`user_${segment}`],
          meta: response.meta
        };
      }
    });
    
    lastResponse.value = response;
  } catch (error) {
    addError('Load Selected Segments', error.message);
  } finally {
    loading.value.bulk = false;
  }
};

const loadAllContext = async () => {
  loading.value.all = true;
  try {
    const response = await userContextApi.getAllContext();
    
    // Update all segment data
    segments.forEach(segment => {
      if (response.data[`user_${segment}`]) {
        segmentData.value[segment] = {
          data: response.data[`user_${segment}`],
          meta: response.meta
        };
      }
    });
    
    lastResponse.value = response;
  } catch (error) {
    addError('Load All Context', error.message);
  } finally {
    loading.value.all = false;
  }
};

const updateContext = async () => {
  loading.value.update = true;
  try {
    const response = await userContextApi.updateContext();
    lastResponse.value = response;
    
    // Refresh cache status
    await getCacheStatus();
  } catch (error) {
    addError('Update Context', error.message);
  } finally {
    loading.value.update = false;
  }
};

const clearCache = async () => {
  loading.value.clear = true;
  try {
    const response = await userContextApi.clearCache();
    lastResponse.value = response;
    
    // Clear local segment data
    segmentData.value = {};
    
    // Refresh cache status
    await getCacheStatus();
  } catch (error) {
    addError('Clear Cache', error.message);
  } finally {
    loading.value.clear = false;
  }
};

// Initialize
onMounted(async () => {
  await checkHealth();
  await getCacheStatus();
});
</script>

<style scoped>
/* Add any component-specific styles here */
</style>
