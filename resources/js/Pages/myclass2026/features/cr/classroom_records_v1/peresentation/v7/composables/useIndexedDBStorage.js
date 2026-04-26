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
        groups: presentation.groups || null,
        gameSettings: presentation.gameSettings || null,
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
      return result;
    } catch (error) {
      console.error('Error saving presentation:', error);
      storageError.value = error.message;
      throw error;
    }
  };

  // Load presentation by ID
  const loadPresentation = async (id) => {
    if (!isStorageAvailable()) {
      throw new Error('IndexedDB is not available');
    }

    try {
      const presentation = await db.loadPresentation(id);
      return presentation;
    } catch (error) {
      console.error('Error loading presentation:', error);
      storageError.value = error.message;
      throw error;
    }
  };

  // Delete presentation by ID
  const deletePresentation = async (id) => {
    if (!isStorageAvailable()) {
      throw new Error('IndexedDB is not available');
    }

    try {
      await db.deletePresentation(id);
      await getStorageStats();
      return true;
    } catch (error) {
      console.error('Error deleting presentation:', error);
      storageError.value = error.message;
      throw error;
    }
  };

  // Get current presentation (most recent or set as current)
  const getCurrentPresentation = async () => {
    if (!isStorageAvailable()) {
      console.warn('IndexedDB is not available - cannot get current presentation');
      return null;
    }

    try {
      // Try to get the current presentation from metadata
      const currentMetadata = await db.offline_metadata.get('current_presentation');
      if (currentMetadata && currentMetadata.value) {
        return await db.loadPresentation(currentMetadata.value);
      }
      
      // If no current presentation set, get the most recent one
      const presentations = await db.presentations.orderBy('updated_at').reverse().limit(1).toArray();
      return presentations.length > 0 ? presentations[0] : null;
    } catch (error) {
      console.error('Error getting current presentation:', error);
      storageError.value = error.message;
      return null;
    }
  };

  // List all presentations
  const listPresentations = async () => {
    if (!isStorageAvailable()) {
      console.warn('IndexedDB is not available');
      return [];
    }

    try {
      const presentations = await db.presentations.orderBy('updated_at').reverse().toArray();
      return presentations;
    } catch (error) {
      console.error('Error listing presentations:', error);
      storageError.value = error.message;
      return [];
    }
  };

  // Get all presentations (alias for listPresentations)
  const getAllPresentations = async () => {
    return listPresentations();
  };

  // Export presentation to JSON
  const exportPresentation = async (id) => {
    try {
      const presentation = await loadPresentation(id);
      if (!presentation) {
        throw new Error('Presentation not found');
      }
      
      return {
        ...presentation,
        exportedAt: new Date().toISOString(),
        version: '1.0'
      };
    } catch (error) {
      console.error('Error exporting presentation:', error);
      storageError.value = error.message;
      throw error;
    }
  };

  // Import presentation from JSON
  const importPresentation = async (presentationData, options = {}) => {
    const {
      overwrite = false,
      createBackup = true
    } = options;

    try {
      // Validate presentation data
      if (!presentationData || !presentationData.title) {
        throw new Error('Invalid presentation data');
      }

      // Set new metadata for import
      const importedPresentation = {
        ...presentationData,
        importedAt: new Date().toISOString(),
        originalId: presentationData.id
      };
      
      // Remove the old ID to let the database generate a new one
      delete importedPresentation.id;

      const result = await savePresentation(importedPresentation, {
        overwrite,
        createBackup
      });

      await getStorageStats();
      return result;
    } catch (error) {
      console.error('Error importing presentation:', error);
      storageError.value = error.message;
      throw error;
    }
  };

  // Clear all storage
  const clearAllStorage = async () => {
    if (!isStorageAvailable()) {
      throw new Error('IndexedDB is not available');
    }

    try {
      await db.clear();
      storageStats.value = {
        totalPresentations: 0,
        totalBackups: 0,
        totalSize: 0,
        totalSizeFormatted: '0 B',
        availableSpace: 'Unknown',
        availableSpaceFormatted: 'Unknown',
        averageSize: 0
      };
      return true;
    } catch (error) {
      console.error('Error clearing storage:', error);
      storageError.value = error.message;
      throw error;
    }
  };

  return {
    // State
    storageError: computed(() => storageError.value),
    storageStats: computed(() => storageStats.value),
    
    // Database instance (for direct access)
    db,
    
    // Methods
    isStorageAvailable,
    getStorageStats,
    savePresentation,
    loadPresentation,
    deletePresentation,
    getCurrentPresentation,
    listPresentations,
    getAllPresentations,
    exportPresentation,
    importPresentation,
    clearAllStorage
  };
}
