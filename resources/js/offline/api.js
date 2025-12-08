/**
 * Centralized API Management for Offline-First System
 *
 * This module provides a unified API interface that automatically handles online/offline states,
 * queues requests when offline, and manages network status detection.
 *
 * @author Education Management System
 * @version 1.0.0
 */

import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { queueRequest, processQueue } from './syncQueue.js';
import { useNetworkStore } from '@/Stores/networkStore.js';

// Use the global network store for consistency
const networkStore = useNetworkStore();

/**
 * Network status management - now using global store
 */
export const isOnline = computed(() => networkStore.isOnline);
export const lastOnlineTime = computed(() => networkStore.lastOnlineTime);
export const connectionQuality = ref('good'); // 'good', 'slow', 'poor'

/**
 * Network status computed properties
 */
export const networkStatus = computed(() => ({
  online: isOnline.value,
  lastOnline: lastOnlineTime.value,
  quality: connectionQuality.value,
  statusText: isOnline.value ? 'Online' : 'Offline'
}));

/**
 * Initialize network status monitoring
 */
function initNetworkMonitoring() {
  // Browser online/offline events
  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);

  // Periodic connectivity check
  setInterval(checkConnectivity, 30000); // Check every 30 seconds

  // Initial connectivity check
  checkConnectivity();
}

/**
 * Handle online event
 */
function handleOnline() {
  console.log('🌐 Network: Back online');
  networkStore.updateOnlineStatus(true);
  connectionQuality.value = 'good';

  // Process queued requests
  processQueue()
    .then(result => {
      if (result.processed > 0) {
        console.log(`✅ Processed ${result.processed} queued requests`);
      }
    })
    .catch(error => {
      console.error('❌ Error processing queue:', error);
    });
}

/**
 * Handle offline event
 */
function handleOffline() {
  console.log('📴 Network: Gone offline');
  networkStore.updateOnlineStatus(false);
  connectionQuality.value = 'poor';
}

/**
 * Check actual connectivity by making a test request
 */
function checkConnectivity() {
  if (!navigator.onLine) {
    networkStore.updateOnlineStatus(false);
    return;
  }

  const startTime = Date.now();

  // Test with a small request to your Laravel API
  fetch('/api/health-check', {
    method: 'HEAD',
    cache: 'no-cache',
    timeout: 5000
  })
    .then(response => {
      const responseTime = Date.now() - startTime;

      if (response.ok) {
        networkStore.updateOnlineStatus(true);

        // Determine connection quality based on response time
        if (responseTime < 500) {
          connectionQuality.value = 'good';
        } else if (responseTime < 2000) {
          connectionQuality.value = 'slow';
        } else {
          connectionQuality.value = 'poor';
        }
      } else {
        networkStore.updateOnlineStatus(false);
      }
    })
    .catch(() => {
      networkStore.updateOnlineStatus(false);
      connectionQuality.value = 'poor';
    });
}

/**
 * API Configuration
 */
const apiConfig = {
  baseURL: '/api',
  timeout: 10000,
  retryAttempts: 3,
  retryDelay: 1000
};

/**
 * Create axios instance with default configuration
 */
const apiClient = axios.create({
  baseURL: apiConfig.baseURL,
  timeout: apiConfig.timeout,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
});

/**
 * Add CSRF token to requests
 */
apiClient.interceptors.request.use(config => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
  }
  return config;
});

/**
 * Response interceptor for error handling
 */
apiClient.interceptors.response.use(
  response => response,
  error => {
    // Network error or timeout
    if (!error.response) {
      networkStore.updateOnlineStatus(false);
    }
    return Promise.reject(error);
  }
);

/**
 * Main API request function with offline support
 * @param {string} method - HTTP method (GET, POST, PUT, DELETE)
 * @param {string} url - API endpoint URL
 * @param {Object} data - Request data
 * @param {Object} options - Additional options
 * @returns {Promise} Promise resolving to response data
 */
export function apiRequest(method, url, data = null, options = {}) {
  const requestConfig = {
    method: method.toUpperCase(),
    url,
    data,
    ...options
  };

  // If online, try to make the request
  if (isOnline.value) {
    return makeRequest(requestConfig)
      .then(response => {
        // Update online status on successful request
        if (!isOnline.value) {
          handleOnline();
        }
        return response.data;
      })
      .catch(error => {
        // If request fails due to network, queue it
        if (isNetworkError(error)) {
          console.log('📤 Network error, queuing request:', method, url);
          return queueRequest(method, url, data, options)
            .then(() => {
              throw new OfflineError('Request queued for later sync', { method, url, data });
            });
        }
        throw error;
      });
  } else {
    // If offline, queue the request immediately
    console.log('📴 Offline, queuing request:', method, url);
    return queueRequest(method, url, data, options)
      .then(() => {
        throw new OfflineError('Request queued for later sync', { method, url, data });
      });
  }
}

/**
 * Make HTTP request with retry logic
 * @param {Object} config - Request configuration
 * @param {number} attempt - Current attempt number
 * @returns {Promise} Promise resolving to response
 */
function makeRequest(config, attempt = 1) {
  return apiClient(config)
    .catch(error => {
      if (attempt < apiConfig.retryAttempts && isRetryableError(error)) {
        console.log(`🔄 Retrying request (attempt ${attempt + 1}):`, config.url);
        return new Promise(resolve => {
          setTimeout(() => {
            resolve(makeRequest(config, attempt + 1));
          }, apiConfig.retryDelay * attempt);
        });
      }
      throw error;
    });
}

/**
 * Check if error is a network error
 * @param {Error} error - Error object
 * @returns {boolean} True if network error
 */
function isNetworkError(error) {
  return !error.response ||
         error.code === 'NETWORK_ERROR' ||
         error.code === 'ECONNABORTED' ||
         error.message.includes('Network Error');
}

/**
 * Check if error is retryable
 * @param {Error} error - Error object
 * @returns {boolean} True if retryable
 */
function isRetryableError(error) {
  if (isNetworkError(error)) return true;
  if (error.response) {
    const status = error.response.status;
    return status >= 500 || status === 408 || status === 429;
  }
  return false;
}

/**
 * Custom error class for offline scenarios
 */
export class OfflineError extends Error {
  constructor(message, requestInfo) {
    super(message);
    this.name = 'OfflineError';
    this.requestInfo = requestInfo;
    this.isOfflineError = true;
  }
}

/**
 * Convenience methods for common HTTP operations
 */
export const api = {
  /**
   * GET request
   * @param {string} url - Endpoint URL
   * @param {Object} params - Query parameters
   * @param {Object} options - Additional options
   * @returns {Promise} Promise resolving to response data
   */
  get(url, params = {}, options = {}) {
    const config = { params, ...options };
    return apiRequest('GET', url, null, config);
  },

  /**
   * POST request
   * @param {string} url - Endpoint URL
   * @param {Object} data - Request data
   * @param {Object} options - Additional options
   * @returns {Promise} Promise resolving to response data
   */
  post(url, data = {}, options = {}) {
    return apiRequest('POST', url, data, options);
  },

  /**
   * PUT request
   * @param {string} url - Endpoint URL
   * @param {Object} data - Request data
   * @param {Object} options - Additional options
   * @returns {Promise} Promise resolving to response data
   */
  put(url, data = {}, options = {}) {
    return apiRequest('PUT', url, data, options);
  },

  /**
   * DELETE request
   * @param {string} url - Endpoint URL
   * @param {Object} options - Additional options
   * @returns {Promise} Promise resolving to response data
   */
  delete(url, options = {}) {
    return apiRequest('DELETE', url, null, options);
  },

  /**
   * PATCH request
   * @param {string} url - Endpoint URL
   * @param {Object} data - Request data
   * @param {Object} options - Additional options
   * @returns {Promise} Promise resolving to response data
   */
  patch(url, data = {}, options = {}) {
    return apiRequest('PATCH', url, data, options);
  }
};

/**
 * Resource-specific API helpers
 */
export function createResourceAPI(resourceName) {
  const baseUrl = `/${resourceName.replace(/_/g, '-')}`;

  return {
    /**
     * Get all resources
     * @param {Object} params - Query parameters
     * @returns {Promise} Promise resolving to resources array
     */
    getAll(params = {}) {
      return api.get(baseUrl, params);
    },

    /**
     * Get resource by ID
     * @param {number|string} id - Resource ID
     * @returns {Promise} Promise resolving to resource object
     */
    getById(id) {
      return api.get(`${baseUrl}/${id}`);
    },

    /**
     * Create new resource
     * @param {Object} data - Resource data
     * @returns {Promise} Promise resolving to created resource
     */
    create(data) {
      return api.post(baseUrl, data);
    },

    /**
     * Update existing resource
     * @param {number|string} id - Resource ID
     * @param {Object} data - Updated data
     * @returns {Promise} Promise resolving to updated resource
     */
    update(id, data) {
      return api.put(`${baseUrl}/${id}`, data);
    },

    /**
     * Delete resource
     * @param {number|string} id - Resource ID
     * @returns {Promise} Promise resolving to deletion result
     */
    delete(id) {
      return api.delete(`${baseUrl}/${id}`);
    }
  };
}

// Initialize network monitoring when module loads
initNetworkMonitoring();

// Watch for online status changes and log them
watch(isOnline, (online) => {
  console.log(`🌐 Network status changed: ${online ? 'Online' : 'Offline'}`);
});

export default api;
