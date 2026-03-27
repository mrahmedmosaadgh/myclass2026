<template>
  <div class="command-sender" :class="wrapperClass">
    <!-- Header -->
    <div v-if="showHeader" class="mb-4">
      <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
      <p v-if="description" class="text-sm text-gray-600 mt-1">{{ description }}</p>
    </div>
    
    <!-- Connection status -->
    <div v-if="showConnectionStatus" class="mb-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center text-sm">
          <div class="w-2 h-2 rounded-full mr-2" :class="connectionIndicatorClass"></div>
          <span class="text-gray-600">{{ connectionText }}</span>
        </div>
        <div v-if="pendingCount > 0" class="text-sm text-blue-600">
          {{ pendingCount }} pending
        </div>
      </div>
    </div>
    
    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center p-8 bg-gray-50 rounded-lg">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mr-3"></div>
      <span class="text-gray-600">Initializing...</span>
    </div>
    
    <!-- Error state -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div>
          <h3 class="text-red-800 font-medium">Connection Error</h3>
          <p class="text-red-600 text-sm">{{ error }}</p>
        </div>
      </div>
    </div>
    
    <!-- Command interface -->
    <div v-else class="space-y-4">
      <!-- Command buttons -->
      <div v-if="commandButtons.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        <button
          v-for="cmd in commandButtons"
          :key="cmd.id"
          @click="sendCommand(cmd)"
          :disabled="!canSendCommand || cmd.disabled"
          class="command-btn"
          :class="[
            cmd.className || defaultButtonClass,
            {
              'opacity-50 cursor-not-allowed': !canSendCommand || cmd.disabled,
              'opacity-75 cursor-wait': sendingCommand === cmd.id
            }
          ]"
        >
          <div class="flex flex-col items-center">
            <svg v-if="cmd.icon" class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" :stroke-width="2" :d="cmd.icon"/>
            </svg>
            <span class="text-sm font-medium">{{ cmd.label }}</span>
            <span v-if="cmd.description" class="text-xs opacity-75 mt-1">{{ cmd.description }}</span>
          </div>
        </button>
      </div>
      
      <!-- Custom command form -->
      <div v-if="showCustomCommand" class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Custom Command</h4>
        
        <div class="space-y-3">
          <!-- Command type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Command Type</label>
            <input
              v-model="customCommand.type"
              type="text"
              placeholder="e.g., update_value, reset_state"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <!-- Command payload -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payload (JSON)</label>
            <textarea
              v-model="customCommand.payloadText"
              placeholder='{"key": "value"}'
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono text-sm"
            ></textarea>
            <div v-if="payloadError" class="text-red-500 text-xs mt-1">{{ payloadError }}</div>
          </div>
          
          <!-- Priority -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <select
              v-model="customCommand.priority"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="normal">Normal</option>
              <option value="high">High</option>
              <option value="low">Low</option>
            </select>
          </div>
          
          <!-- Send button -->
          <button
            @click="sendCustomCommand"
            :disabled="!canSendCustomCommand"
            class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Send Custom Command
          </button>
        </div>
      </div>
      
      <!-- Quick actions -->
      <div v-if="showQuickActions" class="flex flex-wrap gap-2">
        <button
          @click="clearQueue"
          :disabled="pendingCount === 0"
          class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          Clear Queue ({{ pendingCount }})
        </button>
        
        <button
          @click="reconnect"
          class="px-3 py-1 text-sm bg-yellow-200 text-yellow-800 rounded hover:bg-yellow-300 transition-colors"
        >
          Reconnect
        </button>
        
        <button
          @click="showHistory = !showHistory"
          class="px-3 py-1 text-sm bg-purple-200 text-purple-800 rounded hover:bg-purple-300 transition-colors"
        >
          {{ showHistory ? 'Hide' : 'Show' }} History
        </button>
      </div>
      
      <!-- Command history -->
      <div v-if="showHistory && commandHistory.length > 0" class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Command History</h4>
        <div class="space-y-2 max-h-48 overflow-y-auto">
          <div
            v-for="cmd in commandHistory.slice(-10).reverse()"
            :key="cmd.id"
            class="flex items-center justify-between text-sm p-2 bg-white rounded border"
          >
            <div class="flex-1">
              <span class="font-medium">{{ cmd.type }}</span>
              <span v-if="cmd.payload" class="text-gray-500 ml-2">
                {{ JSON.stringify(cmd.payload).substring(0, 50) }}...
              </span>
            </div>
            <div class="flex items-center space-x-2">
              <span class="text-gray-400">{{ formatTime(cmd.timestamp) }}</span>
              <div class="w-2 h-2 rounded-full" :class="getStatusClass(cmd.status)"></div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Status indicator -->
      <div v-if="sendingCommand" class="flex items-center justify-center p-4 bg-blue-50 rounded-lg">
        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500 mr-2"></div>
        <span class="text-blue-600 text-sm">Sending command...</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, inject, watch, onMounted, onUnmounted } from 'vue'
import { useRealtimeChannel } from '../core/composables/useRealtimeChannel.js'

export default {
  name: 'CommandSender',
  props: {
    /**
     * Channel ID to send commands to
     */
    channelId: {
      type: String,
      required: false // Can be injected from ChannelProvider
    },
    
    /**
     * Array of command definitions
     */
    commands: {
      type: Array,
      default: () => []
    },
    
    /**
     * Component title
     */
    title: {
      type: String,
      default: 'Command Sender'
    },
    
    /**
     * Component description
     */
    description: {
      type: String,
      default: ''
    },
    
    /**
     * Show header section
     */
    showHeader: {
      type: Boolean,
      default: true
    },
    
    /**
     * Show connection status
     */
    showConnectionStatus: {
      type: Boolean,
      default: true
    },
    
    /**
     * Show custom command form
     */
    showCustomCommand: {
      type: Boolean,
      default: true
    },
    
    /**
     * Show quick actions
     */
    showQuickActions: {
      type: Boolean,
      default: true
    },
    
    /**
     * Default button style
     */
    buttonStyle: {
      type: String,
      default: 'primary' // primary, secondary, outline
    },
    
    /**
     * Custom CSS classes
     */
    wrapperClass: {
      type: String,
      default: ''
    }
  },
  emits: ['command-sent', 'command-failed', 'connection-change'],
  setup(props, { emit }) {
    // Inject channel if available
    const injectedChannel = inject('channel', null)
    const injectedChannelId = inject('channelId', null)
    
    // Get channel ID from props or injection
    const channelId = computed(() => props.channelId || injectedChannelId)
    
    // State
    const loading = ref(true)
    const error = ref(null)
    const sendingCommand = ref(null)
    const showHistory = ref(false)
    const commandHistory = ref([])
    
    // Custom command form
    const customCommand = ref({
      type: '',
      payloadText: '{}',
      priority: 'normal'
    })
    const payloadError = ref('')
    
    // Channel instance
    let channel = injectedChannel
    
    // Initialize channel if not injected
    if (!channel && channelId.value) {
      channel = useRealtimeChannel(channelId.value, {
        persistence: true,
        debounce: 300
      })
    }
    
    // Computed properties
    const commandButtons = computed(() => {
      return props.commands.map(cmd => ({
        id: cmd.id || cmd.type,
        type: cmd.type,
        label: cmd.label || cmd.type,
        description: cmd.description || '',
        icon: cmd.icon || '',
        payload: cmd.payload || {},
        className: cmd.className || '',
        disabled: cmd.disabled || false
      }))
    })
    
    const defaultButtonClass = computed(() => {
      switch (props.buttonStyle) {
        case 'secondary':
          return 'bg-gray-500 text-white hover:bg-gray-600'
        case 'outline':
          return 'border-2 border-gray-300 text-gray-700 hover:bg-gray-50'
        default:
          return 'bg-blue-500 text-white hover:bg-blue-600'
      }
    })
    
    const connectionStatus = computed(() => {
      if (!channel) return 'disconnected'
      return channel.isConnected.value || 'disconnected'
    })
    
    const connectionIndicatorClass = computed(() => {
      switch (connectionStatus.value) {
        case 'connected':
          return 'bg-green-500'
        case 'reconnecting':
          return 'bg-yellow-500 animate-pulse'
        case 'error':
          return 'bg-red-500'
        default:
          return 'bg-gray-400'
      }
    })
    
    const connectionText = computed(() => {
      switch (connectionStatus.value) {
        case 'connected':
          return 'Connected'
        case 'reconnecting':
          return 'Reconnecting...'
        case 'error':
          return 'Connection Error'
        default:
          return 'Disconnected'
      }
    })
    
    const canSendCommand = computed(() => {
      return channel && connectionStatus.value === 'connected'
    })
    
    const pendingCount = computed(() => {
      return channel?.pendingCommands?.value?.length || 0
    })
    
    const canSendCustomCommand = computed(() => {
      return canSendCommand.value && 
             customCommand.value.type.trim() !== '' && 
             payloadError.value === ''
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
    
    const validatePayload = () => {
      try {
        JSON.parse(customCommand.value.payloadText)
        payloadError.value = ''
        return true
      } catch (err) {
        payloadError.value = 'Invalid JSON: ' + err.message
        return false
      }
    }
    
    const sendCommand = async (commandDef) => {
      if (!canSendCommand.value) return
      
      sendingCommand.value = commandDef.id
      
      try {
        const commandId = await channel.sendCommand(
          commandDef.type,
          commandDef.payload,
          {
            priority: commandDef.priority || 'normal'
          }
        )
        
        // Add to history
        commandHistory.value.push({
          id: commandId,
          type: commandDef.type,
          payload: commandDef.payload,
          timestamp: new Date().toISOString(),
          status: 'sent'
        })
        
        emit('command-sent', {
          id: commandId,
          type: commandDef.type,
          payload: commandDef.payload
        })
        
      } catch (err) {
        console.error('Failed to send command:', err)
        
        // Add failed command to history
        commandHistory.value.push({
          id: Date.now().toString(),
          type: commandDef.type,
          payload: commandDef.payload,
          timestamp: new Date().toISOString(),
          status: 'failed'
        })
        
        emit('command-failed', {
          type: commandDef.type,
          payload: commandDef.payload,
          error: err
        })
      } finally {
        sendingCommand.value = null
      }
    }
    
    const sendCustomCommand = async () => {
      if (!canSendCustomCommand.value) return
      
      let payload = {}
      try {
        payload = JSON.parse(customCommand.value.payloadText)
      } catch (err) {
        return
      }
      
      sendingCommand.value = 'custom'
      
      try {
        const commandId = await channel.sendCommand(
          customCommand.value.type,
          payload,
          {
            priority: customCommand.value.priority
          }
        )
        
        // Add to history
        commandHistory.value.push({
          id: commandId,
          type: customCommand.value.type,
          payload,
          timestamp: new Date().toISOString(),
          status: 'sent'
        })
        
        // Reset form
        customCommand.value.type = ''
        customCommand.value.payloadText = '{}'
        customCommand.value.priority = 'normal'
        
        emit('command-sent', {
          id: commandId,
          type: customCommand.value.type,
          payload
        })
        
      } catch (err) {
        console.error('Failed to send custom command:', err)
        
        commandHistory.value.push({
          id: Date.now().toString(),
          type: customCommand.value.type,
          payload,
          timestamp: new Date().toISOString(),
          status: 'failed'
        })
        
        emit('command-failed', {
          type: customCommand.value.type,
          payload,
          error: err
        })
      } finally {
        sendingCommand.value = null
      }
    }
    
    const clearQueue = () => {
      if (channel) {
        channel.clearQueue()
      }
    }
    
    const reconnect = () => {
      if (channel) {
        channel.reconnect()
      }
    }
    
    // Watchers
    let unwatchConnection = null
    
    const setupWatchers = () => {
      if (!channel) return
      
      // Watch connection changes
      if (channel.isConnected) {
        unwatchConnection = watch(
          () => channel.isConnected.value,
          (newStatus) => {
            emit('connection-change', newStatus)
          }
        )
      }
    }
    
    // Watch payload text for validation
    watch(() => customCommand.value.payloadText, validatePayload)
    
    // Initialize
    const initialize = async () => {
      try {
        loading.value = true
        error.value = null
        
        if (!channel) {
          throw new Error('No channel available')
        }
        
        setupWatchers()
        loading.value = false
        
      } catch (err) {
        console.error('Failed to initialize CommandSender:', err)
        error.value = err.message
        loading.value = false
      }
    }
    
    // Lifecycle
    onMounted(() => {
      initialize()
    })
    
    onUnmounted(() => {
      if (unwatchConnection) unwatchConnection()
    })
    
    return {
      // State
      loading,
      error,
      sendingCommand,
      showHistory,
      commandHistory,
      customCommand,
      payloadError,
      
      // Computed
      commandButtons,
      defaultButtonClass,
      connectionStatus,
      connectionIndicatorClass,
      connectionText,
      canSendCommand,
      pendingCount,
      canSendCustomCommand,
      
      // Methods
      formatTime,
      getStatusClass,
      sendCommand,
      sendCustomCommand,
      clearQueue,
      reconnect,
      validatePayload
    }
  }
}
</script>

<style scoped>
.command-sender {
  @apply w-full;
}

.command-btn {
  @apply p-3 rounded-lg border transition-all duration-200 flex flex-col items-center justify-center min-h-[80px];
}

.command-btn:hover:not(:disabled) {
  @apply transform scale-105 shadow-lg;
}

.command-btn:active:not(:disabled) {
  @apply transform scale-95;
}

/* Custom scrollbar for history */
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
</style>
