import { ref, computed } from 'vue';
import { useIndexedDBStorage } from './useIndexedDBStorage.js';

export function usePresentationSync() {
  const indexedDBStorage = useIndexedDBStorage();
  
  // State
  const syncStatus = ref('idle'); // 'idle' | 'syncing' | 'synced' | 'error'
  const lastSyncTime = ref(null);
  const syncError = ref(null);
  const pendingChanges = ref([]);
  const onlineStatus = ref(navigator.onLine);
  const syncQueue = ref([]);
  
  // Computed
  const isOnline = computed(() => onlineStatus.value);
  const isSyncing = computed(() => syncStatus.value === 'syncing');
  const hasPendingChanges = computed(() => pendingChanges.value.length > 0);
  const hasSyncQueue = computed(() => syncQueue.value.length > 0);
  
  // Network status monitoring
  const setupNetworkMonitoring = () => {
    window.addEventListener('online', () => {
      onlineStatus.value = true;
      processSyncQueue();
    });
    
    window.addEventListener('offline', () => {
      onlineStatus.value = false;
    });
  };
  
  // API helper functions
  const apiRequest = async (endpoint, options = {}) => {
    try {
      const response = await fetch(`/api/${endpoint}`, {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        ...options
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      
      return await response.json();
    } catch (error) {
      console.error(`API request failed: ${endpoint}`, error);
      throw error;
    }
  };
  
  // Sync presentation to server
  const syncPresentationToServer = async (presentation) => {
    try {
      syncStatus.value = 'syncing';
      const categoryId = presentation.crPresentationCategoryId ?? presentation.categoryId ?? null;
      
      const payload = {
        title: presentation.title,
        description: presentation.description || '',
        category_id: categoryId,
        cr_presentation_category_id: categoryId,
        slides: presentation.slides,
        current_slide_index: presentation.currentSlideIndex || 0,
        use_phases: presentation.usePhases || false,
        has_initialized_phases: presentation.hasInitializedPhases || false,
        status: 'draft',
        is_public: false,
        is_template: false
      };
      
      let result;
      
      if (presentation.serverId) {
        // Update existing presentation
        result = await apiRequest(`presentations/${presentation.serverId}`, {
          method: 'PUT',
          body: JSON.stringify(payload)
        });
      } else {
        // Create new presentation
        result = await apiRequest('presentations', {
          method: 'POST',
          body: JSON.stringify(payload)
        });
      }
      
      if (result.success) {
        // Update local presentation with server data
        const updatedPresentation = {
          ...presentation,
          serverId: result.data.id,
          slug: result.data.slug,
          syncedAt: new Date().toISOString(),
          lastSyncTime: new Date().toISOString()
        };
        
        await indexedDBStorage.savePresentation(updatedPresentation, {
          overwrite: true,
          createBackup: false
        });
        
        syncStatus.value = 'synced';
        lastSyncTime.value = new Date();
        syncError.value = null;
        
        return updatedPresentation;
      } else {
        throw new Error(result.message || 'Sync failed');
      }
    } catch (error) {
      syncStatus.value = 'error';
      syncError.value = error.message;
      throw error;
    }
  };
  
  // Load presentation from server
  const loadPresentationFromServer = async (presentationId) => {
    try {
      const result = await apiRequest(`presentations/${presentationId}`);
      
      if (result.success) {
        const serverPresentation = result.data;
        
        // Convert to local format
        const localPresentation = {
          id: serverPresentation.id,
          title: serverPresentation.title,
          description: serverPresentation.description,
          slides: serverPresentation.slides,
          currentSlideIndex: serverPresentation.current_slide_index,
          usePhases: serverPresentation.use_phases,
          hasInitializedPhases: serverPresentation.has_initialized_phases,
          categoryId: serverPresentation.cr_presentation_category_id ?? serverPresentation.category_id,
          crPresentationCategoryId: serverPresentation.cr_presentation_category_id ?? serverPresentation.category_id,
          serverId: serverPresentation.id,
          slug: serverPresentation.slug,
          syncedAt: new Date().toISOString(),
          lastSyncTime: new Date().toISOString(),
          metadata: {
            ...serverPresentation.metadata,
            serverSynced: true
          }
        };
        
        // Save to IndexedDB
        await indexedDBStorage.savePresentation(localPresentation, {
          overwrite: true,
          createBackup: false
        });
        
        return localPresentation;
      } else {
        throw new Error(result.message || 'Failed to load presentation');
      }
    } catch (error) {
      console.error('Error loading presentation from server:', error);
      throw error;
    }
  };
  
  // Sync all presentations
  const syncAllPresentations = async () => {
    if (!isOnline.value) {
      throw new Error('Cannot sync while offline');
    }
    
    try {
      const presentations = await indexedDBStorage.getAllPresentations();
      const syncResults = [];
      
      for (const presentationMeta of presentations) {
        try {
          const presentation = await indexedDBStorage.loadPresentation(presentationMeta.id);
          
          // Only sync if it has changes since last sync
          if (!presentation.syncedAt || presentation.updatedAt > presentation.syncedAt) {
            const synced = await syncPresentationToServer(presentation);
            syncResults.push({ success: true, id: presentation.id, synced });
          }
        } catch (error) {
          syncResults.push({ success: false, id: presentationMeta.id, error: error.message });
        }
      }
      
      return syncResults;
    } catch (error) {
      console.error('Error syncing presentations:', error);
      throw error;
    }
  };
  
  // Load categories from server
  const loadCategoriesFromServer = async () => {
    try {
      const result = await apiRequest('cr-presentation-categories');
      
      if (result.success) {
        // Save categories to IndexedDB metadata
        await indexedDBStorage.db.offline_metadata.put({
          key: 'cr_presentation_categories',
          value: JSON.stringify(result.data),
          updated_at: new Date().toISOString()
        }, 'cr_presentation_categories');
        
        return result.data;
      } else {
        throw new Error(result.message || 'Failed to load CR presentation categories');
      }
    } catch (error) {
      console.error('Error loading CR presentation categories from server:', error);
      throw error;
    }
  };
  
  // Get categories (try server first, fallback to IndexedDB)
  const getCategories = async () => {
    if (isOnline.value) {
      try {
        return await loadCategoriesFromServer();
      } catch (error) {
        console.warn('Failed to load categories from server, using cached:', error);
      }
    }
    
    // Fallback to cached categories
    try {
      const cached = await indexedDBStorage.db.offline_metadata.get('cr_presentation_categories');
      if (cached && cached.value) {
        return JSON.parse(cached.value);
      }
    } catch (error) {
      console.error('Error loading cached CR presentation categories:', error);
    }
    
    // Return default categories if nothing available
    return [
      { id: null, name: 'General', slug: 'general', color: '#6b7280', icon: 'presentation', is_system: true }
    ];
  };
  
  // Add to sync queue
  const addToSyncQueue = (action, presentationId, data = null) => {
    const queueItem = {
      id: Date.now() + Math.random(),
      action, // 'create', 'update', 'delete'
      presentationId,
      data,
      timestamp: new Date().toISOString(),
      retryCount: 0
    };
    
    syncQueue.value.push(queueItem);
    saveSyncQueue();
    
    // Try to process immediately if online
    if (isOnline.value) {
      processSyncQueue();
    }
  };
  
  // Process sync queue
  const processSyncQueue = async () => {
    if (!isOnline.value || syncQueue.value.length === 0) {
      return;
    }
    
    try {
      syncStatus.value = 'syncing';
      
      const queueToProcess = [...syncQueue.value];
      syncQueue.value = [];
      
      for (const item of queueToProcess) {
        try {
          switch (item.action) {
            case 'create':
            case 'update':
              const presentation = await indexedDBStorage.loadPresentation(item.presentationId);
              await syncPresentationToServer(presentation);
              break;
              
            case 'delete':
              if (item.data?.serverId) {
                await apiRequest(`presentations/${item.data.serverId}`, {
                  method: 'DELETE'
                });
              }
              break;
          }
        } catch (error) {
          // Add back to queue with retry count
          item.retryCount++;
          if (item.retryCount < 3) {
            syncQueue.value.push(item);
          } else {
            console.error(`Failed to sync ${item.action} for presentation ${item.presentationId} after 3 retries:`, error);
          }
        }
      }
      
      saveSyncQueue();
      syncStatus.value = 'synced';
      lastSyncTime.value = new Date();
    } catch (error) {
      syncStatus.value = 'error';
      syncError.value = error.message;
    }
  };
  
  // Save sync queue to IndexedDB
  const saveSyncQueue = async () => {
    try {
      await indexedDBStorage.db.offline_metadata.put({
        key: 'presentation_sync_queue',
        value: JSON.stringify(syncQueue.value),
        updated_at: new Date().toISOString()
      }, 'presentation_sync_queue');
    } catch (error) {
      console.error('Error saving sync queue:', error);
    }
  };
  
  // Load sync queue from IndexedDB
  const loadSyncQueue = async () => {
    try {
      const cached = await indexedDBStorage.db.offline_metadata.get('presentation_sync_queue');
      if (cached && cached.value) {
        syncQueue.value = JSON.parse(cached.value);
      }
    } catch (error) {
      console.error('Error loading sync queue:', error);
    }
  };
  
  // Auto-save with sync
  const autoSaveWithSync = async (presentation, delay = 1000) => {
    // Save to IndexedDB first
    await indexedDBStorage.savePresentation(presentation, {
      overwrite: true,
      createBackup: false
    });
    
    // Add to sync queue
    addToSyncQueue(presentation.serverId ? 'update' : 'create', presentation.id, {
      serverId: presentation.serverId
    });
  };
  
  // Force sync now
  const forceSync = async () => {
    if (!isOnline.value) {
      throw new Error('Cannot sync while offline');
    }
    
    await processSyncQueue();
    await syncAllPresentations();
  };
  
  // Clear sync queue
  const clearSyncQueue = async () => {
    syncQueue.value = [];
    await saveSyncQueue();
  };
  
  // Initialize
  const initialize = async () => {
    setupNetworkMonitoring();
    await loadSyncQueue();
    
    // Process queue if online
    if (isOnline.value && hasSyncQueue.value) {
      processSyncQueue();
    }
  };
  
  return {
    // State
    syncStatus: computed(() => syncStatus.value),
    lastSyncTime: computed(() => lastSyncTime.value),
    syncError: computed(() => syncError.value),
    isOnline,
    isSyncing,
    hasPendingChanges,
    hasSyncQueue,
    syncQueue: computed(() => syncQueue.value),
    
    // Methods
    syncPresentationToServer,
    loadPresentationFromServer,
    syncAllPresentations,
    loadCategoriesFromServer,
    getCategories,
    addToSyncQueue,
    processSyncQueue,
    autoSaveWithSync,
    forceSync,
    clearSyncQueue,
    initialize,
    
    // Utilities
    apiRequest
  };
}
