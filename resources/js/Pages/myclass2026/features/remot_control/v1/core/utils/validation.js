/**
 * Validation Utilities
 * Generic validation for commands, state, and data in the real-time communication system
 */

import { isCommand, isChannelState, isChannelEvent } from '../types/channel.types.js'

/**
 * Validation result structure
 * @typedef {Object} ValidationResult
 * @property {boolean} isValid - Whether validation passed
 * @property {string[]} errors - Array of error messages
 * @property {any} sanitized - Sanitized data if applicable
 */

/**
 * Generic validator for commands
 * @param {any} command - Command object to validate
 * @param {Object} [schema] - Optional validation schema
 * @returns {ValidationResult} Validation result
 */
export const validateCommand = (command, schema = null) => {
  const errors = []
  let sanitized = command

  // Basic structure validation
  if (!command || typeof command !== 'object') {
    errors.push('Command must be an object')
    return { isValid: false, errors, sanitized: null }
  }

  // Required fields validation
  const requiredFields = ['commandId', 'channelId', 'type', 'metadata']
  requiredFields.forEach(field => {
    if (!command[field]) {
      errors.push(`Missing required field: ${field}`)
    } else if (typeof command[field] !== 'string') {
      errors.push(`Field ${field} must be a string`)
    }
  })

  // Validate commandId format (UUID-like)
  if (command.commandId && !isValidId(command.commandId)) {
    errors.push('Invalid commandId format')
  }

  // Validate metadata structure
  if (command.metadata && typeof command.metadata === 'object') {
    const metadataFields = ['timestamp', 'senderId']
    metadataFields.forEach(field => {
      if (!command.metadata[field]) {
        errors.push(`Missing metadata field: ${field}`)
      } else if (typeof command.metadata[field] !== 'string') {
        errors.push(`Metadata field ${field} must be a string`)
      }
    })

    // Validate timestamp format
    if (command.metadata.timestamp && !isValidTimestamp(command.metadata.timestamp)) {
      errors.push('Invalid timestamp format in metadata')
    }

    // Validate priority if present
    if (command.metadata.priority && !['normal', 'high', 'low'].includes(command.metadata.priority)) {
      errors.push('Invalid priority value, must be: normal, high, or low')
    }
  }

  // Apply custom schema validation if provided
  if (schema && typeof schema.validate === 'function') {
    const schemaResult = schema.validate(command)
    if (!schemaResult.isValid) {
      errors.push(...schemaResult.errors)
      sanitized = schemaResult.sanitized || sanitized
    }
  }

  // Sanitize dangerous content
  sanitized = sanitizeCommand(sanitized)

  return {
    isValid: errors.length === 0,
    errors,
    sanitized
  }
}

/**
 * Generic validator for channel state
 * @param {any} state - State object to validate
 * @param {Object} [schema] - Optional validation schema
 * @returns {ValidationResult} Validation result
 */
export const validateState = (state, schema = null) => {
  const errors = []
  let sanitized = state

  // Basic structure validation
  if (!state || typeof state !== 'object') {
    errors.push('State must be an object')
    return { isValid: false, errors, sanitized: null }
  }

  // Required fields validation
  const requiredFields = ['channelId', 'data', 'metadata']
  requiredFields.forEach(field => {
    if (state[field] === undefined) {
      errors.push(`Missing required field: ${field}`)
    }
  })

  // Validate channelId
  if (state.channelId && typeof state.channelId !== 'string') {
    errors.push('channelId must be a string')
  }

  // Validate metadata structure
  if (state.metadata && typeof state.metadata === 'object') {
    const metadataFields = ['lastUpdated', 'updatedBy', 'version']
    metadataFields.forEach(field => {
      if (state.metadata[field] === undefined) {
        errors.push(`Missing metadata field: ${field}`)
      }
    })

    // Validate timestamp format
    if (state.metadata.lastUpdated && !isValidTimestamp(state.metadata.lastUpdated)) {
      errors.push('Invalid lastUpdated format in metadata')
    }

    // Validate version type
    if (state.metadata.version !== undefined && typeof state.metadata.version !== 'number') {
      errors.push('metadata.version must be a number')
    }
  }

  // Apply custom schema validation if provided
  if (schema && typeof schema.validate === 'function') {
    const schemaResult = schema.validate(state)
    if (!schemaResult.isValid) {
      errors.push(...schemaResult.errors)
      sanitized = schemaResult.sanitized || sanitized
    }
  }

  // Sanitize dangerous content
  sanitized = sanitizeData(sanitized)

  return {
    isValid: errors.length === 0,
    errors,
    sanitized
  }
}

/**
 * Generic validator for events
 * @param {any} event - Event object to validate
 * @returns {ValidationResult} Validation result
 */
export const validateEvent = (event) => {
  const errors = []
  let sanitized = event

  // Basic structure validation
  if (!event || typeof event !== 'object') {
    errors.push('Event must be an object')
    return { isValid: false, errors, sanitized: null }
  }

  // Required fields validation
  const requiredFields = ['eventId', 'channelId', 'type', 'timestamp']
  requiredFields.forEach(field => {
    if (!event[field]) {
      errors.push(`Missing required field: ${field}`)
    } else if (typeof event[field] !== 'string') {
      errors.push(`Field ${field} must be a string`)
    }
  })

  // Validate eventId format
  if (event.eventId && !isValidId(event.eventId)) {
    errors.push('Invalid eventId format')
  }

  // Validate event type
  const validEventTypes = ['state_change', 'command_sent', 'command_received', 'connection', 'error', 'offline_sync']
  if (event.type && !validEventTypes.includes(event.type)) {
    errors.push(`Invalid event type, must be one of: ${validEventTypes.join(', ')}`)
  }

  // Validate timestamp format
  if (event.timestamp && !isValidTimestamp(event.timestamp)) {
    errors.push('Invalid timestamp format')
  }

  // Validate metadata structure
  if (event.metadata && typeof event.metadata === 'object') {
    if (event.metadata.source && !['local', 'remote'].includes(event.metadata.source)) {
      errors.push('Invalid metadata.source, must be: local or remote')
    }
  }

  // Sanitize dangerous content
  sanitized = sanitizeData(sanitized)

  return {
    isValid: errors.length === 0,
    errors,
    sanitized
  }
}

/**
 * Sanitize command data to prevent security issues
 * @param {any} command - Command to sanitize
 * @returns {any} Sanitized command
 */
export const sanitizeCommand = (command) => {
  if (!command || typeof command !== 'object') {
    return command
  }

  // Create a deep copy to avoid mutating original
  const sanitized = JSON.parse(JSON.stringify(command))

  // Remove dangerous fields
  const dangerousFields = ['__proto__', 'constructor', 'prototype']
  dangerousFields.forEach(field => {
    if (sanitized[field]) {
      delete sanitized[field]
    }
  })

  // Sanitize string fields to prevent XSS
  const sanitizeString = (str) => {
    if (typeof str !== 'string') return str
    
    // Remove potential script tags and dangerous content
    return str
      .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
      .replace(/javascript:/gi, '')
      .replace(/on\w+\s*=/gi, '')
      .trim()
  }

  // Recursively sanitize all string values
  const sanitizeObject = (obj) => {
    if (typeof obj === 'string') {
      return sanitizeString(obj)
    } else if (Array.isArray(obj)) {
      return obj.map(sanitizeObject)
    } else if (obj && typeof obj === 'object') {
      const result = {}
      Object.keys(obj).forEach(key => {
        result[key] = sanitizeObject(obj[key])
      })
      return result
    }
    return obj
  }

  // Sanitize payload and metadata
  if (sanitized.payload) {
    sanitized.payload = sanitizeObject(sanitized.payload)
  }
  if (sanitized.metadata) {
    sanitized.metadata = sanitizeObject(sanitized.metadata)
  }

  return sanitized
}

/**
 * Sanitize general data
 * @param {any} data - Data to sanitize
 * @returns {any} Sanitized data
 */
export const sanitizeData = (data) => {
  if (!data || typeof data !== 'object') {
    return data
  }

  // Create a deep copy
  const sanitized = JSON.parse(JSON.stringify(data))

  // Remove dangerous fields
  const dangerousFields = ['__proto__', 'constructor', 'prototype']
  dangerousFields.forEach(field => {
    if (sanitized[field]) {
      delete sanitized[field]
    }
  })

  // Sanitize string values
  const sanitizeString = (str) => {
    if (typeof str !== 'string') return str
    
    return str
      .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
      .replace(/javascript:/gi, '')
      .replace(/on\w+\s*=/gi, '')
      .trim()
  }

  const sanitizeObject = (obj) => {
    if (typeof obj === 'string') {
      return sanitizeString(obj)
    } else if (Array.isArray(obj)) {
      return obj.map(sanitizeObject)
    } else if (obj && typeof obj === 'object') {
      const result = {}
      Object.keys(obj).forEach(key => {
        result[key] = sanitizeObject(obj[key])
      })
      return result
    }
    return obj
  }

  return sanitizeObject(sanitized)
}

/**
 * Validate ID format (UUID-like)
 * @param {string} id - ID to validate
 * @returns {boolean} Whether ID is valid
 */
export const isValidId = (id) => {
  if (typeof id !== 'string') return false
  
  // Basic UUID format validation (allows for custom UUIDs)
  const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i
  const customIdRegex = /^[a-zA-Z0-9_-]+$/
  
  return uuidRegex.test(id) || customIdRegex.test(id)
}

/**
 * Validate timestamp format (ISO 8601)
 * @param {string} timestamp - Timestamp to validate
 * @returns {boolean} Whether timestamp is valid
 */
export const isValidTimestamp = (timestamp) => {
  if (typeof timestamp !== 'string') return false
  
  const date = new Date(timestamp)
  return !isNaN(date.getTime()) && timestamp === date.toISOString()
}

/**
 * Create a custom validator function
 * @param {Object} schema - Validation schema
 * @returns {Function} Validator function
 */
export const createValidator = (schema) => {
  return (data) => {
    const errors = []
    let sanitized = data

    // Apply schema rules
    if (schema.required) {
      schema.required.forEach(field => {
        if (!data[field]) {
          errors.push(`Required field missing: ${field}`)
        }
      })
    }

    if (schema.types) {
      Object.keys(schema.types).forEach(field => {
        if (data[field] !== undefined && typeof data[field] !== schema.types[field]) {
          errors.push(`Field ${field} must be of type ${schema.types[field]}`)
        }
      })
    }

    if (schema.enum) {
      Object.keys(schema.enum).forEach(field => {
        if (data[field] !== undefined && !schema.enum[field].includes(data[field])) {
          errors.push(`Field ${field} must be one of: ${schema.enum[field].join(', ')}`)
        }
      })
    }

    if (schema.custom && typeof schema.custom === 'function') {
      const customResult = schema.custom(data)
      if (customResult.errors) {
        errors.push(...customResult.errors)
      }
      if (customResult.sanitized) {
        sanitized = customResult.sanitized
      }
    }

    return {
      isValid: errors.length === 0,
      errors,
      sanitized
    }
  }
}

/**
 * Batch validation for multiple items
 * @param {any[]} items - Items to validate
 * @param {Function} validator - Validator function
 * @returns {ValidationResult[]} Array of validation results
 */
export const validateBatch = (items, validator) => {
  if (!Array.isArray(items)) {
    return [validateCommand(items)]
  }

  return items.map(item => validator(item))
}

/**
 * Get validation statistics
 * @param {ValidationResult[]} results - Array of validation results
 * @returns {Object} Statistics
 */
export const getValidationStats = (results) => {
  const total = results.length
  const valid = results.filter(r => r.isValid).length
  const invalid = total - valid
  const totalErrors = results.reduce((sum, r) => sum + r.errors.length, 0)

  return {
    total,
    valid,
    invalid,
    errorRate: total > 0 ? (invalid / total) * 100 : 0,
    totalErrors,
    averageErrorsPerItem: total > 0 ? totalErrors / total : 0
  }
}
