import { ref, computed } from 'vue';
import { db } from '../../../../../../../../offline/dexie.js';

export function useIndexedDBStorage() {
  const storageError = ref(null);
  const storageStats = ref({
    totalPresentations: 0,
    totalBackups: 0,
    totalSize: 0,
    totalSizeFormatted: '0 B',
    availableSpace: 'Unknown',
    availableSpaceFormatted: 'Unknown',
    averageSize: 0
  });

  // Check if IndexedDB is available
  const isStorageAvailable = () => {
    return 'indexedDB' in window && indexedDB !== null;
  };

  // Get current storage statistics
  const getStorageStats = async () => {
    try {
      const stats = await db.getPresentationStorageStats();
      storageStats.value = stats;
      return stats;
    } catch (error) {
      console.error('Error getting storage stats:', error);
      storageError.value = error.message;
      return storageStats.value;
    }
  };

  // Save presentation with metadata
  const savePresentation = async (presentation, options = {}) => {
    if (!isStorageAvailable()) {
      throw new Error('IndexedDB is not available');
    }

    const {
      name = presentation.title || 'Untitled Presentation',
      overwrite = false,
      createBackup = true
    } = options;

    try {
      const presentationData = {
        title: name,
        description: typeof presentation.description === 'string'
          ? presentation.description
          : String(presentation.description || ''),
        slides: presentation.slides || [],
        currentSlideIndex: presentation.currentSlideIndex || 0,
        usePhases: presentation.usePhases || false,
        hasInitializedPhases: presentation.hasInitializedPhases || false,
        metadata: {
          size: JSON.stringify(presentation).length,
          slideCount: (presentation.slides || []).length,
          version: '1.0',
          ...presentation.metadata
        }
      };

      const result = await db.savePresentation(presentationData, {
        overwrite,
        createBackup
      });

      await getStorageStats();
      storageError.value = null;
      
      return result;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Load presentation by key
  const loadPresentation = async (key) => {
    try {
      const presentation = await db.loadPresentation(key);
      
      // Set as current presentation in metadata
      await db.offline_metadata.put({
        key: 'current_presentation',
        value: key,
        updated_at: new Date().toISOString()
      }, 'current_presentation');
      
      return presentation;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Get all presentations metadata
  const getAllPresentations = async () => {
    try {
      return await db.getAllPresentations();
    } catch (error) {
      console.error('Error getting presentations:', error);
      storageError.value = error.message;
      return [];
    }
  };

  // Delete presentation
  const deletePresentation = async (key) => {
    try {
      await db.deletePresentation(key);
      await getStorageStats();
      return true;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Get current presentation
  const getCurrentPresentation = async () => {
    try {
      const metadata = await db.offline_metadata.get('current_presentation');
      if (metadata && metadata.value) {
        return await loadPresentation(metadata.value);
      }
      return null;
    } catch (error) {
      console.error('Error loading current presentation:', error);
      return null;
    }
  };

  // Export presentation as JSON
  const exportPresentation = async (presentation) => {
    try {
      const exportData = {
        ...presentation,
        exportedAt: new Date().toISOString(),
        exportedBy: 'Classroom Records v5 (IndexedDB)'
      };
      
      const jsonString = JSON.stringify(exportData, null, 2);
      const blob = new Blob([jsonString], { type: 'application/json' });
      
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `presentation-${presentation.metadata?.name || presentation.title || 'untitled'}-${new Date().toISOString().split('T')[0]}.json`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Import presentation from JSON
  const importPresentation = async (jsonString, options = {}) => {
    try {
      const presentation = JSON.parse(jsonString);
      
      // Validate presentation structure
      if (!presentation.slides || !Array.isArray(presentation.slides)) {
        throw new Error('Invalid presentation format');
      }

      return await savePresentation(presentation, options);
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Create backup copy
  const createBackupCopy = async (presentationKey) => {
    try {
      const presentation = await db.presentations.get(presentationKey);
      if (presentation) {
        await db.createPresentationBackup(presentationKey, presentation);
        return true;
      }
      return false;
    } catch (error) {
      console.error('Error creating backup:', error);
      return false;
    }
  };

  // Get presentation backups
  const getPresentationBackups = async (presentationKey = null) => {
    try {
      return await db.getPresentationBackups(presentationKey);
    } catch (error) {
      console.error('Error getting backups:', error);
      return [];
    }
  };

  // Restore from backup
  const restoreFromBackup = async (backupId) => {
    try {
      const result = await db.restoreFromBackup(backupId);
      await getStorageStats();
      return result;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Clear all presentation data
  const clearAllPresentations = async () => {
    try {
      await db.presentations.clear();
      await db.presentation_backups.clear();
      
      // Clear current presentation metadata
      await db.offline_metadata.delete('current_presentation');
      
      await getStorageStats();
      return true;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Get storage quota information
  const getStorageQuota = async () => {
    try {
      if ('storage' in navigator && 'estimate' in navigator.storage) {
        const estimate = await navigator.storage.estimate();
        return {
          quota: estimate.quota,
          usage: estimate.usage,
          available: estimate.quota - estimate.usage,
          quotaFormatted: db.formatBytes(estimate.quota),
          usageFormatted: db.formatBytes(estimate.usage),
          availableFormatted: db.formatBytes(estimate.quota - estimate.usage)
        };
      }
      return null;
    } catch (error) {
      console.error('Error getting storage quota:', error);
      return null;
    }
  };

  // Initialize storage stats
  const initializeStorage = async () => {
    try {
      await db.open();
      await getStorageStats();
      return true;
    } catch (error) {
      console.error('Error initializing storage:', error);
      storageError.value = error.message;
      return false;
    }
  };

  // Auto-save functionality
  const autoSave = async (presentationData, delay = 1000) => {
    let timeoutId;
    
    return new Promise((resolve, reject) => {
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
      
      timeoutId = setTimeout(async () => {
        try {
          const result = await savePresentation(presentationData, { 
            overwrite: true, 
            createBackup: false 
          });
          resolve(result);
        } catch (error) {
          reject(error);
        }
      }, delay);
    });
  };

  // Initialize on creation
  initializeStorage();

  return {
    // State
    storageError: computed(() => storageError.value),
    storageStats: computed(() => storageStats.value),
    
    // Methods
    savePresentation,
    loadPresentation,
    getAllPresentations,
    deletePresentation,
    getCurrentPresentation,
    exportPresentation,
    importPresentation,
    createBackupCopy,
    getPresentationBackups,
    restoreFromBackup,
    clearAllPresentations,
    getStorageStats,
    getStorageQuota,
    autoSave,
    initializeStorage,
    
    // Utilities
    isStorageAvailable,
    
    // Database access
    db
  };
}
