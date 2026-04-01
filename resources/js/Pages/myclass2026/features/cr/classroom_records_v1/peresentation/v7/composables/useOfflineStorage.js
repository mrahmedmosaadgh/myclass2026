import { ref, computed } from 'vue';

// Storage configuration and utilities
const STORAGE_CONFIG = {
  MAX_LOCAL_STORAGE_SIZE: 5 * 1024 * 1024, // 5MB
  MAX_PRESENTATION_SIZE: 2 * 1024 * 1024, // 2MB per presentation
  MAX_PRESENTATIONS_COUNT: 10,
  STORAGE_KEYS: {
    PRESENTATIONS: 'cr_v5_presentations',
    CURRENT_PRESENTATION: 'cr_v5_current_presentation',
    SETTINGS: 'cr_v5_settings',
    BACKUP: 'cr_v5_backup'
  }
};

export function useOfflineStorage() {
  const storageError = ref(null);
  const storageStats = ref({
    used: 0,
    available: 0,
    presentationsCount: 0
  });

  // Get current localStorage usage
  const getStorageStats = () => {
    try {
      let totalSize = 0;
      let presentationsCount = 0;
      
      for (let key in localStorage) {
        if (localStorage.hasOwnProperty(key)) {
          totalSize += localStorage[key].length;
          if (key.startsWith('cr_v5_presentation_')) {
            presentationsCount++;
          }
        }
      }
      
      storageStats.value = {
        used: totalSize,
        available: STORAGE_CONFIG.MAX_LOCAL_STORAGE_SIZE - totalSize,
        presentationsCount
      };
      
      return storageStats.value;
    } catch (error) {
      console.error('Error calculating storage stats:', error);
      return { used: 0, available: STORAGE_CONFIG.MAX_LOCAL_STORAGE_SIZE, presentationsCount: 0 };
    }
  };

  // Check if storage is available
  const isStorageAvailable = () => {
    try {
      const test = '__storage_test__';
      localStorage.setItem(test, test);
      localStorage.removeItem(test);
      return true;
    } catch (error) {
      return false;
    }
  };

  // Estimate presentation size
  const estimatePresentationSize = (presentation) => {
    const jsonString = JSON.stringify(presentation);
    return new Blob([jsonString]).size;
  };

  // Save presentation with metadata
  const savePresentation = async (presentation, options = {}) => {
    if (!isStorageAvailable()) {
      throw new Error('LocalStorage is not available');
    }

    const {
      name = presentation.title || 'Untitled Presentation',
      overwrite = false,
      createBackup = true
    } = options;

    try {
      const presentationSize = estimatePresentationSize(presentation);
      
      if (presentationSize > STORAGE_CONFIG.MAX_PRESENTATION_SIZE) {
        throw new Error(`Presentation too large (${Math.round(presentationSize / 1024)}KB). Maximum size is ${Math.round(STORAGE_CONFIG.MAX_PRESENTATION_SIZE / 1024)}KB`);
      }

      const stats = getStorageStats();
      if (stats.available < presentationSize) {
        throw new Error('Not enough storage space available');
      }

      // Get existing presentations
      const presentations = getAllPresentations();
      
      // Check for duplicates
      const existingIndex = presentations.findIndex(p => p.name === name);
      if (existingIndex !== -1 && !overwrite) {
        throw new Error(`Presentation "${name}" already exists. Use overwrite option to replace it.`);
      }

      // Create backup if requested
      if (createBackup && presentations.length > 0) {
        await createBackupCopy();
      }

      // Prepare presentation data with metadata
      const presentationData = {
        ...presentation,
        metadata: {
          name,
          size: presentationSize,
          createdAt: new Date().toISOString(),
          updatedAt: new Date().toISOString(),
          version: '1.0'
        }
      };

      // Generate unique key
      const key = existingIndex !== -1 
        ? presentations[existingIndex].key 
        : `cr_v5_presentation_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;

      // Save to localStorage
      localStorage.setItem(key, JSON.stringify(presentationData));

      // Update presentations index
      if (existingIndex === -1) {
        presentations.push({
          key,
          name,
          size: presentationSize,
          createdAt: presentationData.metadata.createdAt,
          updatedAt: presentationData.metadata.updatedAt
        });
      } else {
        presentations[existingIndex] = {
          ...presentations[existingIndex],
          size: presentationSize,
          updatedAt: presentationData.metadata.updatedAt
        };
      }

      localStorage.setItem(STORAGE_CONFIG.STORAGE_KEYS.PRESENTATIONS, JSON.stringify(presentations));
      
      // Update current presentation
      localStorage.setItem(STORAGE_CONFIG.STORAGE_KEYS.CURRENT_PRESENTATION, key);

      getStorageStats();
      storageError.value = null;
      
      return { key, name, size: presentationSize };
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Load presentation by key
  const loadPresentation = (key) => {
    try {
      const data = localStorage.getItem(key);
      if (!data) {
        throw new Error('Presentation not found');
      }
      
      const presentation = JSON.parse(data);
      
      // Set as current presentation
      localStorage.setItem(STORAGE_CONFIG.STORAGE_KEYS.CURRENT_PRESENTATION, key);
      
      return presentation;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Get all presentations metadata
  const getAllPresentations = () => {
    try {
      const data = localStorage.getItem(STORAGE_CONFIG.STORAGE_KEYS.PRESENTATIONS);
      return data ? JSON.parse(data) : [];
    } catch (error) {
      console.error('Error loading presentations list:', error);
      return [];
    }
  };

  // Delete presentation
  const deletePresentation = (key) => {
    try {
      const presentations = getAllPresentations();
      const index = presentations.findIndex(p => p.key === key);
      
      if (index === -1) {
        throw new Error('Presentation not found');
      }

      // Remove from localStorage
      localStorage.removeItem(key);
      
      // Remove from index
      presentations.splice(index, 1);
      localStorage.setItem(STORAGE_CONFIG.STORAGE_KEYS.PRESENTATIONS, JSON.stringify(presentations));

      // Update current presentation if needed
      const currentKey = localStorage.getItem(STORAGE_CONFIG.STORAGE_KEYS.CURRENT_PRESENTATION);
      if (currentKey === key) {
        localStorage.removeItem(STORAGE_CONFIG.STORAGE_KEYS.CURRENT_PRESENTATION);
      }

      getStorageStats();
      return true;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Get current presentation
  const getCurrentPresentation = () => {
    try {
      const currentKey = localStorage.getItem(STORAGE_CONFIG.STORAGE_KEYS.CURRENT_PRESENTATION);
      if (!currentKey) {
        return null;
      }
      return loadPresentation(currentKey);
    } catch (error) {
      console.error('Error loading current presentation:', error);
      return null;
    }
  };

  // Export presentation as JSON
  const exportPresentation = (presentation) => {
    const exportData = {
      ...presentation,
      exportedAt: new Date().toISOString(),
      exportedBy: 'Classroom Records v5'
    };
    
    const jsonString = JSON.stringify(exportData, null, 2);
    const blob = new Blob([jsonString], { type: 'application/json' });
    
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `presentation-${presentation.metadata?.name || 'untitled'}-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
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
  const createBackupCopy = async () => {
    try {
      const presentations = getAllPresentations();
      const backupData = {
        presentations,
        createdAt: new Date().toISOString(),
        version: '1.0'
      };
      
      localStorage.setItem(STORAGE_CONFIG.STORAGE_KEYS.BACKUP, JSON.stringify(backupData));
      return true;
    } catch (error) {
      console.error('Error creating backup:', error);
      return false;
    }
  };

  // Restore from backup
  const restoreFromBackup = async () => {
    try {
      const backupData = localStorage.getItem(STORAGE_CONFIG.STORAGE_KEYS.BACKUP);
      if (!backupData) {
        throw new Error('No backup found');
      }

      const backup = JSON.parse(backupData);
      
      // Restore presentations
      if (backup.presentations && Array.isArray(backup.presentations)) {
        localStorage.setItem(STORAGE_CONFIG.STORAGE_KEYS.PRESENTATIONS, JSON.stringify(backup.presentations));
      }

      return backup;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Clear all storage
  const clearAllStorage = () => {
    try {
      const keysToRemove = [];
      for (let key in localStorage) {
        if (key.startsWith('cr_v5_')) {
          keysToRemove.push(key);
        }
      }
      
      keysToRemove.forEach(key => localStorage.removeItem(key));
      getStorageStats();
      return true;
    } catch (error) {
      storageError.value = error.message;
      throw error;
    }
  };

  // Initialize storage stats
  getStorageStats();

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
    restoreFromBackup,
    clearAllStorage,
    getStorageStats,
    isStorageAvailable,
    
    // Constants
    STORAGE_CONFIG
  };
}
