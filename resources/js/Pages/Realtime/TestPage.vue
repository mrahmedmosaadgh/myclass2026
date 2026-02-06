<template>
  <AppLayout title="Realtime System Test">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        🔴 Realtime System Test Dashboard
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Connection Status -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
            <span :class="connectionStatus.color">●</span>
            Connection Status
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
              <p class="text-sm text-gray-600 dark:text-gray-400">Firebase Status</p>
              <p class="text-lg font-bold" :class="connectionStatus.color">
                {{ connectionStatus.firebase }}
              </p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
              <p class="text-sm text-gray-600 dark:text-gray-400">Database URL</p>
              <p class="text-xs font-mono break-all">{{ databaseUrl }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
              <p class="text-sm text-gray-600 dark:text-gray-400">Active Listeners</p>
              <p class="text-lg font-bold">{{ activeListeners }}</p>
            </div>
          </div>
        </div>

        <!-- Public Channel Test -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h3 class="text-lg font-bold mb-4">📢 Public Channel Test</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Test system-wide broadcasts that all users can receive
          </p>
          
          <div class="space-y-4">
            <div class="flex gap-2">
              <input 
                v-model="publicMessage" 
                type="text" 
                placeholder="Enter broadcast message"
                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <button 
                @click="sendPublicBroadcast"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                Broadcast
              </button>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded max-h-48 overflow-y-auto">
              <p class="text-xs text-gray-500 mb-2">Received Broadcasts:</p>
              <div v-if="publicChannelData" class="space-y-2">
                <div class="bg-white dark:bg-gray-800 p-2 rounded text-sm">
                  <p><strong>Event:</strong> {{ publicChannelData.event }}</p>
                  <p><strong>Context:</strong> {{ JSON.stringify(publicChannelData.context) }}</p>
                  <p class="text-xs text-gray-500">{{ formatTimestamp(publicChannelData.timestamp) }}</p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500">No broadcasts received yet</p>
            </div>
          </div>
        </div>

        <!-- Private Channel Test -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h3 class="text-lg font-bold mb-4">🔒 Private Channel Test (User-Specific)</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Test private notifications sent only to specific users
          </p>
          
          <div class="space-y-4">
            <div class="flex gap-2">
              <input 
                v-model="privateUserId" 
                type="number" 
                placeholder="Target User ID"
                class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <input 
                v-model="privateMessage" 
                type="text" 
                placeholder="Private message"
                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <button 
                @click="sendPrivateNotification"
                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700"
              >
                Send Private
              </button>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded max-h-48 overflow-y-auto">
              <p class="text-xs text-gray-500 mb-2">Your Private Notifications (User {{ currentUserId }}):</p>
              <div v-if="privateChannelData" class="space-y-2">
                <div class="bg-white dark:bg-gray-800 p-2 rounded text-sm">
                  <p><strong>Event:</strong> {{ privateChannelData.event }}</p>
                  <p><strong>Context:</strong> {{ JSON.stringify(privateChannelData.context) }}</p>
                  <p class="text-xs text-gray-500">{{ formatTimestamp(privateChannelData.timestamp) }}</p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500">No private notifications received</p>
            </div>
          </div>
        </div>

        <!-- Chat Notification Test -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h3 class="text-lg font-bold mb-4">💬 Chat Notification Test</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Test real-time chat notifications (no database storage)
          </p>
          
          <div class="space-y-4">
            <div class="flex gap-2">
              <input 
                v-model="chatRoomId" 
                type="text" 
                placeholder="Chat Room ID"
                class="w-40 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <input 
                v-model="chatMessage" 
                type="text" 
                placeholder="Chat message"
                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <button 
                @click="sendChatMessage"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
              >
                Send Chat
              </button>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded max-h-64 overflow-y-auto">
              <p class="text-xs text-gray-500 mb-2">Chat Messages (Room: {{ chatRoomId || 'none' }}):</p>
              <div v-if="chatMessages.length > 0" class="space-y-2">
                <div 
                  v-for="(msg, idx) in chatMessages" 
                  :key="idx"
                  class="bg-white dark:bg-gray-800 p-2 rounded text-sm"
                >
                  <p><strong>{{ msg.sender }}:</strong> {{ msg.message }}</p>
                  <p class="text-xs text-gray-500">{{ formatTimestamp(msg.timestamp) }}</p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500">No messages yet</p>
            </div>
          </div>
        </div>

        <!-- Live Question/Response Test -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h3 class="text-lg font-bold mb-4">❓ Live Question/Response Test</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Test live question responses collected on-the-fly (no database)
          </p>
          
          <div class="space-y-4">
            <div class="flex gap-2">
              <input 
                v-model="questionId" 
                type="text" 
                placeholder="Question ID"
                class="w-40 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <input 
                v-model="responseText" 
                type="text" 
                placeholder="Your response"
                class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
              />
              <button 
                @click="submitResponse"
                class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
              >
                Submit Response
              </button>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded max-h-64 overflow-y-auto">
              <p class="text-xs text-gray-500 mb-2">Live Responses (Question: {{ questionId || 'none' }}):</p>
              <div v-if="liveResponses.length > 0" class="space-y-2">
                <div 
                  v-for="(response, idx) in liveResponses" 
                  :key="idx"
                  class="bg-white dark:bg-gray-800 p-2 rounded text-sm"
                >
                  <p><strong>User {{ response.userId }}:</strong> {{ response.answer }}</p>
                  <p class="text-xs text-gray-500">{{ formatTimestamp(response.timestamp) }}</p>
                </div>
              </div>
              <p v-else class="text-sm text-gray-500">No responses yet</p>
            </div>
          </div>
        </div>

        <!-- Error Simulation -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h3 class="text-lg font-bold mb-4 text-red-600">⚠️ Error Simulation & Testing</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Test error handling and connection issues
          </p>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <button 
              @click="simulateConnectionLoss"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
            >
              Simulate Disconnect
            </button>
            <button 
              @click="simulateReconnect"
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
            >
              Reconnect
            </button>
            <button 
              @click="testInvalidChannel"
              class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700"
            >
              Invalid Channel
            </button>
            <button 
              @click="clearAllListeners"
              class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
            >
              Clear All
            </button>
          </div>

          <div v-if="errorLog.length > 0" class="mt-4 bg-red-50 dark:bg-red-900/20 p-4 rounded max-h-48 overflow-y-auto">
            <p class="text-xs text-red-600 dark:text-red-400 mb-2">Error Log:</p>
            <div class="space-y-1">
              <p 
                v-for="(error, idx) in errorLog" 
                :key="idx"
                class="text-xs font-mono text-red-700 dark:text-red-300"
              >
                [{{ formatTimestamp(error.timestamp) }}] {{ error.message }}
              </p>
            </div>
          </div>
        </div>

        <!-- Debug Info -->
        <div class="bg-gray-900 text-green-400 overflow-hidden shadow-xl sm:rounded-lg p-6 font-mono text-xs">
          <h3 class="text-lg font-bold mb-4">🐛 Debug Information</h3>
          <pre class="overflow-x-auto">{{ debugInfo }}</pre>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useRealtimeChannel } from '@/composables/useRealtimeChannel';
import { database } from '@/firebase/init';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Current user
const currentUserId = computed(() => usePage().props.auth?.user?.id || 1);

// Connection status
const connectionStatus = ref({
  firebase: database ? 'Connected' : 'Disconnected',
  color: database ? 'text-green-600' : 'text-red-600'
});

const databaseUrl = computed(() => {
  try {
    return database?._repoInternal?.databaseURL || 'Not configured';
  } catch {
    return 'Not available';
  }
});

const activeListeners = ref(0);

// Public channel
const publicMessage = ref('');
const publicChannelData = ref(null);

useRealtimeChannel('system.all', (signal) => {
  console.log('📢 Public broadcast received:', signal);
  publicChannelData.value = signal;
});

// Private channel
const privateUserId = ref(currentUserId.value);
const privateMessage = ref('');
const privateChannelData = ref(null);

useRealtimeChannel(`user.${currentUserId.value}`, (signal) => {
  console.log('🔒 Private notification received:', signal);
  // Manually update local ref to ensure UI Reactivity
  privateChannelData.value = signal;
});

// Chat
const chatRoomId = ref('test-room-1');
const chatMessage = ref('');
const chatMessages = ref([]);
let chatListener = null;

// Live questions
const questionId = ref('q1');
const responseText = ref('');
const liveResponses = ref([]);
let questionListener = null;

// Error handling
const errorLog = ref([]);

// Actions
const sendPublicBroadcast = async () => {
  try {
    await axios.post('/api/realtime/test/broadcast', {
      message: publicMessage.value
    });
    publicMessage.value = '';
  } catch (error) {
    logError('Failed to send broadcast: ' + error.message);
  }
};

const sendPrivateNotification = async () => {
  try {
    await axios.post('/api/realtime/test/private', {
      userId: privateUserId.value,
      message: privateMessage.value
    });
    privateMessage.value = '';
  } catch (error) {
    logError('Failed to send private notification: ' + error.message);
  }
};

const sendChatMessage = async () => {
  try {
    await axios.post('/api/realtime/test/chat', {
      roomId: chatRoomId.value,
      message: chatMessage.value,
      sender: `User ${currentUserId.value}`
    });
    chatMessage.value = '';
  } catch (error) {
    logError('Failed to send chat message: ' + error.message);
  }
};

const submitResponse = async () => {
  try {
    await axios.post('/api/realtime/test/question', {
      questionId: questionId.value,
      answer: responseText.value,
      userId: currentUserId.value
    });
    responseText.value = '';
  } catch (error) {
    logError('Failed to submit response: ' + error.message);
  }
};

// Error simulation
const simulateConnectionLoss = () => {
  connectionStatus.value = {
    firebase: 'Disconnected',
    color: 'text-red-600'
  };
  logError('Connection lost (simulated)');
};

const simulateReconnect = () => {
  connectionStatus.value = {
    firebase: 'Connected',
    color: 'text-green-600'
  };
  console.log('✅ Reconnected');
};

const testInvalidChannel = async () => {
  try {
    await axios.post('/api/realtime/test/broadcast', {
      channel: 'invalid..channel..name',
      message: 'This should fail'
    });
  } catch (error) {
    logError('Invalid channel test: ' + error.message);
  }
};

const clearAllListeners = () => {
  chatMessages.value = [];
  liveResponses.value = [];
  errorLog.value = [];
  console.log('🧹 All listeners cleared');
};

// Utilities
const formatTimestamp = (timestamp) => {
  if (!timestamp) return 'N/A';
  return new Date(timestamp * 1000).toLocaleTimeString();
};

const logError = (message) => {
  errorLog.value.unshift({
    message,
    timestamp: Date.now() / 1000
  });
  if (errorLog.value.length > 10) errorLog.value.pop();
};

// Debug info
const debugInfo = computed(() => ({
  firebase_enabled: !!database,
  current_user: currentUserId.value,
  active_channels: {
    public: !!publicChannelData.value,
    private: !!privateChannelData.value,
    chat: chatMessages.value.length,
    questions: liveResponses.value.length
  },
  last_signals: {
    public: publicChannelData.value,
    private: privateChannelData.value
  }
}));

// Setup chat listener
onMounted(() => {
  activeListeners.value = 2; // public + private

  // Chat listener
  chatListener = useRealtimeChannel(`chat.${chatRoomId.value}`, (signal) => {
    if (signal.event === 'NEW_MESSAGE') {
      chatMessages.value.push({
        sender: signal.context.sender,
        message: signal.context.message,
        timestamp: signal.timestamp
      });
      activeListeners.value++;
    }
  });

  // Question listener
  questionListener = useRealtimeChannel(`question.${questionId.value}`, (signal) => {
    if (signal.event === 'NEW_RESPONSE') {
      liveResponses.value.push({
        userId: signal.context.userId,
        answer: signal.context.answer,
        timestamp: signal.timestamp
      });
      activeListeners.value++;
    }
  });
});

onUnmounted(() => {
  if (chatListener) chatListener.stopListening();
  if (questionListener) questionListener.stopListening();
});
</script>
