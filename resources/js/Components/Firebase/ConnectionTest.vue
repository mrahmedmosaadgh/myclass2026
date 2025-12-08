<template>
  <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
    <h2 class="text-lg font-semibold mb-4">Firebase Connection Test</h2>
    
    <div class="mb-4">
      <div class="flex items-center mb-2">
        <div class="w-3 h-3 rounded-full mr-2" :class="connectionStatus.color"></div>
        <span>{{ connectionStatus.text }}</span>
      </div>
      <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ connectionMessage }}
      </p>
    </div>
    
    <div class="space-y-4">
      <div>
        <h3 class="font-medium mb-2">Test Write Operation</h3>
        <div class="flex space-x-2">
          <input 
            v-model="testMessage" 
            type="text" 
            class="flex-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Enter a test message"
          />
          <button 
            @click="writeTestData" 
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            :disabled="writing"
          >
            {{ writing ? 'Writing...' : 'Write' }}
          </button>
        </div>
        <p v-if="writeResult" class="mt-2 text-sm" :class="writeResult.success ? 'text-green-600' : 'text-red-600'">
          {{ writeResult.message }}
        </p>
      </div>
      
      <div>
        <h3 class="font-medium mb-2">Test Read Operation</h3>
        <button 
          @click="readTestData" 
          class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
          :disabled="reading"
        >
          {{ reading ? 'Reading...' : 'Read Test Data' }}
        </button>
        <div v-if="readResult" class="mt-2">
          <p class="text-sm" :class="readResult.success ? 'text-green-600' : 'text-red-600'">
            {{ readResult.message }}
          </p>
          <pre v-if="readResult.data" class="mt-2 p-2 bg-gray-100 dark:bg-gray-700 rounded text-xs overflow-auto">{{ JSON.stringify(readResult.data, null, 2) }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { database } from '@/firebase/init';
import { ref as dbRef, onValue, set, get, push, serverTimestamp, off } from 'firebase/database';

// State
const connected = ref(false);
const connectionMessage = ref('Checking connection...');
const testMessage = ref('Hello from Firebase test!');
const writing = ref(false);
const reading = ref(false);
const writeResult = ref(null);
const readResult = ref(null);
const connectionRef = ref(null);

// Computed
const connectionStatus = computed(() => {
  if (connected.value) {
    return {
      text: 'Connected',
      color: 'bg-green-500'
    };
  } else {
    return {
      text: 'Disconnected',
      color: 'bg-red-500'
    };
  }
});

// Methods
const checkConnection = () => {
  connectionRef.value = dbRef(database, '.info/connected');
  
  onValue(connectionRef.value, (snapshot) => {
    connected.value = snapshot.val() === true;
    connectionMessage.value = connected.value 
      ? 'Successfully connected to Firebase Realtime Database.' 
      : 'Not connected to Firebase Realtime Database. Check your configuration.';
  });
};

const writeTestData = async () => {
  if (!connected.value) {
    writeResult.value = {
      success: false,
      message: 'Cannot write data: Not connected to Firebase.'
    };
    return;
  }
  
  writing.value = true;
  writeResult.value = null;
  
  try {
    const testRef = dbRef(database, 'firebase_test/messages');
    const newMessageRef = push(testRef);
    
    await set(newMessageRef, {
      text: testMessage.value,
      timestamp: serverTimestamp()
    });
    
    writeResult.value = {
      success: true,
      message: 'Test message written successfully!'
    };
  } catch (error) {
    console.error('Error writing test data:', error);
    writeResult.value = {
      success: false,
      message: `Error writing data: ${error.message}`
    };
  } finally {
    writing.value = false;
  }
};

const readTestData = async () => {
  if (!connected.value) {
    readResult.value = {
      success: false,
      message: 'Cannot read data: Not connected to Firebase.'
    };
    return;
  }
  
  reading.value = true;
  readResult.value = null;
  
  try {
    const testRef = dbRef(database, 'firebase_test/messages');
    const snapshot = await get(testRef);
    
    if (snapshot.exists()) {
      readResult.value = {
        success: true,
        message: 'Data retrieved successfully!',
        data: snapshot.val()
      };
    } else {
      readResult.value = {
        success: true,
        message: 'No test messages found. Try writing some data first.',
        data: null
      };
    }
  } catch (error) {
    console.error('Error reading test data:', error);
    readResult.value = {
      success: false,
      message: `Error reading data: ${error.message}`
    };
  } finally {
    reading.value = false;
  }
};

// Lifecycle hooks
onMounted(() => {
  checkConnection();
});

onUnmounted(() => {
  if (connectionRef.value) {
    off(connectionRef.value);
  }
});
</script>
