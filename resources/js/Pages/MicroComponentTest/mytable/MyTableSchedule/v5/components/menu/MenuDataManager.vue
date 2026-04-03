<template>
  <div class="menu-data-manager">
    <h3 class="section-title">Data Manager</h3>
    <p class="section-desc">Import, export, and backup your schedule data.</p>

    <!-- Export Section -->
    <div class="section">
      <h4 class="sub-section-title">Export</h4>
      <div class="export-grid">
        <button class="export-btn" @click="exportAll">
          <span class="export-icon">📦</span>
          <span class="export-label">Full Backup</span>
          <span class="export-desc">All data with timestamps</span>
        </button>
        <button class="export-btn" @click="exportTimings">
          <span class="export-icon">⏰</span>
          <span class="export-label">Timings Only</span>
          <span class="export-desc">Default + overrides</span>
        </button>
        <button class="export-btn" @click="exportSchedule">
          <span class="export-icon">📅</span>
          <span class="export-label">Schedule Only</span>
          <span class="export-desc">Personal schedule</span>
        </button>
        <button class="export-btn" @click="exportSchool">
          <span class="export-icon">🏫</span>
          <span class="export-label">School Timetable</span>
          <span class="export-desc">Teacher assignments</span>
        </button>
      </div>
    </div>

    <!-- Import Section -->
    <div class="section">
      <h4 class="sub-section-title">Import</h4>
      <div class="import-area">
        <textarea
          v-model="pasteJson"
          class="paste-textarea"
          placeholder="Paste JSON data here..."
          rows="4"
        ></textarea>
        <div class="paste-actions">
          <select v-model="importTarget" class="target-select">
            <option value="">Choose target...</option>
            <option value="full-backup">Full Backup</option>
            <option value="timings">Timings Only</option>
            <option value="schedule">Schedule Only</option>
            <option value="school">School Timetable</option>
          </select>
          <button class="btn-primary" @click="importPasted" :disabled="!importTarget || !pasteJson.trim()">
            📥 Import
          </button>
        </div>
      </div>

      <div v-if="importMessage" class="import-status" :class="importType">
        {{ importMessage }}
      </div>
    </div>

    <!-- File Upload -->
    <div class="section">
      <h4 class="sub-section-title">File Upload</h4>
      <div class="upload-area" :class="{ active: dragActive }" @drop="handleDrop" @dragover.prevent @dragenter.prevent @dragleave="dragActive = false">
        <input type="file" ref="fileInput" @change="handleFileSelect" accept=".json" style="display: none;">
        <div class="upload-prompt">
          <span class="upload-icon">📁</span>
          <p>Drop JSON file here or <button class="link-btn" @click="$refs.fileInput.click()">browse</button></p>
        </div>
      </div>
    </div>

    <!-- Storage Info -->
    <div class="section">
      <h4 class="sub-section-title">Storage</h4>
      <div class="storage-info" v-if="storageInfo">
        <div class="storage-row">
          <span class="storage-label">Database</span>
          <span class="storage-value">{{ storageInfo.dbName }} v{{ storageInfo.version }}</span>
        </div>
        <div v-for="(info, store) in storageInfo.stores" :key="store" class="storage-row">
          <span class="storage-label">{{ store }}</span>
          <span class="storage-value">{{ info.count }} items • {{ formatSize(info.sizeBytes) }}</span>
        </div>
      </div>
      <div class="storage-actions">
        <button class="btn-danger" @click="clearAllData">🗑 Clear All Data</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();

const pasteJson = ref('');
const importTarget = ref('');
const importMessage = ref('');
const importType = ref('success');
const dragActive = ref(false);
const storageInfo = ref(null);
const fileInput = ref(null);

const clone = (v) => JSON.parse(JSON.stringify(v));

const formatSize = (bytes) => {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const downloadJson = (data, filename) => {
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
};

const exportAll = async () => {
  const payload = await store.db.exportAll();
  downloadJson(payload, `schedule-v5-full-backup-${new Date().toISOString().slice(0, 10)}.json`);
};

const exportTimings = async () => {
  const config = await store.db.getTimingConfig();
  downloadJson({
    type: 'stage_day_timings',
    version: '5.0',
    timestamp: new Date().toISOString(),
    data: { mode: 'full_config', config }
  }, `schedule-v5-timings-${new Date().toISOString().slice(0, 10)}.json`);
};

const exportSchedule = async () => {
  const data = await store.db.getPersonalSchedule();
  downloadJson({
    type: 'personal_schedule',
    version: '5.0',
    timestamp: new Date().toISOString(),
    data
  }, `schedule-v5-personal-${new Date().toISOString().slice(0, 10)}.json`);
};

const exportSchool = async () => {
  const data = await store.db.getSchoolTimetable();
  downloadJson({
    type: 'school_timetable',
    version: '5.0',
    timestamp: new Date().toISOString(),
    data
  }, `schedule-v5-school-${new Date().toISOString().slice(0, 10)}.json`);
};

const importPasted = async () => {
  importMessage.value = '';
  try {
    const json = JSON.parse(pasteJson.value);
    if (importTarget.value === 'full-backup') {
      await store.db.importAll(json);
      await store.initialize(); // reload store
      importMessage.value = 'Full backup imported successfully!';
      importType.value = 'success';
    } else if (importTarget.value === 'timings') {
      const config = json.data?.config || json.data || json;
      await store.setTimingsConfig(config);
      importMessage.value = 'Timings imported successfully!';
      importType.value = 'success';
    } else if (importTarget.value === 'schedule') {
      const data = json.data || json;
      await store.setScheduleData(data.schedule || data);
      importMessage.value = 'Schedule imported successfully!';
      importType.value = 'success';
    } else if (importTarget.value === 'school') {
      const data = json.data || json;
      await store.setSchoolTimetable(data);
      importMessage.value = 'School timetable imported successfully!';
      importType.value = 'success';
    }
    pasteJson.value = '';
  } catch (e) {
    importMessage.value = `Import failed: ${e.message}`;
    importType.value = 'error';
  }
  setTimeout(() => { importMessage.value = ''; }, 4000);
};

const handleFileSelect = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (ev) => {
    pasteJson.value = ev.target.result;
  };
  reader.readAsText(file);
};

const handleDrop = (e) => {
  e.preventDefault();
  dragActive.value = false;
  const file = e.dataTransfer.files[0];
  if (!file || !file.name.endsWith('.json')) return;
  const reader = new FileReader();
  reader.onload = (ev) => {
    pasteJson.value = ev.target.result;
  };
  reader.readAsText(file);
};

const clearAllData = async () => {
  if (!confirm('Are you sure you want to delete ALL data? This cannot be undone.')) return;
  try {
    await store.db.clear('timings');
    await store.db.clear('personalSchedule');
    await store.db.clear('schoolTimetable');
    await store.db.clear('appSettings');
    await store.db.clear('syncQueue');
    await store.initialize();
    importMessage.value = 'All data cleared.';
    importType.value = 'info';
  } catch (e) {
    importMessage.value = `Clear failed: ${e.message}`;
    importType.value = 'error';
  }
  setTimeout(() => { importMessage.value = ''; }, 4000);
};

onMounted(async () => {
  storageInfo.value = await store.db.getStorageInfo();
});
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
  margin: 0 0 1.5rem 0;
}

.sub-section-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 0.75rem 0;
}

.section { margin-bottom: 2rem; }

.export-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 0.75rem;
}

.export-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.75rem 0.5rem;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  color: #e2e8f0;
  cursor: pointer;
  transition: all 0.2s;
  min-height: 44px;
}

.export-btn:hover {
  background: rgba(59, 130, 246, 0.15);
  border-color: rgba(59, 130, 246, 0.3);
}

.export-icon { font-size: 1.25rem; }
.export-label { font-weight: 600; font-size: 0.75rem; }
.export-desc { font-size: 0.6rem; color: #64748b; text-align: center; }

.import-area {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.paste-textarea {
  width: 100%;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  color: #e2e8f0;
  font-family: monospace;
  font-size: 0.75rem;
  padding: 0.75rem;
  resize: vertical;
}

.paste-actions {
  display: flex;
  gap: 0.5rem;
}

.target-select {
  flex: 1;
  padding: 0.5rem;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  font-size: 0.8rem;
}

.btn-primary {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: none;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  font-weight: 600;
  font-size: 0.8rem;
  cursor: pointer;
  min-height: 44px;
}

.btn-primary:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.import-status {
  margin-top: 0.5rem;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  font-size: 0.8rem;
  text-align: center;
}

.import-status.success { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.import-status.error { background: rgba(239, 68, 68, 0.2); color: #f87171; }
.import-status.info { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }

.upload-area {
  border: 2px dashed rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  transition: all 0.2s;
  cursor: pointer;
}

.upload-area.active {
  border-color: #60a5fa;
  background: rgba(59, 130, 246, 0.05);
}

.upload-icon { font-size: 2rem; margin-bottom: 0.5rem; }
.upload-prompt p { margin: 0; color: #94a3b8; font-size: 0.85rem; }
.link-btn {
  background: none;
  border: none;
  color: #60a5fa;
  text-decoration: underline;
  cursor: pointer;
  font-size: inherit;
}

.storage-info {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 10px;
  padding: 0.75rem;
  margin-bottom: 0.75rem;
}

.storage-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.25rem 0;
  font-size: 0.75rem;
}

.storage-label { color: #94a3b8; }
.storage-value { color: #e2e8f0; font-family: monospace; }

.storage-actions {
  display: flex;
  justify-content: flex-end;
}

.btn-danger {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid rgba(239, 68, 68, 0.3);
  background: rgba(239, 68, 68, 0.1);
  color: #f87171;
  font-weight: 600;
  font-size: 0.8rem;
  cursor: pointer;
  min-height: 44px;
}
</style>
