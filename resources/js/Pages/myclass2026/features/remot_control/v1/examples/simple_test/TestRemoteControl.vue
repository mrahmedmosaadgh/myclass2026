<template>
  <div class="test-remote-control bg-white rounded-lg shadow-lg p-6">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Test Remote Control</h2>
      <p class="text-gray-600">Send commands to control the display</p>
    </div>
    
    <!-- Connection Status -->
    <div class="mb-4">
      <ConnectionStatus
        :is-connected="channel?.isConnected"
        :connection-attempts="channel?.connectionAttempts"
        :last-error="channel?.lastError"
        :pending-commands="channel?.pendingCommands?.length"
        variant="detailed"
        @reconnect="channel?.reconnect"
      />
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
      <p class="text-gray-600">Initializing remote control...</p>
    </div>
    
    <!-- Control Interface -->
    <div v-else class="space-y-6">
      <!-- Quick Actions -->
      <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <button
            @click="sendCommand('increment', { amount: 1 })"
            :disabled="!canSendCommand"
            class="control-btn bg-green-500 hover:bg-green-600 text-white"
          >
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span class="text-sm font-medium">Increment</span>
          </button>
          
          <button
            @click="sendCommand('decrement', { amount: 1 })"
            :disabled="!canSendCommand"
            class="control-btn bg-red-500 hover:bg-red-600 text-white"
          >
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
            <span class="text-sm font-medium">Decrement</span>
          </button>
          
          <button
            @click="sendCommand('reset')"
            :disabled="!canSendCommand"
            class="control-btn bg-yellow-500 hover:bg-yellow-600 text-white"
          >
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="text-sm font-medium">Reset</span>
          </button>
          
          <button
            @click="sendRandomColor"
            :disabled="!canSendCommand"
            class="control-btn bg-purple-500 hover:bg-purple-600 text-white"
          >
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
            </svg>
            <span class="text-sm font-medium">Random Color</span>
          </button>
        </div>
      </div>
      
      <!-- Message Control -->
      <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Message Control</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Custom Message</label>
            <div class="flex space-x-3">
              <input
                v-model="customMessage"
                type="text"
                placeholder="Enter a message..."
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                @keyup.enter="sendCustomMessage"
              />
              <button
                @click="sendCustomMessage"
                :disabled="!canSendCommand || !customMessage.trim()"
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Send Message
              </button>
            </div>
          </div>
          
          <!-- Quick Messages -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quick Messages</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="msg in quickMessages"
                :key="msg"
                @click="sendCommand('set_message', { message: msg })"
                :disabled="!canSendCommand"
                class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                {{ msg }}
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Color Control -->
      <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Color Control</h3>
        
        <div class="space-y-4">
          <!-- Color Picker -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Color Picker</label>
            <div class="flex items-center space-x-4">
              <input
                v-model="selectedColor"
                type="color"
                class="w-16 h-16 border-2 border-gray-300 rounded cursor-pointer"
              />
              <div class="flex-1">
                <input
                  v-model="selectedColor"
                  type="text"
                  placeholder="#3b82f6"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                />
                <p class="text-xs text-gray-500 mt-1">Hex color code</p>
              </div>
              <button
                @click="sendSelectedColor"
                :disabled="!canSendCommand"
                class="px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Apply Color
              </button>
            </div>
          </div>
          
          <!-- Preset Colors -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Preset Colors</label>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="color in presetColors"
                :key="color"
                @click="sendCommand('set_color', { color })"
                :disabled="!canSendCommand"
                class="w-10 h-10 rounded-lg border-2 border-gray-300 hover:scale-110 transition-transform disabled:opacity-50 disabled:cursor-not-allowed"
                :style="{ backgroundColor: color }"
                :title="color"
              ></button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Advanced Controls -->
      <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Advanced Controls</h3>
        
        <div class="space-y-4">
          <!-- Count Control -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Set Count</label>
            <div class="flex items-center space-x-3">
              <input
                v-model.number="customCount"
                type="number"
                min="0"
                max="100"
                class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
                @click="sendCommand('set_count', { count: customCount })"
                :disabled="!canSendCommand || customCount < 0"
                class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Set Count
              </button>
              <button
                @click="sendCommand('increment', { amount: 5 })"
                :disabled="!canSendCommand"
                class="px-3 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                +5
              </button>
              <button
                @click="sendCommand('increment', { amount: 10 })"
                :disabled="!canSendCommand"
                class="px-3 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                +10
              </button>
            </div>
          </div>
          
          <!-- Custom Command -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Custom Command</label>
            <div class="space-y-3">
              <input
                v-model="customCommand.type"
                type="text"
                placeholder="Command type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <textarea
                v-model="customCommand.payload"
                placeholder='{"key": "value"}'
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
              ></textarea>
              <button
                @click="sendCustomCommand"
                :disabled="!canSendCommand || !customCommand.type.trim()"
                class="w-full px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                Send Custom Command
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Command History -->
      <div class="bg-white border rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold text-gray-800">Command History</h3>
          <div class="flex space-x-2">
            <button 
              @click="clearHistory"
              class="text-sm text-gray-500 hover:text-gray-700"
            >
              Clear
            </button>
            <button 
              @click="showHistory = !showHistory"
              class="text-sm text-gray-500 hover:text-gray-700"
            >
              {{ showHistory ? 'Hide' : 'Show' }}
            </button>
          </div>
        </div>
        
        <div v-if="!showHistory || commandHistory.length === 0" class="text-center py-4 text-gray-500">
          No commands sent yet
        </div>
        
        <div v-else class="space-y-2 max-h-48 overflow-y-auto">
          <div
            v-for="(cmd, index) in commandHistory.slice(-10).reverse()"
            :key="index"
            class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm"
          >
            <div class="flex items-center space-x-3">
              <div 
                class="w-2 h-2 rounded-full"
                :class="getStatusClass(cmd.status)"
              ></div>
              <span class="font-medium">{{ cmd.type }}</span>
              <span v-if="cmd.payload" class="text-gray-500">
                {{ JSON.stringify(cmd.payload).substring(0, 30) }}...
              </span>
            </div>
            <span class="text-gray-400">{{ formatTime(cmd.timestamp) }}</span>
          </div>
        </div>
      </div>
      
      <!-- Current State Display -->
      <div class="bg-gray-900 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-400 mb-3">Current State</h3>
        <pre class="text-green-400 text-sm font-mono overflow-x-auto">{{ JSON.stringify(currentState, null, 2) }}</pre>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, inject, watch, onMounted, onUnmounted } from 'vue'
import { useRealtimeChannel } from '../../core/composables/useRealtimeChannel.js'
import ConnectionStatus from '../../components/ConnectionStatus.vue'

export default {
  name: 'TestRemoteControl',
  components: {
    ConnectionStatus
  },
  props: {
    /**
     * Channel ID to control
     */
    channelId: {
      type: String,
      default: 'test-channel-1'
    }
  },
  setup(props, { emit }) {
    // Inject channel if available
    const injectedChannel = inject('channel', null)
    
    // State
    const loading = ref(true)
    const showHistory = ref(true)
    const commandHistory = ref([])
    const currentState = ref({})
    
    // Form inputs
    const customMessage = ref('')
    const selectedColor = ref('#3b82f6')
    const customCount = ref(0)
    const customCommand = ref({
      type: '',
      payload: '{}'
    })
    
    // Channel instance
    const channel = injectedChannel || useRealtimeChannel(props.channelId, {
      persistence: true,
      debounce: 300,
      validateCommands: true
    })
    
    // Quick messages
    const quickMessages = [
      'Hello World',
      'Testing...',
      'Remote Control Active',
      'System Online',
      'Success!',
      'Error: Test'
    ]
    
    // Preset colors
    const presetColors = [
      '#ef4444', // red
      '#f97316', // orange
      '#eab308', // yellow
      '#84cc16', // lime
      '#22c55e', // green
      '#14b8a6', // teal
      '#06b6d4', // cyan
      '#3b82f6', // blue
      '#6366f1', // indigo
      '#8b5cf6', // violet
      '#a855f7', // purple
      '#ec4899'  // pink
    ]
    
    // Computed
    const canSendCommand = computed(() => {
      return channel && channel.isConnected.value === 'connected'
    })
    
    // Methods
    const formatTime = (timestamp) => {
      if (!timestamp) return ''
      
      const date = new Date(timestamp)
      const now = new Date()
      const diff = now - date
      
      if (diff < 60000) {
        return 'just now'
      } else if (diff < 3600000) {
        return `${Math.floor(diff / 60000)}m ago`
      } else {
        return date.toLocaleTimeString()
      }
    }
    
    const getStatusClass = (status) => {
      switch (status) {
        case 'sent':
          return 'bg-green-500'
        case 'failed':
          return 'bg-red-500'
        case 'pending':
          return 'bg-yellow-500'
        default:
          return 'bg-gray-400'
      }
    }
    
    const sendCommand = async (type, payload = {}) => {
      if (!canSendCommand.value) return
      
      try {
        const commandId = await channel.sendCommand(type, payload)
        
        // Add to history
        commandHistory.value.push({
          id: commandId,
          type,
          payload,
          timestamp: new Date().toISOString(),
          status: 'sent'
        })
        
      } catch (err) {
        console.error('Failed to send command:', err)
        
        commandHistory.value.push({
          id: Date.now().toString(),
          type,
          payload,
          timestamp: new Date().toISOString(),
          status: 'failed'
        })
      }
    }
    
    const sendCustomMessage = () => {
      if (!customMessage.value.trim()) return
      sendCommand('set_message', { message: customMessage.value })
      customMessage.value = ''
    }
    
    const sendSelectedColor = () => {
      sendCommand('set_color', { color: selectedColor.value })
    }
    
    const sendRandomColor = () => {
      const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')
      selectedColor.value = randomColor
      sendCommand('set_color', { color: randomColor })
    }
    
    const sendCustomCommand = () => {
      if (!customCommand.value.type.trim()) return
      
      let payload = {}
      try {
        if (customCommand.value.payload.trim()) {
          payload = JSON.parse(customCommand.value.payload)
        }
      } catch (err) {
        console.error('Invalid JSON payload:', err)
        return
      }
      
      sendCommand(customCommand.value.type, payload)
      customCommand.value = { type: '', payload: '{}' }
    }
    
    const clearHistory = () => {
      commandHistory.value = []
    }
    
    // Watch for state changes
    const unwatchState = watch(() => channel.state.value, (newState) => {
      if (newState && newState.data) {
        currentState.value = newState.data
        loading.value = false
        emit('state-change', newState)
      }
    })
    
    // Watch for connection changes
    const unwatchConnection = watch(() => channel.isConnected.value, (newStatus) => {
      emit('connection-change', newStatus)
    })
    
    // Initialize
    const initialize = async () => {
      try {
        loading.value = true
        
        // Wait for channel to initialize
        await new Promise(resolve => setTimeout(resolve, 500))
        
        // Get current state
        if (channel && channel.state.value?.data) {
          currentState.value = channel.state.value.data
          customCount.value = currentState.value.count || 0
          selectedColor.value = currentState.value.color || '#3b82f6'
        }
        
        loading.value = false
        
      } catch (err) {
        console.error('Failed to initialize TestRemoteControl:', err)
        loading.value = false
      }
    }
    
    // Lifecycle
    onMounted(() => {
      initialize()
    })
    
    onUnmounted(() => {
      if (unwatchState) unwatchState()
    })
    
    return {
      // State
      loading,
      showHistory,
      commandHistory,
      currentState,
      customMessage,
      selectedColor,
      customCount,
      customCommand,
      channel,
      
      // Data
      quickMessages,
      presetColors,
      
      // Computed
      canSendCommand,
      
      // Methods
      formatTime,
      getStatusClass,
      sendCommand,
      sendCustomMessage,
      sendSelectedColor,
      sendRandomColor,
      sendCustomCommand,
      clearHistory
    }
  }
}
</script>

<style scoped>
.test-remote-control {
  max-width: 800px;
  margin: 0 auto;
}

.control-btn {
  @apply p-4 rounded-lg transition-all duration-200 flex flex-col items-center justify-center min-h-[80px];
}

.control-btn:hover:not(:disabled) {
  @apply transform scale-105 shadow-lg;
}

.control-btn:active:not(:disabled) {
  @apply transform scale-95;
}

/* Custom scrollbar */
.max-h-48 {
  scrollbar-width: thin;
  scrollbar-color: #CBD5E0 #F7FAFC;
}

.max-h-48::-webkit-scrollbar {
  width: 4px;
}

.max-h-48::-webkit-scrollbar-track {
  background: #F7FAFC;
}

.max-h-48::-webkit-scrollbar-thumb {
  background-color: #CBD5E0;
  border-radius: 2px;
}

.max-h-48::-webkit-scrollbar-thumb:hover {
  background-color: #A0AEC0;
}

/* JSON display scrollbar */
.bg-gray-900 {
  scrollbar-width: thin;
  scrollbar-color: #4B5563 #1F2937;
}

.bg-gray-900::-webkit-scrollbar {
  width: 6px;
}

.bg-gray-900::-webkit-scrollbar-track {
  background: #1F2937;
}

.bg-gray-900::-webkit-scrollbar-thumb {
  background-color: #4B5563;
  border-radius: 3px;
}

.bg-gray-900::-webkit-scrollbar-thumb:hover {
  background-color: #6B7280;
}

/* Color picker styling */
input[type="color"] {
  cursor: pointer;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 0.375rem;
}
</style>
