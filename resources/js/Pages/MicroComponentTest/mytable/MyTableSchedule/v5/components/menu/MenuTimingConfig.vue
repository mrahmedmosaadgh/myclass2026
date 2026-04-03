<template>
  <div class="menu-timing">
    <h3 class="section-title">Timing Configuration</h3>
    <p class="section-desc">Edit period start/end times per stage and day.</p>

    <!-- Stage / Day Selector -->
    <div class="selector-row">
      <div class="selector-group">
        <label class="selector-label">Stage</label>
        <div class="selector-btns">
          <button
            v-for="s in stages"
            :key="s.id"
            class="sel-btn"
            :class="{ active: editStage === s.id }"
            @click="handleStageChange(s.id)"
          >{{ s.label }}</button>
        </div>
      </div>

      <div class="selector-group">
        <label class="selector-label">Day</label>
        <div class="selector-btns">
          <button
            class="sel-btn"
            :class="{ active: editDay === '' }"
            @click="handleDayChange('')"
          >All</button>
          <button
            v-for="d in days"
            :key="d.id"
            class="sel-btn"
            :class="{ active: editDay === d.id, 'has-custom': customDays.includes(d.id) }"
            @click="handleDayChange(d.id)"
          >{{ d.short }}</button>
        </div>
      </div>
    </div>

    <!-- Active Slots Editor -->
    <div class="slots-editor">
      <div class="slots-header">
        <span class="slots-title">{{ slotsTitle }}</span>
        <button class="action-sm" @click="addSlot">+ Add</button>
      </div>

      <div v-if="editableSlots.length === 0" class="empty-state">
        <p v-if="editDay">No custom timing for this day. Using stage default.</p>
        <p v-else>No stage default timing set. Click "Copy from default" to load the default schedule.</p>
        <button class="action-sm" @click="copyFromDefault">Copy from default</button>
      </div>

      <TransitionGroup name="slot-list" tag="div" class="slots-list">
        <div
          v-for="(slot, index) in editableSlots"
          :key="slot._key || index"
          class="slot-row"
        >
          <input
            v-model="slot.title"
            class="slot-input title"
            placeholder="Title"
            @change="onSlotChange"
          />
          <select v-model="slot.type" class="slot-input type" @change="onSlotChange">
            <option value="lesson">Lesson</option>
            <option value="break">Break</option>
            <option value="activity">Activity</option>
          </select>
          <input
            v-model="slot.start"
            type="time"
            class="slot-input time"
            @change="onSlotChange"
          />
          <input
            v-model="slot.end"
            type="time"
            class="slot-input time"
            @change="onSlotChange"
          />
          <button class="slot-remove" @click="removeSlot(index)">✕</button>
        </div>
      </TransitionGroup>
    </div>

    <!-- Actions -->
    <div class="timing-actions">
      <button class="btn-primary" @click="applyTimings" :disabled="editableSlots.length === 0">
        ✅ Apply
      </button>
      <button class="btn-secondary" @click="resetToDefault">
        ↩ Reset
      </button>
      <button class="btn-export" @click="exportStageData">
        📤 Export
      </button>
      <button class="btn-import" @click="showImportDialog = true">
        📥 Import
      </button>
    </div>

    <!-- Import Dialog -->
    <div v-if="showImportDialog" class="import-dialog-overlay" @click.self="showImportDialog = false">
      <div class="import-dialog">
        <div class="dialog-header">
          <h4>Import Stage Timings</h4>
          <button class="close-btn" @click="showImportDialog = false">✕</button>
        </div>
        
        <div class="import-steps">
          <!-- Step 1: File Upload -->
          <div class="step-section">
            <h5>1. Choose JSON File</h5>
            <div class="file-upload-area" :class="{ active: dragActive }" 
                 @drop="handleImportDrop" @dragover.prevent @dragenter.prevent @dragleave="dragActive = false">
              <input type="file" ref="importFileInput" @change="handleImportFileSelect" accept=".json" style="display: none;">
              <div class="upload-content">
                <div class="upload-icon">📁</div>
                <p class="upload-text">Drop JSON file here or <button class="link-btn" @click="$refs.importFileInput.click()">browse files</button></p>
                <p class="upload-hint">Supports stage timing JSON files</p>
              </div>
            </div>
          </div>

          <!-- Step 2: JSON Preview -->
          <div v-if="importJsonData" class="step-section">
            <h5>2. Preview & Validate</h5>
            <div class="json-preview">
              <div class="preview-header">
                <span class="preview-status" :class="importValidation.valid ? 'valid' : 'invalid'">
                  {{ importValidation.valid ? '✅ Valid JSON' : '❌ Invalid JSON' }}
                </span>
                <button class="copy-btn" @click="copyImportJson">📋 Copy</button>
              </div>
              <pre class="json-content">{{ formatJson(importJsonData) }}</pre>
            </div>
            <div v-if="!importValidation.valid" class="validation-error">
              ❌ {{ importValidation.error }}
            </div>
          </div>

          <!-- Step 3: Apply -->
          <div v-if="importValidation.valid" class="step-section">
            <h5>3. Apply Changes</h5>
            <div class="apply-actions">
              <button class="btn-primary" @click="applyImportData">
                📥 Import & Apply
              </button>
              <button class="btn-secondary" @click="showImportDialog = false">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="statusMessage" class="status-msg" :class="statusType">
      {{ statusMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();

const stages = [
  { id: 'prim', label: 'Primary' },
  { id: 'middle', label: 'Middle' },
  { id: 'sec', label: 'Secondary' }
];

const days = [
  { id: 'd1', short: 'D1' },
  { id: 'd2', short: 'D2' },
  { id: 'd3', short: 'D3' },
  { id: 'd4', short: 'D4' },
  { id: 'd5', short: 'D5' },
  { id: 'd6', short: 'D6' }
];

const editStage = ref(store.selectedStage.value || 'prim');
const editDay = ref('');
const editableSlots = ref([]);
const statusMessage = ref('');
const statusType = ref('success');

// Import/Export state
const showImportDialog = ref(false);
const dragActive = ref(false);
const importJsonData = ref('');
const importValidation = ref({ valid: false, error: '' });
const hasUnsavedChanges = ref(false);

let keyCounter = 0;

const clone = (v) => JSON.parse(JSON.stringify(v));

const customDays = computed(() => {
  const stageOverride = store.timingsConfig.value?.overrides?.[editStage.value]?.days || {};
  return Object.entries(stageOverride)
    .filter(([, v]) => Array.isArray(v) && v.length > 0)
    .map(([k]) => k);
});

const slotsTitle = computed(() => {
  const stageLabel = stages.find(s => s.id === editStage.value)?.label || editStage.value;
  if (editDay.value) {
    const dayLabel = days.find(d => d.id === editDay.value)?.short || editDay.value;
    return `${stageLabel} — ${dayLabel}`;
  }
  return `${stageLabel} — Default`;
});

const resolveCurrentSlots = () => {
  const config = store.timingsConfig.value;
  const stageOverride = config?.overrides?.[editStage.value];

  if (editDay.value && stageOverride?.days?.[editDay.value]) {
    return clone(stageOverride.days[editDay.value]);
  }

  if (!editDay.value && stageOverride?.default) {
    return clone(stageOverride.default);
  }

  // No default available - return empty
  return [];
};

const loadSlots = () => {
  if (hasUnsavedChanges.value) {
    if (!confirm('You have unsaved changes. Are you sure you want to continue? Your changes will be lost.')) {
      return; // Don't switch if user cancels
    }
  }
  const slots = resolveCurrentSlots();
  editableSlots.value = slots.map(s => ({ ...s, _key: ++keyCounter }));
  hasUnsavedChanges.value = false; // Reset after loading
};

watch([editStage, editDay], loadSlots, { immediate: true });

const addSlot = () => {
  const last = editableSlots.value[editableSlots.value.length - 1];
  editableSlots.value.push({
    _key: ++keyCounter,
    id: editableSlots.value.length + 1,
    title: `Period ${editableSlots.value.length + 1}`,
    type: 'lesson',
    start: last?.end || '09:00',
    end: last ? addMinutes(last.end, 30) : '09:30'
  });
  hasUnsavedChanges.value = true;
};

const addMinutes = (timeStr, mins) => {
  const [h, m] = timeStr.split(':').map(Number);
  const total = h * 60 + m + mins;
  const nh = Math.floor(total / 60) % 24;
  const nm = total % 60;
  return `${String(nh).padStart(2, '0')}:${String(nm).padStart(2, '0')}`;
};

const removeSlot = (index) => {
  editableSlots.value.splice(index, 1);
  hasUnsavedChanges.value = true;
};

const onSlotChange = () => {
  hasUnsavedChanges.value = true;
};

const copyFromDefault = async () => {
  let source = [];
  
  // Try to get stage default from config first
  const config = store.timingsConfig.value;
  if (config?.overrides?.[editStage.value]?.default) {
    source = config.overrides[editStage.value].default;
  } else {
    // Load from stage file if no default is set
    try {
      const stageTimings = await import(`../../data/timings/${editStage.value}.json`);
      source = stageTimings.default;
    } catch (e) {
      console.warn('Failed to load stage defaults:', e);
    }
  }
  
  editableSlots.value = clone(source).map(s => ({ ...s, _key: ++keyCounter }));
};

const applyTimings = async () => {
  const config = clone(store.timingsConfig.value);
  const slots = editableSlots.value.map(({ _key, ...rest }) => rest);

  if (!config.overrides) config.overrides = {};
  if (!config.overrides[editStage.value]) {
    config.overrides[editStage.value] = { default: null, days: { d1: null, d2: null, d3: null, d4: null, d5: null, d6: null } };
  }

  if (editDay.value) {
    config.overrides[editStage.value].days[editDay.value] = slots;
  } else {
    config.overrides[editStage.value].default = slots;
  }

  await store.setTimingsConfig(config);
  hasUnsavedChanges.value = false;
  statusMessage.value = 'Timing applied successfully!';
  statusType.value = 'success';
  setTimeout(() => { statusMessage.value = ''; }, 3000);
};

const resetToDefault = async () => {
  const config = clone(store.timingsConfig.value);

  if (!config.overrides?.[editStage.value]) return;

  if (editDay.value) {
    // Reset specific day to inherit stage default
    config.overrides[editStage.value].days[editDay.value] = null;
  } else {
    // Reset stage default - load from the stage file
    try {
      const stageTimings = await import(`../../data/timings/${editStage.value}.json`);
      config.overrides[editStage.value].default = stageTimings.default;
    } catch (e) {
      console.warn('Failed to load stage defaults:', e);
      config.overrides[editStage.value].default = null;
    }
  }

  await store.setTimingsConfig(config);
  loadSlots();
  hasUnsavedChanges.value = false;
  statusMessage.value = 'Reset to default.';
  statusType.value = 'info';
  setTimeout(() => { statusMessage.value = ''; }, 3000);
};

// Import/Export functions
const exportStageData = () => {
  const config = store.timingsConfig.value;
  const stageData = config?.overrides?.[editStage.value] || { default: null, days: {} };
  
  const exportData = {
    type: 'stage_timings',
    version: '5.0',
    stage: editStage.value,
    timestamp: new Date().toISOString(),
    data: stageData
  };
  
  const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `schedule-v5-${editStage.value}-timings-${new Date().toISOString().slice(0, 10)}.json`;
  a.click();
  URL.revokeObjectURL(url);
};

const handleImportFileSelect = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  readImportFile(file);
};

const handleImportDrop = (e) => {
  e.preventDefault();
  dragActive.value = false;
  const file = e.dataTransfer.files[0];
  if (!file || !file.name.endsWith('.json')) return;
  readImportFile(file);
};

const readImportFile = (file) => {
  const reader = new FileReader();
  reader.onload = (e) => {
    importJsonData.value = e.target.result;
    validateImportData();
  };
  reader.readAsText(file);
};

const validateImportData = () => {
  try {
    const json = JSON.parse(importJsonData.value);
    
    // Validate structure
    if (!json.data || typeof json.data !== 'object') {
      importValidation.value = { valid: false, error: 'Invalid file structure: missing data object' };
      return;
    }
    
    const data = json.data;
    if (data.default && !Array.isArray(data.default)) {
      importValidation.value = { valid: false, error: 'Invalid default timings: must be an array' };
      return;
    }
    
    if (data.days && typeof data.days !== 'object') {
      importValidation.value = { valid: false, error: 'Invalid days data: must be an object' };
      return;
    }
    
    importValidation.value = { valid: true, error: '' };
  } catch (e) {
    importValidation.value = { valid: false, error: `JSON parsing error: ${e.message}` };
  }
};

const formatJson = (jsonString) => {
  try {
    return JSON.stringify(JSON.parse(jsonString), null, 2);
  } catch {
    return jsonString;
  }
};

const copyImportJson = () => {
  navigator.clipboard.writeText(importJsonData.value).then(() => {
    // Show copy success message briefly
    const originalText = importJsonData.value;
    importJsonData.value = '✅ Copied to clipboard!';
    setTimeout(() => {
      importJsonData.value = originalText;
    }, 1000);
  });
};

const applyImportData = async () => {
  try {
    const json = JSON.parse(importJsonData.value);
    const stageData = json.data;
    
    // Update the current stage's timings
    const config = clone(store.timingsConfig.value);
    if (!config.overrides) config.overrides = {};
    if (!config.overrides[editStage.value]) {
      config.overrides[editStage.value] = { default: null, days: {} };
    }
    
    config.overrides[editStage.value] = stageData;
    await store.setTimingsConfig(config);
    
    // Reload current view
    loadSlots();
    hasUnsavedChanges.value = false;
    
    // Close dialog and show success message
    showImportDialog.value = false;
    statusMessage.value = 'Stage timings imported successfully!';
    statusType.value = 'success';
    setTimeout(() => { statusMessage.value = ''; }, 3000);
    
    // Reset import state
    importJsonData.value = '';
    importValidation.value = { valid: false, error: '' };
  } catch (e) {
    statusMessage.value = `Import failed: ${e.message}`;
    statusType.value = 'error';
    setTimeout(() => { statusMessage.value = ''; }, 4000);
  }
};
</script>

<style scoped>
.section-title {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #64748b;
  margin: 0 0 0.25rem 0;
}

.section-desc {
  color: #475569;
  font-size: 0.8rem;
  margin: 0 0 1rem 0;
}

.selector-row {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.selector-label {
  font-size: 0.7rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.25rem;
  display: block;
}

.selector-btns {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.sel-btn {
  padding: 0.35rem 0.6rem;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: transparent;
  color: #94a3b8;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  min-height: 32px;
  min-width: 32px;
}

.sel-btn:hover { background: rgba(255, 255, 255, 0.05); }
.sel-btn.active { background: rgba(59, 130, 246, 0.25); color: #60a5fa; border-color: rgba(59, 130, 246, 0.4); }
.sel-btn.has-custom { border-color: rgba(16, 185, 129, 0.4); }

.slots-editor {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 12px;
  padding: 0.75rem;
  margin-bottom: 1rem;
}

.slots-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.slots-title {
  font-weight: 600;
  font-size: 0.85rem;
}

.action-sm {
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  border: 1px solid rgba(59, 130, 246, 0.3);
  background: rgba(59, 130, 246, 0.1);
  color: #60a5fa;
  font-size: 0.75rem;
  cursor: pointer;
  min-height: 28px;
}

.empty-state {
  text-align: center;
  padding: 1rem;
  color: #64748b;
  font-size: 0.8rem;
}

.empty-state .action-sm { margin-top: 0.5rem; }

.slots-list {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.slot-row {
  display: grid;
  grid-template-columns: 1fr auto auto auto auto;
  gap: 0.25rem;
  align-items: center;
}

.slot-input {
  padding: 0.35rem 0.4rem;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  font-size: 0.75rem;
  min-height: 32px;
}

.slot-input.title { min-width: 80px; }
.slot-input.type { min-width: 70px; }
.slot-input.time { min-width: 70px; }

/* Import/Export Buttons */
.btn-export {
  background: #10b981;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background 0.2s;
}

.btn-export:hover {
  background: #059669;
}

.btn-import {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  transition: background 0.2s;
}

.btn-import:hover {
  background: #2563eb;
}

/* Import Dialog */
.import-dialog-overlay {
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

.import-dialog {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.dialog-header h4 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #6b7280;
  padding: 0.25rem;
  border-radius: 4px;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.import-steps {
  padding: 1.5rem;
}

.step-section {
  margin-bottom: 2rem;
}

.step-section:last-child {
  margin-bottom: 0;
}

.step-section h5 {
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
}

/* File Upload */
.file-upload-area {
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
}

.file-upload-area:hover,
.file-upload-area.active {
  border-color: #3b82f6;
  background: #f0f9ff;
}

.upload-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.upload-text {
  margin: 0.5rem 0;
  color: #374151;
}

.link-btn {
  background: none;
  border: none;
  color: #3b82f6;
  text-decoration: underline;
  cursor: pointer;
  font-size: inherit;
}

.upload-hint {
  margin: 0.5rem 0 0 0;
  font-size: 0.75rem;
  color: #6b7280;
}

/* JSON Preview */
.json-preview {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.preview-status.valid {
  color: #059669;
  font-weight: 500;
}

.preview-status.invalid {
  color: #dc2626;
  font-weight: 500;
}

.copy-btn {
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.75rem;
  transition: background 0.2s;
}

.copy-btn:hover {
  background: #e5e7eb;
}

.json-content {
  margin: 0;
  padding: 1rem;
  background: #1f2937;
  color: #f9fafb;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.75rem;
  line-height: 1.5;
  overflow-x: auto;
  max-height: 300px;
}

.validation-error {
  margin-top: 0.5rem;
  padding: 0.75rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 6px;
  color: #dc2626;
  font-size: 0.875rem;
}

/* Apply Actions */
.apply-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .import-dialog {
    background: #1f2937;
    color: #f9fafb;
  }
  
  .dialog-header {
    border-bottom-color: #374151;
  }
  
  .dialog-header h4 {
    color: #f9fafb;
  }
  
  .close-btn {
    color: #9ca3af;
  }
  
  .close-btn:hover {
    background: #374151;
    color: #f9fafb;
  }
  
  .step-section h5 {
    color: #f9fafb;
  }
  
  .file-upload-area {
    border-color: #4b5563;
    background: #111827;
  }
  
  .file-upload-area:hover,
  .file-upload-area.active {
    border-color: #3b82f6;
    background: #1e3a8a;
  }
  
  .upload-text {
    color: #f9fafb;
  }
  
  .upload-hint {
    color: #9ca3af;
  }
  
  .json-preview {
    border-color: #374151;
  }
  
  .preview-header {
    background: #111827;
    border-bottom-color: #374151;
  }
  
  .copy-btn {
    background: #374151;
    border-color: #4b5563;
    color: #f9fafb;
  }
  
  .copy-btn:hover {
    background: #4b5563;
  }
  
  .validation-error {
    background: #7f1d1d;
    border-color: #991b1b;
    color: #fca5a5;
  }
}

.slot-list-enter-active,
.slot-list-leave-active {
  transition: all 0.2s ease;
}
.slot-list-enter-from,
.slot-list-leave-to {
  opacity: 0;
  transform: translateX(-10px);
}

@media (max-width: 400px) {
  .slot-row {
    grid-template-columns: 1fr 1fr;
    gap: 0.2rem;
  }
  .slot-remove { grid-column: span 2; justify-self: end; }
}
</style>
