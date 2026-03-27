/**
 * Event Logger Composable
 * Comprehensive event logging for debugging, analytics, and audit trails
 */

import { ref, reactive, onMounted, onUnmounted, watch } from 'vue'
import { useOfflineStorage } from './useOfflineStorage.js'
import { validateEvent, sanitizeData } from '../utils/validation.js'
import { debounce } from '../utils/debounce.js'
import { EventType } from '../types/channel.types.js'
import { database } from '@/firebase/init'
import { ref as dbRef, onValue, off, set, push } from 'firebase/database'
import { ToolsSwitcher } from '@/Utils/toolsSwitcher'

/**
 * Event logger composable for comprehensive event tracking
 * @param {string} channelId - Channel identifier
 * @param {Object} [options={}] - Configuration options
 * @param {boolean} [options.persistence=true] - Enable offline persistence
 * @param {boolean} [options.remoteLogging=true] - Enable Firebase logging
 * @param {number} [options.maxLocalEvents=1000] - Maximum events to store locally
 * @param {number} [options.maxRemoteEvents=10000] - Maximum events to store remotely
 * @param {number} [options.flushInterval=5000] - Auto-flush interval in ms
 * @param {boolean} [options.enableFiltering=true] - Enable event filtering
 * @param {Array} [options.eventTypes] - Event types to log (all if not specified)
 * @param {boolean} [options.enableCompression=false] - Enable event compression
 * @param {boolean} [options.enableAnalytics=false] - Enable analytics collection
 * @returns {Object} Event logger API
 */
export function useEventLogger(channelId, options = {}) {
  const config = {
    persistence: true,
    remoteLogging: true,
    maxLocalEvents: 1000,
    maxRemoteEvents: 10000,
    flushInterval: 5000,
    enableFiltering: true,
    eventTypes: null, // Log all types if null
    enableCompression: false,
    enableAnalytics: false,
    ...options
  }

  // Reactive state
  const events = ref([])
  const isLogging = ref(false)
  const stats = reactive({
    totalEvents: 0,
    eventsByType: {},
    eventsBySource: {},
    averageEventsPerMinute: 0,
    firstEventTime: null,
    lastEventTime: null,
    errorCount: 0
  })

  // Internal state
  const internal = reactive({
    offlineStorage: null,
    flushTimer: null,
    pendingEvents: [],
    analytics: {
      sessionStart: Date.now(),
      eventsPerMinute: [],
      errorPatterns: new Map(),
      performanceMetrics: new Map()
    },
    userId: null,
    sessionId: null
  })

  /**
   * Initialize the event logger
   */
  const initialize = async () => {
    try {
      // Initialize offline storage if enabled
      if (config.persistence) {
        internal.offlineStorage = useOfflineStorage(`rt_logger_${channelId}`)
        
        // Load cached events
        const cachedEvents = internal.offlineStorage.load('events', [])
        events.value = cachedEvents
        
        // Load stats
        const cachedStats = internal.offlineStorage.load('stats', stats)
        Object.assign(stats, cachedStats)
        
        // Load analytics if enabled
        if (config.enableAnalytics) {
          const cachedAnalytics = internal.offlineStorage.load('analytics', internal.analytics)
          Object.assign(internal.analytics, cachedAnalytics)
        }
      }

      // Generate session and user IDs
      internal.userId = getUserId()
      internal.sessionId = generateSessionId()

      // Start auto-flush timer
      if (config.flushInterval > 0) {
        startFlushTimer()
      }

      // Log initialization event
      logEvent(EventType.CONNECTION, {
        action: 'logger_initialized',
        config: sanitizeConfig(config)
      })

      isLogging.value = true
      console.log(`Event logger initialized for channel: ${channelId}`)
      
    } catch (error) {
      console.error('Failed to initialize event logger:', error)
    }
  }

  /**
   * Log an event
   * @param {string} type - Event type
   * @param {any} data - Event data
   * @param {Object} [metadata={}] - Additional metadata
   * @returns {string} Event ID
   */
  const logEvent = (type, data, metadata = {}) => {
    if (!isLogging.value) {
      console.warn('Event logger not initialized')
      return null
    }

    // Check event type filter
    if (config.enableFiltering && config.eventTypes && !config.eventTypes.includes(type)) {
      return null
    }

    // Create event object
    const event = {
      eventId: generateEventId(),
      channelId,
      type,
      data: sanitizeData(data),
      timestamp: new Date().toISOString(),
      metadata: {
        source: metadata.source || 'local',
        userId: internal.userId,
        sessionId: internal.sessionId,
        userAgent: navigator.userAgent,
        url: window.location.href,
        ...metadata
      }
    }

    // Validate event
    const validated = validateEvent(event)
    if (!validated.isValid) {
      console.warn('Invalid event:', validated.errors)
      return null
    }

    // Add to local events
    events.value.push(validated.sanitized)
    
    // Limit local events
    if (events.value.length > config.maxLocalEvents) {
      events.value = events.value.slice(-config.maxLocalEvents)
    }

    // Add to pending events for remote logging
    if (config.remoteLogging) {
      internal.pendingEvents.push(validated.sanitized)
    }

    // Update stats
    updateStats(validated.sanitized)

    // Update analytics if enabled
    if (config.enableAnalytics) {
      updateAnalytics(validated.sanitized)
    }

    // Persist locally
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('events', events.value)
      internal.offlineStorage.save('stats', stats)
    }

    return validated.sanitized.eventId
  }

  /**
   * Update statistics
   * @param {Object} event - Event to update stats for
   */
  const updateStats = (event) => {
    stats.totalEvents++
    
    // Update events by type
    if (!stats.eventsByType[event.type]) {
      stats.eventsByType[event.type] = 0
    }
    stats.eventsByType[event.type]++
    
    // Update events by source
    if (!stats.eventsBySource[event.metadata.source]) {
      stats.eventsBySource[event.metadata.source] = 0
    }
    stats.eventsBySource[event.metadata.source]++
    
    // Update timestamps
    if (!stats.firstEventTime) {
      stats.firstEventTime = event.timestamp
    }
    stats.lastEventTime = event.timestamp
    
    // Update error count
    if (event.type === EventType.ERROR) {
      stats.errorCount++
    }
    
    // Calculate average events per minute
    updateAverageEventsPerMinute()
  }

  /**
   * Update analytics data
   * @param {Object} event - Event to analyze
   */
  const updateAnalytics = (event) => {
    // Track events per minute
    const now = Date.now()
    const minuteKey = Math.floor(now / 60000)
    
    if (!internal.analytics.eventsPerMinute[minuteKey]) {
      internal.analytics.eventsPerMinute[minuteKey] = 0
    }
    internal.analytics.eventsPerMinute[minuteKey]++
    
    // Track error patterns
    if (event.type === EventType.ERROR && event.data) {
      const errorKey = event.data.error || event.data.message || 'unknown'
      if (!internal.analytics.errorPatterns.has(errorKey)) {
        internal.analytics.errorPatterns.set(errorKey, 0)
      }
      internal.analytics.errorPatterns.set(errorKey, internal.analytics.errorPatterns.get(errorKey) + 1)
    }
    
    // Track performance metrics
    if (event.type === EventType.STATE_CHANGE && event.metadata.duration) {
      const operationKey = event.data.operation || 'unknown'
      if (!internal.analytics.performanceMetrics.has(operationKey)) {
        internal.analytics.performanceMetrics.set(operationKey, {
          count: 0,
          totalDuration: 0,
          minDuration: Infinity,
          maxDuration: 0
        })
      }
      
      const metrics = internal.analytics.performanceMetrics.get(operationKey)
      metrics.count++
      metrics.totalDuration += event.metadata.duration
      metrics.minDuration = Math.min(metrics.minDuration, event.metadata.duration)
      metrics.maxDuration = Math.max(metrics.maxDuration, event.metadata.duration)
    }
    
    // Persist analytics
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('analytics', internal.analytics)
    }
  }

  /**
   * Calculate average events per minute
   */
  const updateAverageEventsPerMinute = () => {
    if (!stats.firstEventTime) {
      return
    }
    
    const now = new Date(stats.lastEventTime)
    const first = new Date(stats.firstEventTime)
    const minutesDiff = (now - first) / (1000 * 60)
    
    if (minutesDiff > 0) {
      stats.averageEventsPerMinute = stats.totalEvents / minutesDiff
    }
  }

  /**
   * Flush pending events to remote storage
   */
  const flushEvents = async () => {
    if (!config.remoteLogging || internal.pendingEvents.length === 0) {
      return
    }

    try {
      if (!database) {
        throw new Error('Firebase database not available')
      }
      
      for (const batch of batches) {
        const promises = batch.map(event => {
          const eventRef = dbRef(database, `channels/${channelId}/events/${event.eventId}`)
          return set(eventRef, event)
        })
        
        await Promise.allSettled(promises)
      }

      // Clear pending events
      internal.pendingEvents = []
      
      console.log(`Flushed ${batches.length * batchSize} events to remote storage`)
      
    } catch (error) {
      console.error('Failed to flush events:', error)
      
      // Don't clear pending events on error, will retry on next flush
    }
  }

  /**
   * Start auto-flush timer
   */
  const startFlushTimer = () => {
    if (internal.flushTimer) {
      clearInterval(internal.flushTimer)
    }

    internal.flushTimer = setInterval(() => {
      flushEvents()
    }, config.flushInterval)
  }

  /**
   * Stop auto-flush timer
   */
  const stopFlushTimer = () => {
    if (internal.flushTimer) {
      clearInterval(internal.flushTimer)
      internal.flushTimer = null
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
   * Get events by type
   * @param {string} eventType - Event type to filter by
   * @param {number} [limit] - Limit number of results
   * @returns {Array} Filtered events
   */
  const getEventsByType = (eventType, limit = null) => {
    const filtered = events.value.filter(event => event.type === eventType)
    return limit ? filtered.slice(-limit) : filtered
  }

  /**
   * Get events by time range
   * @param {Date|string} startTime - Start time
   * @param {Date|string} endTime - End time
   * @returns {Array} Filtered events
   */
  const getEventsByTimeRange = (startTime, endTime) => {
    const start = new Date(startTime)
    const end = new Date(endTime)
    
    return events.value.filter(event => {
      const eventTime = new Date(event.timestamp)
      return eventTime >= start && eventTime <= end
    })
  }

  /**
   * Get events by source
   * @param {string} source - Source to filter by
   * @returns {Array} Filtered events
   */
  const getEventsBySource = (source) => {
    return events.value.filter(event => event.metadata.source === source)
  }

  /**
   * Search events
   * @param {string} query - Search query
   * @param {Object} [options] - Search options
   * @returns {Array} Matching events
   */
  const searchEvents = (query, options = {}) => {
    const {
      caseSensitive = false,
      includeData = true,
      includeMetadata = true,
      eventTypes = null
    } = options

    const searchTerm = caseSensitive ? query : query.toLowerCase()
    
    return events.value.filter(event => {
      // Filter by event type if specified
      if (eventTypes && !eventTypes.includes(event.type)) {
        return false
      }

      // Search in event type
      const typeMatch = caseSensitive 
        ? event.type.includes(searchTerm)
        : event.type.toLowerCase().includes(searchTerm)
      
      if (typeMatch) return true

      // Search in data
      if (includeData && event.data) {
        const dataString = JSON.stringify(event.data)
        const dataMatch = caseSensitive
          ? dataString.includes(searchTerm)
          : dataString.toLowerCase().includes(searchTerm)
        if (dataMatch) return true
      }

      // Search in metadata
      if (includeMetadata && event.metadata) {
        const metadataString = JSON.stringify(event.metadata)
        const metadataMatch = caseSensitive
          ? metadataString.includes(searchTerm)
          : metadataString.toLowerCase().includes(searchTerm)
        if (metadataMatch) return true
      }

      return false
    })
  }

  /**
   * Get analytics data
   * @returns {Object} Analytics summary
   */
  const getAnalytics = () => {
    if (!config.enableAnalytics) {
      return null
    }

    const now = Date.now()
    const sessionDuration = now - internal.analytics.sessionStart
    
    // Calculate recent events per minute
    const recentMinutes = 10
    const recentEventsPerMinute = []
    for (let i = 0; i < recentMinutes; i++) {
      const minuteKey = Math.floor((now - i * 60000) / 60000)
      recentEventsPerMinute.push(internal.analytics.eventsPerMinute[minuteKey] || 0)
    }

    return {
      sessionDuration,
      totalEvents: stats.totalEvents,
      eventsPerMinute: recentEventsPerMinute.reverse(),
      errorPatterns: Array.from(internal.analytics.errorPatterns.entries()),
      performanceMetrics: Array.from(internal.analytics.performanceMetrics.entries()).map(([key, metrics]) => ({
        operation: key,
        ...metrics,
        averageDuration: metrics.count > 0 ? metrics.totalDuration / metrics.count : 0
      }))
    }
  }

  /**
   * Export events data
   * @param {Object} [options] - Export options
   * @returns {Object} Export data
   */
  const exportEvents = (options = {}) => {
    const {
      includeStats = true,
      includeAnalytics = config.enableAnalytics,
      eventTypes = null,
      timeRange = null,
      format = 'json'
    } = options

    let filteredEvents = events.value

    // Apply filters
    if (eventTypes) {
      filteredEvents = filteredEvents.filter(event => eventTypes.includes(event.type))
    }

    if (timeRange) {
      filteredEvents = getEventsByTimeRange(timeRange.start, timeRange.end)
    }

    const exportData = {
      events: filteredEvents,
      exportInfo: {
        timestamp: new Date().toISOString(),
        channelId,
        totalEvents: filteredEvents.length,
        format
      }
    }

    if (includeStats) {
      exportData.stats = { ...stats }
    }

    if (includeAnalytics) {
      exportData.analytics = getAnalytics()
    }

    return exportData
  }

  /**
   * Clear all events
   * @param {boolean} [clearRemote=false] - Also clear remote events
   */
  const clearEvents = async (clearRemote = false) => {
    // Clear local events
    events.value = []
    internal.pendingEvents = []
    
    // Reset stats
    Object.assign(stats, {
      totalEvents: 0,
      eventsByType: {},
      eventsBySource: {},
      averageEventsPerMinute: 0,
      firstEventTime: null,
      lastEventTime: null,
      errorCount: 0
    })

    // Clear analytics
    if (config.enableAnalytics) {
      internal.analytics = {
        sessionStart: Date.now(),
        eventsPerMinute: [],
        errorPatterns: new Map(),
        performanceMetrics: new Map()
      }
    }

    // Persist cleared state
    if (config.persistence && internal.offlineStorage) {
      internal.offlineStorage.save('events', [])
      internal.offlineStorage.save('stats', stats)
      if (config.enableAnalytics) {
        internal.offlineStorage.save('analytics', internal.analytics)
      }
    }

    // Clear remote events if requested
    if (clearRemote && config.remoteLogging) {
      try {
        if (database) {
          const eventsRef = dbRef(database, `channels/${channelId}/events`)
          await set(eventsRef, null) // Use set with null to clear
        }
      } catch (error) {
        console.error('Failed to clear remote events:', error)
      }
    }

    console.log('Event logger cleared')
  }

  /**
   * Cleanup function
   */
  const cleanup = () => {
    stopFlushTimer()
    
    // Flush any pending events
    if (internal.pendingEvents.length > 0) {
      flushEvents()
    }
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
    events,
    isLogging,
    stats,
    
    // Methods
    logEvent,
    getEventsByType,
    getEventsByTimeRange,
    getEventsBySource,
    searchEvents,
    getAnalytics,
    exportEvents,
    clearEvents,
    flushEvents,
    cleanup,
    
    // Internal access
    pendingEvents: internal.pendingEvents
  }
}

/**
 * Utility functions
 */

/**
 * Generate unique event ID
 */
function generateEventId() {
  return 'evt_' + Date.now().toString(36) + Math.random().toString(36).substr(2)
}

/**
 * Generate session ID
 */
function generateSessionId() {
  return 'sess_' + Date.now().toString(36) + Math.random().toString(36).substr(2)
}

/**
 * Get or generate user ID
 */
function getUserId() {
  const key = 'rt_logger_user_id'
  let userId = localStorage.getItem(key)
  
  if (!userId) {
    userId = 'user_' + Date.now().toString(36) + Math.random().toString(36).substr(2)
    localStorage.setItem(key, userId)
  }
  
  return userId
}

/**
 * Sanitize config object for logging
 */
function sanitizeConfig(config) {
  const sanitized = { ...config }
  
  // Remove sensitive or large fields
  delete sanitized.eventTypes
  delete sanitized.offlineStorage
  
  return sanitized
}
