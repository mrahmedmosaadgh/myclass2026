<template>
  <div class="connection-status" :class="wrapperClass">
    <!-- Compact version -->
    <div v-if="variant === 'compact'" class="flex items-center space-x-2">
      <div class="flex items-center">
        <div 
          class="w-2 h-2 rounded-full mr-2 transition-all duration-300"
          :class="indicatorClass"
        ></div>
        <span class="text-sm font-medium" :class="textClass">{{ statusText }}</span>
      </div>
      
      <!-- Reconnecting animation -->
      <div v-if="isReconnecting" class="flex items-center space-x-1">
        <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
        <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
        <div class="w-1 h-1 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
      </div>
    </div>
    
    <!-- Badge version -->
    <div v-else-if="variant === 'badge'" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="badgeClass">
      <div 
        class="w-2 h-2 rounded-full mr-2"
        :class="indicatorClass"
      ></div>
      {{ statusText }}
    </div>
    
    <!-- Detailed version -->
    <div v-else-if="variant === 'detailed'" class="bg-white rounded-lg border p-4">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center">
          <div 
            class="w-3 h-3 rounded-full mr-3 transition-all duration-300"
            :class="indicatorClass"
          ></div>
          <div>
            <h3 class="font-semibold text-gray-800">{{ statusText }}</h3>
            <p class="text-sm text-gray-600">{{ statusDescription }}</p>
          </div>
        </div>
        
        <!-- Action buttons -->
        <div class="flex space-x-2">
          <button
            v-if="showReconnectButton && canReconnect"
            @click="$emit('reconnect')"
            class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
          >
            Reconnect
          </button>
          
          <button
            v-if="showDetailsButton"
            @click="showDetails = !showDetails"
            class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors"
          >
            {{ showDetails ? 'Hide' : 'Show' }} Details
          </button>
        </div>
      </div>
      
      <!-- Progress bar for reconnecting -->
      <div v-if="isReconnecting" class="mb-3">
        <div class="flex items-center justify-between text-sm text-gray-600 mb-1">
          <span>Reconnecting...</span>
          <span>{{ connectionAttempts }}/{{ maxRetries }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div 
            class="bg-blue-500 h-2 rounded-full transition-all duration-500"
            :style="{ width: reconnectProgress + '%' }"
          ></div>
        </div>
      </div>
      
      <!-- Details section -->
      <div v-if="showDetails" class="border-t pt-3 space-y-2">
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <span class="text-gray-500">Channel ID:</span>
            <span class="ml-2 font-mono text-gray-800">{{ channelId || 'N/A' }}</span>
          </div>
          <div>
            <span class="text-gray-500">Status:</span>
            <span class="ml-2 font-mono" :class="textClass">{{ connectionStatus }}</span>
          </div>
          <div>
            <span class="text-gray-500">Attempts:</span>
            <span class="ml-2 font-mono text-gray-800">{{ connectionAttempts }}</span>
          </div>
          <div>
            <span class="text-gray-500">Pending:</span>
            <span class="ml-2 font-mono text-gray-800">{{ pendingCommands || 0 }}</span>
          </div>
        </div>
        
        <!-- Error details -->
        <div v-if="lastError" class="bg-red-50 border border-red-200 rounded p-3">
          <div class="flex items-start">
            <svg class="w-4 h-4 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
              <h4 class="text-red-800 font-medium text-sm">Connection Error</h4>
              <p class="text-red-600 text-sm">{{ lastError }}</p>
            </div>
          </div>
        </div>
        
        <!-- Last activity -->
        <div v-if="lastActivity" class="text-sm text-gray-600">
          <span class="text-gray-500">Last activity:</span>
          <span class="ml-2">{{ formatDateTime(lastActivity) }}</span>
        </div>
      </div>
    </div>
    
    <!-- Icon only version -->
    <div v-else-if="variant === 'icon'" class="relative">
      <div 
        class="w-4 h-4 rounded-full transition-all duration-300"
        :class="indicatorClass"
        :title="statusText"
      ></div>
      
      <!-- Pulse animation for connected -->
      <div 
        v-if="connectionStatus === 'connected'"
        class="absolute inset-0 w-4 h-4 rounded-full bg-green-400 animate-ping"
      ></div>
    </div>
    
    <!-- Tooltip version -->
    <div v-else-if="variant === 'tooltip'" class="relative inline-block">
      <div 
        class="w-3 h-3 rounded-full transition-all duration-300 cursor-help"
        :class="indicatorClass"
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
      ></div>
      
      <!-- Tooltip -->
      <div 
        v-if="showTooltip"
        class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded whitespace-nowrap z-50"
      >
        {{ statusText }}
        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
          <div class="border-4 border-transparent border-t-gray-800"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'

export default {
  name: 'ConnectionStatus',
  props: {
    /**
     * Connection status from channel
     */
    isConnected: {
      type: [String, Boolean],
      default: 'disconnected'
    },
    
    /**
     * Number of connection attempts
     */
    connectionAttempts: {
      type: Number,
      default: 0
    },
    
    /**
     * Maximum retry attempts
     */
    maxRetries: {
      type: Number,
      default: 3
    },
    
    /**
     * Last error message
     */
    lastError: {
      type: String,
      default: ''
    },
    
    /**
     * Number of pending commands
     */
    pendingCommands: {
      type: Number,
      default: 0
    },
    
    /**
     * Channel ID
     */
    channelId: {
      type: String,
      default: ''
    },
    
    /**
     * Last activity timestamp
     */
    lastActivity: {
      type: String,
      default: ''
    },
    
    /**
     * Display variant
     */
    variant: {
      type: String,
      default: 'compact', // compact, badge, detailed, icon, tooltip
      validator: (value) => ['compact', 'badge', 'detailed', 'icon', 'tooltip'].includes(value)
    },
    
    /**
     * Show reconnect button
     */
    showReconnectButton: {
      type: Boolean,
      default: true
    },
    
    /**
     * Show details button
     */
    showDetailsButton: {
      type: Boolean,
      default: true
    },
    
    /**
     * Custom CSS classes
     */
    wrapperClass: {
      type: String,
      default: ''
    }
  },
  emits: ['reconnect'],
  setup(props) {
    const showDetails = ref(false)
    const showTooltip = ref(false)
    
    // Computed properties
    const connectionStatus = computed(() => {
      // Handle boolean or string status
      if (typeof props.isConnected === 'boolean') {
        return props.isConnected ? 'connected' : 'disconnected'
      }
      return props.isConnected || 'disconnected'
    })
    
    const isReconnecting = computed(() => {
      return connectionStatus.value === 'reconnecting'
    })
    
    const canReconnect = computed(() => {
      return connectionStatus.value === 'disconnected' || connectionStatus.value === 'error'
    })
    
    const indicatorClass = computed(() => {
      switch (connectionStatus.value) {
        case 'connected':
          return 'bg-green-500 shadow-green-200 shadow-lg'
        case 'reconnecting':
          return 'bg-yellow-500 animate-pulse shadow-yellow-200 shadow-lg'
        case 'error':
          return 'bg-red-500 shadow-red-200 shadow-lg'
        default:
          return 'bg-gray-400'
      }
    })
    
    const textClass = computed(() => {
      switch (connectionStatus.value) {
        case 'connected':
          return 'text-green-600'
        case 'reconnecting':
          return 'text-yellow-600'
        case 'error':
          return 'text-red-600'
        default:
          return 'text-gray-600'
      }
    })
    
    const badgeClass = computed(() => {
      switch (connectionStatus.value) {
        case 'connected':
          return 'bg-green-100 text-green-800 border-green-200'
        case 'reconnecting':
          return 'bg-yellow-100 text-yellow-800 border-yellow-200'
        case 'error':
          return 'bg-red-100 text-red-800 border-red-200'
        default:
          return 'bg-gray-100 text-gray-800 border-gray-200'
      }
    })
    
    const statusText = computed(() => {
      switch (connectionStatus.value) {
        case 'connected':
          return 'Connected'
        case 'reconnecting':
          return 'Reconnecting'
        case 'error':
          return 'Error'
        default:
          return 'Disconnected'
      }
    })
    
    const statusDescription = computed(() => {
      switch (connectionStatus.value) {
        case 'connected':
          return 'Successfully connected to the channel'
        case 'reconnecting':
          return 'Attempting to reconnect to the channel'
        case 'error':
          return 'Connection failed. Please check your network connection.'
        default:
          return 'Not connected to the channel'
      }
    })
    
    const reconnectProgress = computed(() => {
      if (props.maxRetries === 0) return 0
      return Math.min((props.connectionAttempts / props.maxRetries) * 100, 100)
    })
    
    // Methods
    const formatDateTime = (timestamp) => {
      if (!timestamp) return ''
      
      const date = new Date(timestamp)
      return date.toLocaleString()
    }
    
    return {
      // State
      showDetails,
      showTooltip,
      
      // Computed
      connectionStatus,
      isReconnecting,
      canReconnect,
      indicatorClass,
      textClass,
      badgeClass,
      statusText,
      statusDescription,
      reconnectProgress,
      
      // Methods
      formatDateTime
    }
  }
}
</script>

<style scoped>
.connection-status {
  @apply w-full;
}

/* Custom animations */
@keyframes bounce-delay {
  0%, 80%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}

.animate-bounce {
  animation: bounce-delay 1.4s infinite ease-in-out both;
}

/* Tooltip positioning */
.relative .absolute {
  pointer-events: none;
}

/* Badge styling */
.inline-flex {
  display: inline-flex;
  align-items: center;
}

/* Progress bar animation */
.transition-all.duration-500 {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 500ms;
}

/* Ping animation for icon variant */
@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Pulse animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Shadow utilities */
.shadow-green-200 {
  box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
}

.shadow-yellow-200 {
  box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.2);
}

.shadow-red-200 {
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
}

.shadow-lg {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
