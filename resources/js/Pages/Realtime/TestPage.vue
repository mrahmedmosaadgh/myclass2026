<template>
  <AppLayout title="Realtime System Test">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
          <span class="animate-pulse text-red-500">●</span> Realtime Dashboard
        </h2>
        <div class="flex items-center gap-2 text-sm">
          <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
            User ID: <strong>{{ currentUserId }}</strong>
          </span>
          <span 
            class="px-3 py-1 rounded-full text-white font-bold transition-colors duration-300"
            :class="connectionStatus.firebase === 'Connected' ? 'bg-green-500' : 'bg-red-500'"
          >
            {{ connectionStatus.firebase }}
          </span>
        </div>
      </div>
    </template>

    <div class="py-12 bg-gray-50 dark:bg-gray-900 min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- 1. Private Notifications (Priority) -->
          <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-purple-50 to-white dark:from-gray-700 dark:to-gray-800">
              <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                🔒 Private Channel
                <span class="text-xs font-normal px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full">User Only</span>
              </h3>
              <div class="h-2 w-2 rounded-full bg-green-500 animate-ping"></div>
            </div>
            
            <div class="p-6 space-y-6">
              <!-- Quick Actions -->
              <div class="flex flex-wrap gap-2 mb-4">
                <button @click="fillPrivate('Hello World 👋')" class="quick-tag bg-purple-50 text-purple-600 hover:bg-purple-100">� Hello</button>
                <button @click="fillPrivate('Urgent Alert 🚨')" class="quick-tag bg-red-50 text-red-600 hover:bg-red-100">🚨 Alert</button>
                <button @click="fillPrivate('New Task Assigned ✅')" class="quick-tag bg-blue-50 text-blue-600 hover:bg-blue-100">✅ Task</button>
              </div>

              <div class="flex gap-2">
                <input 
                  v-model="privateMessage" 
                  @keyup.enter="sendPrivateNotification"
                  type="text" 
                  placeholder="Send a private message..."
                  class="flex-1 rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-900 focus:ring-purple-500 focus:border-purple-500 transition-shadow"
                />
                <button 
                  @click="sendPrivateNotification"
                  class="px-6 py-3 bg-purple-600 text-white rounded-xl shadow-lg shadow-purple-200 hover:bg-purple-700 transition-all active:scale-95 font-medium"
                >
                  Send
                </button>
              </div>

              <!-- Result Card -->
              <transition name="fade">
                  <div v-if="privateChannelData" class="bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 rounded-xl p-4">
                    <p class="text-xs text-purple-600 font-bold mb-1 uppercase tracking-wider">Latest Notification</p>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white text-lg">{{ privateChannelData.event }}</p>
                            <p class="text-gray-600 dark:text-gray-300 mt-1">{{ privateChannelData.context?.message || privateChannelData.context }}</p>
                        </div>
                        <span class="text-xs text-purple-400 font-mono">{{ formatTimestamp(privateChannelData.timestamp) }}</span>
                    </div>
                  </div>
                  <div v-else class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-100 rounded-xl">
                    <p>No new notifications</p>
                  </div>
              </transition>
            </div>
          </div>

          <!-- 2. Public Broadcasts -->
          <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-blue-50 to-white dark:from-gray-700 dark:to-gray-800">
              <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                📢 Public Broadcast
                <span class="text-xs font-normal px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">System Wide</span>
              </h3>
            </div>
            
            <div class="p-6 space-y-6">
               <!-- Quick Actions -->
               <div class="flex flex-wrap gap-2 mb-4">
                <button @click="fillPublic('System Maintenance 🛠️')" class="quick-tag bg-yellow-50 text-yellow-600 hover:bg-yellow-100">🛠️ Maint</button>
                <button @click="fillPublic('New Feature Live 🎉')" class="quick-tag bg-green-50 text-green-600 hover:bg-green-100">🎉 Feature</button>
              </div>

              <div class="flex gap-2">
                <input 
                  v-model="publicMessage" 
                  @keyup.enter="sendPublicBroadcast"
                  type="text" 
                  placeholder="Broadcast message..."
                  class="flex-1 rounded-xl border-gray-200 dark:border-gray-600 dark:bg-gray-900 focus:ring-blue-500 focus:border-blue-500 transition-shadow"
                />
                <button 
                  @click="sendPublicBroadcast"
                  class="px-6 py-3 bg-blue-600 text-white rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all active:scale-95 font-medium"
                >
                  Post
                </button>
              </div>

              <!-- Result Card -->
              <transition name="fade">
                  <div v-if="publicChannelData" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4">
                    <p class="text-xs text-blue-600 font-bold mb-1 uppercase tracking-wider">Latest Broadcast</p>
                     <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">{{ publicChannelData.event }}</p>
                            <p class="text-gray-600 dark:text-gray-300 mt-1">{{ publicChannelData.context?.message || publicChannelData.context }}</p>
                        </div>
                        <span class="text-xs text-blue-400 font-mono">{{ formatTimestamp(publicChannelData.timestamp) }}</span>
                    </div>
                  </div>
                  <div v-else class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-100 rounded-xl">
                    <p>No broadcasts</p>
                  </div>
              </transition>
            </div>
          </div>
          
          <!-- 3. Chat Simulation -->
          <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-white dark:from-gray-700 dark:to-gray-800">
              <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                💬 Chat Room
                <span class="text-xs font-normal px-2 py-0.5 bg-green-100 text-green-700 rounded-full">{{ chatRoomId }}</span>
              </h3>
            </div>
            
            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 h-48 overflow-y-auto space-y-3">
                 <div v-if="chatMessages.length === 0" class="h-full flex items-center justify-center text-gray-400">
                    <p>No messages yet</p>
                 </div>
                 <template v-else>
                     <div 
                        v-for="(msg, idx) in chatMessages" 
                        :key="idx" 
                        class="flex flex-col animate-in fade-in slide-in-from-bottom-2"
                        :class="msg.sender.includes(currentUserId) ? 'items-end' : 'items-start'"
                     >
                        <div 
                            class="px-4 py-2 rounded-2xl text-sm max-w-[80%]"
                            :class="msg.sender.includes(currentUserId) 
                                ? 'bg-green-600 text-white rounded-br-none' 
                                : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white border border-gray-100 dark:border-gray-600 rounded-bl-none shadow-sm'"
                        >
                            {{ msg.message }}
                        </div>
                        <span class="text-[10px] text-gray-400 mt-1 px-1">{{ formatTimestamp(msg.timestamp) }}</span>
                     </div>
                 </template>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                <div class="flex gap-2">
                    <input 
                        v-model="chatMessage" 
                        @keyup.enter="sendChatMessage"
                        type="text" 
                        placeholder="Type a message..."
                        class="flex-1 rounded-full border-gray-200 dark:border-gray-600 dark:bg-gray-900 text-sm px-4 focus:ring-green-500 focus:border-green-500"
                    />
                    <button 
                        @click="sendChatMessage" 
                        class="p-2 bg-green-600 text-white rounded-full hover:bg-green-700 transition-colors shadow-md"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                    </button>
                </div>
            </div>
          </div>

          <!-- 4. Error & Debug -->
           <div class="bg-white dark:bg-gray-800 shadow-xl rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-red-50 to-white dark:from-gray-700 dark:to-gray-800">
              <h3 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                ⚠️ Simulation & Logs
              </h3>
            </div>
             <div class="p-6">
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <button @click="simulateConnectionLoss" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 border border-red-100">🚫 Disconnect</button>
                    <button @click="simulateReconnect" class="px-4 py-2 bg-green-50 text-green-600 rounded-lg text-sm font-medium hover:bg-green-100 border border-green-100">✅ Reconnect</button>
                </div>
                 <div class="bg-gray-900 rounded-xl p-4 font-mono text-xs text-green-400 h-32 overflow-y-auto">
                    <div v-for="(log, idx) in errorLog" :key="idx" class="mb-1 border-l-2 border-green-600 pl-2">
                         <span class="text-gray-500">[{{ formatTimestamp(log.timestamp) }}]</span> {{ log.message }}
                    </div>
                     <div v-if="errorLog.length === 0" class="text-gray-600 italic">System ready... No errors.</div>
                 </div>
             </div>
           </div>

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

// Visual State Helper
const fillPrivate = (msg) => { privateMessage.value = msg; };
const fillPublic = (msg) => { publicMessage.value = msg; };

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
  // Manual trigger for UI
  publicChannelData.value = signal;
});

// Private channel
const privateUserId = ref(currentUserId.value);
const privateMessage = ref('');
const privateChannelData = ref(null);

useRealtimeChannel(`user.${currentUserId.value}`, (signal) => {
  console.log('🔒 Private notification received:', signal);
  // Manual trigger for UI
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
  if (!publicMessage.value) return;
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
    if (!privateMessage.value) return;
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
    if (!chatMessage.value) return;
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
    logError('Connection Restored');
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
  if (!timestamp) return 'Now';
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

<style scoped>
.quick-tag {
    @apply px-3 py-1 rounded-full text-xs font-semibold cursor-pointer transition-all border border-transparent;
}
.quick-tag:active {
    @apply scale-95;
}
</style>
