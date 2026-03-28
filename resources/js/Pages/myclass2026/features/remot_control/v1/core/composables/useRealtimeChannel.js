/**
 * Core Realtime Channel Composable
 * The foundation of the generic real-time communication system
 */

import { ref, reactive, getCurrentInstance, onUnmounted } from 'vue'
import { useOfflineStorage } from './useOfflineStorage.js'
import { validateCommand, validateState, sanitizeData } from '../utils/validation.js'
import { debounce, createRateLimiter } from '../utils/debounce.js'
import { ConnectionStatus, EventType } from '../types/channel.types.js'
import { database } from '@/firebase/init'
import { ref as dbRef, onValue, onChildAdded, off, set, push } from 'firebase/database'
import { ToolsSwitcher } from '@/Utils/toolsSwitcher'

/**
 * Core composable for real-time bidirectional communication
 * @param {string} channelId - Unique channel identifier
 * @param {Object} [options={}] - Channel configuration options
 * @param {boolean} [options.persistence=true] - Enable offline storage
 * @param {boolean} [options.encryption=false] - Enable data encryption
 * @param {number} [options.debounce=300] - Debounce delay in ms
 * @param {string} [options.firebasePath='channels'] - Firebase path prefix
 * @param {number} [options.maxRetries=3] - Maximum retry attempts
 * @param {number} [options.retryDelay=1000] - Base retry delay
 * @param {boolean} [options.validateCommands=true] - Enable command validation
 * @param {boolean} [options.logEvents=false] - Enable event logging
 * @param {number} [options.rateLimitMaxCalls=50] - Maximum rate limit calls per window
 * @param {number} [options.rateLimitWindowMs=1000] - Rate limit time window in ms
 * @returns {Object} Channel API and reactive state
 */
export function useRealtimeChannel(channelId, options = {}) {
  const config = {
    persistence: true,
    encryption: false,
    debounce: 300,
    firebasePath: 'channels',
    maxRetries: 3,
    retryDelay: 1000,
    validateCommands: true,
    logEvents: false,
    rateLimitMaxCalls: 50,
    rateLimitWindowMs: 1000,
    ...options
  }

  // Reactive state
  const state = ref(null)
  const isConnected = ref(ConnectionStatus.DISCONNECTED)
  const lastError = ref(null)
  const pendingCommands = ref([])
  const history = ref([])
  const connectionAttempts = ref(0)

  // Internal state
  const internal = reactive({
    listeners: new Map(),
    firebaseRefs: new Map(),
    firebaseListeners: new Map(),
    offlineStorage: null,
    rateLimiter: null,
    debouncedUpdateState: null,
    reconnectTimer: null,
    userId: null,
    initialized: false
  })

  // Firebase paths
  const paths = {
    state: `${config.firebasePath}/${channelId}/state`,
    commands: `${config.firebasePath}/${channelId}/commands`,
    events: `${config.firebasePath}/${channelId}/events`,
    queue: `${config.firebasePath}/${channelId}/queue`
  }

  /**
   * Initialize the channel
   */
  const initialize = async () => {
    if (internal.initialized) {
      return
    }

    internal.initialized = true

    try {
      // Initialize offline storage if enabled
      if (config.persistence) {
        internal.offlineStorage = useOfflineStorage(`rt_channel_${channelId}`)
        
        // Load cached state
        const cachedState = internal.offlineStorage.load('state')
        if (cachedState) {
          state.value = cachedState
        }
        
        // Load pending commands
        const cachedCommands = internal.offlineStorage.load('pending_commands', [])
        pendingCommands.value = cachedCommands
      }

      // Initialize rate limiter - using configurable values
      internal.rateLimiter = createRateLimiter(config.rateLimitMaxCalls, config.rateLimitWindowMs)

      // Create debounced state update function
      internal.debouncedUpdateState = debounce((newState) => {
        updateFirebaseState(newState)
      }, config.debounce)

      // Generate or load user ID
      internal.userId = getUserId()

      // Connect to Firebase
      await connect()
      
    } catch (error) {
      console.error('Failed to initialize channel:', error)
      lastError.value = error
      isConnected.value = ConnectionStatus.ERROR
    }
  }

  /**
   * Connect to Firebase and set up listeners
   */
  const connect = async () => {
    if (isConnected.value === ConnectionStatus.CONNECTED) {
      return
    }

    try {
      isConnected.value = ConnectionStatus.RECONNECTING
      connectionAttempts.value++

    // Check if Firebase is enabled and available
    if (!ToolsSwitcher.isFirebaseEnabled() || !database) {
      console.warn('Firebase disabled or database unavailable')
      isConnected.value = ConnectionStatus.DISCONNECTED
      return
    }

    // Set up state listener
    const stateRef = dbRef(database, paths.state)
    internal.firebaseRefs.set('state', stateRef)
    
    internal.firebaseListeners.set('state', onValue(stateRef, (snapshot) => {
        const data = snapshot.val()
        if (data) {
          const validated = validateState(data)
          if (validated.isValid) {
            state.value = validated.sanitized
            
            // Cache state locally
            if (config.persistence && internal.offlineStorage) {
              internal.offlineStorage.save('state', validated.sanitized)
            }
            
            // Add to history
            addToHistory('state_change', data, 'remote')
            
            // Trigger state change listeners
            triggerStateChange(validated.sanitized)
          } else {
            console.warn('Invalid state received:', validated.errors)
          }
        }
      }))

    // Set up commands listener using onChildAdded for real-time updates
    const commandsRef = dbRef(database, paths.commands)
    internal.firebaseRefs.set('commands', commandsRef)
    
    // Listen for new commands in real-time
    internal.firebaseListeners.set('commands', onChildAdded(commandsRef, (snapshot) => {
      const command = snapshot.val()
      if (command && command.channelId === channelId) {
        console.log('🔥 Received command from Firebase:', command.commandId)
        handleIncomingCommand(command)
      }
    }))

    // Set up connection listener
    const connectedRef = dbRef(database, '.info/connected')
    internal.firebaseRefs.set('connected', connectedRef)
    
    internal.firebaseListeners.set('connected', onValue(connectedRef, (snapshot) => {
      if (snapshot.val() === true) {
        isConnected.value = ConnectionStatus.CONNECTED
        connectionAttempts.value = 0
        
        // Process pending commands when coming back online
        if (pendingCommands.value.length > 0) {
          processPendingCommands()
        }
      } else {
        isConnected.value = ConnectionStatus.DISCONNECTED
      }
    }))

    console.log(`Connected to channel: ${channelId}`)
    
    } catch (error) {
      console.error('Failed to connect:', error)
      lastError.value = error
      isConnected.value = ConnectionStatus.ERROR
      
      // Attempt reconnection
      if (connectionAttempts.value < config.maxRetries) {
        scheduleReconnect()
      }
    }
  }

  /**
   * Disconnect from Firebase
   */
  const disconnect = () => {
    // Clear reconnection timer
    if (internal.reconnectTimer) {
      clearTimeout(internal.reconnectTimer)
      internal.reconnectTimer = null
    }

    // Remove Firebase listeners using the new API
    internal.firebaseListeners.forEach((unsub, key) => {
      try {
        if (typeof unsub === 'function') {
          unsub()
        }
      } catch (error) {
        console.warn(`Failed to remove Firebase listener for ${key}:`, error)
      }
    })
    internal.firebaseListeners.clear()
    internal.firebaseRefs.clear()

    // Clear all event listeners
    internal.listeners.clear()

    isConnected.value = ConnectionStatus.DISCONNECTED
    console.log(`Disconnected from channel: ${channelId}`)
  }

  /**
   * Reconnect to the channel
   */
  const reconnect = async () => {
    disconnect()
    await connect()
  }

  /**
   * Schedule reconnection with exponential backoff
   */
  const scheduleReconnect = () => {
    if (internal.reconnectTimer) {
      clearTimeout(internal.reconnectTimer)
    }

    const delay = config.retryDelay * Math.pow(2, connectionAttempts.value)
    internal.reconnectTimer = setTimeout(() => {
      connect()
    }, delay)
  }

  /**
   * Update shared state
   * @param {any} newState - New state data
   */
  const updateState = (newState) => {
    if (!newState || typeof newState !== 'object') {
      console.error('State must be an object')
      return
    }

    // Create state object
    const stateObj = {
      channelId,
      data: newState,
      metadata: {
        lastUpdated: new Date().toISOString(),
        updatedBy: internal.userId,
        version: (state.value?.metadata?.version || 0) + 1
      }
    }

    // Validate state
    const validated = validateState(stateObj)
    if (!validated.isValid) {
      console.error('Invalid state:', validated.errors)
      return
    }

    // Update local state immediately
    state.value = validated.sanitized

    // Add to history
    addToHistory('state_change', validated.sanitized, 'local')

    // Debounced Firebase update
    if (internal.debouncedUpdateState) {
      internal.debouncedUpdateState(validated.sanitized)
    }

    // Cache locally
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('state', validated.sanitized)
    }

    // Trigger state change listeners
    triggerStateChange(validated.sanitized)
  }

  /**
   * Update state in Firebase
   * @param {Object} stateObj - State object to update
   */
  const updateFirebaseState = async (stateObj) => {
    if (isConnected.value !== ConnectionStatus.CONNECTED) {
      console.warn('Not connected, state will be synced when online')
      return
    }

    try {
      if (!database) {
        throw new Error('Firebase database not available')
      }

      console.log('🔥 Updating state in Firebase:', paths.state)
      console.log('🔥 State data:', stateObj)
      
      const stateRef = dbRef(database, paths.state)
      await set(stateRef, stateObj)
      
      console.log('✅ State updated in Firebase successfully')
      
      // Log event if enabled
      if (config.logEvents) {
        logEvent(EventType.STATE_CHANGE, { stateId: stateObj.metadata.version })
      }
      
    } catch (error) {
      console.error('❌ Failed to update Firebase state:', error)
      console.error('❌ Error details:', error.message)
      console.error('❌ Firebase path attempted:', paths.state)
      console.error('❌ Database available:', !!database)
      lastError.value = error
      
      // Retry if connection issue
      if (isConnectionError(error)) {
        scheduleReconnect()
      }
    }
  }

  /**
   * Send a command to the channel
   * @param {string} type - Command type
   * @param {any} payload - Command payload
   * @param {Object} [metadata={}] - Additional metadata
   */
  const sendCommand = async (type, payload, metadata = {}) => {
    // Check rate limit with better handling
    if (!internal.rateLimiter?.check()) {
      const remaining = internal.rateLimiter?.remaining() || 0
      const nextAvailable = internal.rateLimiter?.nextAvailable() || 0
      
      console.warn(`Rate limit exceeded, command dropped. Remaining: ${remaining}, Next available in: ${nextAvailable}ms`)
      
      // For high-priority commands, queue them instead of dropping
      if (metadata.priority === 'high') {
        console.log('High-priority command queued despite rate limit')
        // Add to pending commands queue
        const command = {
          commandId: generateId(),
          channelId,
          type,
          payload: sanitizeData(payload),
          metadata: {
            timestamp: new Date().toISOString(),
            senderId: internal.userId,
            priority: 'high',
            requiresAck: metadata.requiresAck || false,
            retryCount: 0,
            rateLimited: true,
            ...metadata
          }
        }
        pendingCommands.value.push(command)
        return true
      }
      
      return false
    }

    // Create command object
    const command = {
      commandId: generateId(),
      channelId,
      type,
      payload: sanitizeData(payload),
      metadata: {
        timestamp: new Date().toISOString(),
        senderId: internal.userId,
        priority: metadata.priority || 'normal',
        requiresAck: metadata.requiresAck || false,
        retryCount: 0,
        ...metadata
      }
    }

    // Validate command
    if (config.validateCommands) {
      const validated = validateCommand(command)
      if (!validated.isValid) {
        console.error('Invalid command:', validated.errors)
        return false
      }
      command.payload = validated.sanitized.payload
    }

    // Add to pending commands
    pendingCommands.value.push(command)
    
    // Cache pending commands
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('pending_commands', pendingCommands.value)
    }

    // Send immediately if connected
    if (isConnected.value === ConnectionStatus.CONNECTED) {
      return await sendCommandToFirebase(command)
    } else {
      console.log('Command queued for when online:', command.commandId)
      return true
    }
  }

  /**
   * Send command to Firebase
   * @param {Object} command - Command to send
   */
  const sendCommandToFirebase = async (command) => {
    try {
      if (!database) {
        throw new Error('Firebase database not available')
      }

      console.log('🔥 Writing command to Firebase:', command.commandId)
      console.log('🔥 Firebase path:', paths.commands)
      
      const commandsRef = dbRef(database, paths.commands)
      const newCommandRef = push(commandsRef)
      await set(newCommandRef, command)
      
      console.log('✅ Command written to Firebase successfully')
      
      // Remove from pending
      const index = pendingCommands.value.findIndex(c => c.commandId === command.commandId)
      if (index !== -1) {
        pendingCommands.value.splice(index, 1)
        
        // Update cache
        if (config.persistence && internal.offlineStorage) {
          internal.offlineStorage.save('pending_commands', pendingCommands.value)
        }
      }

      // Log event if enabled
      if (config.logEvents) {
        logEvent(EventType.COMMAND_SENT, { commandId: command.commandId, type: command.type })
      }

      console.log('Command sent:', command.commandId)
      return true
      
    } catch (error) {
      console.error('❌ Failed to send command to Firebase:', error)
      console.error('❌ Error details:', error.message)
      console.error('❌ Firebase path attempted:', paths.commands)
      console.error('❌ Database available:', !!database)
      lastError.value = error
      
      // Increment retry count
      command.metadata.retryCount++
      
      // Remove from pending if max retries exceeded
      if (command.metadata.retryCount >= config.maxRetries) {
        const index = pendingCommands.value.findIndex(c => c.commandId === command.commandId)
        if (index !== -1) {
          pendingCommands.value.splice(index, 1)
          console.warn('Command failed after max retries:', command.commandId)
        }
      }
      
      return false
    }
  }

  /**
   * Handle incoming command
   * @param {Object} command - Incoming command
   */
  const handleIncomingCommand = (command) => {
    if (!command || command.channelId !== channelId) {
      return
    }

    // Validate command
    if (config.validateCommands) {
      const validated = validateCommand(command)
      if (!validated.isValid) {
        console.warn('Invalid incoming command:', validated.errors)
        return
      }
      command = validated.sanitized
    }

    // Ignore own commands unless explicitly required
    if (command.metadata.senderId === internal.userId && !command.metadata.requiresAck) {
      return
    }

    // Log event if enabled
    if (config.logEvents) {
      logEvent(EventType.COMMAND_RECEIVED, { commandId: command.commandId, type: command.type })
    }

    // Trigger command listeners
    triggerCommand(command)
  }

  /**
   * Process pending commands when coming back online
   */
  const processPendingCommands = async () => {
    if (pendingCommands.value.length === 0) {
      return
    }

    console.log(`Processing ${pendingCommands.value.length} pending commands`)
    
    const commands = [...pendingCommands.value]
    for (const command of commands) {
      await sendCommandToFirebase(command)
      // Small delay between commands
      await new Promise(resolve => setTimeout(resolve, 100))
    }
  }

  /**
   * Add item to history
   * @param {string} action - Action type
   * @param {any} data - Data
   * @param {string} source - Source (local/remote)
   */
  const addToHistory = (action, data, source) => {
    history.value.push({
      action,
      data,
      source,
      timestamp: new Date().toISOString(),
      userId: internal.userId
    })

    // Limit history size
    if (history.value.length > 100) {
      history.value = history.value.slice(-100)
    }
  }

  /**
   * Trigger command listeners
   * @param {Object} command - Command to trigger
   */
  const triggerCommand = (command) => {
    const listeners = internal.listeners.get('command') || []
    listeners.forEach(listener => {
      try {
        listener(command)
      } catch (error) {
        console.error('Error in command listener:', error)
      }
    })
  }

  /**
   * Trigger state change listeners
   * @param {any} newState - New state
   */
  const triggerStateChange = (newState) => {
    const listeners = internal.listeners.get('stateChange') || []
    listeners.forEach(listener => {
      try {
        listener(newState)
      } catch (error) {
        console.error('Error in state change listener:', error)
      }
    })
  }

  /**
   * Log an event
   * @param {string} type - Event type
   * @param {any} data - Event data
   */
  const logEvent = async (type, data) => {
    try {
      if (!database) {
        return
      }

      const event = {
        eventId: generateId(),
        channelId,
        type,
        data,
        timestamp: new Date().toISOString(),
        metadata: {
          source: 'local',
          userId: internal.userId
        }
      }

      const eventsRef = dbRef(database, paths.events)
      const newEventRef = push(eventsRef)
      await set(newEventRef, event)
      
    } catch (error) {
      console.error('Failed to log event:', error)
    }
  }

  /**
   * Listen for commands
   * @param {Function} callback - Callback function
   * @returns {Function} Unsubscribe function
   */
  const onCommand = (callback) => {
    if (typeof callback !== 'function') {
      throw new Error('Callback must be a function')
    }

    const listeners = internal.listeners.get('command') || []
    listeners.push(callback)
    internal.listeners.set('command', listeners)

    return () => {
      const currentListeners = internal.listeners.get('command') || []
      const index = currentListeners.indexOf(callback)
      if (index !== -1) {
        currentListeners.splice(index, 1)
        internal.listeners.set('command', currentListeners)
      }
    }
  }

  /**
   * Listen for state changes
   * @param {Function} callback - Callback function
   * @returns {Function} Unsubscribe function
   */
  const onStateChange = (callback) => {
    if (typeof callback !== 'function') {
      throw new Error('Callback must be a function')
    }

    const listeners = internal.listeners.get('stateChange') || []
    listeners.push(callback)
    internal.listeners.set('stateChange', listeners)

    return () => {
      const currentListeners = internal.listeners.get('stateChange') || []
      const index = currentListeners.indexOf(callback)
      if (index !== -1) {
        currentListeners.splice(index, 1)
        internal.listeners.set('stateChange', currentListeners)
      }
    }
  }

  /**
   * Clear pending commands queue
   */
  const clearQueue = () => {
    pendingCommands.value = []
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('pending_commands', [])
    }
  }

  /**
   * Get rate limiter instance
   */
  const getRateLimiter = () => internal.rateLimiter

  initialize()

  const instance = getCurrentInstance()
  if (instance) {
    onUnmounted(() => {
      disconnect()
    })
  }

  return {
    // Reactive state
    state,
    isConnected,
    lastError,
    pendingCommands,
    history,
    connectionAttempts,
    
    // Methods
    sendCommand,
    updateState,
    onCommand,
    onStateChange,
    disconnect,
    reconnect,
    clearQueue,
    
    // Internal access (for advanced use)
    paths,
    config
  }
}

/**
 * Utility functions
 */

/**
 * Generate unique ID
 */
function generateId() {
  return Date.now().toString(36) + Math.random().toString(36).substr(2)
}

/**
 * Get or generate user ID
 */
function getUserId() {
  // Generate unique ID per session/tab for testing real-time sync
  // This ensures different tabs have different user IDs
  const sessionId = 'user_' + generateId() + '_' + Date.now()
  return sessionId
}

/**
 * Check if error is connection-related
 */
function isConnectionError(error) {
  return error.message?.includes('network') || 
         error.message?.includes('connection') ||
         error.code === 'UNAVAILABLE' ||
         error.code === 'NETWORK_ERROR'
}
