/**
 * Sync Queue Management for Offline-First System
 * 
 * This module manages the queue of requests that need to be synchronized with the server.
 * It handles queuing, processing, retry logic, and conflict resolution.
 * 
 * @author Education Management System
 * @version 1.0.0
 */

import { ref, computed } from 'vue';
import { db } from './dexie.js';
import axios from 'axios';

/**
 * Sync queue status
 */
export const syncStatus = ref('idle'); // 'idle', 'syncing', 'error'
export const syncProgress = ref({ current: 0, total: 0 });
export const lastSyncTime = ref(null);
export const syncErrors = ref([]);

/**
 * Sync queue computed properties
 */
export const isSyncing = computed(() => syncStatus.value === 'syncing');
export const hasSyncErrors = computed(() => syncErrors.value.length > 0);
export const syncPercentage = computed(() => {
  if (syncProgress.value.total === 0) return 100;
  return Math.round((syncProgress.value.current / syncProgress.value.total) * 100);
});

/**
 * Sync priorities
 */
export const SYNC_PRIORITIES = {
  CRITICAL: 1,  // User data, quiz answers
  HIGH: 2,      // Lessons, assignments
  MEDIUM: 3,    // Messages, announcements
  LOW: 4        // Logs, analytics
};

/**
 * Sync statuses
 */
export const SYNC_STATUSES = {
  PENDING: 'pending',
  SYNCING: 'syncing',
  COMPLETED: 'completed',
  FAILED: 'failed',
  CONFLICT: 'conflict'
};

/**
 * Queue a request for later synchronization
 * @param {string} method - HTTP method
 * @param {string} url - Request URL
 * @param {Object} payload - Request data
 * @param {Object} options - Additional options
 * @returns {Promise<number>} Queue item ID
 */
export function queueRequest(method, url, payload = null, options = {}) {
  const queueItem = {
    method: method.toUpperCase(),
    url,
    payload,
    timestamp: new Date().toISOString(),
    retry_count: 0,
    status: SYNC_STATUSES.PENDING,
    priority: options.priority || SYNC_PRIORITIES.MEDIUM,
    resource_type: extractResourceType(url),
    options: options
  };

  return db.sync_queue.add(queueItem)
    .then(id => {
      console.log('📤 Request queued:', { id, method, url });
      return id;
    })
    .catch(error => {
      console.error('❌ Failed to queue request:', error);
      throw error;
    });
}

/**
 * Process all pending requests in the sync queue
 * @param {Object} options - Processing options
 * @returns {Promise<Object>} Sync results
 */
export function processQueue(options = {}) {
  if (isSyncing.value) {
    console.log('⏳ Sync already in progress');
    return Promise.resolve({ processed: 0, errors: 0 });
  }

  syncStatus.value = 'syncing';
  syncErrors.value = [];
  
  return db.sync_queue
    .where('status')
    .equals(SYNC_STATUSES.PENDING)
    .or('status')
    .equals(SYNC_STATUSES.FAILED)
    .sortBy('priority')
    .then(queueItems => {
      if (queueItems.length === 0) {
        syncStatus.value = 'idle';
        return { processed: 0, errors: 0 };
      }

      syncProgress.value = { current: 0, total: queueItems.length };
      
      return processQueueItems(queueItems, options);
    })
    .then(results => {
      syncStatus.value = results.errors > 0 ? 'error' : 'idle';
      lastSyncTime.value = new Date();
      
      console.log('✅ Sync completed:', results);
      return results;
    })
    .catch(error => {
      syncStatus.value = 'error';
      console.error('❌ Sync failed:', error);
      throw error;
    });
}

/**
 * Process individual queue items
 * @param {Array} queueItems - Items to process
 * @param {Object} options - Processing options
 * @returns {Promise<Object>} Processing results
 */
function processQueueItems(queueItems, options = {}) {
  const results = { processed: 0, errors: 0, conflicts: 0 };
  
  // Process items sequentially to avoid overwhelming the server
  return queueItems.reduce((promise, item) => {
    return promise.then(() => {
      return processQueueItem(item)
        .then(result => {
          if (result.success) {
            results.processed++;
          } else if (result.conflict) {
            results.conflicts++;
          } else {
            results.errors++;
          }
          
          syncProgress.value.current++;
        })
        .catch(error => {
          console.error('❌ Failed to process queue item:', item.id, error);
          results.errors++;
          syncProgress.value.current++;
        });
    });
  }, Promise.resolve())
    .then(() => results);
}

/**
 * Process a single queue item
 * @param {Object} item - Queue item to process
 * @returns {Promise<Object>} Processing result
 */
function processQueueItem(item) {
  // Mark as syncing
  return db.sync_queue.update(item.id, { 
    status: SYNC_STATUSES.SYNCING,
    retry_count: item.retry_count + 1
  })
    .then(() => {
      // Make the actual request
      return makeQueuedRequest(item);
    })
    .then(response => {
      // Success - remove from queue
      return db.sync_queue.delete(item.id)
        .then(() => ({ success: true, response }));
    })
    .catch(error => {
      return handleQueueItemError(item, error);
    });
}

/**
 * Make the actual HTTP request for a queued item
 * @param {Object} item - Queue item
 * @returns {Promise} Request promise
 */
function makeQueuedRequest(item) {
  const config = {
    method: item.method,
    url: item.url,
    timeout: 10000,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  };

  // Add CSRF token
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
  }

  // Add data for POST/PUT/PATCH requests
  if (item.payload && ['POST', 'PUT', 'PATCH'].includes(item.method)) {
    config.data = item.payload;
  }

  // Add query parameters for GET requests
  if (item.payload && item.method === 'GET') {
    config.params = item.payload;
  }

  return axios(config);
}

/**
 * Handle errors when processing queue items
 * @param {Object} item - Queue item that failed
 * @param {Error} error - Error that occurred
 * @returns {Promise<Object>} Error handling result
 */
function handleQueueItemError(item, error) {
  const maxRetries = 5;
  const isConflict = error.response?.status === 409;
  const isServerError = error.response?.status >= 500;
  const isNetworkError = !error.response;

  if (isConflict) {
    // Handle conflict - mark for manual resolution
    return db.sync_queue.update(item.id, {
      status: SYNC_STATUSES.CONFLICT,
      error_message: 'Data conflict detected'
    }).then(() => {
      syncErrors.value.push({
        id: item.id,
        type: 'conflict',
        message: 'Data conflict requires manual resolution',
        item
      });
      return { conflict: true };
    });
  }

  if (item.retry_count >= maxRetries) {
    // Max retries reached - mark as failed
    return db.sync_queue.update(item.id, {
      status: SYNC_STATUSES.FAILED,
      error_message: error.message
    }).then(() => {
      syncErrors.value.push({
        id: item.id,
        type: 'failed',
        message: `Failed after ${maxRetries} attempts: ${error.message}`,
        item
      });
      return { success: false };
    });
  }

  if (isServerError || isNetworkError) {
    // Temporary error - reset to pending for retry
    return db.sync_queue.update(item.id, {
      status: SYNC_STATUSES.PENDING,
      error_message: error.message
    }).then(() => {
      return { success: false };
    });
  }

  // Client error (4xx) - likely permanent, mark as failed
  return db.sync_queue.update(item.id, {
    status: SYNC_STATUSES.FAILED,
    error_message: error.message
  }).then(() => {
    syncErrors.value.push({
      id: item.id,
      type: 'client_error',
      message: `Client error: ${error.message}`,
      item
    });
    return { success: false };
  });
}

/**
 * Get sync queue statistics
 * @returns {Promise<Object>} Queue statistics
 */
export function getSyncQueueStats() {
  return Promise.all([
    db.sync_queue.where('status').equals(SYNC_STATUSES.PENDING).count(),
    db.sync_queue.where('status').equals(SYNC_STATUSES.FAILED).count(),
    db.sync_queue.where('status').equals(SYNC_STATUSES.CONFLICT).count(),
    db.sync_queue.count()
  ]).then(([pending, failed, conflicts, total]) => ({
    pending,
    failed,
    conflicts,
    total,
    needsAttention: failed + conflicts
  }));
}

/**
 * Clear completed and old failed items from queue
 * @param {number} maxAge - Maximum age in milliseconds (default: 7 days)
 * @returns {Promise<number>} Number of items cleared
 */
export function cleanupQueue(maxAge = 7 * 24 * 60 * 60 * 1000) {
  const cutoffDate = new Date(Date.now() - maxAge).toISOString();
  
  return db.sync_queue
    .where('timestamp')
    .below(cutoffDate)
    .and(item => item.status === SYNC_STATUSES.COMPLETED || item.status === SYNC_STATUSES.FAILED)
    .delete();
}

/**
 * Retry failed queue items
 * @param {Array<number>} itemIds - Specific item IDs to retry (optional)
 * @returns {Promise<number>} Number of items reset for retry
 */
export function retryFailedItems(itemIds = null) {
  let query = db.sync_queue.where('status').equals(SYNC_STATUSES.FAILED);
  
  if (itemIds) {
    query = query.and(item => itemIds.includes(item.id));
  }
  
  return query.modify({
    status: SYNC_STATUSES.PENDING,
    retry_count: 0,
    error_message: null
  });
}

/**
 * Clear all items from sync queue
 * @returns {Promise<void>}
 */
export function clearQueue() {
  return db.sync_queue.clear();
}

/**
 * Extract resource type from URL
 * @param {string} url - Request URL
 * @returns {string} Resource type
 */
function extractResourceType(url) {
  const match = url.match(/\/api\/([^\/\?]+)/);
  return match ? match[1] : 'unknown';
}

/**
 * Get pending queue items for a specific resource
 * @param {string} resourceType - Resource type to filter by
 * @returns {Promise<Array>} Pending items for the resource
 */
export function getPendingItemsForResource(resourceType) {
  return db.sync_queue
    .where('resource_type')
    .equals(resourceType)
    .and(item => item.status === SYNC_STATUSES.PENDING)
    .toArray();
}

export default {
  queueRequest,
  processQueue,
  getSyncQueueStats,
  cleanupQueue,
  retryFailedItems,
  clearQueue,
  getPendingItemsForResource,
  
  // Reactive properties
  syncStatus,
  syncProgress,
  lastSyncTime,
  syncErrors,
  isSyncing,
  hasSyncErrors,
  syncPercentage,
  
  // Constants
  SYNC_PRIORITIES,
  SYNC_STATUSES
};
