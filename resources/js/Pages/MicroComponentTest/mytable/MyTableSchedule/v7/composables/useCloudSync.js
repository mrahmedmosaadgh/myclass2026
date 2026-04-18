import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Cloud sync composable for authenticated Schedule App V7.
 * All data is stored server-side with user authentication.
 */
export function useCloudSync() {
  const syncStatus = ref('idle'); // idle | syncing | synced | error | offline
  const syncMessage = ref('');
  const isOnline = ref(navigator.onLine);
  const lastSyncTime = ref(null);
  const isSyncing = ref(false);
  const error = ref('');

  // Computed
  const canSync = computed(() => isOnline.value && !isSyncing.value);

  // Listen for online/offline
  if (typeof window !== 'undefined') {
    window.addEventListener('online', () => { isOnline.value = true; });
    window.addEventListener('offline', () => { isOnline.value = false; });
  }

  /**
   * Save data to authenticated server.
   * @param {Object} data - Full app state snapshot
   * @returns {Object} { success, error? }
   */
  const saveData = async (data) => {
    if (!canSync.value) {
      syncStatus.value = 'offline';
      syncMessage.value = 'Offline — saved locally';
      return { success: false, error: 'offline' };
    }

    isSyncing.value = true;
    syncStatus.value = 'syncing';
    syncMessage.value = 'Saving to cloud...';
    error.value = '';

    try {
      const payload = {
        schedule_data: data,
        last_modified: String(Date.now()),
        version: '7.0'
      };

      const response = await axios.post('/api/schedule-app-v7/save-data', payload, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = response.data;

      if (result.success) {
        lastSyncTime.value = Date.now();
        syncStatus.value = 'synced';
        syncMessage.value = 'Saved to cloud';
        return { success: true };
      }

      throw new Error(result.error || 'Save failed');
    } catch (e) {
      syncStatus.value = 'error';
      syncMessage.value = 'Save failed — data safe locally';
      error.value = e.message;
      return { success: false, error: e.message };
    } finally {
      isSyncing.value = false;
    }
  };

  /**
   * Load data from authenticated server.
   * @returns {Object} { success, data?, error? }
   */
  const loadData = async () => {
    if (!canSync.value) {
      return { success: false, error: 'offline' };
    }

    isSyncing.value = true;
    syncStatus.value = 'syncing';
    syncMessage.value = 'Loading from cloud...';
    error.value = '';

    try {
      const response = await axios.get('/api/schedule-app-v7/load-data', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = response.data;

      if (result.success) {
        lastSyncTime.value = Date.now();
        syncStatus.value = 'synced';
        syncMessage.value = 'Loaded from cloud';

        const raw = result.data;
        const serverScheduleData = raw?.schedule_data ?? raw?.data?.schedule_data ?? raw?.data ?? raw;
        const serverLastModified = Number(raw?.last_modified ?? raw?.data?.last_modified ?? 0);

        return {
          success: true,
          data: {
            ...serverScheduleData,
            lastModified: serverLastModified || Date.now()
          }
        };
      }

      throw new Error(result.error || 'Load failed');
    } catch (e) {
      if (e.response?.status === 404) {
        // No data found, that's okay for new users
        syncStatus.value = 'idle';
        syncMessage.value = 'No data found';
        return { success: false, error: 'no_data' };
      }

      syncStatus.value = 'error';
      syncMessage.value = 'Failed to load from cloud';
      error.value = e.message;
      return { success: false, error: e.message };
    } finally {
      isSyncing.value = false;
    }
  };

  /**
   * Backward-compatible wrapper used by the app store.
   * @param {number} localLastModified
   * @returns {Object} { success, source: 'server'|'local', data? }
   */
  const pullFromServer = async (localLastModified = 0) => {
    const result = await loadData();

    if (!result.success) {
      if (result.error === 'offline' || result.error === 'no_data') {
        return { success: true, source: 'local' };
      }
      return { success: false, error: result.error };
    }

    const serverData = result.data;
    const serverLastModified = Number(serverData?.lastModified || 0);

    if (serverLastModified > Number(localLastModified || 0)) {
      return { success: true, source: 'server', data: serverData };
    }

    return { success: true, source: 'local', data: serverData };
  };

  /**
   * Backward-compatible wrapper used by the app store.
   * @param {Object} snapshot
   * @returns {Object} { success, error? }
   */
  const pushToServer = async (snapshot) => {
    return saveData(snapshot);
  };

  /**
   * Backward-compatible wrapper used by the app store.
   * @param {Array} pending
   * @param {Function} markSynced
   */
  const processQueue = async (pending = [], markSynced = null) => {
    if (!Array.isArray(pending) || pending.length === 0) return;
    for (const item of pending) {
      try {
        if (item?.action === 'save-snapshot') {
          const res = await pushToServer(item.data);
          if (res?.success && typeof markSynced === 'function' && item?.id != null) {
            await markSynced(item.id);
          }
        }
      } catch (e) {
        // Keep item pending; next sync attempt will retry.
      }
    }
  };

  /**
   * Sync data with conflict resolution.
   * @param {Object} data - Current local data
   * @returns {Object} { success, action?, error? }
   */
  const syncData = async (data) => {
    if (!canSync.value) {
      return { success: false, error: 'offline' };
    }

    isSyncing.value = true;
    syncStatus.value = 'syncing';
    syncMessage.value = 'Syncing...';
    error.value = '';

    try {
      const dataHash = md5(JSON.stringify(data));
      
      const response = await axios.post('/api/schedule-app-v7/sync', {
        client_timestamp: new Date().toISOString(),
        data_hash: dataHash,
        schedule_data: data
      }, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = response.data;

      if (result.action === 'accept_client') {
        // Save client data to server
        await saveData(data);
        return { success: true, action: 'client_wins' };
      } else if (result.action === 'no_conflict') {
        syncStatus.value = 'synced';
        syncMessage.value = 'Already in sync';
        return { success: true, action: 'no_conflict' };
      } else if (result.action === 'conflict') {
        syncStatus.value = 'error';
        syncMessage.value = 'Conflict detected';
        error.value = result.message;
        return { success: false, action: 'conflict', serverData: result.server_data };
      }

      return { success: true };

    } catch (e) {
      syncStatus.value = 'error';
      syncMessage.value = 'Sync failed';
      error.value = e.message;
      return { success: false, error: e.message };
    } finally {
      isSyncing.value = false;
    }
  };

  /**
   * Get backup list from server.
   * @returns {Object} { success, backups?, error? }
   */
  const getBackups = async () => {
    try {
      const response = await axios.get('/api/schedule-app-v7/backups', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      return { success: true, backups: response.data.backups };
    } catch (e) {
      error.value = e.message;
      return { success: false, error: e.message };
    }
  };

  /**
   * Export data as JSON.
   * @returns {Object} { success, data?, error? }
   */
  const exportData = async () => {
    try {
      const response = await axios.get('/api/schedule-app-v7/export', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      return { success: true, data: response.data.export_data };
    } catch (e) {
      error.value = e.message;
      return { success: false, error: e.message };
    }
  };

  /**
   * Clear all user data from server.
   * @returns {Object} { success, error? }
   */
  const clearData = async () => {
    try {
      const response = await axios.delete('/api/schedule-app-v7/clear-data', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      // Reset local state
      lastSyncTime.value = null;
      syncStatus.value = 'idle';
      syncMessage.value = 'Data cleared';

      return { success: true };
    } catch (e) {
      error.value = e.message;
      return { success: false, error: e.message };
    }
  };

  // Simple MD5 implementation for data hashing
  const md5 = (string) => {
    let hash = 0;
    if (string.length === 0) return hash.toString();
    for (let i = 0; i < string.length; i++) {
      const char = string.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash; // Convert to 32-bit integer
    }
    return Math.abs(hash).toString(16);
  };

  return {
    // State
    syncStatus,
    syncMessage,
    isOnline,
    lastSyncTime,
    isSyncing,
    error,
    
    // Computed
    canSync,
    
    // Methods
    saveData,
    loadData,
    syncData,
    pullFromServer,
    pushToServer,
    processQueue,
    getBackups,
    exportData,
    clearData
  };
}
