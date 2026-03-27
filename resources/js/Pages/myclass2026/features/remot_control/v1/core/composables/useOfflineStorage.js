/**
 * Offline Storage Composable
 * Handles local storage for offline persistence with compression support
 */

import { ref, onUnmounted } from 'vue'

/**
 * Composable for managing offline storage with compression and error handling
 * @param {string} prefix - Storage key prefix for namespacing
 * @returns {Object} Storage API
 */
export function useOfflineStorage(prefix = 'rt_channel') {
  const isAvailable = ref(true)
  const lastError = ref(null)
  
  // Check if localStorage is available
  const checkStorage = () => {
    try {
      const testKey = '__storage_test__'
      localStorage.setItem(testKey, 'test')
      localStorage.removeItem(testKey)
      isAvailable.value = true
      return true
    } catch (error) {
      console.warn('LocalStorage not available:', error.message)
      isAvailable.value = false
      lastError.value = error
      return false
    }
  }

  // Simple compression for large data (optional)
  const compress = (data) => {
    try {
      // For now, just return as-is. Can implement LZ-string or similar later
      return JSON.stringify(data)
    } catch (error) {
      console.error('Compression error:', error)
      return null
    }
  }

  // Simple decompression
  const decompress = (compressed) => {
    try {
      return JSON.parse(compressed)
    } catch (error) {
      console.error('Decompression error:', error)
      return null
    }
  }

  /**
   * Save data to localStorage with namespacing
   * @param {string} key - Storage key (without prefix)
   * @param {any} data - Data to store
   * @returns {boolean} Success status
   */
  const save = (key, data) => {
    if (!isAvailable.value) {
      console.warn('Storage not available, cannot save:', key)
      return false
    }

    try {
      const fullKey = `${prefix}_${key}`
      const compressed = compress(data)
      
      if (compressed === null) {
        throw new Error('Failed to compress data')
      }

      localStorage.setItem(fullKey, compressed)
      return true
    } catch (error) {
      console.error('Failed to save to localStorage:', error)
      lastError.value = error
      
      // Handle quota exceeded error
      if (error.name === 'QuotaExceededError') {
        console.warn('Storage quota exceeded, attempting to clear old data')
        clearOld()
        // Retry once
        try {
          const fullKey = `${prefix}_${key}`
          const compressed = compress(data)
          if (compressed !== null) {
            localStorage.setItem(fullKey, compressed)
            return true
          }
        } catch (retryError) {
          console.error('Retry failed:', retryError)
        }
      }
      
      return false
    }
  }

  /**
   * Load data from localStorage
   * @param {string} key - Storage key (without prefix)
   * @param {any} defaultValue - Default value if key doesn't exist
   * @returns {any} Loaded data or default value
   */
  const load = (key, defaultValue = null) => {
    if (!isAvailable.value) {
      console.warn('Storage not available, returning default for:', key)
      return defaultValue
    }

    try {
      const fullKey = `${prefix}_${key}`
      const compressed = localStorage.getItem(fullKey)
      
      if (compressed === null) {
        return defaultValue
      }

      const data = decompress(compressed)
      return data !== null ? data : defaultValue
    } catch (error) {
      console.error('Failed to load from localStorage:', error)
      lastError.value = error
      return defaultValue
    }
  }

  /**
   * Remove specific key from storage
   * @param {string} key - Storage key (without prefix)
   * @returns {boolean} Success status
   */
  const remove = (key) => {
    if (!isAvailable.value) {
      return false
    }

    try {
      const fullKey = `${prefix}_${key}`
      localStorage.removeItem(fullKey)
      return true
    } catch (error) {
      console.error('Failed to remove from localStorage:', error)
      lastError.value = error
      return false
    }
  }

  /**
   * Clear all data with the prefix
   * @returns {boolean} Success status
   */
  const clear = () => {
    if (!isAvailable.value) {
      return false
    }

    try {
      const allKeys = getKeys()
      allKeys.forEach(key => {
        localStorage.removeItem(`${prefix}_${key}`)
      })
      return true
    } catch (error) {
      console.error('Failed to clear localStorage:', error)
      lastError.value = error
      return false
    }
  }

  /**
   * Get all keys with the prefix
   * @returns {string[]} Array of keys (without prefix)
   */
  const getKeys = () => {
    if (!isAvailable.value) {
      return []
    }

    try {
      const result = []
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i)
        if (key && key.startsWith(`${prefix}_`)) {
          result.push(key.replace(`${prefix}_`, ''))
        }
      }
      return result
    } catch (error) {
      console.error('Failed to get localStorage keys:', error)
      lastError.value = error
      return []
    }
  }

  /**
   * Get storage usage information
   * @returns {Object} Storage stats
   */
  const getStats = () => {
    if (!isAvailable.value) {
      return { used: 0, available: 0, count: 0 }
    }

    try {
      let used = 0
      let count = 0
      const keysList = keys()
      
      keysList.forEach(key => {
        const fullKey = `${prefix}_${key}`
        const value = localStorage.getItem(fullKey)
        if (value) {
          used += new Blob([value]).size
          count++
        }
      })

      // Estimate available space (rough approximation)
      const total = 5 * 1024 * 1024 // 5MB typical limit
      const available = total - used

      return { used, available, count }
    } catch (error) {
      console.error('Failed to get storage stats:', error)
      return { used: 0, available: 0, count: 0 }
    }
  }

  /**
   * Clear old data to free up space
   * Removes oldest items first
   */
  const clearOld = () => {
    if (!isAvailable.value) {
      return
    }

    try {
      const keysList = keys()
      if (keysList.length === 0) {
        return
      }

      // Remove oldest 25% of items
      const itemsToRemove = Math.max(1, Math.floor(keysList.length * 0.25))
      
      // Get timestamps from keys (assuming timestamp in key)
      const itemsWithTimestamp = keysList.map(key => {
        const parts = key.split('_')
        const timestamp = parts.length > 1 ? parseInt(parts[parts.length - 1]) : 0
        return { key, timestamp }
      }).sort((a, b) => a.timestamp - b.timestamp)

      for (let i = 0; i < itemsToRemove && i < itemsWithTimestamp.length; i++) {
        remove(itemsWithTimestamp[i].key)
      }

      console.log(`Cleared ${itemsToRemove} old items from storage`)
    } catch (error) {
      console.error('Failed to clear old storage:', error)
    }
  }

  /**
   * Export all data as JSON
   * @returns {Object} All stored data
   */
  const exportData = () => {
    const result = {}
    const keysList = keys()
    
    keysList.forEach(key => {
      result[key] = load(key)
    })
    
    return result
  }

  /**
   * Import data from JSON object
   * @param {Object} data - Data to import
   * @returns {boolean} Success status
   */
  const importData = (data) => {
    if (!data || typeof data !== 'object') {
      return false
    }

    try {
      Object.keys(data).forEach(key => {
        save(key, data[key])
      })
      return true
    } catch (error) {
      console.error('Failed to import data:', error)
      lastError.value = error
      return false
    }
  }

  // Initialize storage availability check
  checkStorage()

  return {
    // Reactive state
    isAvailable,
    lastError,
    
    // Methods
    save,
    load,
    remove,
    clear,
    getKeys,
    getStats,
    clearOld,
    exportData,
    importData,
    
    // Utility
    checkStorage
  }
}
