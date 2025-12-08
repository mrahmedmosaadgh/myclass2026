/**
 * Vue Composable for Offline-First Resource Management
 * 
 * This composable provides a unified interface for managing any resource type
 * with automatic offline support, sync queue management, and reactive state.
 * 
 * @author Education Management System
 * @version 1.0.0
 */

import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { db, dbUtils } from './dexie.js';
import { api, isOnline, OfflineError } from './api.js';
import { queueRequest, processQueue, SYNC_PRIORITIES } from './syncQueue.js';

/**
 * Main composable for offline resource management
 * @param {string} resourceName - Name of the resource (e.g., 'lessons', 'students')
 * @param {Object} options - Configuration options
 * @returns {Object} Resource management interface
 */
export function useOfflineResource(resourceName, options = {}) {
  // Validate resource name
  if (!dbUtils.resourceExists(resourceName)) {
    throw new Error(`Resource '${resourceName}' does not exist in database schema`);
  }

  // Configuration with defaults
  const config = {
    syncPriority: SYNC_PRIORITIES.MEDIUM,
    cacheDuration: 3600000, // 1 hour
    maxCacheSize: 1000,
    offlineCapabilities: ['read', 'create', 'update', 'delete'],
    autoSync: true,
    optimisticUpdates: true,
    ...options
  };

  // Reactive state
  const data = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const lastLoadTime = ref(null);
  const syncInProgress = ref(false);

  // Computed properties
  const isEmpty = computed(() => data.value.length === 0);
  const hasData = computed(() => data.value.length > 0);
  const isStale = computed(() => {
    if (!lastLoadTime.value) return true;
    return Date.now() - lastLoadTime.value.getTime() > config.cacheDuration;
  });

  // Sync status for this resource
  const syncStatus = computed(() => {
    if (syncInProgress.value) return 'syncing';
    if (error.value) return 'error';
    if (isStale.value) return 'stale';
    return 'synced';
  });

  const hasUnsyncedChanges = computed(() => {
    return db.getDirtyRecords(resourceName)
      .then(records => records.length > 0)
      .catch(() => false);
  });

  /**
   * Load all items for the resource
   * @param {Object} options - Loading options
   * @returns {Promise<Array>} Array of items
   */
  function loadAll(options = {}) {
    const { forceRefresh = false, includeDeleted = false } = options;
    
    loading.value = true;
    error.value = null;

    // If online and (no cache or force refresh), try server first
    if (isOnline.value && (forceRefresh || !hasData.value || isStale.value)) {
      return loadFromServer()
        .then(serverData => {
          return updateLocalCache(serverData)
            .then(() => {
              data.value = serverData;
              lastLoadTime.value = new Date();
              return serverData;
            });
        })
        .catch(serverError => {
          console.warn('Server load failed, falling back to cache:', serverError);
          return loadFromCache(includeDeleted);
        })
        .finally(() => {
          loading.value = false;
        });
    } else {
      // Load from cache
      return loadFromCache(includeDeleted)
        .finally(() => {
          loading.value = false;
        });
    }
  }

  /**
   * Load a specific item by ID
   * @param {number|string} id - Item ID
   * @param {Object} options - Loading options
   * @returns {Promise<Object>} Item object
   */
  function loadById(id, options = {}) {
    const { forceRefresh = false } = options;
    
    loading.value = true;
    error.value = null;

    // Try cache first
    return db[resourceName].get(id)
      .then(cachedItem => {
        if (cachedItem && !forceRefresh) {
          loading.value = false;
          return cachedItem;
        }

        // If online, try server
        if (isOnline.value) {
          return api.get(`/${resourceName.replace(/_/g, '-')}/${id}`)
            .then(serverItem => {
              // Update cache
              return db[resourceName].put(serverItem)
                .then(() => serverItem);
            })
            .catch(serverError => {
              if (cachedItem) {
                console.warn('Server load failed, using cached version:', serverError);
                return cachedItem;
              }
              throw serverError;
            });
        } else if (cachedItem) {
          return cachedItem;
        } else {
          throw new OfflineError('Item not available offline', { id });
        }
      })
      .finally(() => {
        loading.value = false;
      });
  }

  /**
   * Create a new item
   * @param {Object} itemData - Item data
   * @param {Object} options - Creation options
   * @returns {Promise<Object>} Created item
   */
  function create(itemData, options = {}) {
    const { optimistic = config.optimisticUpdates } = options;
    
    if (!config.offlineCapabilities.includes('create')) {
      throw new Error(`Create operation not allowed offline for ${resourceName}`);
    }

    loading.value = true;
    error.value = null;

    // Generate temporary ID for optimistic updates
    const tempId = `temp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    const itemWithTempId = { ...itemData, id: tempId };

    // Optimistic update - add to local data immediately
    if (optimistic) {
      data.value.unshift(itemWithTempId);
    }

    // Save to local database
    return db[resourceName].add(itemWithTempId)
      .then(localId => {
        if (isOnline.value) {
          // Try to sync immediately
          return api.post(`/${resourceName.replace(/_/g, '-')}`, itemData)
            .then(serverItem => {
              // Replace temp item with server item
              return db[resourceName].update(localId, {
                ...serverItem,
                is_dirty: false,
                synced_at: new Date().toISOString()
              }).then(() => {
                if (optimistic) {
                  const index = data.value.findIndex(item => item.id === tempId);
                  if (index !== -1) {
                    data.value[index] = serverItem;
                  }
                }
                return serverItem;
              });
            })
            .catch(serverError => {
              console.warn('Server create failed, queued for sync:', serverError);
              if (serverError instanceof OfflineError) {
                // Already queued
                return itemWithTempId;
              }
              // Queue for later sync
              return queueRequest('POST', `/${resourceName.replace(/_/g, '-')}`, itemData, {
                priority: config.syncPriority
              }).then(() => itemWithTempId);
            });
        } else {
          // Offline - already saved locally and marked as dirty
          return itemWithTempId;
        }
      })
      .catch(localError => {
        // Remove optimistic update on error
        if (optimistic) {
          const index = data.value.findIndex(item => item.id === tempId);
          if (index !== -1) {
            data.value.splice(index, 1);
          }
        }
        error.value = localError;
        throw localError;
      })
      .finally(() => {
        loading.value = false;
      });
  }

  /**
   * Update an existing item
   * @param {number|string} id - Item ID
   * @param {Object} updateData - Updated data
   * @param {Object} options - Update options
   * @returns {Promise<Object>} Updated item
   */
  function update(id, updateData, options = {}) {
    const { optimistic = config.optimisticUpdates, merge = true } = options;
    
    if (!config.offlineCapabilities.includes('update')) {
      throw new Error(`Update operation not allowed offline for ${resourceName}`);
    }

    loading.value = true;
    error.value = null;

    // Get current item
    return db[resourceName].get(id)
      .then(currentItem => {
        if (!currentItem) {
          throw new Error(`Item with ID ${id} not found`);
        }

        const updatedItem = merge ? { ...currentItem, ...updateData } : updateData;
        updatedItem.id = id; // Ensure ID is preserved

        // Optimistic update
        if (optimistic) {
          const index = data.value.findIndex(item => item.id === id);
          if (index !== -1) {
            data.value[index] = updatedItem;
          }
        }

        // Update local database
        return db[resourceName].put(updatedItem)
          .then(() => {
            if (isOnline.value) {
              // Try to sync immediately
              return api.put(`/${resourceName.replace(/_/g, '-')}/${id}`, updateData)
                .then(serverItem => {
                  // Update with server response
                  return db[resourceName].update(id, {
                    ...serverItem,
                    is_dirty: false,
                    synced_at: new Date().toISOString()
                  }).then(() => {
                    if (optimistic) {
                      const index = data.value.findIndex(item => item.id === id);
                      if (index !== -1) {
                        data.value[index] = serverItem;
                      }
                    }
                    return serverItem;
                  });
                })
                .catch(serverError => {
                  console.warn('Server update failed, queued for sync:', serverError);
                  if (!(serverError instanceof OfflineError)) {
                    // Queue for later sync
                    queueRequest('PUT', `/${resourceName.replace(/_/g, '-')}/${id}`, updateData, {
                      priority: config.syncPriority
                    });
                  }
                  return updatedItem;
                });
            } else {
              // Offline - already saved locally and marked as dirty
              return updatedItem;
            }
          });
      })
      .catch(updateError => {
        error.value = updateError;
        throw updateError;
      })
      .finally(() => {
        loading.value = false;
      });
  }

  /**
   * Delete an item
   * @param {number|string} id - Item ID
   * @param {Object} options - Deletion options
   * @returns {Promise<boolean>} Success status
   */
  function deleteItem(id, options = {}) {
    const { soft = true, optimistic = config.optimisticUpdates } = options;
    
    if (!config.offlineCapabilities.includes('delete')) {
      throw new Error(`Delete operation not allowed offline for ${resourceName}`);
    }

    loading.value = true;
    error.value = null;

    // Optimistic update - remove from local data
    let removedItem = null;
    let removedIndex = -1;
    if (optimistic) {
      removedIndex = data.value.findIndex(item => item.id === id);
      if (removedIndex !== -1) {
        removedItem = data.value.splice(removedIndex, 1)[0];
      }
    }

    const deletePromise = soft 
      ? db[resourceName].update(id, { deleted_at: new Date().toISOString(), is_dirty: true })
      : db[resourceName].delete(id);

    return deletePromise
      .then(() => {
        if (isOnline.value) {
          // Try to sync immediately
          return api.delete(`/${resourceName.replace(/_/g, '-')}/${id}`)
            .then(() => {
              if (soft) {
                // Mark as synced
                return db[resourceName].update(id, {
                  is_dirty: false,
                  synced_at: new Date().toISOString()
                });
              }
              return true;
            })
            .catch(serverError => {
              console.warn('Server delete failed, queued for sync:', serverError);
              if (!(serverError instanceof OfflineError)) {
                // Queue for later sync
                queueRequest('DELETE', `/${resourceName.replace(/_/g, '-')}/${id}`, null, {
                  priority: config.syncPriority
                });
              }
              return true;
            });
        } else {
          // Offline - already handled locally
          return true;
        }
      })
      .catch(deleteError => {
        // Restore optimistic update on error
        if (optimistic && removedItem && removedIndex !== -1) {
          data.value.splice(removedIndex, 0, removedItem);
        }
        error.value = deleteError;
        throw deleteError;
      })
      .finally(() => {
        loading.value = false;
      });
  }

  /**
   * Manually trigger sync for this resource
   * @returns {Promise<Object>} Sync results
   */
  function sync() {
    syncInProgress.value = true;
    return processQueue()
      .then(results => {
        if (results.processed > 0) {
          // Reload data after successful sync
          return loadAll({ forceRefresh: true });
        }
        return results;
      })
      .finally(() => {
        syncInProgress.value = false;
      });
  }

  /**
   * Clear local cache for this resource
   * @returns {Promise<void>}
   */
  function clearCache() {
    return db.clearResource(resourceName)
      .then(() => {
        data.value = [];
        lastLoadTime.value = null;
      });
  }

  // Helper functions
  function loadFromServer() {
    return api.get(`/${resourceName.replace(/_/g, '-')}`);
  }

  function loadFromCache(includeDeleted = false) {
    let query = db[resourceName].toArray();
    
    if (!includeDeleted) {
      query = db[resourceName].where('deleted_at').equals(undefined).toArray();
    }
    
    return query.then(cachedData => {
      data.value = cachedData;
      lastLoadTime.value = new Date();
      return cachedData;
    });
  }

  function updateLocalCache(serverData) {
    // Clear and repopulate cache
    return db[resourceName].clear()
      .then(() => {
        if (serverData.length > 0) {
          return db[resourceName].bulkAdd(
            serverData.map(item => ({
              ...item,
              is_dirty: false,
              synced_at: new Date().toISOString()
            }))
          );
        }
      });
  }

  // Auto-sync when coming back online
  if (config.autoSync) {
    watch(isOnline, (online) => {
      if (online && !syncInProgress.value) {
        sync().catch(error => {
          console.warn('Auto-sync failed:', error);
        });
      }
    });
  }

  // Return the composable interface
  return {
    // Reactive data
    data,
    loading,
    error,
    lastLoadTime,
    
    // Computed properties
    isEmpty,
    hasData,
    isStale,
    syncStatus,
    hasUnsyncedChanges,
    
    // Methods
    loadAll,
    loadById,
    create,
    update,
    delete: deleteItem,
    sync,
    clearCache,
    
    // Network status
    isOnline,
    
    // Configuration
    config
  };
}

export default useOfflineResource;
