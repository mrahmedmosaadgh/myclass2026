/**
 * Generic Real-Time Communication System Type Definitions
 * These types are completely use-case agnostic and can be used for any real-time scenario
 */

/**
 * Generic channel configuration options
 * @typedef {Object} ChannelConfig
 * @property {boolean} [persistence=true] - Enable offline storage for state and commands
 * @property {boolean} [encryption=false] - Enable data encryption for sensitive data
 * @property {number} [debounce=300] - Debounce delay in milliseconds for state updates
 * @property {string} [firebasePath='channels'] - Custom Firebase path prefix
 * @property {number} [maxRetries=3] - Maximum retry attempts for failed operations
 * @property {number} [retryDelay=1000] - Base delay for retry attempts (exponential backoff)
 * @property {boolean} [validateCommands=true] - Enable command validation
 * @property {boolean} [logEvents=false] - Enable event logging for debugging
 */

/**
 * Generic channel state structure - completely flexible data
 * @typedef {Object} ChannelState
 * @property {string} channelId - Unique channel identifier
 * @property {any} data - ANY JSON data structure - completely flexible
 * @property {Object} metadata - Metadata about the state
 * @property {string} metadata.lastUpdated - ISO timestamp of last update
 * @property {string} metadata.updatedBy - User ID who made the update
 * @property {number} metadata.version - State version number for conflict resolution
 */

/**
 * Generic command structure - completely flexible
 * @typedef {Object} Command
 * @property {string} commandId - Auto-generated unique identifier
 * @property {string} channelId - Target channel identifier
 * @property {string} type - ANY command type - completely flexible
 * @property {any} payload - ANY payload structure - completely flexible
 * @property {Object} metadata - Command metadata
 * @property {string} metadata.timestamp - ISO timestamp when command was created
 * @property {string} metadata.senderId - User ID who sent the command
 * @property {'normal'|'high'|'low'} [metadata.priority='normal'] - Command priority
 * @property {boolean} [metadata.requiresAck=false] - Requires acknowledgment
 * @property {number} [metadata.retryCount=0] - Number of retry attempts
 */

/**
 * Event structure for logging and debugging
 * @typedef {Object} ChannelEvent
 * @property {string} eventId - Auto-generated unique identifier
 * @property {string} channelId - Channel identifier
 * @property {'state_change'|'command_sent'|'command_received'|'connection'|'error'|'offline_sync'} type - Event type
 * @property {any} data - Event-specific data
 * @property {string} timestamp - ISO timestamp
 * @property {Object} metadata - Event metadata
 * @property {string} metadata.source - Source of the event (local/remote)
 * @property {string} [metadata.error] - Error message if applicable
 */

/**
 * Connection status types
 * @typedef {'connected'|'disconnected'|'reconnecting'|'error'} ConnectionStatus
 */

/**
 * Command queue item for offline storage
 * @typedef {Object} QueuedCommand
 * @property {Command} command - The command to send
 * @property {number} timestamp - When it was queued
 * @property {number} retryCount - Number of retry attempts
 */

/**
 * State history item for undo/redo functionality
 * @typedef {Object} StateHistoryItem
 * @property {any} state - The state at this point in time
 * @property {string} timestamp - When this state was active
 * @property {string} action - Action that led to this state
 * @property {string} userId - User who made the change
 */

/**
 * Firebase path structure
 * @typedef {Object} FirebasePaths
 * @property {string} state - Path to channel state (/channels/{channelId}/state)
 * @property {string} commands - Path to commands (/channels/{channelId}/commands)
 * @property {string} events - Path to events (/channels/{channelId}/events)
 * @property {string} queue - Path to offline queue (/channels/{channelId}/queue)
 */

/**
 * Validation schema for commands and state
 * @typedef {Object} ValidationSchema
 * @property {Object} [state] - Schema for state validation
 * @property {Object} [commands] - Schema for command validation
 * @property {Function} [customValidator] - Custom validation function
 */

/**
 * Real-time channel instance returned by useRealtimeChannel
 * @typedef {Object} RealtimeChannel
 * @property {import('vue').Ref<ChannelState|null>} state - Reactive channel state
 * @property {import('vue').Ref<ConnectionStatus>} isConnected - Connection status
 * @property {import('vue').Ref<Command[]>} pendingCommands - Commands waiting to be sent
 * @property {Function} sendCommand - Send a command to the channel
 * @property {Function} updateState - Update the shared state
 * @property {Function} onCommand - Listen for incoming commands
 * @property {Function} onStateChange - Listen for state changes
 * @property {Function} disconnect - Disconnect from channel
 * @property {Function} reconnect - Reconnect to channel
 * @property {Function} clearQueue - Clear pending commands queue
 * @property {import('vue').Ref<StateHistoryItem[]>} history - State change history
 */

/**
 * Export all types for use in other files
 */

export const ChannelPriority = {
  LOW: 'low',
  NORMAL: 'normal',
  HIGH: 'high'
}

export const ConnectionStatus = {
  CONNECTED: 'connected',
  DISCONNECTED: 'disconnected',
  RECONNECTING: 'reconnecting',
  ERROR: 'error'
}

export const EventType = {
  STATE_CHANGE: 'state_change',
  COMMAND_SENT: 'command_sent',
  COMMAND_RECEIVED: 'command_received',
  CONNECTION: 'connection',
  ERROR: 'error',
  OFFLINE_SYNC: 'offline_sync'
}

/**
 * Type guards for runtime validation
 */
export const isCommand = (obj) => {
  return obj && 
         typeof obj === 'object' &&
         typeof obj.commandId === 'string' &&
         typeof obj.channelId === 'string' &&
         typeof obj.type === 'string' &&
         obj.metadata &&
         typeof obj.metadata.timestamp === 'string' &&
         typeof obj.metadata.senderId === 'string'
}

export const isChannelState = (obj) => {
  return obj &&
         typeof obj === 'object' &&
         typeof obj.channelId === 'string' &&
         obj.metadata &&
         typeof obj.metadata.lastUpdated === 'string' &&
         typeof obj.metadata.updatedBy === 'string' &&
         typeof obj.metadata.version === 'number'
}

export const isChannelEvent = (obj) => {
  return obj &&
         typeof obj === 'object' &&
         typeof obj.eventId === 'string' &&
         typeof obj.channelId === 'string' &&
         typeof obj.type === 'string' &&
         typeof obj.timestamp === 'string' &&
         obj.metadata &&
         typeof obj.metadata.source === 'string'
}
