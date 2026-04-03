import { ref } from 'vue';

/**
 * Cloud sync composable with local-wins conflict resolution.
 * All data lives in IndexedDB first; cloud is a secondary persistence layer.
 */
export function useCloudSync() {
  const syncStatus = ref('idle'); // idle | syncing | synced | error | offline
  const syncMessage = ref('');
  const isOnline = ref(navigator.onLine);
  const lastSyncTime = ref(null);
  const isSyncing = ref(false);

  // Listen for online/offline
  if (typeof window !== 'undefined') {
    window.addEventListener('online', () => { isOnline.value = true; });
    window.addEventListener('offline', () => { isOnline.value = false; });
  }

  const getUserId = () => {
    // IndexedDB stores user ID via appSettings, but for API headers we need it synchronously.
    // Fall back to a generated ID stored in a closure.
    if (!getUserId._cached) {
      try {
        // Try to read from IDB via a sync cookie (set during app init)
        getUserId._cached = document.cookie
          .split('; ')
          .find(c => c.startsWith('sv5uid='))
          ?.split('=')[1] || null;
      } catch { /* ignore */ }

      if (!getUserId._cached) {
        getUserId._cached = 'user-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        document.cookie = `sv5uid=${getUserId._cached};path=/;max-age=31536000;SameSite=Lax`;
      }
    }
    return getUserId._cached;
  };

  /**
   * Push local data to server.
   * @param {Object} data - Full app state snapshot
   * @returns {Object} { success, error? }
   */
  const pushToServer = async (data) => {
    if (!isOnline.value) {
      syncStatus.value = 'offline';
      syncMessage.value = 'Offline — saved locally';
      return { success: false, error: 'offline' };
    }

    if (isSyncing.value) return { success: false, error: 'already syncing' };
    isSyncing.value = true;
    syncStatus.value = 'syncing';
    syncMessage.value = 'Saving to cloud...';

    try {
      const response = await fetch('/api/v5/save-data', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-User-ID': getUserId()
        },
        body: JSON.stringify({
          ...data,
          timestamp: Date.now()
        })
      });

      const result = await response.json();

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
      return { success: false, error: e.message };
    } finally {
      isSyncing.value = false;
    }
  };

  /**
   * Pull data from server, applying local-wins rule.
   * @param {number} localLastModified - Timestamp of most recent local change
   * @returns {Object} { success, data?, source: 'local'|'server' }
   */
  const pullFromServer = async (localLastModified = 0) => {
    if (!isOnline.value) {
      return { success: false, source: 'local', error: 'offline' };
    }

    try {
      const response = await fetch('/api/v5/load-data', {
        headers: { 'X-User-ID': getUserId() }
      });

      const result = await response.json();

      if (!result.success || !result.data) {
        return { success: false, source: 'local', error: 'no server data' };
      }

      const serverTimestamp = result.data.timestamp || 0;

      // Local-wins: if local is newer, keep local and push it to server
      if (localLastModified > serverTimestamp) {
        return { success: true, source: 'local', serverData: result.data };
      }

      // Server is newer — accept server data
      return { success: true, source: 'server', data: result.data };
    } catch (e) {
      return { success: false, source: 'local', error: e.message };
    }
  };

  /**
   * Process queued sync items (items that failed to sync earlier).
   * @param {Array} queueItems - Array of { id, action, data }
   * @param {Function} markSyncedFn - Callback to mark item as synced in IDB
   */
  const processQueue = async (queueItems, markSyncedFn) => {
    if (!isOnline.value || !queueItems.length) return;

    for (const item of queueItems) {
      const result = await pushToServer(item.data);
      if (result.success && markSyncedFn) {
        await markSyncedFn(item.id);
      }
    }
  };

  return {
    syncStatus,
    syncMessage,
    isOnline,
    lastSyncTime,
    isSyncing,
    getUserId,
    pushToServer,
    pullFromServer,
    processQueue
  };
}
