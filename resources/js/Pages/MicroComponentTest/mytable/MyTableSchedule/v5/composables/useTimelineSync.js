import { ref, computed, watch } from 'vue';
import { useTimelineAuth } from './useTimelineAuth.js';

// Timeline Data Sync Service
export function useTimelineSync() {
  const { api, isAuthenticated, user } = useTimelineAuth();
  
  const timelineData = ref(null);
  const isOnline = ref(navigator.onLine);
  const isSyncing = ref(false);
  const lastSyncAt = ref(null);
  const syncError = ref(null);
  const pendingChanges = ref(false);

  // Local storage keys
  const LOCAL_DATA_KEY = 'timeline_data';
  const LOCAL_SETTINGS_KEY = 'timeline_settings';
  const SYNC_STATUS_KEY = 'timeline_sync_status';

  // Initialize sync status
  const syncStatus = ref({
    version: '1.0',
    lastSyncAt: null,
    deviceId: null,
    hasLocalChanges: false,
    conflictCount: 0
  });

  // Watch online status
  window.addEventListener('online', () => {
    isOnline.value = true;
    if (pendingChanges.value) {
      syncData();
    }
  });

  window.addEventListener('offline', () => {
    isOnline.value = false;
  });

  // Get local data
  function getLocalData() {
    try {
      const data = localStorage.getItem(LOCAL_DATA_KEY);
      return data ? JSON.parse(data) : null;
    } catch (err) {
      console.error('Error loading local data:', err);
      return null;
    }
  }

  // Save local data
  function saveLocalData(data) {
    try {
      localStorage.setItem(LOCAL_DATA_KEY, JSON.stringify(data));
      pendingChanges.value = true;
      return true;
    } catch (err) {
      console.error('Error saving local data:', err);
      return false;
    }
  }

  // Get local settings
  function getLocalSettings() {
    try {
      const settings = localStorage.getItem(LOCAL_SETTINGS_KEY);
      return settings ? JSON.parse(settings) : {};
    } catch (err) {
      console.error('Error loading local settings:', err);
      return {};
    }
  }

  // Save local settings
  function saveLocalSettings(settings) {
    try {
      localStorage.setItem(LOCAL_SETTINGS_KEY, JSON.stringify(settings));
      return true;
    } catch (err) {
      console.error('Error saving local settings:', err);
      return false;
    }
  }

  // Load timeline data from server
  async function loadTimelineData() {
    if (!isAuthenticated.value) {
      return { success: false, message: 'Not authenticated' };
    }

    try {
      isSyncing.value = true;
      syncError.value = null;

      const response = await api.get('/data');
      
      if (response.data.success) {
        timelineData.value = response.data.data;
        saveLocalData(response.data.data);
        
        // Update sync status
        syncStatus.value = {
          ...syncStatus.value,
          lastSyncAt: response.data.data.sync_metadata?.last_sync_at || new Date().toISOString(),
          version: response.data.data.version || '1.0',
          hasLocalChanges: false
        };
        
        saveSyncStatus();
        pendingChanges.value = false;
        lastSyncAt.value = new Date();
        
        return { success: true, data: response.data.data };
      } else {
        syncError.value = response.data.message || 'Failed to load data';
        return { success: false, message: syncError.value };
      }
    } catch (err) {
      syncError.value = err.response?.data?.message || 'Network error';
      
      // Fall back to local data if available
      const localData = getLocalData();
      if (localData) {
        timelineData.value = localData;
        return { success: true, data: localData, fromCache: true };
      }
      
      return { success: false, message: syncError.value };
    } finally {
      isSyncing.value = false;
    }
  }

  // Save timeline data to server
  async function saveTimelineData(data) {
    if (!isAuthenticated.value) {
      // Save locally if not authenticated
      saveLocalData(data);
      timelineData.value = data;
      return { success: true, savedLocally: true };
    }

    try {
      isSyncing.value = true;
      syncError.value = null;

      // Generate version
      const version = generateVersion();
      
      const payload = {
        data: data,
        version: version,
        client_timestamp: new Date().toISOString()
      };

      const response = await api.post('/data', payload);
      
      if (response.data.success) {
        timelineData.value = data;
        saveLocalData(data);
        
        // Update sync status
        syncStatus.value = {
          ...syncStatus.value,
          lastSyncAt: response.data.updated_at,
          version: response.data.version,
          hasLocalChanges: false
        };
        
        saveSyncStatus();
        pendingChanges.value = false;
        lastSyncAt.value = new Date();
        
        return { success: true, version: response.data.version };
      } else {
        syncError.value = response.data.message || 'Failed to save data';
        
        // Save locally if server save failed
        saveLocalData(data);
        timelineData.value = data;
        
        return { success: false, message: syncError.value, savedLocally: true };
      }
    } catch (err) {
      syncError.value = err.response?.data?.message || 'Network error';
      
      // Save locally if network error
      saveLocalData(data);
      timelineData.value = data;
      
      return { success: false, message: syncError.value, savedLocally: true };
    } finally {
      isSyncing.value = false;
    }
  }

  // Sync data with server
  async function syncData() {
    if (!isAuthenticated.value || !isOnline.value) {
      return { success: false, message: 'Cannot sync: not authenticated or offline' };
    }

    try {
      isSyncing.value = true;
      syncError.value = null;

      const localData = getLocalData();
      const clientVersion = localData?.version || '1.0';
      
      const payload = {
        client_version: clientVersion,
        client_timestamp: new Date().toISOString(),
        device_id: getDeviceId(),
        sync_data: pendingChanges.value ? localData?.data : null
      };

      const response = await api.post('/sync', payload);
      
      if (response.data.success) {
        const { sync_result } = response.data;
        
        if (sync_result.action === 'server_to_client' && sync_result.server_data) {
          // Server has newer data - update local
          timelineData.value = sync_result.server_data;
          saveLocalData(sync_result.server_data);
        }
        
        // Update sync status
        syncStatus.value = {
          ...syncStatus.value,
          lastSyncAt: response.data.sync_timestamp,
          version: response.data.server_version,
          hasLocalChanges: false
        };
        
        saveSyncStatus();
        pendingChanges.value = false;
        lastSyncAt.value = new Date();
        
        return { success: true, syncResult: sync_result };
      } else {
        syncError.value = response.data.message || 'Sync failed';
        return { success: false, message: syncError.value };
      }
    } catch (err) {
      syncError.value = err.response?.data?.message || 'Network error';
      return { success: false, message: syncError.value };
    } finally {
      isSyncing.value = false;
    }
  }

  // Auto-sync on data changes
  watch(timelineData, (newData) => {
    if (newData && isAuthenticated.value && isOnline.value) {
      // Debounce sync
      setTimeout(() => {
        if (pendingChanges.value) {
          syncData();
        }
      }, 5000);
    }
  }, { deep: true });

  // Get device ID
  function getDeviceId() {
    return localStorage.getItem('timeline_device_id') || 'unknown';
  }

  // Generate version
  function generateVersion() {
    const now = new Date();
    const timestamp = now.getTime();
    const random = Math.random().toString(36).substr(2, 5);
    return `${timestamp}-${random}`;
  }

  // Save sync status
  function saveSyncStatus() {
    try {
      localStorage.setItem(SYNC_STATUS_KEY, JSON.stringify(syncStatus.value));
    } catch (err) {
      console.error('Error saving sync status:', err);
    }
  }

  // Load sync status
  function loadSyncStatus() {
    try {
      const status = localStorage.getItem(SYNC_STATUS_KEY);
      if (status) {
        syncStatus.value = JSON.parse(status);
        lastSyncAt.value = syncStatus.value.lastSyncAt ? new Date(syncStatus.value.lastSyncAt) : null;
      }
    } catch (err) {
      console.error('Error loading sync status:', err);
    }
  }

  // Initialize
  function initialize() {
    loadSyncStatus();
    
    // Load local data first
    const localData = getLocalData();
    if (localData) {
      timelineData.value = localData;
    }
    
    // Then try to sync with server if authenticated
    if (isAuthenticated.value) {
      loadTimelineData();
    }
  }

  // Auto-initialize
  initialize();

  // Computed properties
  const hasLocalData = computed(() => !!getLocalData());
  const needsSync = computed(() => pendingChanges.value && isOnline.value && isAuthenticated.value);
  const syncStatusText = computed(() => {
    if (isSyncing.value) return 'Syncing...';
    if (!isOnline.value) return 'Offline';
    if (!isAuthenticated.value) return 'Not logged in';
    if (needsSync.value) return 'Pending sync';
    if (lastSyncAt.value) return `Last sync: ${formatTime(lastSyncAt.value)}`;
    return 'Never synced';
  });

  // Format time
  function formatTime(date) {
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes} min ago`;
    
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    
    const days = Math.floor(hours / 24);
    return `${days} day${days > 1 ? 's' : ''} ago`;
  }

  return {
    // State
    timelineData,
    isOnline,
    isSyncing,
    lastSyncAt,
    syncError,
    pendingChanges,
    syncStatus,
    hasLocalData,
    needsSync,
    syncStatusText,
    
    // Methods
    loadTimelineData,
    saveTimelineData,
    syncData,
    getLocalData,
    saveLocalData,
    getLocalSettings,
    saveLocalSettings
  };
}
