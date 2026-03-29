<template>
  <div class="data-manager">
    <div class="manager-header">
      <h3 class="manager-title">📁 Data Management</h3>
      <p class="manager-subtitle">Export and import your schedule data</p>
    </div>

    <!-- Export Section -->
    <div class="section">
      <h4 class="section-title">📤 Export Data</h4>
      
      <div class="export-options">
        <!-- Namecode Input -->
        <div class="namecode-input">
          <label class="input-label">Name Code (optional):</label>
          <input
            v-model="namecode"
            type="text"
            class="namecode-field"
            placeholder="e.g., backup_2026, school_primary"
            maxlength="50"
          />
          <small class="input-hint">Used in filename: datatype_namecode_timestamp.json</small>
        </div>

        <!-- Export Buttons -->
        <div class="export-buttons">
          <button
            @click="exportPersonal"
            :disabled="isExporting"
            class="export-btn personal"
          >
            <span class="btn-icon">👤</span>
            <span class="btn-text">Personal Schedule</span>
            <span class="btn-desc">Your classes and timings</span>
          </button>

          <button
            @click="exportSchool"
            :disabled="isExporting"
            class="export-btn school"
          >
            <span class="btn-icon">🏫</span>
            <span class="btn-text">School Timetable</span>
            <span class="btn-desc">All teachers and stages</span>
          </button>

          <button
            @click="exportSettings"
            :disabled="isExporting"
            class="export-btn settings"
          >
            <span class="btn-icon">⚙️</span>
            <span class="btn-text">App Settings</span>
            <span class="btn-desc">Preferences and config</span>
          </button>

          <button
            @click="exportAll"
            :disabled="isExporting"
            class="export-btn all"
          >
            <span class="btn-icon">📦</span>
            <span class="btn-text">Complete Backup</span>
            <span class="btn-desc">All data in 3 files</span>
          </button>
        </div>
      </div>

      <!-- Export Progress -->
      <div v-if="isExporting" class="progress-section">
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ width: `${exportProgress}%` }"
          ></div>
        </div>
        <p class="progress-text">Exporting... {{ exportProgress }}%</p>
      </div>

      <!-- Export Results -->
      <div v-if="exportResults.length > 0" class="results-section">
        <h5 class="results-title">✅ Export Complete</h5>
        <div class="results-list">
          <div
            v-for="result in exportResults"
            :key="result.filename"
            class="result-item"
          >
            <span class="result-icon">📄</span>
            <span class="result-filename">{{ result.filename }}</span>
            <span class="result-size">{{ formatFileSize(result.size) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Import Section -->
    <div class="section">
      <h4 class="section-title">📥 Import Data</h4>

      <div class="paste-import-panel">
        <div class="paste-header-row">
          <div>
            <h5 class="paste-title">Paste Data Directly</h5>
            <p class="paste-subtitle">Choose what the pasted JSON is for, then import it with validation.</p>
          </div>
        </div>

        <div class="import-target-grid">
          <button
            v-for="target in importTargets"
            :key="target.id"
            type="button"
            class="target-card"
            :class="{ active: pasteTarget === target.id }"
            @click="pasteTarget = target.id"
          >
            <span class="target-title">{{ target.label }}</span>
            <span class="target-desc">{{ target.description }}</span>
            <span class="target-example">{{ target.example }}</span>
          </button>
        </div>

        <label class="input-label" for="paste-json-input">Paste JSON</label>
        <textarea
          id="paste-json-input"
          v-model="pastedJson"
          class="paste-textarea"
          placeholder='Paste valid JSON here, for example: { "schedule": [], "timings": [] }'
          spellcheck="false"
        ></textarea>
        <small class="input-hint">Available options: personal schedule, school timetable, timing only, and app settings.</small>

        <div v-if="pasteValidationMessage" class="paste-validation" :class="{ error: pasteValidationState === 'error', success: pasteValidationState === 'success' }">
          {{ pasteValidationMessage }}
        </div>

        <div class="paste-actions">
          <button
            type="button"
            class="browse-btn secondary"
            @click="validatePastedJson"
          >
            Validate Paste
          </button>
          <button
            type="button"
            class="import-btn"
            :disabled="isImporting || !pasteTarget || !pastedJson.trim()"
            @click="importPastedData"
          >
            <span v-if="!isImporting">Paste & Import</span>
            <span v-else>Importing... {{ importProgress }}%</span>
          </button>
        </div>
      </div>
      
      <div class="import-area" :class="{ 'drag-over': isDragOver }" @drop="handleDrop" @dragover.prevent @dragleave="isDragOver = false" @dragenter.prevent="isDragOver = true">
        <div class="import-content">
          <div class="import-icon">📁</div>
          <p class="import-text">Drop JSON files here or click to browse</p>
          <p class="import-hint">Supports: personal_schedule, school_timetable, stage_day_timings, app_settings</p>
          
          <input
            ref="fileInput"
            type="file"
            accept=".json"
            multiple
            @change="handleFileSelect"
            class="file-input"
          />
          
          <button @click="openFilePicker" class="browse-btn">
            Browse Files
          </button>
        </div>
      </div>

      <!-- Selected Files -->
      <div v-if="selectedFiles.length > 0" class="selected-files">
        <h5 class="files-title">Selected Files:</h5>
        <div class="files-list">
          <div
            v-for="(file, index) in selectedFiles"
            :key="index"
            class="file-item"
          >
            <span class="file-icon">📄</span>
            <span class="file-name">{{ file.name }}</span>
            <span class="file-size">{{ formatFileSize(file.size) }}</span>
            <button @click="removeFile(index)" class="remove-btn">×</button>
          </div>
        </div>
        
        <button
          @click="importSelectedFiles"
          :disabled="isImporting || selectedFiles.length === 0"
          class="import-btn"
        >
          <span v-if="!isImporting">📥 Import {{ selectedFiles.length }} File(s)</span>
          <span v-else>Importing... {{ importProgress }}%</span>
        </button>
      </div>

      <!-- Import Progress -->
      <div v-if="isImporting" class="progress-section">
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ width: `${importProgress}%` }"
          ></div>
        </div>
        <p class="progress-text">Importing... {{ importProgress }}%</p>
      </div>

      <!-- Import Results -->
      <div v-if="importResults.length > 0" class="results-section">
        <h5 class="results-title">📋 Import Results</h5>
        <div class="results-list">
          <div
            v-for="result in importResults"
            :key="result.filename"
            class="result-item"
            :class="{ 'error': !result.success }"
          >
            <span class="result-icon">{{ result.success ? '✅' : '❌' }}</span>
            <span class="result-filename">{{ result.filename }}</span>
            <span class="result-message">{{ result.message }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <button @click="clearAllData" class="action-btn danger">
        🗑️ Clear All Local Data
      </button>
      <button @click="resetToDefaults" class="action-btn warning">
        🔄 Reset to Defaults
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useDataImportExport } from '../composables/useDataImportExport.js';

const emit = defineEmits(['notification']);

// Import/Export composable
const {
  isImporting,
  isExporting,
  importProgress,
  exportProgress,
  exportPersonalSchedule,
  exportSchoolTimetable,
  exportAllData,
  exportAppSettings,
  importFromFile,
  importFromText,
  importTargets
} = useDataImportExport();

// Local state
const namecode = ref('');
const selectedFiles = ref([]);
const exportResults = ref([]);
const importResults = ref([]);
const isDragOver = ref(false);
const fileInput = ref(null);
const pasteTarget = ref('personal_schedule');
const pastedJson = ref('');
const pasteValidationMessage = ref('');
const pasteValidationState = ref('');

const openFilePicker = () => {
  fileInput.value?.click();
};

// Methods
const exportPersonal = async () => {
  const result = await exportPersonalSchedule(namecode.value);
  handleExportResult(result, 'Personal Schedule');
};

const exportSchool = async () => {
  const result = await exportSchoolTimetable(namecode.value);
  handleExportResult(result, 'School Timetable');
};

const exportSettings = async () => {
  const result = await exportAppSettings(namecode.value);
  handleExportResult(result, 'App Settings');
};

const exportAll = async () => {
  const result = await exportAllData(namecode.value);
  if (result.success) {
    exportResults.value = result.files.map(file => ({
      filename: file.filename,
      size: file.size
    }));
    emit('notification', 'Export Complete', 'All data exported successfully');
  } else {
    emit('notification', 'Export Failed', result.error);
  }
};

const handleExportResult = (result, dataType) => {
  if (result.success) {
    exportResults.value = [{
      filename: result.filename,
      size: result.size
    }];
    emit('notification', 'Export Complete', `${dataType} exported as ${result.filename}`);
  } else {
    emit('notification', 'Export Failed', result.error);
  }
};

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  selectedFiles.value = [...selectedFiles.value, ...files];
  event.target.value = ''; // Reset input
};

const handleDrop = (event) => {
  event.preventDefault();
  isDragOver.value = false;
  
  const files = Array.from(event.dataTransfer.files).filter(file => 
    file.type === 'application/json' || file.name.endsWith('.json')
  );
  
  selectedFiles.value = [...selectedFiles.value, ...files];
};

const removeFile = (index) => {
  selectedFiles.value.splice(index, 1);
};

const importSelectedFiles = async () => {
  importResults.value = [];
  
  for (const file of selectedFiles.value) {
    const result = await importFromFile(file);
    importResults.value.push({
      filename: file.name,
      success: result.success,
      message: result.message || (result.success ? 'Import successful' : result.error)
    });
    
    if (result.success) {
      emit('notification', 'Import Success', `${file.name} imported successfully`);
    } else {
      emit('notification', 'Import Failed', `${file.name}: ${result.error}`);
    }
  }
  
  selectedFiles.value = [];
};

const validatePastedJson = async () => {
  pasteValidationMessage.value = '';
  pasteValidationState.value = '';

  if (!pasteTarget.value) {
    pasteValidationState.value = 'error';
    pasteValidationMessage.value = 'Choose what this pasted data is for first.';
    return;
  }

  if (!pastedJson.value.trim()) {
    pasteValidationState.value = 'error';
    pasteValidationMessage.value = 'Paste JSON data before validating.';
    return;
  }

  const result = await importFromText(pastedJson.value, pasteTarget.value, { validateOnly: true });
  if (result.success) {
    pasteValidationState.value = 'success';
    pasteValidationMessage.value = `Valid ${importTargets.find(target => target.id === pasteTarget.value)?.label || 'data'} JSON. Ready to import.`;
  } else {
    pasteValidationState.value = 'error';
    pasteValidationMessage.value = result.error;
  }
};

const importPastedData = async () => {
  importResults.value = [];
  pasteValidationMessage.value = '';
  pasteValidationState.value = '';

  const result = await importFromText(pastedJson.value, pasteTarget.value);
  importResults.value.push({
    filename: `Pasted ${pasteTarget.value}`,
    success: result.success,
    message: result.message || result.error
  });

  if (result.success) {
    pasteValidationState.value = 'success';
    pasteValidationMessage.value = result.message || 'Pasted JSON imported successfully.';
    emit('notification', 'Import Success', result.message || 'Pasted JSON imported successfully');
    pastedJson.value = '';
  } else {
    pasteValidationState.value = 'error';
    pasteValidationMessage.value = result.error || 'Import failed';
    emit('notification', 'Import Failed', result.error || 'Import failed');
  }
};

const clearAllData = () => {
  if (confirm('Are you sure you want to clear all local data? This action cannot be undone.')) {
    // Clear relevant localStorage items
    const keysToRemove = [
      'schedule-app-view-mode',
      'schedule-show-all-days',
      'notifications-enabled',
      'school-timings-v2',
      'imported-personal-schedule',
      'imported-personal-timings',
      'imported-school-timetable'
    ];
    
    keysToRemove.forEach(key => localStorage.removeItem(key));
    
    emit('notification', 'Data Cleared', 'All local data has been removed');
  }
};

const resetToDefaults = () => {
  if (confirm('Reset all settings to defaults? This will clear customizations.')) {
    // Reset to default view mode
    localStorage.setItem('schedule-app-view-mode', 'card');
    localStorage.setItem('schedule-show-all-days', 'true');
    
    emit('notification', 'Reset Complete', 'Settings have been reset to defaults');
  }
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '~1KB';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

</script>

<style scoped>
.data-manager {
  max-width: 600px;
  margin: 0 auto;
  padding: 1.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.manager-header {
  text-align: center;
  margin-bottom: 2rem;
}

.manager-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.manager-subtitle {
  color: #475569;
  font-size: 0.875rem;
  margin: 0;
}

.section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.namecode-input {
  margin-bottom: 1.5rem;
}

.input-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.5rem;
}

.namecode-field {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.875rem;
  transition: border-color 0.3s ease;
}

.namecode-field:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-hint {
  display: block;
  font-size: 0.75rem;
  color: #475569;
  margin-top: 0.25rem;
}

.export-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.export-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.export-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.export-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.export-btn.personal {
  border-color: #3b82f6;
  background: #f0f9ff;
}

.export-btn.personal:hover:not(:disabled) {
  border-color: #2563eb;
  background: #e0f2fe;
}

.export-btn.school {
  border-color: #10b981;
  background: #f0fdf4;
}

.export-btn.school:hover:not(:disabled) {
  border-color: #059669;
  background: #dcfce7;
}

.export-btn.settings {
  border-color: #f59e0b;
  background: #fffbeb;
}

.export-btn.settings:hover:not(:disabled) {
  border-color: #d97706;
  background: #fef3c7;
}

.export-btn.all {
  border-color: #8b5cf6;
  background: #f5f3ff;
}

.export-btn.all:hover:not(:disabled) {
  border-color: #7c3aed;
  background: #ede9fe;
}

.btn-icon {
  font-size: 2rem;
}

.btn-text {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.875rem;
}

.btn-desc {
  font-size: 0.75rem;
  color: #475569;
}

.import-area {
  border: 2px dashed #cbd5e1;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
  background: #f8fafc;
}

.paste-import-panel {
  margin-bottom: 1.5rem;
  padding: 1rem;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #f8fafc;
}

.paste-header-row {
  margin-bottom: 1rem;
}

.paste-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
}

.paste-subtitle {
  margin: 0.35rem 0 0;
  font-size: 0.85rem;
  color: #475569;
}

.import-target-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.target-card {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.35rem;
  padding: 0.9rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  background: #ffffff;
  text-align: left;
  cursor: pointer;
}

.target-card.active {
  border-color: #3b82f6;
  background: #eff6ff;
}

.target-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1e293b;
}

.target-desc {
  font-size: 0.78rem;
  color: #475569;
}

.target-example {
  font-size: 0.72rem;
  color: #64748b;
}

.paste-textarea {
  width: 100%;
  min-height: 180px;
  padding: 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  background: #ffffff;
  color: #1e293b;
  font-size: 0.85rem;
  line-height: 1.5;
  resize: vertical;
}

.paste-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.paste-validation {
  margin-top: 0.75rem;
  padding: 0.75rem 0.9rem;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 600;
  background: #ecfdf5;
  color: #047857;
  border: 1px solid #a7f3d0;
}

.paste-validation.error {
  background: #fef2f2;
  color: #b91c1c;
  border-color: #fecaca;
}

.paste-validation.success {
  background: #ecfdf5;
  color: #047857;
  border-color: #a7f3d0;
}

.paste-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
}

.browse-btn.secondary {
  background: #ffffff;
  color: #334155;
  border: 1px solid #cbd5e1;
}

.browse-btn.secondary:hover {
  background: #f8fafc;
}

.import-area:hover,
.import-area.drag-over {
  border-color: #3b82f6;
  background: #f0f9ff;
}

.import-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.import-icon {
  font-size: 3rem;
  opacity: 0.85;
}

.import-text {
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.import-hint {
  font-size: 0.875rem;
  color: #475569;
  margin: 0;
}

.file-input {
  display: none;
}

.browse-btn {
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.browse-btn:hover {
  background: #2563eb;
}

.selected-files {
  margin-top: 1.5rem;
}

.files-title {
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1rem 0;
}

.files-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.file-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
}

.file-icon {
  font-size: 1.25rem;
}

.file-name {
  flex: 1;
  font-weight: 500;
  color: #1e293b;
}

.file-size {
  font-size: 0.75rem;
  color: #475569;
}

.remove-btn {
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s ease;
}

.remove-btn:hover {
  background: #dc2626;
}

.import-btn {
  width: 100%;
  padding: 1rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.import-btn:hover:not(:disabled) {
  background: #059669;
}

.import-btn:disabled {
  background: #64748b;
  cursor: not-allowed;
}

.progress-section {
  margin: 1.5rem 0;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #2563eb);
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.875rem;
  color: #475569;
  text-align: center;
  margin: 0;
}

.results-section {
  margin: 1.5rem 0;
}

.results-title {
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1rem 0;
}

.results-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.result-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f0fdf4;
  border: 1px solid #dcfce7;
  border-radius: 8px;
}

.result-item.error {
  background: #fef2f2;
  border-color: #fecaca;
}

.result-icon {
  font-size: 1.25rem;
}

.result-filename {
  flex: 1;
  font-weight: 500;
  color: #1e293b;
}

.result-size,
.result-message {
  font-size: 0.75rem;
  color: #475569;
}

.quick-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e2e8f0;
}

.action-btn {
  flex: 1;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-btn.danger {
  background: #ef4444;
  color: white;
}

.action-btn.danger:hover {
  background: #dc2626;
}

.action-btn.warning {
  background: #f59e0b;
  color: white;
}

.action-btn.warning:hover {
  background: #d97706;
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .data-manager {
    padding: 1rem;
  }

  .import-target-grid {
    grid-template-columns: 1fr;
  }

  .paste-actions {
    flex-direction: column;
  }
  
  .export-buttons {
    grid-template-columns: 1fr;
  }
  
  .quick-actions {
    flex-direction: column;
  }
  
  .import-area {
    padding: 1.5rem;
  }
}
</style>
