<template>
  <div class="enhanced-presentation-manager">
    <!-- Header with Sync Status -->
    <div class="manager-header">
      <div class="header-left">
        <h3>📁 My Presentations</h3>
        <div class="sync-status" :class="syncStatusClass">
          <span class="sync-icon">{{ syncIcon }}</span>
          <span class="sync-text">{{ syncText }}</span>
          <button v-if="hasSyncQueue && isOnline" @click="forceSync" class="sync-btn">
            🔄 Sync Now
          </button>
        </div>
      </div>
      <div class="header-actions">
        <button @click="showImportDialog = true" class="action-btn import">
          📥 Import
        </button>
        <button @click="showNewDialog = true" class="action-btn new">
          ➕ New
        </button>
      </div>
    </div>

    <!-- Categories Filter -->
    <div class="categories-section">
      <div class="categories-header">
        <h4>🏷️ Categories</h4>
        <button @click="showCategoryDialog = true" class="category-add-btn">
          ➕ Add Category
        </button>
      </div>
      <div class="categories-list">
        <button 
          v-for="category in categories" 
          :key="category.id"
          @click="selectCategory(category.id)"
          :class="['category-btn', { active: selectedCategoryId === category.id }]"
          :style="{ borderColor: category.color }"
        >
          <span class="category-icon" v-if="category.icon">{{ category.icon }}</span>
          <span class="category-name">{{ category.name }}</span>
          <span class="category-count">({{ category.presentation_count || 0 }})</span>
        </button>
      </div>
    </div>

    <!-- Search and Filter -->
    <div class="search-section">
      <div class="search-bar">
        <input 
          v-model="searchQuery"
          @input="handleSearch"
          type="text"
          placeholder="🔍 Search presentations..."
          class="search-input"
        />
        <select v-model="statusFilter" @change="filterPresentations" class="status-filter">
          <option value="">All Status</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="archived">Archived</option>
        </select>
        <select v-model="sortBy" @change="sortPresentations" class="sort-filter">
          <option value="updated_at">Recently Updated</option>
          <option value="created_at">Recently Created</option>
          <option value="title">Title A-Z</option>
          <option value="size">Size</option>
        </select>
      </div>
    </div>

    <!-- Storage Stats -->
    <div class="storage-stats">
      <div class="stat-item">
        <span class="stat-label">Storage Used:</span>
        <span class="stat-value">{{ storageStats.totalSizeFormatted }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Available:</span>
        <span class="stat-value">{{ storageStats.availableSpaceFormatted }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Presentations:</span>
        <span class="stat-value">{{ storageStats.totalPresentations }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Backups:</span>
        <span class="stat-value">{{ storageStats.totalBackups }}</span>
      </div>
    </div>

    <!-- Storage Info -->
    <div class="storage-info">
      <small>
        📦 Using IndexedDB + MySQL for hybrid offline/online storage
        <br>
        🗄️ {{ isOnline ? '🟢 Online - Synced with server' : '🔴 Offline - Local storage only' }}
      </small>
    </div>

    <!-- Storage Warning -->
    <div v-if="storageStats.availableSpace !== 'Unknown' && storageStats.availableSpace < 10 * 1024 * 1024" class="storage-warning">
      ⚠️ Low storage space! Consider deleting old presentations or exporting them.
    </div>

    <!-- Presentations List -->
    <div class="presentations-list">
      <div v-if="filteredPresentations.length === 0" class="empty-state">
        <div class="empty-icon">📂</div>
        <h4>No presentations found</h4>
        <p>{{ searchQuery ? 'Try adjusting your search or filters.' : 'Create your first presentation or import an existing one.' }}</p>
        <button @click="showNewDialog = true" class="primary-btn">
          Create Presentation
        </button>
      </div>

      <div v-else class="presentation-cards">
        <div 
          v-for="presentation in filteredPresentations" 
          :key="presentation.id"
          class="presentation-card"
          :class="{ 
            current: presentation.id === currentPresentationId,
            offline: !presentation.serverId,
            needsSync: presentation.needsSync 
          }"
        >
          <div class="card-header">
            <div class="card-title">
              <h4>{{ presentation.title }}</h4>
              <div class="card-badges">
                <span v-if="!presentation.serverId" class="badge offline">📱 Offline</span>
                <span v-if="presentation.needsSync" class="badge sync-needed">🔄 Needs Sync</span>
                <span v-if="presentation.category" class="badge category" :style="{ backgroundColor: presentation.category.color + '20', color: presentation.category.color }">
                  {{ presentation.category.icon }} {{ presentation.category.name }}
                </span>
              </div>
            </div>
            <div class="card-actions">
              <button @click="loadPresentation(presentation.id)" class="card-btn load">
                📂 Open
              </button>
              <button @click="duplicatePresentation(presentation)" class="card-btn duplicate">
                📋 Copy
              </button>
              <button @click="exportPresentation(presentation.id)" class="card-btn export">
                📤 Export
              </button>
              <button @click="deletePresentation(presentation.id)" class="card-btn delete">
                🗑️
              </button>
            </div>
          </div>
          
          <div class="card-details">
            <div class="detail-item">
              <span class="detail-label">Slides:</span>
              <span class="detail-value">{{ presentation.slideCount || 0 }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Size:</span>
              <span class="detail-value">{{ presentation.sizeFormatted || 'Unknown' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Created:</span>
              <span class="detail-value">{{ formatDate(presentation.createdAt) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Updated:</span>
              <span class="detail-value">{{ formatDate(presentation.updatedAt) }}</span>
            </div>
          </div>

          <div v-if="presentation.id === currentPresentationId" class="current-badge">
            📍 Currently Open
          </div>
        </div>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="storageError || syncError" class="error-message">
      ❌ {{ storageError || syncError }}
      <button @click="clearErrors" class="clear-btn">✕</button>
    </div>

    <!-- Category Management Dialog -->
    <div v-if="showCategoryDialog" class="modal-overlay" @click="showCategoryDialog = false">
      <div class="modal-content" @click.stop>
        <h3>🏷️ Add Category</h3>
        <div class="form-group">
          <label>Category Name:</label>
          <input 
            v-model="newCategory.name"
            type="text"
            placeholder="Enter category name..."
            class="form-input"
          />
        </div>
        <div class="form-group">
          <label>Description:</label>
          <textarea 
            v-model="newCategory.description"
            placeholder="Optional description..."
            class="form-textarea"
            rows="3"
          ></textarea>
        </div>
        <div class="form-group">
          <label>Color:</label>
          <input 
            v-model="newCategory.color"
            type="color"
            class="form-color"
          />
        </div>
        <div class="modal-actions">
          <button @click="showCategoryDialog = false" class="cancel-btn">Cancel</button>
          <button 
            @click="createCategory"
            :disabled="!newCategory.name.trim()"
            class="create-btn"
          >
            Create
          </button>
        </div>
      </div>
    </div>

    <!-- Import Dialog -->
    <div v-if="showImportDialog" class="modal-overlay" @click="showImportDialog = false">
      <div class="modal-content" @click.stop>
        <h3>📥 Import Presentation</h3>
        <p>Select a JSON file to import:</p>
        
        <div class="import-area">
          <input 
            ref="fileInput"
            type="file"
            accept=".json"
            @change="handleFileImport"
            style="display: none;"
          />
          <button @click="$refs.fileInput.click()" class="file-select-btn">
            📁 Choose File
          </button>
          <span v-if="selectedFile" class="file-name">{{ selectedFile.name }}</span>
        </div>

        <div class="import-options">
          <label>
            <input v-model="importOptions.overwrite" type="checkbox" />
            Overwrite if presentation with same name exists
          </label>
          <label>
            <input v-model="importOptions.syncToServer" type="checkbox" :disabled="!isOnline" />
            Sync to server after import (online only)
          </label>
        </div>

        <div class="modal-actions">
          <button @click="showImportDialog = false" class="cancel-btn">Cancel</button>
          <button 
            @click="executeImport" 
            :disabled="!selectedFile || isImporting"
            class="import-btn"
          >
            {{ isImporting ? 'Importing...' : 'Import' }}
          </button>
        </div>
      </div>
    </div>

    <!-- New Presentation Dialog -->
    <div v-if="showNewDialog" class="modal-overlay" @click="showNewDialog = false">
      <div class="modal-content" @click.stop>
        <h3>➕ New Presentation</h3>
        <div class="form-group">
          <label>Presentation Name:</label>
          <input 
            v-model="newPresentationName"
            type="text"
            placeholder="Enter presentation name..."
            class="form-input"
            @keyup.enter="createNewPresentation"
          />
        </div>
        <div class="form-group">
          <label>Category:</label>
          <select v-model="newPresentationCategoryId" class="form-select">
            <option value="">No Category</option>
            <option 
              v-for="category in categories" 
              :key="category.id"
              :value="category.id"
            >
              {{ category.icon }} {{ category.name }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label>Description:</label>
          <textarea 
            v-model="newPresentationDescription"
            placeholder="Optional description..."
            class="form-textarea"
            rows="3"
          ></textarea>
        </div>

        <div class="modal-actions">
          <button @click="showNewDialog = false" class="cancel-btn">Cancel</button>
          <button 
            @click="createNewPresentation"
            :disabled="!newPresentationName.trim()"
            class="create-btn"
          >
            Create
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useIndexedDBStorage } from '../composables/useIndexedDBStorage.js';
import { usePresentationSync } from '../composables/usePresentationSync.js';
import { usePresentationStore } from '../stores/presentationStore.js';

const emit = defineEmits(['presentation-loaded', 'presentation-created']);

const storage = useIndexedDBStorage();
const sync = usePresentationSync();
const presentationStore = usePresentationStore();

// State
const showImportDialog = ref(false);
const showNewDialog = ref(false);
const showCategoryDialog = ref(false);
const selectedFile = ref(null);
const isImporting = ref(false);
const newPresentationName = ref('');
const newPresentationDescription = ref('');
const newPresentationCategoryId = ref(null);
const presentations = ref([]);
const categories = ref([]);
const selectedCategoryId = ref(null);
const searchQuery = ref('');
const statusFilter = ref('');
const sortBy = ref('updated_at');
const currentPresentationId = ref(null);

const importOptions = ref({
  overwrite: false,
  syncToServer: true
});

const newCategory = ref({
  name: '',
  description: '',
  color: '#6b7280'
});

// Computed
const storageStats = computed(() => storage.storageStats);
const storageError = computed(() => storage.storageError);
const syncError = computed(() => sync.syncError);
const isOnline = computed(() => sync.isOnline);
const isSyncing = computed(() => sync.isSyncing);
const hasSyncQueue = computed(() => sync.hasSyncQueue);
const syncStatus = computed(() => sync.syncStatus);

const syncStatusClass = computed(() => {
  return {
    'sync-online': isOnline.value,
    'sync-offline': !isOnline.value,
    'sync-syncing': isSyncing.value,
    'sync-error': syncError.value
  };
});

const syncIcon = computed(() => {
  if (!isOnline.value) return '🔴';
  if (isSyncing.value) return '🔄';
  if (hasSyncQueue.value) return '⚠️';
  return '🟢';
});

const syncText = computed(() => {
  if (!isOnline.value) return 'Offline';
  if (isSyncing.value) return 'Syncing...';
  if (hasSyncQueue.value) return `Needs Sync (${sync.syncQueue.value.length} items)`;
  return 'Synced';
});

const filteredPresentations = computed(() => {
  let filtered = presentations.value;

  // Category filter
  if (selectedCategoryId.value !== null) {
    filtered = filtered.filter(p => p.categoryId === selectedCategoryId.value);
  }

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(p => 
      p.title.toLowerCase().includes(query) ||
      (p.description && p.description.toLowerCase().includes(query))
    );
  }

  // Status filter
  if (statusFilter.value) {
    filtered = filtered.filter(p => p.status === statusFilter.value);
  }

  // Sort
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'title':
        return a.title.localeCompare(b.title);
      case 'size':
        return (a.size || 0) - (b.size || 0);
      case 'created_at':
        return new Date(b.createdAt) - new Date(a.createdAt);
      case 'updated_at':
      default:
        return new Date(b.updatedAt) - new Date(a.updatedAt);
    }
  });

  return filtered;
});

// Methods
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const loadPresentation = async (id) => {
  try {
    const presentation = await storage.loadPresentation(id);
    presentationStore.loadPresentation(presentation);
    currentPresentationId.value = id;
    emit('presentation-loaded', presentation);
  } catch (error) {
    console.error('Error loading presentation:', error);
  }
};

const duplicatePresentation = async (presentation) => {
  try {
    const duplicated = await storage.savePresentation({
      ...presentation,
      title: presentation.title + ' (Copy)',
      id: undefined, // Force new ID generation
      serverId: undefined
    }, { overwrite: false, createBackup: false });
    
    await refreshPresentations();
  } catch (error) {
    console.error('Error duplicating presentation:', error);
  }
};

const exportPresentation = async (id) => {
  try {
    const presentation = await storage.loadPresentation(id);
    await storage.exportPresentation(presentation);
  } catch (error) {
    console.error('Error exporting presentation:', error);
  }
};

const deletePresentation = async (id) => {
  if (!confirm('Are you sure you want to delete this presentation?')) return;
  
  try {
    await storage.deletePresentation(id);
    await refreshPresentations();
  } catch (error) {
    console.error('Error deleting presentation:', error);
  }
};

const selectCategory = (categoryId) => {
  selectedCategoryId.value = categoryId === selectedCategoryId.value ? null : categoryId;
};

const handleSearch = () => {
  // Search is handled by computed property
};

const filterPresentations = () => {
  // Filter is handled by computed property
};

const sortPresentations = () => {
  // Sort is handled by computed property
};

const handleFileImport = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
  }
};

const executeImport = async () => {
  if (!selectedFile.value) return;
  
  isImporting.value = true;
  
  try {
    const text = await selectedFile.value.text();
    const result = await storage.importPresentation(text, importOptions.value);
    
    // Sync to server if requested and online
    if (importOptions.value.syncToServer && isOnline.value) {
      await sync.syncPresentationToServer(result);
    }
    
    await refreshPresentations();
    
    showImportDialog.value = false;
    selectedFile.value = null;
  } catch (error) {
    console.error('Error importing presentation:', error);
  } finally {
    isImporting.value = false;
  }
};

const createNewPresentation = async () => {
  if (!newPresentationName.value.trim()) return;
  
  try {
    const presentation = {
      title: newPresentationName.value.trim(),
      description: newPresentationDescription.value.trim(),
      categoryId: newPresentationCategoryId.value,
      slides: [
        {
          id: 'slide-1',
          elements: []
        }
      ],
      currentSlideIndex: 0,
      usePhases: false,
      hasInitializedPhases: false
    };
    
    const result = await storage.savePresentation(presentation, {
      name: presentation.title,
      overwrite: false,
      createBackup: false
    });
    
    // Sync to server if online
    if (isOnline.value) {
      await sync.syncPresentationToServer({
        ...presentation,
        id: result.id
      });
    }
    
    await refreshPresentations();
    
    showNewDialog.value = false;
    newPresentationName.value = '';
    newPresentationDescription.value = '';
    newPresentationCategoryId.value = null;
    
    emit('presentation-created', result);
  } catch (error) {
    console.error('Error creating presentation:', error);
  }
};

const createCategory = async () => {
  if (!newCategory.value.name.trim()) return;
  
  try {
    // For now, just add to local categories
    // In a real implementation, this would call the API
    const category = {
      id: Date.now(),
      name: newCategory.value.name.trim(),
      description: newCategory.value.description.trim(),
      color: newCategory.value.color,
      icon: 'folder',
      presentation_count: 0,
      is_system: false
    };
    
    categories.value.push(category);
    
    showCategoryDialog.value = false;
    newCategory.value = { name: '', description: '', color: '#6b7280' };
  } catch (error) {
    console.error('Error creating category:', error);
  }
};

const refreshPresentations = async () => {
  try {
    presentations.value = await storage.getAllPresentations();
    await storage.getStorageStats();
  } catch (error) {
    console.error('Error refreshing presentations:', error);
  }
};

const refreshCategories = async () => {
  try {
    categories.value = await sync.getCategories();
  } catch (error) {
    console.error('Error refreshing categories:', error);
  }
};

const forceSync = async () => {
  try {
    await sync.forceSync();
    await refreshPresentations();
  } catch (error) {
    console.error('Error forcing sync:', error);
  }
};

const clearErrors = () => {
  // Clear errors would be handled by the storage/sync composables
};

// Initialize
onMounted(async () => {
  await sync.initialize();
  await refreshPresentations();
  await refreshCategories();
  
  // Get current presentation
  try {
    const currentPresentation = await storage.getCurrentPresentation();
    if (currentPresentation) {
      currentPresentationId.value = currentPresentation.id;
    }
  } catch (error) {
    console.error('Error getting current presentation:', error);
  }
});

// Watch for online/offline changes
watch(isOnline, (isOnlineStatus) => {
  if (isOnlineStatus && hasSyncQueue.value) {
    forceSync();
  }
});
</script>

<style scoped>
.enhanced-presentation-manager {
  background: white;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e5e7eb;
  max-width: 1200px;
  margin: 0 auto;
}

.manager-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.header-left h3 {
  margin: 0 0 8px;
  font-size: 20px;
  font-weight: 600;
  color: #111827;
}

.sync-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.sync-status.sync-online {
  background: #dcfce7;
  color: #166534;
}

.sync-status.sync-offline {
  background: #fee2e2;
  color: #dc2626;
}

.sync-status.sync-syncing {
  background: #fef3c7;
  color: #92400e;
}

.sync-status.sync-error {
  background: #fee2e2;
  color: #dc2626;
}

.sync-btn {
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 4px 8px;
  font-size: 11px;
  cursor: pointer;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn.import {
  background: #10b981;
  color: white;
}

.action-btn.import:hover {
  background: #059669;
}

.action-btn.new {
  background: #3b82f6;
  color: white;
}

.action-btn.new:hover {
  background: #2563eb;
}

.categories-section {
  margin-bottom: 20px;
}

.categories-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.categories-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.category-add-btn {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 12px;
  cursor: pointer;
}

.categories-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.category-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border: 2px solid #e5e7eb;
  border-radius: 20px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 13px;
}

.category-btn:hover {
  border-color: #d1d5db;
  transform: translateY(-1px);
}

.category-btn.active {
  background: #eff6ff;
  border-color: #3b82f6;
}

.category-icon {
  font-size: 14px;
}

.category-name {
  font-weight: 500;
}

.category-count {
  color: #6b7280;
  font-size: 11px;
}

.search-section {
  margin-bottom: 20px;
}

.search-bar {
  display: flex;
  gap: 8px;
  align-items: center;
}

.search-input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.status-filter, .sort-filter {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
}

.storage-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  margin-bottom: 20px;
  padding: 16px;
  background: #f8fafc;
  border-radius: 8px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
}

.stat-value {
  font-weight: 600;
  color: #111827;
}

.storage-info {
  padding: 12px;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 6px;
  color: #0c4a6e;
  font-size: 12px;
  margin-bottom: 20px;
  text-align: center;
}

.storage-warning {
  padding: 12px;
  background: #fef3c7;
  border: 1px solid #f59e0b;
  border-radius: 6px;
  color: #92400e;
  font-size: 14px;
  margin-bottom: 20px;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.empty-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.empty-state h4 {
  margin: 0 0 8px;
  color: #374151;
}

.empty-state p {
  margin: 0 0 20px;
}

.primary-btn {
  padding: 10px 20px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.presentation-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 16px;
}

.presentation-card {
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  transition: all 0.2s;
  position: relative;
}

.presentation-card:hover {
  border-color: #d1d5db;
  transform: translateY(-1px);
}

.presentation-card.current {
  border-color: #3b82f6;
  background: #eff6ff;
}

.presentation-card.offline {
  border-color: #f59e0b;
}

.presentation-card.needsSync {
  border-color: #ef4444;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.card-title h4 {
  margin: 0 0 4px;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.card-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.badge {
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 10px;
  font-weight: 500;
}

.badge.offline {
  background: #fef3c7;
  color: #92400e;
}

.badge.sync-needed {
  background: #fee2e2;
  color: #dc2626;
}

.badge.category {
  border: 1px solid currentColor;
}

.card-actions {
  display: flex;
  gap: 4px;
}

.card-btn {
  padding: 4px 8px;
  border: none;
  border-radius: 4px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.card-btn.load {
  background: #3b82f6;
  color: white;
}

.card-btn.duplicate {
  background: #10b981;
  color: white;
}

.card-btn.export {
  background: #f59e0b;
  color: white;
}

.card-btn.delete {
  background: #ef4444;
  color: white;
}

.card-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
}

.detail-label {
  color: #6b7280;
}

.detail-value {
  color: #374151;
  font-weight: 500;
}

.current-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #3b82f6;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}

.error-message {
  padding: 12px;
  background: #fee2e2;
  border: 1px solid #ef4444;
  border-radius: 6px;
  color: #dc2626;
  margin-top: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.clear-btn {
  background: none;
  border: none;
  color: #dc2626;
  cursor: pointer;
  font-size: 16px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 24px;
  max-width: 500px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-content h3 {
  margin: 0 0 16px;
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.form-group {
  margin: 16px 0;
}

.form-group label {
  display: block;
  margin-bottom: 4px;
  font-weight: 500;
  color: #374151;
}

.form-input, .form-select, .form-textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-color {
  width: 60px;
  height: 40px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
}

.import-area {
  margin: 16px 0;
  text-align: center;
}

.file-select-btn {
  padding: 12px 24px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.file-name {
  margin-left: 12px;
  color: #374151;
}

.import-options {
  margin: 16px 0;
}

.import-options label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
  margin-bottom: 8px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 20px;
}

.cancel-btn {
  padding: 8px 16px;
  background: #f3f4f6;
  color: #374151;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.import-btn, .create-btn {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.import-btn:disabled, .create-btn:disabled {
  background: #d1d5db;
  cursor: not-allowed;
}
</style>
