/**
 * Debounce and Throttle Utilities
 * Performance optimization utilities for high-frequency updates in real-time communication
 */

/**
 * Debounce function - delays execution until after wait period
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @param {Object} [options] - Debounce options
 * @param {boolean} [options.leading=false] - Execute on leading edge
 * @param {boolean} [options.trailing=true] - Execute on trailing edge
 * @param {number} [options.maxWait] - Maximum wait time
 * @returns {Function} Debounced function
 */
export function debounce(func, wait, options = {}) {
  const {
    leading = false,
    trailing = true,
    maxWait = null
  } = options

  let timeoutId = null
  let maxTimeoutId = null
  let lastCallTime = 0
  let lastInvokeTime = 0
  let lastArgs = null
  let lastThis = null
  let result = undefined

  const invokeFunc = (time) => {
    const args = lastArgs
    const thisArg = lastThis

    lastArgs = null
    lastThis = null
    lastInvokeTime = time
    result = func.apply(thisArg, args)
    return result
  }

  const leadingEdge = (time) => {
    // Reset any pending max timeout
    if (maxTimeoutId) {
      clearTimeout(maxTimeoutId)
      maxTimeoutId = null
    }

    // Start the timer for the trailing edge
    timeoutId = setTimeout(trailingEdge, wait)

    // Invoke on leading edge if enabled
    if (leading) {
      return invokeFunc(time)
    }
  }

  const trailingEdge = (time) => {
    // Clear the timeout
    timeoutId = null

    // Only invoke if we have args and haven't invoked recently
    if (trailing && lastArgs) {
      return invokeFunc(time)
    }
    
    // Reset args for next call
    lastArgs = null
    lastThis = null
    return result
  }

  const maxWaitCheck = (time) => {
    if (time - lastInvokeTime >= maxWait) {
      if (timeoutId) {
        clearTimeout(timeoutId)
        timeoutId = null
      }
      if (maxTimeoutId) {
        clearTimeout(maxTimeoutId)
        maxTimeoutId = null
      }
      return invokeFunc(time)
    }
  }

  const debounced = function(...args) {
    const time = Date.now()
    const isInvoking = (timeoutId !== null)

    lastArgs = args
    lastThis = this
    lastCallTime = time

    // Start new timer if not already running
    if (!isInvoking) {
      return leadingEdge(lastCallTime)
    }

    // Handle max wait
    if (maxWait && !maxTimeoutId) {
      maxTimeoutId = setTimeout(() => {
        maxWaitCheck(Date.now())
      }, maxWait - (time - lastInvokeTime))
    }

    return result
  }

  // Cancel method
  debounced.cancel = function() {
    if (timeoutId) {
      clearTimeout(timeoutId)
      timeoutId = null
    }
    if (maxTimeoutId) {
      clearTimeout(maxTimeoutId)
      maxTimeoutId = null
    }
    lastArgs = null
    lastThis = null
    lastCallTime = 0
    lastInvokeTime = 0
  }

  // Flush method
  debounced.flush = function() {
    if (timeoutId) {
      return trailingEdge(Date.now())
    }
    return result
  }

  // Pending method
  debounced.pending = function() {
    return timeoutId !== null
  }

  return debounced
}

/**
 * Throttle function - limit execution rate
 * @param {Function} func - Function to throttle
 * @param {number} wait - Wait time in milliseconds
 * @param {Object} [options] - Throttle options
 * @param {boolean} [options.leading=true] - Execute on leading edge
 * @param {boolean} [options.trailing=true] - Execute on trailing edge
 * @returns {Function} Throttled function
 */
export function throttle(func, wait, options = {}) {
  const {
    leading = true,
    trailing = true
  } = options

  let timeoutId = null
  let lastArgs = null
  let lastThis = null
  let lastInvokeTime = 0
  let result = undefined

  const invokeFunc = (time) => {
    const args = lastArgs
    const thisArg = lastThis

    lastArgs = null
    lastThis = null
    lastInvokeTime = time
    result = func.apply(thisArg, args)
    return result
  }

  const leadingEdge = (time) => {
    // Reset any pending timeout
    if (timeoutId) {
      clearTimeout(timeoutId)
      timeoutId = null
    }

    // Set timeout for trailing edge
    if (trailing) {
      timeoutId = setTimeout(() => {
        timeoutId = null
        if (lastArgs) {
          invokeFunc(Date.now())
        }
      }, wait)
    }

    // Invoke on leading edge if enabled
    if (leading) {
      return invokeFunc(time)
    }
  }

  const shouldInvoke = (time) => {
    const timeSinceLastCall = time - lastInvokeTime
    return timeSinceLastCall >= wait
  }

  const throttled = function(...args) {
    const time = Date.now()
    const isInvoking = shouldInvoke(time)

    lastArgs = args
    lastThis = this

    if (isInvoking) {
      if (timeoutId === null) {
        return leadingEdge(time)
      }
    }

    return result
  }

  // Cancel method
  throttled.cancel = function() {
    if (timeoutId) {
      clearTimeout(timeoutId)
      timeoutId = null
    }
    lastArgs = null
    lastThis = null
    lastInvokeTime = 0
  }

  // Flush method
  throttled.flush = function() {
    if (timeoutId) {
      const time = Date.now()
      return invokeFunc(time)
    }
    return result
  }

  // Pending method
  throttled.pending = function() {
    return timeoutId !== null
  }

  return throttled
}

/**
 * Create a debounced version of a function that preserves context
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} Debounced function with context preservation
 */
export function createDebouncedFunction(func, delay = 300) {
  return debounce(func, delay, {
    leading: false,
    trailing: true
  })
}

/**
 * Create a throttled version of a function for real-time updates
 * @param {Function} func - Function to throttle
 * @param {number} interval - Interval in milliseconds
 * @returns {Function} Throttled function
 */
export function createThrottledFunction(func, interval = 100) {
  return throttle(func, interval, {
    leading: true,
    trailing: true
  })
}

/**
 * Debounce composable for Vue
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @param {Object} [options] - Debounce options
 * @returns {Object} Debounced function and utilities
 */
export function useDebounce(func, delay = 300, options = {}) {
  const debouncedFunc = debounce(func, delay, options)
  
  return {
    debounced: debouncedFunc,
    cancel: debouncedFunc.cancel,
    flush: debouncedFunc.flush,
    pending: debouncedFunc.pending
  }
}

/**
 * Throttle composable for Vue
 * @param {Function} func - Function to throttle
 * @param {number} interval - Interval in milliseconds
 * @param {Object} [options] - Throttle options
 * @returns {Object} Throttled function and utilities
 */
export function useThrottle(func, interval = 100, options = {}) {
  const throttledFunc = throttle(func, interval, options)
  
  return {
    throttled: throttledFunc,
    cancel: throttledFunc.cancel,
    flush: throttledFunc.flush,
    pending: throttledFunc.pending
  }
}

/**
 * Rate limiter for API calls
 * @param {number} maxCalls - Maximum number of calls
 * @param {number} windowMs - Time window in milliseconds
 * @returns {Object} Rate limiter with check method
 */
export function createRateLimiter(maxCalls = 10, windowMs = 1000) {
  const calls = []
  
  return {
    check: function() {
      const now = Date.now()
      
      // Remove old calls outside the window
      while (calls.length > 0 && calls[0] <= now - windowMs) {
        calls.shift()
      }
      
      // Check if we can make a call
      if (calls.length < maxCalls) {
        calls.push(now)
        return true
      }
      
      return false
    },
    
    reset: function() {
      calls.length = 0
    },
    
    remaining: function() {
      const now = Date.now()
      while (calls.length > 0 && calls[0] <= now - windowMs) {
        calls.shift()
      }
      return Math.max(0, maxCalls - calls.length)
    },
    
    nextAvailable: function() {
      if (calls.length === 0) return 0
      
      const oldestCall = calls[0]
      const nextAvailable = oldestCall + windowMs - Date.now()
      return Math.max(0, nextAvailable)
    }
  }
}

/**
 * Batch updates - collect multiple updates and process them together
 * @param {Function} processor - Function to process batched updates
 * @param {number} [maxBatchSize=10] - Maximum batch size
 * @param {number} [maxWaitTime=100] - Maximum wait time in ms
 * @returns {Object} Batch processor
 */
export function createBatchProcessor(processor, maxBatchSize = 10, maxWaitTime = 100) {
  let batch = []
  let timeoutId = null
  
  const processBatch = () => {
    if (batch.length > 0) {
      const currentBatch = [...batch]
      batch = []
      processor(currentBatch)
    }
    
    if (timeoutId) {
      clearTimeout(timeoutId)
      timeoutId = null
    }
  }
  
  return {
    add: function(item) {
      batch.push(item)
      
      // Process immediately if batch is full
      if (batch.length >= maxBatchSize) {
        processBatch()
      } else if (!timeoutId) {
        // Set timeout to process batch
        timeoutId = setTimeout(processBatch, maxWaitTime)
      }
    },
    
    flush: function() {
      processBatch()
    },
    
    size: function() {
      return batch.length
    },
    
    clear: function() {
      batch = []
      if (timeoutId) {
        clearTimeout(timeoutId)
        timeoutId = null
      }
    }
  }
}

/**
 * Adaptive debounce - adjusts delay based on call frequency
 * @param {Function} func - Function to debounce
 * @param {number} baseDelay - Base delay in milliseconds
 * @param {Object} [options] - Adaptive options
 * @returns {Function} Adaptively debounced function
 */
export function adaptiveDebounce(func, baseDelay = 300, options = {}) {
  const {
    minDelay = 50,
    maxDelay = 2000,
    adjustmentFactor = 0.1,
    windowSize = 10
  } = options
  
  let callTimes = []
  let currentDelay = baseDelay
  let timeoutId = null
  let lastArgs = null
  let lastThis = null
  
  const adjustDelay = () => {
    if (callTimes.length < 2) return
    
    // Calculate average time between calls
    let totalInterval = 0
    for (let i = 1; i < callTimes.length; i++) {
      totalInterval += callTimes[i] - callTimes[i - 1]
    }
    const avgInterval = totalInterval / (callTimes.length - 1)
    
    // Adjust delay based on frequency
    if (avgInterval < baseDelay) {
      // High frequency - increase delay
      currentDelay = Math.min(maxDelay, currentDelay * (1 + adjustmentFactor))
    } else {
      // Low frequency - decrease delay
      currentDelay = Math.max(minDelay, currentDelay * (1 - adjustmentFactor))
    }
  }
  
  const executeFunc = () => {
    if (lastArgs) {
      const result = func.apply(lastThis, lastArgs)
      lastArgs = null
      lastThis = null
      timeoutId = null
      return result
    }
  }
  
  return function(...args) {
    const now = Date.now()
    
    // Track call times
    callTimes.push(now)
    if (callTimes.length > windowSize) {
      callTimes.shift()
    }
    
    // Adjust delay based on recent call frequency
    adjustDelay()
    
    // Clear existing timeout
    if (timeoutId) {
      clearTimeout(timeoutId)
    }
    
    // Store args and set new timeout
    lastArgs = args
    lastThis = this
    timeoutId = setTimeout(executeFunc, currentDelay)
  }
}
