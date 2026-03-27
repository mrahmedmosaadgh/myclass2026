<template>
  <div class="channel-provider">
    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center p-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
      <span class="ml-2 text-gray-600">Initializing channel...</span>
    </div>
    
    <!-- Error state -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 m-4">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <div>
          <h3 class="text-red-800 font-medium">Channel Error</h3>
          <p class="text-red-600 text-sm">{{ error }}</p>
        </div>
      </div>
    </div>
    
    <!-- Main content -->
    <div v-else>
      <!-- Connection status indicator (optional) -->
      <div v-if="showConnectionStatus" class="mb-2">
        <ConnectionStatus 
          :is-connected="channel?.isConnected" 
          :connection-attempts="channel?.connectionAttempts"
          :last-error="channel?.lastError"
        />
      </div>
      
      <!-- Provide channel context to children -->
      <slot :channel="channel" />
    </div>
  </div>
</template>

<script>
import { ref, computed, provide, watch, onMounted, onUnmounted } from 'vue'
import { useRealtimeChannel } from '../core/composables/useRealtimeChannel.js'
import ConnectionStatus from './ConnectionStatus.vue'

export default {
  name: 'ChannelProvider',
  components: {
    ConnectionStatus
  },
  props: {
    /**
     * Unique channel identifier
     */
    channelId: {
      type: String,
      required: true
    },
    
    /**
     * Channel configuration options
     */
    options: {
      type: Object,
      default: () => ({})
    },
    
    /**
     * Whether to show connection status
     */
    showConnectionStatus: {
      type: Boolean,
      default: false
    },
    
    /**
     * Auto-connect on mount
     */
    autoConnect: {
      type: Boolean,
      default: true
    }
  },
  setup(props, { emit }) {
    const loading = ref(true)
    const error = ref(null)
    
    // Initialize channel
    const channel = useRealtimeChannel(props.channelId, props.options)
    
    // Computed states
    const isConnected = computed(() => channel.isConnected.value)
    const connectionStatus = computed(() => {
      switch (channel.isConnected.value) {
        case 'connected':
          return { text: 'Connected', color: 'green' }
        case 'disconnected':
          return { text: 'Disconnected', color: 'gray' }
        case 'reconnecting':
          return { text: 'Reconnecting...', color: 'yellow' }
        case 'error':
          return { text: 'Error', color: 'red' }
        default:
          return { text: 'Unknown', color: 'gray' }
      }
    })
    
    // Initialize channel
    const initialize = async () => {
      try {
        loading.value = true
        error.value = null
        
        if (props.autoConnect) {
          // Channel is auto-initialized in useRealtimeChannel
          await new Promise(resolve => setTimeout(resolve, 100)) // Small delay for initialization
        }
        
        loading.value = false
        emit('initialized', channel)
        
      } catch (err) {
        console.error('Failed to initialize channel:', err)
        error.value = err.message || 'Failed to initialize channel'
        loading.value = false
        emit('error', err)
      }
    }
    
    // Watch connection status changes
    const unwatchConnection = watch(() => channel.isConnected.value, (newValue, oldValue) => {
      emit('connection-change', { 
        from: oldValue, 
        to: newValue,
        channel: channel
      })
    })
    
    // Watch for errors
    const unwatchError = watch(() => channel.lastError.value, (newError) => {
      if (newError) {
        emit('channel-error', newError)
      }
    })
    
    // Provide channel to child components
    provide('channel', channel)
    provide('channelId', props.channelId)
    
    // Event handlers
    const handleReconnect = () => {
      channel.reconnect()
    }
    
    const handleDisconnect = () => {
      channel.disconnect()
    }
    
    // Expose methods to parent
    const reconnect = () => channel.reconnect()
    const disconnect = () => channel.disconnect()
    const clearQueue = () => channel.clearQueue()
    
    // Get channel statistics
    const getStats = () => ({
      isConnected: channel.isConnected.value,
      connectionAttempts: channel.connectionAttempts.value,
      pendingCommands: channel.pendingCommands.value.length,
      historySize: channel.history.value.length,
      lastError: channel.lastError.value
    })
    
    // Initialize on mount
    onMounted(() => {
      initialize()
    })
    
    // Cleanup on unmount
    onUnmounted(() => {
      if (unwatchConnection) unwatchConnection()
      if (unwatchError) unwatchError()
      channel.disconnect()
    })
    
    return {
      // State
      loading,
      error,
      channel,
      isConnected,
      connectionStatus,
      
      // Methods
      reconnect,
      disconnect,
      clearQueue,
      getStats,
      handleReconnect,
      handleDisconnect
    }
  }
}
</script>

<style scoped>
.channel-provider {
  @apply w-full;
}
</style>
