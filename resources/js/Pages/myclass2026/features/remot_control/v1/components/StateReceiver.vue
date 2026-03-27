<template>
  <div class="state-receiver" :class="wrapperClass">
    <!-- Header -->
    <div v-if="showHeader" class="mb-4">
      <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
      <p v-if="description" class="text-sm text-gray-600 mt-1">{{ description }}</p>
    </div>
    
    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center p-8 bg-gray-50 rounded-lg">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mr-3"></div>
      <span class="text-gray-600">Loading state...</span>
    </div>
    
    <!-- Empty state -->
    <div v-else-if="!hasState" class="text-center p-8 bg-gray-50 rounded-lg">
      <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <p class="text-gray-500">No state data available</p>
    </div>
    
    <!-- State display -->
    <div v-else class="space-y-4">
      <!-- Connection indicator -->
      <div v-if="showConnectionStatus" class="flex items-center text-sm">
        <div class="w-2 h-2 rounded-full mr-2" :class="connectionIndicatorClass"></div>
        <span class="text-gray-600">{{ connectionText }}</span>
        <span v-if="lastUpdated" class="text-gray-400 ml-2">
          Updated {{ formatTime(lastUpdated) }}
        </span>
      </div>
      
      <!-- Custom display slot -->
      <slot name="display" :state="displayState" :raw-state="state">
        <!-- Default JSON display -->
        <div class="bg-gray-900 rounded-lg p-4 overflow-auto">
          <div class="flex items-center justify-between mb-3">
            <span class="text-gray-400 text-sm font-mono">State Data</span>
            <button 
              @click="copyToClipboard"
              class="text-gray-400 hover:text-white transition-colors"
              title="Copy to clipboard"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
            </button>
          </div>
          <pre class="text-green-400 text-sm font-mono">{{ formattedState }}</pre>
        </div>
      </slot>
      
      <!-- State metadata -->
      <div v-if="showMetadata && state?.metadata" class="bg-gray-50 rounded-lg p-3">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Metadata</h4>
        <div class="grid grid-cols-2 gap-2 text-sm">
          <div>
            <span class="text-gray-500">Version:</span>
            <span class="ml-2 font-mono">{{ state.metadata.version }}</span>
          </div>
          <div>
            <span class="text-gray-500">Updated by:</span>
            <span class="ml-2 font-mono">{{ state.metadata.updatedBy }}</span>
          </div>
          <div class="col-span-2">
            <span class="text-gray-500">Last updated:</span>
            <span class="ml-2 font-mono">{{ formatDateTime(state.metadata.lastUpdated) }}</span>
          </div>
        </div>
      </div>
      
      <!-- State changes indicator -->
      <div v-if="showChanges && hasChanges" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
        <div class="flex items-center">
          <svg class="w-4 h-4 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          <span class="text-yellow-800 text-sm">State has unsaved changes</span>
        </div>
      </div>
    </div>
    
    <!-- Copy notification -->
    <div v-if="showCopyNotification" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg transition-all">
      Copied to clipboard!
    </div>
  </div>
</template>

<script>
import { ref, computed, inject, watch, onMounted, onUnmounted } from 'vue'
import { useRealtimeChannel } from '../core/composables/useRealtimeChannel.js'

export default {
  name: 'StateReceiver',
  props: {
    /**
     * Channel ID to subscribe to
     */
    channelId: {
      type: String,
      required: false // Can be injected from ChannelProvider
    },
    
    /**
     * Transform function to apply to state before display
     */
    transformer: {
      type: Function,
      default: null
    },
    
    /**
     * Component title
     */
    title: {
      type: String,
      default: 'State Receiver'
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
     * Show metadata section
     */
    showMetadata: {
      type: Boolean,
      default: true
    },
    
    /**
     * Show unsaved changes indicator
     */
    showChanges: {
      type: Boolean,
      default: false
    },
    
    /**
     * Custom CSS classes
     */
    wrapperClass: {
      type: String,
      default: ''
    },
    
    /**
     * Auto-refresh interval in milliseconds
     */
    refreshInterval: {
      type: Number,
      default: 0 // 0 = no auto-refresh
    }
  },
  emits: ['state-change', 'connection-change', 'error'],
  setup(props, { emit }) {
    // Inject channel if available
    const injectedChannel = inject('channel', null)
    const injectedChannelId = inject('channelId', null)
    
    // Get channel ID from props or injection
    const channelId = computed(() => props.channelId || injectedChannelId)
    
    // State
    const loading = ref(true)
    const error = ref(null)
    const state = ref(null)
    const lastUpdated = ref(null)
    const showCopyNotification = ref(false)
    
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
    const hasState = computed(() => state.value !== null && state.value !== undefined)
    
    const displayState = computed(() => {
      if (!hasState.value) return null
      
      const stateData = state.value.data || state.value
      
      if (props.transformer && typeof props.transformer === 'function') {
        return props.transformer(stateData)
      }
      
      return stateData
    })
    
    const formattedState = computed(() => {
      return JSON.stringify(displayState.value, null, 2)
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
    
    const hasChanges = computed(() => {
      if (!props.showChanges || !channel) return false
      return channel.hasUnsavedChanges?.value || false
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
        return `${Math.floor(diff / 60000)} min ago`
      } else if (diff < 86400000) {
        return `${Math.floor(diff / 3600000)} hours ago`
      } else {
        return date.toLocaleDateString()
      }
    }
    
    const formatDateTime = (timestamp) => {
      if (!timestamp) return ''
      
      const date = new Date(timestamp)
      return date.toLocaleString()
    }
    
    const copyToClipboard = async () => {
      try {
        await navigator.clipboard.writeText(formattedState.value)
        showCopyNotification.value = true
        
        setTimeout(() => {
          showCopyNotification.value = false
        }, 2000)
      } catch (err) {
        console.error('Failed to copy to clipboard:', err)
      }
    }
    
    const refreshState = () => {
      if (channel && channel.state) {
        state.value = channel.state.value
        lastUpdated.value = channel.state.value?.metadata?.lastUpdated
      }
    }
    
    // Watchers
    let unwatchState = null
    let unwatchConnection = null
    let refreshTimer = null
    
    const setupWatchers = () => {
      if (!channel) return
      
      // Watch state changes
      if (channel.state) {
        unwatchState = watch(
          () => channel.state.value,
          (newState) => {
            state.value = newState
            lastUpdated.value = newState?.metadata?.lastUpdated
            loading.value = false
            emit('state-change', newState)
          },
          { immediate: true }
        )
      }
      
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
    
    // Setup auto-refresh
    const setupAutoRefresh = () => {
      if (props.refreshInterval > 0) {
        refreshTimer = setInterval(() => {
          refreshState()
        }, props.refreshInterval)
      }
    }
    
    // Initialize
    const initialize = async () => {
      try {
        loading.value = true
        error.value = null
        
        if (!channel) {
          throw new Error('No channel available')
        }
        
        setupWatchers()
        setupAutoRefresh()
        
        // Initial state
        refreshState()
        
      } catch (err) {
        console.error('Failed to initialize StateReceiver:', err)
        error.value = err.message
        loading.value = false
        emit('error', err)
      }
    }
    
    // Lifecycle
    onMounted(() => {
      initialize()
    })
    
    onUnmounted(() => {
      if (unwatchState) unwatchState()
      if (unwatchConnection) unwatchConnection()
      if (refreshTimer) clearInterval(refreshTimer)
    })
    
    return {
      // State
      loading,
      error,
      state,
      displayState,
      formattedState,
      lastUpdated,
      hasState,
      connectionStatus,
      connectionIndicatorClass,
      connectionText,
      hasChanges,
      showCopyNotification,
      
      // Methods
      formatTime,
      formatDateTime,
      copyToClipboard,
      refreshState
    }
  }
}
</script>

<style scoped>
.state-receiver {
  @apply w-full;
}

/* Custom scrollbar for JSON display */
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
