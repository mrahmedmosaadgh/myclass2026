/**
 * Command Queue Composable
 * Reliable command delivery with retry logic and offline support
 */

import { ref, reactive, onMounted, onUnmounted, watch } from 'vue'
import { useOfflineStorage } from './useOfflineStorage.js'
import { validateCommand, sanitizeData } from '../utils/validation.js'
import { debounce } from '../utils/debounce.js'
import { database } from '@/firebase/init'
import { ref as dbRef, onValue, off, set, push } from 'firebase/database'
import { ToolsSwitcher } from '@/Utils/toolsSwitcher'

/**
 * Command queue composable for reliable command delivery
 * @param {string} channelId - Channel identifier
 * @param {Object} [options={}] - Configuration options
 * @param {boolean} [options.persistence=true] - Enable offline persistence
 * @param {number} [options.maxQueueSize=100] - Maximum queue size
 * @param {number} [options.maxRetries=3] - Maximum retry attempts per command
 * @param {number} [options.retryDelay=1000] - Base retry delay in ms
 * @param {boolean} [options.exponentialBackoff=true] - Use exponential backoff
 * @param {number} [options.batchSize=5] - Commands to send in batch
 * @param {number} [options.batchDelay=100] - Delay between batches
 * @param {boolean} [options.validateCommands=true] - Validate commands before queuing
 * @returns {Object} Command queue API
 */
export function useCommandQueue(channelId, options = {}) {
  const config = {
    persistence: true,
    maxQueueSize: 100,
    maxRetries: 3,
    retryDelay: 1000,
    exponentialBackoff: true,
    batchSize: 5,
    batchDelay: 100,
    validateCommands: true,
    ...options
  }

  // Reactive state
  const queue = ref([])
  const processingQueue = ref(false)
  const isOnline = ref(navigator.onLine)
  const stats = reactive({
    totalQueued: 0,
    totalSent: 0,
    totalFailed: 0,
    totalRetried: 0,
    averageRetryCount: 0
  })

  // Internal state
  const internal = reactive({
    offlineStorage: null,
    processingTimer: null,
    retryTimers: new Map(),
    commandCallbacks: new Map(),
    userId: null
  })

  /**
   * Initialize the command queue
   */
  const initialize = async () => {
    try {
      // Initialize offline storage if enabled
      if (config.persistence) {
        internal.offlineStorage = useOfflineStorage(`rt_queue_${channelId}`)
        
        // Load cached queue
        const cachedQueue = internal.offlineStorage.load('queue', [])
        queue.value = cachedQueue
        
        // Load stats
        const cachedStats = internal.offlineStorage.load('stats', stats)
        Object.assign(stats, cachedStats)
      }

      // Get user ID
      internal.userId = getUserId()

      // Set up online/offline listeners
      window.addEventListener('online', handleOnline)
      window.addEventListener('offline', handleOffline)

      // Start processing if online
      if (isOnline.value) {
        startProcessing()
      }

      console.log(`Command queue initialized for channel: ${channelId}`)
      
    } catch (error) {
      console.error('Failed to initialize command queue:', error)
    }
  }

  /**
   * Add a command to the queue
   * @param {string} type - Command type
   * @param {any} payload - Command payload
   * @param {Object} [metadata={}] - Additional metadata
   * @param {Function} [callback] - Optional callback for command result
   * @returns {string} Command ID
   */
  const sendCommand = async (type, payload, metadata = {}, callback = null) => {
    // Check queue size limit
    if (queue.value.length >= config.maxQueueSize) {
      console.warn('Command queue is full, dropping oldest command')
      queue.value.shift()
    }

    // Create command object
    const command = {
      commandId: generateCommandId(),
      channelId,
      type,
      payload: sanitizeData(payload),
      metadata: {
        timestamp: new Date().toISOString(),
        senderId: internal.userId,
        priority: metadata.priority || 'normal',
        requiresAck: metadata.requiresAck || false,
        retryCount: 0,
        maxRetries: config.maxRetries,
        nextRetryTime: null,
        ...metadata
      },
      status: 'queued',
      createdAt: Date.now()
    }

    // Validate command if enabled
    if (config.validateCommands) {
      const validated = validateCommand(command)
      if (!validated.isValid) {
        console.error('Invalid command:', validated.errors)
        if (callback) callback({ success: false, error: validated.errors })
        return null
      }
      command.payload = validated.sanitized.payload
    }

    // Store callback if provided
    if (callback && typeof callback === 'function') {
      internal.commandCallbacks.set(command.commandId, callback)
    }

    // Add to queue
    queue.value.push(command)
    stats.totalQueued++

    // Persist queue
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('queue', queue.value)
      internal.offlineStorage.save('stats', stats)
    }

    // Start processing if online
    if (isOnline.value && !processingQueue.value) {
      startProcessing()
    }

    console.log('Command queued:', command.commandId)
    return command.commandId
  }

  /**
   * Process the command queue
   */
  const processQueue = async () => {
    if (processingQueue.value || !isOnline.value) {
      return
    }

    processingQueue.value = true

    try {
      // Get commands ready to send
      const readyCommands = queue.value.filter(cmd => 
        cmd.status === 'queued' || 
        (cmd.status === 'retry' && Date.now() >= cmd.metadata.nextRetryTime)
      )

      if (readyCommands.length === 0) {
        return
      }

      // Process in batches
      const batches = createBatches(readyCommands, config.batchSize)
      
      for (const batch of batches) {
        await processBatch(batch)
        
        // Small delay between batches
        if (config.batchDelay > 0) {
          await new Promise(resolve => setTimeout(resolve, config.batchDelay))
        }
      }

    } catch (error) {
      console.error('Error processing command queue:', error)
    } finally {
      processingQueue.value = false
    }
  }

  /**
   * Process a batch of commands
   * @param {Array} batch - Array of commands to process
   */
  const processBatch = async (batch) => {
    const promises = batch.map(command => sendCommandToFirebase(command))
    const results = await Promise.allSettled(promises)

    results.forEach((result, index) => {
      const command = batch[index]
      
      if (result.status === 'fulfilled') {
        handleCommandSuccess(command, result.value)
      } else {
        handleCommandFailure(command, result.reason)
      }
    })

    // Update queue and persist
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('queue', queue.value)
      internal.offlineStorage.save('stats', stats)
    }
  }

  /**
   * Send command to Firebase
   * @param {Object} command - Command to send
   * @returns {Promise} Send result
   */
  const sendCommandToFirebase = async (command) => {
    try {
      if (!database) {
        throw new Error('Firebase database not available')
      }

      const commandRef = dbRef(database, `channels/${channelId}/commands`)
      const newCommandRef = push(commandRef)
      await set(newCommandRef, command)

      return { success: true }
      
    } catch (error) {
      console.error('Failed to send command to Firebase:', error)
      throw error
    }
  }

  /**
   * Handle successful command send
   * @param {Object} command - Command that was sent
   * @param {Object} result - Send result
   */
  const handleCommandSuccess = (command, result) => {
    // Remove from queue
    const index = queue.value.findIndex(cmd => cmd.commandId === command.commandId)
    if (index !== -1) {
      queue.value.splice(index, 1)
    }

    // Update stats
    stats.totalSent++
    updateAverageRetryCount()

    // Trigger callback
    const callback = internal.commandCallbacks.get(command.commandId)
    if (callback) {
      callback({ success: true, result })
      internal.commandCallbacks.delete(command.commandId)
    }

    console.log('Command sent successfully:', command.commandId)
  }

  /**
   * Handle failed command send
   * @param {Object} command - Command that failed
   * @param {Error} error - Error that occurred
   */
  const handleCommandFailure = (command, error) => {
    command.metadata.retryCount++
    stats.totalRetried++

    // Check if we should retry
    if (command.metadata.retryCount <= command.metadata.maxRetries) {
      // Schedule retry
      const delay = calculateRetryDelay(command.metadata.retryCount)
      command.metadata.nextRetryTime = Date.now() + delay
      command.status = 'retry'

      // Set retry timer
      const timerId = setTimeout(() => {
        processQueue()
      }, delay)
      internal.retryTimers.set(command.commandId, timerId)

      console.log(`Command retry scheduled (${command.metadata.retryCount}/${command.metadata.maxRetries}):`, command.commandId)
    } else {
      // Max retries reached, mark as failed
      command.status = 'failed'
      stats.totalFailed++

      // Trigger callback
      const callback = internal.commandCallbacks.get(command.commandId)
      if (callback) {
        callback({ success: false, error: 'Max retries exceeded' })
        internal.commandCallbacks.delete(command.commandId)
      }

      console.error('Command failed after max retries:', command.commandId)
    }

    updateAverageRetryCount()
  }

  /**
   * Calculate retry delay with exponential backoff
   * @param {number} retryCount - Current retry count
   * @returns {number} Delay in milliseconds
   */
  const calculateRetryDelay = (retryCount) => {
    if (config.exponentialBackoff) {
      return config.retryDelay * Math.pow(2, retryCount - 1)
    }
    return config.retryDelay
  }

  /**
   * Update average retry count
   */
  const updateAverageRetryCount = () => {
    const totalCommands = stats.totalSent + stats.totalFailed
    if (totalCommands > 0) {
      stats.averageRetryCount = stats.totalRetried / totalCommands
    }
  }

  /**
   * Create batches from array
   * @param {Array} items - Items to batch
   * @param {number} batchSize - Batch size
   * @returns {Array} Array of batches
   */
  const createBatches = (items, batchSize) => {
    const batches = []
    for (let i = 0; i < items.length; i += batchSize) {
      batches.push(items.slice(i, i + batchSize))
    }
    return batches
  }

  /**
   * Start processing the queue
   */
  const startProcessing = () => {
    if (internal.processingTimer) {
      clearInterval(internal.processingTimer)
    }

    internal.processingTimer = setInterval(() => {
      processQueue()
    }, 1000) // Check every second
  }

  /**
   * Stop processing the queue
   */
  const stopProcessing = () => {
    if (internal.processingTimer) {
      clearInterval(internal.processingTimer)
      internal.processingTimer = null
    }
  }

  /**
   * Handle online event
   */
  const handleOnline = () => {
    isOnline.value = true
    console.log('Back online, processing command queue')
    startProcessing()
  }

  /**
   * Handle offline event
   */
  const handleOffline = () => {
    isOnline.value = false
    console.log('Gone offline, pausing command queue')
    stopProcessing()
  }

  /**
   * Listen for incoming commands (for acknowledgment handling)
   * @param {Function} callback - Callback for incoming commands
   * @returns {Function} Unsubscribe function
   */
  const onCommand = (callback) => {
    if (typeof callback !== 'function') {
      throw new Error('Callback must be a function')
    }

    // Set up Firebase listener for commands
    if (!database) {
      return () => {}
    }

    const commandsRef = dbRef(database, `channels/${channelId}/commands`)
    const listener = onValue(commandsRef, (snapshot) => {
      const commands = snapshot.val()
      if (commands) {
        Object.entries(commands).forEach(([id, command]) => {
          if (command && command.channelId === channelId) {
            callback(command)
          }
        })
      }
    })

    return () => {
      if (typeof listener === 'function') {
        listener()
      }
    }
  }

  /**
   * Clear the queue
   */
  const clearQueue = () => {
    // Clear retry timers
    internal.retryTimers.forEach((timerId, commandId) => {
      clearTimeout(timerId)
    })
    internal.retryTimers.clear()

    // Clear callbacks
    internal.commandCallbacks.clear()

    // Clear queue
    queue.value = []

    // Persist
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('queue', [])
    }

    console.log('Command queue cleared')
  }

  /**
   * Get queue statistics
   * @returns {Object} Queue statistics
   */
  const getQueueStats = () => {
    const queued = queue.value.filter(cmd => cmd.status === 'queued').length
    const retrying = queue.value.filter(cmd => cmd.status === 'retry').length
    const failed = queue.value.filter(cmd => cmd.status === 'failed').length

    return {
      ...stats,
      queueSize: queue.value.length,
      queued,
      retrying,
      failed,
      isOnline: isOnline.value,
      processing: processingQueue.value
    }
  }

  /**
   * Export queue data
   * @returns {Object} Export data
   */
  const exportQueue = () => {
    return {
      queue: queue.value,
      stats,
      config,
      timestamp: new Date().toISOString(),
      channelId
    }
  }

  /**
   * Import queue data
   * @param {Object} data - Data to import
   * @returns {boolean} Success status
   */
  const importQueue = (data) => {
    if (!data || typeof data !== 'object') {
      return false
    }

    try {
      if (data.queue && Array.isArray(data.queue)) {
        queue.value = data.queue
      }

      if (data.stats && typeof data.stats === 'object') {
        Object.assign(stats, data.stats)
      }

      // Persist imported data
      if (config.persistence && internal.offlineStorage) {
        internal.offlineStorage.save('queue', queue.value)
        internal.offlineStorage.save('stats', stats)
      }

      return true
    } catch (error) {
      console.error('Failed to import queue data:', error)
      return false
    }
  }

  /**
   * Cleanup function
   */
  const cleanup = () => {
    stopProcessing()
    
    // Clear timers
    internal.retryTimers.forEach((timerId) => {
      clearTimeout(timerId)
    })
    internal.retryTimers.clear()

    // Remove event listeners
    window.removeEventListener('online', handleOnline)
    window.removeEventListener('offline', handleOffline)

    // Clear callbacks
    internal.commandCallbacks.clear()
  }

  // Initialize on mount
  onMounted(() => {
    initialize()
  })

  // Cleanup on unmount
  onUnmounted(() => {
    cleanup()
  })

  return {
    // Reactive state
    queue,
    processingQueue,
    isOnline,
    stats,
    
    // Methods
    sendCommand,
    onCommand,
    clearQueue,
    getQueueStats,
    exportQueue,
    importQueue,
    cleanup,
    
    // Internal access
    processQueue
  }
}

/**
 * Generate unique command ID
 */
function generateCommandId() {
  return 'cmd_' + Date.now().toString(36) + Math.random().toString(36).substr(2)
}

/**
 * Get or generate user ID
 */
function getUserId() {
  const key = 'rt_queue_user_id'
  let userId = localStorage.getItem(key)
  
  if (!userId) {
    userId = 'user_' + Date.now().toString(36) + Math.random().toString(36).substr(2)
    localStorage.setItem(key, userId)
  }
  
  return userId
}
