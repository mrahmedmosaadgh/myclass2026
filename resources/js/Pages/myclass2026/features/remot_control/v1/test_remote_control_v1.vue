<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-900">Generic Real-Time Communication System</h1>
            <span class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
              v1.0
            </span>
          </div>
          <div class="flex items-center space-x-4">
            <ConnectionStatus
              :is-connected="globalChannel?.isConnected"
              :connection-attempts="globalChannel?.connectionAttempts"
              :last-error="globalChannel?.lastError"
              variant="badge"
            />
            <button
              @click="showDocs = !showDocs"
              class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors"
            >
              {{ showDocs ? 'Hide' : 'Show' }} Docs
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Documentation -->
    <div v-if="showDocs" class="bg-blue-50 border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="bg-white rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">System Documentation</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
              <h3 class="font-medium text-gray-700 mb-2">🚀 Features</h3>
              <ul class="text-sm text-gray-600 space-y-1">
                <li>• 100% Generic & Reusable</li>
                <li>• Offline-First Architecture</li>
                <li>• Real-time Bidirectional Communication</li>
                <li>• Command Queue with Retry Logic</li>
                <li>• Event Logging & Analytics</li>
                <li>• Type-Safe Validation</li>
              </ul>
            </div>
            <div>
              <h3 class="font-medium text-gray-700 mb-2">🔧 Components</h3>
              <ul class="text-sm text-gray-600 space-y-1">
                <li>• ChannelProvider (Context)</li>
                <li>• StateReceiver (Display)</li>
                <li>• CommandSender (Controls)</li>
                <li>• ConnectionStatus (Indicator)</li>
                <li>• TestDataDisplay (Example)</li>
                <li>• TestRemoteControl (Example)</li>
              </ul>
            </div>
            <div>
              <h3 class="font-medium text-gray-700 mb-2">📦 Use Cases</h3>
              <ul class="text-sm text-gray-600 space-y-1">
                <li>• Presentation Control</li>
                <li>• Game State Sync</li>
                <li>• IoT Device Control</li>
                <li>• Collaborative Tools</li>
                <li>• Real-time Dashboards</li>
                <li>• Remote Administration</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Tab Navigation -->
      <div class="mb-8">
        <nav class="flex space-x-1 bg-gray-200 rounded-lg p-1">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-colors"
            :class="activeTab === tab.id 
              ? 'bg-white text-gray-900 shadow-sm' 
              : 'text-gray-600 hover:text-gray-900'"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="space-y-8">
        <!-- Simple Test Tab -->
        <div v-if="activeTab === 'simple'" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Display Component -->
          <div>
            <ChannelProvider 
              :channel-id="customChannelId || 'test-channel-1'"
              :show-connection-status="true"
              @initialized="onChannelInitialized"
            >
              <template #default="{ channel }">
                <TestDataDisplay />
              </template>
            </ChannelProvider>
          </div>
          
          <!-- Control Component -->
          <div>
            <ChannelProvider 
              :channel-id="customChannelId || 'test-channel-1'"
              :show-connection-status="true"
            >
              <template #default="{ channel }">
                <TestRemoteControl />
              </template>
            </ChannelProvider>
          </div>
        </div>

        <!-- Generic Components Tab -->
        <div v-if="activeTab === 'components'" class="space-y-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- State Receiver Example -->
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-4">State Receiver Component</h3>
              <ChannelProvider channel-id="demo-channel-1">
                <template #default="{ channel }">
                  <StateReceiver
                    title="Generic State Display"
                    description="Displays any JSON state data"
                    :show-metadata="true"
                    :show-changes="true"
                    :transformer="stateTransformer"
                  />
                </template>
              </ChannelProvider>
            </div>

            <!-- Command Sender Example -->
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Command Sender Component</h3>
              <ChannelProvider channel-id="demo-channel-1">
                <template #default="{ channel }">
                  <CommandSender
                    title="Generic Command Sender"
                    description="Send any type of commands"
                    :commands="demoCommands"
                    :show-custom-command="true"
                    :show-quick-actions="true"
                    button-style="primary"
                  />
                </template>
              </ChannelProvider>
            </div>
          </div>
        </div>

        <!-- Chat Example Tab -->
        <div v-if="activeTab === 'chat'" class="space-y-8">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Real-Time Chat Example</h3>
            <p class="text-gray-600 mb-6">
              Demonstrates real-time messaging between multiple users/tabs using the generic remote control system.
              Open this page in multiple tabs to see real-time synchronization.
            </p>
            
            <ChatExample :channel-id="customChannelId || 'chat-example-1'" />
          </div>
        </div>

        <!-- API Demo Tab -->
        <div v-if="activeTab === 'api'" class="space-y-8">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Direct API Usage</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Channel API -->
              <div>
                <h4 class="font-medium text-gray-700 mb-3">Channel API</h4>
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">Connection Status:</span>
                    <span class="text-sm" :class="getConnectionColor()">
                      {{ globalChannel?.isConnected || 'disconnected' }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">Pending Commands:</span>
                    <span class="text-sm">{{ globalChannel?.pendingCommands?.length || 0 }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium">History Size:</span>
                    <span class="text-sm">{{ globalChannel?.history?.length || 0 }}</span>
                  </div>
                  <div class="flex space-x-2">
                    <button
                      @click="testDirectAPI"
                      class="px-3 py-1 text-sm bg-blue-500 text-white rounded hover:bg-blue-600"
                    >
                      Test API
                    </button>
                    <button
                      @click="clearChannelData"
                      class="px-3 py-1 text-sm bg-red-500 text-white rounded hover:bg-red-600"
                    >
                      Clear Data
                    </button>
                  </div>
                </div>
              </div>

              <!-- Raw State -->
              <div>
                <h4 class="font-medium text-gray-700 mb-3">Raw Channel State</h4>
                <div class="bg-gray-900 rounded-lg p-4 max-h-64 overflow-auto">
                  <pre class="text-green-400 text-xs font-mono">{{ JSON.stringify(directState, null, 2) }}</pre>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Tab -->
        <div v-if="activeTab === 'performance'" class="space-y-8">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Performance Metrics</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ performance.totalCommands }}</div>
                <div class="text-sm text-gray-600">Total Commands</div>
              </div>
              <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ performance.successRate }}%</div>
                <div class="text-sm text-gray-600">Success Rate</div>
              </div>
              <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ performance.avgLatency }}ms</div>
                <div class="text-sm text-gray-600">Avg Latency</div>
              </div>
              <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ performance.uptime }}s</div>
                <div class="text-sm text-gray-600">Uptime</div>
              </div>
            </div>

            <!-- Performance Chart -->
            <div class="mt-6">
              <h4 class="font-medium text-gray-700 mb-3">Command Timeline</h4>
              <div class="bg-gray-50 rounded-lg p-4 h-32 flex items-center space-x-1">
                <div
                  v-for="(point, index) in performance.timeline"
                  :key="index"
                  class="flex-1 bg-blue-500 rounded"
                  :style="{ height: point + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="text-center text-sm text-gray-500">
          Generic Real-Time Communication System v1.0 • Built with Vue 3 & Firebase
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref, reactive, onMounted, onUnmounted, watch } from 'vue'
import { useRealtimeChannel } from './core/composables/useRealtimeChannel.js'
import ChannelProvider from './components/ChannelProvider.vue'
import StateReceiver from './components/StateReceiver.vue'
import CommandSender from './components/CommandSender.vue'
import ConnectionStatus from './components/ConnectionStatus.vue'
import TestDataDisplay from './examples/simple_test/TestDataDisplay.vue'
import TestRemoteControl from './examples/simple_test/TestRemoteControl.vue'
import ChatExample from './examples/simple_chat/ChatExample.vue'

export default {
  name: 'TestRemoteControlV1',
  components: {
    ChannelProvider,
    StateReceiver,
    CommandSender,
    ConnectionStatus,
    TestDataDisplay,
    TestRemoteControl,
    ChatExample
  },
  setup() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search)
    const exampleParam = urlParams.get('example')
    const channelParam = urlParams.get('channel')
    
    // State
    const showDocs = ref(true)
    const activeTab = ref(exampleParam || 'simple')
    const customChannelId = ref(channelParam || '')
    const globalChannel = ref(null)
    const directState = ref({})
    const startTime = Date.now()

    // Performance tracking
    const performance = reactive({
      totalCommands: 0,
      successfulCommands: 0,
      avgLatency: 0,
      uptime: 0,
      timeline: Array(20).fill(0).map(() => Math.random() * 80 + 20)
    })

    // Tab configuration
    const tabs = [
      { id: 'simple', label: 'Simple Test' },
      { id: 'chat', label: 'Chat Example' },
      { id: 'components', label: 'Generic Components' },
      { id: 'api', label: 'Direct API' },
      { id: 'performance', label: 'Performance' }
    ]

    // Demo commands for CommandSender
    const demoCommands = [
      {
        id: 'increment',
        type: 'increment',
        label: 'Increment',
        description: 'Add 1 to count',
        icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6',
        payload: { amount: 1 }
      },
      {
        id: 'decrement',
        type: 'decrement',
        label: 'Decrement',
        description: 'Subtract 1 from count',
        icon: 'M20 12H4',
        payload: { amount: 1 }
      },
      {
        id: 'reset',
        type: 'reset',
        label: 'Reset',
        description: 'Reset to defaults',
        icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'
      },
      {
        id: 'random_color',
        type: 'set_color',
        label: 'Random Color',
        description: 'Set random color',
        icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'
      }
    ]

    // Methods
    const onChannelInitialized = (channel) => {
      globalChannel.value = channel
      console.log('Test channel initialized:', channel)
    }

    const stateTransformer = (state) => {
      // Transform state for display
      if (!state) return null
      
      return {
        ...state,
        transformed: true,
        timestamp: new Date().toISOString(),
        summary: `Count: ${state.count || 0}, Message: "${state.message || 'N/A'}"`
      }
    }

    const getConnectionColor = () => {
      switch (globalChannel.value?.isConnected) {
        case 'connected':
          return 'text-green-600'
        case 'reconnecting':
          return 'text-yellow-600'
        case 'error':
          return 'text-red-600'
        default:
          return 'text-gray-600'
      }
    }

    const testDirectAPI = async () => {
      if (!globalChannel.value) return

      const startTime = Date.now()
      
      try {
        // Send test command
        await globalChannel.value.sendCommand('test_api', { 
          timestamp: startTime,
          random: Math.random()
        })
        
        performance.totalCommands++
        performance.successfulCommands++
        
        const latency = Date.now() - startTime
        performance.avgLatency = Math.round((performance.avgLatency + latency) / 2)
        
        // Update timeline
        performance.timeline.shift()
        performance.timeline.push(latency / 10)
        
      } catch (error) {
        performance.totalCommands++
        console.error('API test failed:', error)
      }
    }

    const clearChannelData = () => {
      if (globalChannel.value) {
        globalChannel.value.clearQueue()
        globalChannel.value.updateState({
          count: 0,
          message: 'Cleared',
          color: '#3b82f6'
        })
      }
    }

    // Update uptime
    const updateUptime = () => {
      performance.uptime = Math.floor((Date.now() - startTime) / 1000)
    }

    // Update performance success rate
    const updateSuccessRate = () => {
      if (performance.totalCommands > 0) {
        performance.successRate = Math.round((performance.successfulCommands / performance.totalCommands) * 100)
      }
    }

    // Watch for state changes
    let unwatchState = null
    const setupStateWatcher = () => {
      if (globalChannel.value) {
        unwatchState = watch(() => globalChannel.value.state.value, (newState) => {
          directState.value = newState || {}
        })
      }
    }

    // Initialize global channel for API demo
    const initializeGlobalChannel = async () => {
      try {
        globalChannel.value = useRealtimeChannel('demo-channel-1', {
          persistence: true,
          debounce: 300
        })
        
        // Wait for initialization
        await new Promise(resolve => setTimeout(resolve, 500))
        
        setupStateWatcher()
        
        // Set initial state
        globalChannel.value.updateState({
          count: 0,
          message: 'API Demo Active',
          color: '#3b82f6',
          lastApiTest: null
        })
        
      } catch (error) {
        console.error('Failed to initialize global channel:', error)
      }
    }

    // Timers
    let uptimeTimer = null
    let performanceTimer = null

    // Lifecycle
    onMounted(() => {
      initializeGlobalChannel()
      
      // Update uptime every second
      uptimeTimer = setInterval(updateUptime, 1000)
      
      // Update performance metrics every 5 seconds
      performanceTimer = setInterval(() => {
        updateSuccessRate()
        
        // Update timeline with random data for demo
        performance.timeline.shift()
        performance.timeline.push(Math.random() * 80 + 20)
      }, 5000)
    })

    onUnmounted(() => {
      if (unwatchState) unwatchState()
      if (uptimeTimer) clearInterval(uptimeTimer)
      if (performanceTimer) clearInterval(performanceTimer)
      if (globalChannel.value) {
        globalChannel.value.disconnect()
      }
    })

    return {
      // State
      showDocs,
      activeTab,
      customChannelId,
      globalChannel,
      directState,
      performance,
      
      // Data
      tabs,
      demoCommands,
      
      // Methods
      onChannelInitialized,
      stateTransformer,
      getConnectionColor,
      testDirectAPI,
      clearChannelData
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for JSON displays */
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

/* Performance chart animation */
.bg-blue-500 {
  transition: height 0.3s ease-in-out;
}

/* Tab transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
