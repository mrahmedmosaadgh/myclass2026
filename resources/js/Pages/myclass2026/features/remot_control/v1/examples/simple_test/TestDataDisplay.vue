<template>
  <div class="test-data-display bg-white rounded-lg shadow-lg p-6">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-800 mb-2">Test Data Display</h2>
      <p class="text-gray-600">Displays and responds to real-time data updates</p>
    </div>
    
    <!-- Connection Status -->
    <div class="mb-4">
      <ConnectionStatus
        :is-connected="channel?.isConnected"
        :connection-attempts="channel?.connectionAttempts"
        :last-error="channel?.lastError"
        variant="detailed"
        @reconnect="channel?.reconnect"
      />
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
      <p class="text-gray-600">Initializing display...</p>
    </div>
    
    <!-- Data Display -->
    <div v-else class="space-y-6">
      <!-- Visual State Display -->
      <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Current State</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Count Display -->
          <div class="bg-white rounded-lg p-4 text-center">
            <div class="text-3xl font-bold text-blue-600">{{ state.count }}</div>
            <div class="text-sm text-gray-600 mt-1">Count</div>
          </div>
          
          <!-- Message Display -->
          <div class="bg-white rounded-lg p-4 text-center">
            <div class="text-lg font-semibold text-purple-600 truncate">{{ state.message }}</div>
            <div class="text-sm text-gray-600 mt-1">Message</div>
          </div>
          
          <!-- Color Display -->
          <div class="bg-white rounded-lg p-4 text-center">
            <div 
              class="w-12 h-12 rounded-full mx-auto mb-2 transition-all duration-300"
              :style="{ backgroundColor: state.color }"
            ></div>
            <div class="text-sm text-gray-600">{{ state.color }}</div>
          </div>
        </div>
      </div>
      
      <!-- Interactive Elements -->
      <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Interactive Elements</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Counter Animation -->
          <div>
            <h4 class="text-sm font-medium text-gray-700 mb-2">Counter Animation</h4>
            <div class="flex items-center space-x-4">
              <button 
                @click="localCount = Math.max(0, localCount - 1)"
                class="px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors"
              >
                -
              </button>
              <div class="text-2xl font-bold text-gray-800 w-12 text-center">{{ localCount }}</div>
              <button 
                @click="localCount = localCount + 1"
                class="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors"
              >
                +
              </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Local counter (not synced)</p>
          </div>
          
          <!-- Color Preview -->
          <div>
            <h4 class="text-sm font-medium text-gray-700 mb-2">Color Preview</h4>
            <div class="flex items-center space-x-3">
              <div 
                class="w-16 h-16 rounded-lg border-2 border-gray-300 transition-all duration-300"
                :style="{ backgroundColor: state.color }"
              ></div>
              <div>
                <div class="font-mono text-sm">{{ state.color }}</div>
                <div class="text-xs text-gray-500">Current color</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Command Log -->
      <div class="bg-white border rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold text-gray-800">Command Log</h3>
          <button 
            @click="clearLog"
            class="text-sm text-gray-500 hover:text-gray-700"
          >
            Clear Log
          </button>
        </div>
        
        <div v-if="commandLog.length === 0" class="text-center py-4 text-gray-500">
          No commands received yet
        </div>
        
        <div v-else class="space-y-2 max-h-48 overflow-y-auto">
          <div
            v-for="(entry, index) in commandLog.slice(-10).reverse()"
            :key="index"
            class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm"
          >
            <div class="flex items-center space-x-3">
              <div 
                class="w-2 h-2 rounded-full"
                :class="getCommandTypeColor(entry.type)"
              ></div>
              <span class="font-medium">{{ entry.type }}</span>
              <span v-if="entry.payload" class="text-gray-500">
                {{ JSON.stringify(entry.payload).substring(0, 30) }}...
              </span>
            </div>
            <span class="text-gray-400">{{ formatTime(entry.timestamp) }}</span>
          </div>
        </div>
      </div>
      
      <!-- Raw State (for debugging) -->
      <div class="bg-gray-900 rounded-lg p-4">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-sm font-medium text-gray-400">Raw State Data</h3>
          <button 
            @click="copyState"
            class="text-gray-400 hover:text-white transition-colors"
            title="Copy to clipboard"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
          </button>
        </div>
        <pre class="text-green-400 text-sm font-mono overflow-x-auto">{{ JSON.stringify(state, null, 2) }}</pre>
      </div>
    </div>
    
    <!-- Copy Notification -->
    <div v-if="showCopyNotification" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transition-all">
      Copied to clipboard!
    </div>
  </div>
</template>

<script>
import { ref, computed, inject, watch, onMounted, onUnmounted } from 'vue'
import { useRealtimeChannel } from '../../core/composables/useRealtimeChannel.js'
import ConnectionStatus from '../../components/ConnectionStatus.vue'

export default {
  name: 'TestDataDisplay',
  components: {
    ConnectionStatus
  },
  props: {
    /**
     * Channel ID to connect to
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
    const state = ref({
      count: 0,
      message: 'Hello World',
      color: '#3b82f6'
    })
    const localCount = ref(0)
    const commandLog = ref([])
    const showCopyNotification = ref(false)
    
    // Channel instance
    const channel = injectedChannel || useRealtimeChannel(props.channelId, {
      persistence: true,
      debounce: 300,
      validateCommands: true
    })
    
    // Default state
    const defaultState = {
      count: 0,
      message: 'Hello World',
      color: '#3b82f6'
    }
    
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
    
    const getCommandTypeColor = (type) => {
      switch (type) {
        case 'increment':
          return 'bg-green-500'
        case 'decrement':
          return 'bg-red-500'
        case 'reset':
          return 'bg-yellow-500'
        case 'set_message':
          return 'bg-blue-500'
        case 'set_color':
          return 'bg-purple-500'
        default:
          return 'bg-gray-500'
      }
    }
    
    const clearLog = () => {
      commandLog.value = []
    }
    
    const copyState = async () => {
      try {
        await navigator.clipboard.writeText(JSON.stringify(state.value, null, 2))
        showCopyNotification.value = true
        
        setTimeout(() => {
          showCopyNotification.value = false
        }, 2000)
      } catch (err) {
        console.error('Failed to copy state:', err)
      }
    }
    
    const handleCommand = (command) => {
      // Add to command log
      commandLog.value.push({
        type: command.type,
        payload: command.payload,
        timestamp: new Date().toISOString()
      })
      
      // Limit log size
      if (commandLog.value.length > 50) {
        commandLog.value = commandLog.value.slice(-50)
      }
      
      // Handle different command types
      switch (command.type) {
        case 'increment':
          state.value.count = (state.value.count || 0) + (command.payload?.amount || 1)
          break
          
        case 'decrement':
          state.value.count = Math.max(0, (state.value.count || 0) - (command.payload?.amount || 1))
          break
          
        case 'reset':
          state.value = { ...defaultState }
          localCount.value = 0
          break
          
        case 'set_message':
          if (command.payload?.message) {
            state.value.message = command.payload.message
          }
          break
          
        case 'set_color':
          if (command.payload?.color) {
            state.value.color = command.payload.color
          }
          break
          
        case 'set_count':
          if (typeof command.payload?.count === 'number') {
            state.value.count = Math.max(0, command.payload.count)
            localCount.value = state.value.count
          }
          break
          
        default:
          console.log('Unknown command type:', command.type, command.payload)
      }
      
      // Update channel state
      if (channel) {
        channel.updateState(state.value)
      }
    }
    
    // Watch for state changes from channel
    const unwatchState = watch(() => channel.state.value, (newState) => {
      if (newState && newState.data) {
        state.value = newState.data
        localCount.value = newState.data.count || 0
        loading.value = false
        emit('state-change', newState)
      }
    })
    
    // Watch for connection
    const unwatchConnection = watch(() => channel.isConnected.value, (isConnected) => {
      emit('connection-change', isConnected)
      if (isConnected && !state.value) {
        // Initialize state when connected
        channel.updateState(defaultState)
      }
    })
    
    // Set up command listener
    const unwatchCommand = channel?.onCommand?.(handleCommand)
    
    // Initialize
    const initialize = async () => {
      try {
        loading.value = true
        
        // Wait a bit for channel to initialize
        await new Promise(resolve => setTimeout(resolve, 500))
        
        // Set initial state if not already set
        if (channel && channel.state.value?.data) {
          state.value = channel.state.value.data
          localCount.value = channel.state.value.data.count || 0
        } else if (channel) {
          channel.updateState(defaultState)
        }
        
        loading.value = false
        
      } catch (err) {
        console.error('Failed to initialize TestDataDisplay:', err)
        loading.value = false
      }
    }
    
    // Lifecycle
    onMounted(() => {
      initialize()
    })
    
    onUnmounted(() => {
      if (unwatchState) unwatchState()
      if (unwatchConnection) unwatchConnection()
      if (unwatchCommand) unwatchCommand()
    })
    
    return {
      // State
      loading,
      state,
      localCount,
      commandLog,
      showCopyNotification,
      channel,
      
      // Methods
      formatTime,
      getCommandTypeColor,
      clearLog,
      copyState
    }
  }
}
</script>

<style scoped>
.test-data-display {
  max-width: 800px;
  margin: 0 auto;
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
</style>
