/**
 * Simplified Realtime State Composable
 * Built on top of useRealtimeChannel for state-only synchronization
 */

import { ref, computed, watch } from 'vue'
import { useRealtimeChannel } from './useRealtimeChannel.js'

/**
 * Simplified composable for state-only synchronization
 * @param {string} channelId - Unique channel identifier
 * @param {any} initialState - Initial state object
 * @param {Object} [options={}] - Configuration options
 * @returns {Object} State synchronization API
 */
export function useRealtimeState(channelId, initialState = {}, options = {}) {
  const config = {
    debounce: 300,
    persistence: true,
    validateState: true,
    maxHistorySize: 50,
    enableUndo: false,
    enableRedo: false,
    ...options
  }

  // Use the core channel composable
  const channel = useRealtimeChannel(channelId, {
    debounce: config.debounce,
    persistence: config.persistence,
    validateCommands: false, // Not needed for state-only
    logEvents: false
  })

  // Local state
  const localState = ref(initialState)
  const history = ref([])
  const historyIndex = ref(-1)
  const isUpdating = ref(false)

  // Computed properties
  const state = computed(() => channel.state.value?.data || localState.value)
  const isConnected = computed(() => channel.isConnected.value)
  const canUndo = computed(() => config.enableUndo && historyIndex.value > 0)
  const canRedo = computed(() => config.enableRedo && historyIndex.value < history.value.length - 1)
  const hasUnsavedChanges = computed(() => {
    if (!channel.state.value) return true
    return JSON.stringify(state.value) !== JSON.stringify(channel.state.value.data)
  })

  /**
   * Update state locally and remotely
   * @param {any} newState - New state data
   * @param {boolean} [addToHistory=true] - Whether to add to history
   */
  const updateState = (newState, addToHistory = true) => {
    if (isUpdating.value) {
      return // Prevent recursive updates
    }

    // Merge with current state
    const mergedState = typeof newState === 'object' && newState !== null
      ? { ...state.value, ...newState }
      : newState

    // Add to history if enabled
    if (config.enableUndo && addToHistory) {
      addToHistory(mergedState, 'update')
    }

    // Update local state immediately
    localState.value = mergedState

    // Update remote state
    channel.updateState(mergedState)
  }

  /**
   * Set state (replace entire state)
   * @param {any} newState - New state data
   */
  const setState = (newState) => {
    if (isUpdating.value) {
      return
    }

    if (config.enableUndo) {
      addToHistory(newState, 'set')
    }

    localState.value = newState
    channel.updateState(newState)
  }

  /**
   * Reset state to initial value
   */
  const resetState = () => {
    setState(initialState)
  }

  /**
   * Add state to history for undo/redo
   * @param {any} stateData - State data to add
   * @param {string} action - Action type
   */
  const addToHistory = (stateData, action = 'update') => {
    if (!config.enableUndo) {
      return
    }

    // Remove any future history if we're not at the end
    if (historyIndex.value < history.value.length - 1) {
      history.value = history.value.slice(0, historyIndex.value + 1)
    }

    // Add new history item
    history.value.push({
      state: JSON.parse(JSON.stringify(stateData)), // Deep clone
      action,
      timestamp: new Date().toISOString()
    })

    // Limit history size
    if (history.value.length > config.maxHistorySize) {
      history.value.shift()
    } else {
      historyIndex.value = history.value.length - 1
    }
  }

  /**
   * Undo last state change
   */
  const undo = () => {
    if (!canUndo.value) {
      return false
    }

    historyIndex.value--
    const historyItem = history.value[historyIndex.value]
    
    isUpdating.value = true
    localState.value = JSON.parse(JSON.stringify(historyItem.state))
    channel.updateState(historyItem.state)
    
    // Use nextTick to reset the flag
    setTimeout(() => {
      isUpdating.value = false
    }, 0)

    return true
  }

  /**
   * Redo next state change
   */
  const redo = () => {
    if (!canRedo.value) {
      return false
    }

    historyIndex.value++
    const historyItem = history.value[historyIndex.value]
    
    isUpdating.value = true
    localState.value = JSON.parse(JSON.stringify(historyItem.state))
    channel.updateState(historyItem.state)
    
    setTimeout(() => {
      isUpdating.value = false
    }, 0)

    return true
  }

  /**
   * Clear history
   */
  const clearHistory = () => {
    history.value = []
    historyIndex.value = -1
  }

  /**
   * Get state difference
   * @param {any} oldState - Old state
   * @param {any} newState - New state
   * @returns {Object} Difference object
   */
  const getStateDiff = (oldState, newState) => {
    const diff = {}
    
    // Simple diff implementation
    if (typeof oldState === 'object' && typeof newState === 'object') {
      // Find changed keys
      const allKeys = new Set([...Object.keys(oldState), ...Object.keys(newState)])
      
      allKeys.forEach(key => {
        if (JSON.stringify(oldState[key]) !== JSON.stringify(newState[key])) {
          diff[key] = {
            old: oldState[key],
            new: newState[key]
          }
        }
      })
    } else if (oldState !== newState) {
      diff.value = { old: oldState, new: newState }
    }
    
    return diff
  }

  /**
   * Watch for remote state changes
   */
  const unwatchStateChange = channel.onStateChange((newState) => {
    if (isUpdating.value) {
      return // Ignore changes we triggered
    }

    isUpdating.value = true
    
    // Calculate diff for debugging
    const diff = getStateDiff(localState.value, newState.data)
    if (Object.keys(diff).length > 0) {
      console.log('State changed remotely:', diff)
    }

    // Update local state
    localState.value = newState.data

    // Add to history if it's a remote change
    if (config.enableUndo) {
      addToHistory(newState.data, 'remote')
    }

    setTimeout(() => {
      isUpdating.value = false
    }, 0)
  })

  /**
   * Watch for connection changes
   */
  watch(isConnected, (connected) => {
    if (connected && hasUnsavedChanges.value) {
      // Sync local state when coming back online
      channel.updateState(localState.value)
    }
  })

  /**
   * Export state data
   * @returns {Object} Export data
   */
  const exportState = () => {
    return {
      state: state.value,
      history: config.enableUndo ? history.value : null,
      historyIndex: config.enableUndo ? historyIndex.value : null,
      timestamp: new Date().toISOString(),
      channelId
    }
  }

  /**
   * Import state data
   * @param {Object} data - Data to import
   * @returns {boolean} Success status
   */
  const importState = (data) => {
    if (!data || typeof data !== 'object') {
      return false
    }

    try {
      if (data.state !== undefined) {
        setState(data.state)
      }

      if (config.enableUndo && data.history) {
        history.value = data.history
        historyIndex.value = data.historyIndex || data.history.length - 1
      }

      return true
    } catch (error) {
      console.error('Failed to import state:', error)
      return false
    }
  }

  /**
   * Get state statistics
   * @returns {Object} Statistics
   */
  const getStateStats = () => {
    const currentState = state.value
    const stats = {
      stateSize: JSON.stringify(currentState).length,
      historySize: history.value.length,
      historyIndex: historyIndex.value,
      isConnected: isConnected.value,
      lastUpdated: channel.state.value?.metadata?.lastUpdated,
      version: channel.state.value?.metadata?.version,
      hasUnsavedChanges: hasUnsavedChanges.value
    }

    if (typeof currentState === 'object' && currentState !== null) {
      stats.keyCount = Object.keys(currentState).length
      stats.keys = Object.keys(currentState)
    }

    return stats
  }

  /**
   * Cleanup function
   */
  const cleanup = () => {
    unwatchStateChange()
    channel.disconnect()
  }

  return {
    // Reactive state
    state,
    localState,
    isConnected,
    isUpdating,
    hasUnsavedChanges,
    
    // History (if enabled)
    history,
    canUndo,
    canRedo,
    
    // Methods
    updateState,
    setState,
    resetState,
    undo,
    redo,
    clearHistory,
    exportState,
    importState,
    getStateDiff,
    getStateStats,
    cleanup,
    
    // Channel access (for advanced use)
    channel
  }
}

/**
 * Create a state watcher for specific properties
 * @param {Function} stateRef - State ref to watch
 * @param {string[]} properties - Properties to watch
 * @param {Function} callback - Callback when properties change
 * @returns {Function} Stop watching function
 */
export function watchStateProperties(stateRef, properties, callback) {
  if (!Array.isArray(properties) || typeof callback !== 'function') {
    throw new Error('Invalid parameters for watchStateProperties')
  }

  let previousValues = {}

  // Initialize previous values
  properties.forEach(prop => {
    previousValues[prop] = stateRef.value?.[prop]
  })

  const stopWatch = watch(stateRef, (newState, oldState) => {
    const changes = {}

    properties.forEach(prop => {
      const newValue = newState?.[prop]
      const oldValue = previousValues[prop]

      if (JSON.stringify(newValue) !== JSON.stringify(oldValue)) {
        changes[prop] = {
          old: oldValue,
          new: newValue
        }
        previousValues[prop] = newValue
      }
    })

    if (Object.keys(changes).length > 0) {
      callback(changes, newState, oldState)
    }
  }, { deep: true })

  return stopWatch
}

/**
 * Create a state validator
 * @param {Object} schema - Validation schema
 * @returns {Function} Validator function
 */
export function createStateValidator(schema) {
  return (state) => {
    const errors = []
    
    if (!state || typeof state !== 'object') {
      errors.push('State must be an object')
      return { isValid: false, errors }
    }

    // Validate required fields
    if (schema.required) {
      schema.required.forEach(field => {
        if (!(field in state)) {
          errors.push(`Required field missing: ${field}`)
        }
      })
    }

    // Validate field types
    if (schema.types) {
      Object.keys(schema.types).forEach(field => {
        if (field in state && typeof state[field] !== schema.types[field]) {
          errors.push(`Field ${field} must be of type ${schema.types[field]}`)
        }
      })
    }

    // Validate field values
    if (schema.values) {
      Object.keys(schema.values).forEach(field => {
        if (field in state && !schema.values[field].includes(state[field])) {
          errors.push(`Field ${field} must be one of: ${schema.values[field].join(', ')}`)
        }
      })
    }

    // Custom validation
    if (schema.custom && typeof schema.custom === 'function') {
      const customErrors = schema.custom(state)
      if (customErrors && customErrors.length > 0) {
        errors.push(...customErrors)
      }
    }

    return {
      isValid: errors.length === 0,
      errors
    }
  }
}
