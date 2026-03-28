<template>
  <div class="presentation-manager">
    <div class="manager-header">
      <h3>📁 My Presentations</h3>
      <div class="header-actions">
        <button @click="showImportDialog = true" class="action-btn import">
          📥 Import
        </button>
        <button @click="showNewDialog = true" class="action-btn new">
          ➕ New
        </button>
      </div>
    </div>

    <!-- Storage Stats -->
    <div class="storage-stats">
      <div class="stat-item">
        <span class="stat-label">Storage Used:</span>
        <span class="stat-value">{{ formatBytes(storageStats.used) }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Available:</span>
        <span class="stat-value">{{ formatBytes(storageStats.available) }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Presentations:</span>
        <span class="stat-value">{{ storageStats.presentationsCount }} / {{ STORAGE_CONFIG.MAX_PRESENTATIONS_COUNT }}</span>
      </div>
    </div>

    <!-- Storage Warning -->
    <div v-if="storageStats.available < 1024 * 1024" class="storage-warning">
      ⚠️ Low storage space! Consider deleting old presentations or exporting them.
    </div>

    <!-- Presentations List -->
    <div class="presentations-list">
      <div v-if="presentations.length === 0" class="empty-state">
        <div class="empty-icon">📂</div>
        <h4>No presentations yet</h4>
        <p>Create your first presentation or import an existing one.</p>
        <button @click="showNewDialog = true" class="primary-btn">
          Create Presentation
        </button>
      </div>

      <div v-else class="presentation-cards">
        <div 
          v-for="presentation in presentations" 
          :key="presentation.key"
          class="presentation-card"
          :class="{ current: presentation.key === currentPresentationKey }"
        >
          <div class="card-header">
            <h4>{{ presentation.name }}</h4>
            <div class="card-actions">
              <button @click="loadPresentation(presentation.key)" class="card-btn load">
                📂 Open
              </button>
              <button @click="exportPresentation(presentation.key)" class="card-btn export">
                📤 Export
              </button>
              <button @click="deletePresentation(presentation.key)" class="card-btn delete">
                🗑️
              </button>
            </div>
          </div>
          
          <div class="card-details">
            <div class="detail-item">
              <span class="detail-label">Size:</span>
              <span class="detail-value">{{ formatBytes(presentation.size) }}</span>
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

          <div v-if="presentation.key === currentPresentationKey" class="current-badge">
            📍 Currently Open
          </div>
        </div>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="storageError" class="error-message">
      ❌ {{ storageError }}
      <button @click="clearError" class="clear-btn">✕</button>
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
import { ref, computed, onMounted } from 'vue';
import { useOfflineStorage } from '../composables/useOfflineStorage.js';
import { usePresentationStore } from '../stores/presentationStore.js';

const emit = defineEmits(['presentation-loaded', 'presentation-created']);

const storage = useOfflineStorage();
const presentationStore = usePresentationStore();

// State
const showImportDialog = ref(false);
const showNewDialog = ref(false);
const selectedFile = ref(null);
const isImporting = ref(false);
const newPresentationName = ref('');

const importOptions = ref({
  overwrite: false
});

// Computed
const presentations = computed(() => storage.getAllPresentations());
const storageStats = computed(() => storage.storageStats);
const storageError = computed(() => storage.storageError);
const currentPresentationKey = computed(() => {
  return localStorage.getItem('cr_v5_current_presentation');
});

// Methods
const formatBytes = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const loadPresentation = async (key) => {
  try {
    const presentation = storage.loadPresentation(key);
    presentationStore.loadPresentation(presentation);
    emit('presentation-loaded', presentation);
  } catch (error) {
    console.error('Error loading presentation:', error);
  }
};

const exportPresentation = async (key) => {
  try {
    const presentation = storage.loadPresentation(key);
    storage.exportPresentation(presentation);
  } catch (error) {
    console.error('Error exporting presentation:', error);
  }
};

const deletePresentation = async (key) => {
  if (!confirm('Are you sure you want to delete this presentation?')) return;
  
  try {
    await storage.deletePresentation(key);
  } catch (error) {
    console.error('Error deleting presentation:', error);
  }
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
    
    // Load the imported presentation
    await loadPresentation(result.key);
    
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
    const newPresentation = {
      title: newPresentationName.value.trim(),
      slides: [
        {
          id: 'slide-1',
          elements: []
        }
      ],
      currentSlideIndex: 0
    };
    
    const result = await storage.savePresentation(newPresentation, {
      name: newPresentationName.value.trim()
    });
    
    // Load the new presentation
    await loadPresentation(result.key);
    
    showNewDialog.value = false;
    newPresentationName.value = '';
    
    emit('presentation-created', result);
  } catch (error) {
    console.error('Error creating presentation:', error);
  }
};

const clearError = () => {
  storageError.value = null;
};

// Initialize
onMounted(() => {
  storage.getStorageStats();
});
</script>

<style scoped>
.presentation-manager {
  background: white;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e5e7eb;
  max-width: 800px;
  margin: 0 auto;
}

.manager-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.manager-header h3 {
  margin: 0;
  font-size: 20px;
  font-weight: 600;
  color: #111827;
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
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.card-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
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

.card-btn.export {
  background: #10b981;
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

.form-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
