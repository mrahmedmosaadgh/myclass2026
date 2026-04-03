<template>
  <div class="menu-data-manager">
    <h3 class="section-title">Data Manager</h3>
    <p class="section-desc">Import, export, and backup your schedule data.</p>

    <!-- Tab Navigation -->
    <div class="tab-nav">
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'export' }"
        @click="activeTab = 'export'"
      >
        <span class="tab-icon">📤</span>
        <span>Export</span>
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'import' }"
        @click="activeTab = 'import'"
      >
        <span class="tab-icon">📥</span>
        <span>Import</span>
      </button>
      <button 
        class="tab-btn" 
        :class="{ active: activeTab === 'storage' }"
        @click="activeTab = 'storage'"
      >
        <span class="tab-icon">�</span>
        <span>Storage</span>
      </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Export Tab -->
      <div v-if="activeTab === 'export'" class="tab-panel">
        <div class="export-section">
          <h4 class="panel-title">Stage Timings</h4>
          <div class="export-grid">
            <button class="export-card" @click="exportStageTimings('prim')">
              <div class="card-icon">🎓</div>
              <div class="card-content">
                <h5>Primary Timings</h5>
                <p>Primary stage schedule</p>
              </div>
              <div class="card-action">Export</div>
            </button>
            <button class="export-card" @click="exportStageTimings('middle')">
              <div class="card-icon">📚</div>
              <div class="card-content">
                <h5>Middle Timings</h5>
                <p>Middle stage schedule</p>
              </div>
              <div class="card-action">Export</div>
            </button>
            <button class="export-card" @click="exportStageTimings('sec')">
              <div class="card-icon">🎓</div>
              <div class="card-content">
                <h5>Secondary Timings</h5>
                <p>Secondary stage schedule</p>
              </div>
              <div class="card-action">Export</div>
            </button>
            <button class="export-card" @click="exportTimings">
              <div class="card-icon">📦</div>
              <div class="card-content">
                <h5>All Timings</h5>
                <p>Full timing configuration</p>
              </div>
              <div class="card-action">Export</div>
            </button>
          </div>
        </div>
        
        <div class="export-section">
          <h4 class="panel-title">Other Data</h4>
          <div class="export-grid">
            <button class="export-card" @click="exportSchedule">
              <div class="card-icon">📅</div>
              <div class="card-content">
                <h5>Personal Schedule</h5>
                <p>Your weekly schedule</p>
              </div>
              <div class="card-action">Export</div>
            </button>
            <button class="export-card" @click="exportSchool">
              <div class="card-icon">🏫</div>
              <div class="card-content">
                <h5>School Timetable</h5>
                <p>Teacher assignments</p>
              </div>
              <div class="card-action">Export</div>
            </button>
            <button class="export-card" @click="exportAll">
              <div class="card-icon">💾</div>
              <div class="card-content">
                <h5>Full Backup</h5>
                <p>All data with timestamps</p>
              </div>
              <div class="card-action">Export</div>
            </button>
          </div>
        </div>
      </div>

      <!-- Import Tab -->
      <div v-if="activeTab === 'import'" class="tab-panel">
        <div class="import-section">
          <h4 class="panel-title">Paste JSON Data</h4>
          <div class="paste-area">
            <textarea
              v-model="pasteJson"
              class="paste-textarea"
              placeholder="Paste your JSON data here..."
              rows="6"
            ></textarea>
            <div class="paste-controls">
              <select v-model="importTarget" class="target-select">
                <optgroup label="Stage Timings">
                  <option value="prim-timings">Primary Timings</option>
                  <option value="middle-timings">Middle Timings</option>
                  <option value="sec-timings">Secondary Timings</option>
                </optgroup>
                <optgroup label="Other Data">
                  <option value="timings">All Timings</option>
                  <option value="schedule">Personal Schedule</option>
                  <option value="school">School Timetable</option>
                  <option value="full-backup">Full Backup</option>
                </optgroup>
              </select>
              <button 
                class="btn-primary" 
                @click="importPasted" 
                :disabled="!importTarget || !pasteJson.trim()"
              >
                <span class="btn-icon">📥</span>
                Import Data
              </button>
            </div>
          </div>

          <div v-if="importMessage" class="import-status" :class="importType">
            <span class="status-icon">{{ importType === 'success' ? '✅' : '❌' }}</span>
            {{ importMessage }}
          </div>
        </div>

        <div class="import-section">
          <h4 class="panel-title">Upload File</h4>
          <div class="upload-area" :class="{ active: dragActive }" @drop="handleDrop" @dragover.prevent @dragenter.prevent @dragleave="dragActive = false">
            <input type="file" ref="fileInput" @change="handleFileSelect" accept=".json" style="display: none;">
            <div class="upload-content">
              <div class="upload-icon">📁</div>
              <p class="upload-text">Drop JSON file here or <button class="link-btn" @click="$refs.fileInput.click()">browse files</button></p>
              <p class="upload-hint">Supports .json files up to 5MB</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Storage Tab -->
      <div v-if="activeTab === 'storage'" class="tab-panel">
        <div class="storage-section">
          <h4 class="panel-title">Database Information</h4>
          <div class="storage-info" v-if="storageInfo">
            <div class="info-card">
              <div class="info-header">
                <span class="info-icon">🗄️</span>
                <span class="info-title">{{ storageInfo.dbName }} v{{ storageInfo.version }}</span>
              </div>
              <div class="info-body">
                <div v-for="(info, store) in storageInfo.stores" :key="store" class="storage-row">
                  <span class="store-name">{{ formatStoreName(store) }}</span>
                  <span class="store-stats">{{ info.count }} items • {{ formatSize(info.sizeBytes) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="storage-section">
          <h4 class="panel-title">Storage Actions</h4>
          <div class="action-grid">
            <button class="action-card danger" @click="clearAllData">
              <div class="action-icon">🗑️</div>
              <div class="action-content">
                <h5>Clear All Data</h5>
                <p>Delete everything from local storage</p>
              </div>
            </button>
            <button class="action-card" @click="exportAll">
              <div class="action-icon">💾</div>
              <div class="action-content">
                <h5>Backup Now</h5>
                <p>Create full backup before clearing</p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();

// Tab management
const activeTab = ref('export');

const pasteJson = ref('');
const importTarget = ref('');
const importMessage = ref('');
const importType = ref('info');
const dragActive = ref(false);
const fileInput = ref(null);
const storageInfo = ref(null);

const clone = (v) => JSON.parse(JSON.stringify(v));

const formatSize = (bytes) => {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const formatStoreName = (store) => {
  const names = {
    'timings': 'Timings',
    'personalSchedule': 'Personal Schedule',
    'schoolTimetable': 'School Timetable',
    'appSettings': 'App Settings',
    'syncQueue': 'Sync Queue'
  };
  return names[store] || store;
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

const exportStageTimings = async (stage) => {
  const config = await store.db.getTimingConfig();
  const stageData = config?.overrides?.[stage] || { default: null, days: {} };
  
  downloadJson({
    type: 'stage_timings',
    version: '5.0',
    stage: stage,
    timestamp: new Date().toISOString(),
    data: stageData
  }, `schedule-v5-${stage}-timings-${new Date().toISOString().slice(0, 10)}.json`);
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
    } else if (importTarget.value === 'prim-timings') {
      await importStageTimings('prim', json);
      importMessage.value = 'Primary timings imported successfully!';
      importType.value = 'success';
    } else if (importTarget.value === 'middle-timings') {
      await importStageTimings('middle', json);
      importMessage.value = 'Middle timings imported successfully!';
      importType.value = 'success';
    } else if (importTarget.value === 'sec-timings') {
      await importStageTimings('sec', json);
      importMessage.value = 'Secondary timings imported successfully!';
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

const importStageTimings = async (stage, json) => {
  const config = await store.db.getTimingConfig();
  const stageData = json.data || json;
  
  // Update only the specified stage's timings
  const updatedConfig = {
    ...config,
    overrides: {
      ...config.overrides,
      [stage]: stageData
    }
  };
  
  await store.setTimingsConfig(updatedConfig);
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

/* Tab Navigation */
.tab-nav {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 0;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  border-radius: 8px 8px 0 0;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.tab-btn:hover {
  color: #475569;
  background: rgba(148, 163, 184, 0.1);
}

.tab-btn.active {
  color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);
  border-bottom: 2px solid #3b82f6;
  margin-bottom: -1px;
}

.tab-icon {
  font-size: 1rem;
}

/* Tab Content */
.tab-content {
  min-height: 300px;
}

.tab-panel {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.panel-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 1rem 0;
}

/* Export Section */
.export-section {
  margin-bottom: 2rem;
}

.export-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.export-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.5rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.export-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
  transform: translateY(-2px);
}

.card-icon {
  font-size: 2rem;
  margin-bottom: 0.75rem;
}

.card-content h5 {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.card-content p {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0;
}

.card-action {
  margin-top: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #3b82f6;
}

/* Import Section */
.import-section {
  margin-bottom: 2rem;
}

.paste-area {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
}

.paste-textarea {
  width: 100%;
  min-height: 100px;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-family: 'Monaco', 'Menlo', monospace;
  font-size: 0.75rem;
  resize: vertical;
}

.paste-controls {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
  align-items: center;
}

.target-select {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.8rem;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.import-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 6px;
  font-size: 0.8rem;
  margin-top: 1rem;
}

.import-status.success {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.import-status.error {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

/* Upload Area */
.upload-area {
  border: 2px dashed #e2e8f0;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.upload-area.active {
  border-color: #3b82f6;
  background: rgba(59, 130, 246, 0.05);
}

.upload-content {
  pointer-events: none;
}

.upload-icon {
  font-size: 2rem;
  margin-bottom: 0.75rem;
}

.upload-text {
  font-size: 0.9rem;
  color: #475569;
  margin: 0 0 0.5rem 0;
}

.upload-hint {
  font-size: 0.75rem;
  color: #94a3b8;
  margin: 0;
}

.link-btn {
  background: none;
  border: none;
  color: #3b82f6;
  text-decoration: underline;
  cursor: pointer;
  font-size: inherit;
}

/* Storage Section */
.storage-section {
  margin-bottom: 2rem;
}

.info-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}

.info-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}

.info-icon {
  font-size: 1.2rem;
}

.info-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

.info-body {
  padding: 1rem;
}

.storage-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.storage-row:last-child {
  border-bottom: none;
}

.store-name {
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
}

.store-stats {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Action Grid */
.action-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
}

.action-card.danger:hover {
  border-color: #dc2626;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.15);
}

.action-icon {
  font-size: 1.5rem;
}

.action-content h5 {
  font-size: 0.8rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.25rem 0;
}

.action-content p {
  font-size: 0.7rem;
  color: #64748b;
  margin: 0;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
  .tab-btn {
    color: #94a3b8;
  }

  .tab-btn:hover {
    color: #cbd5e1;
    background: rgba(148, 163, 184, 0.1);
  }

  .tab-btn.active {
    color: #60a5fa;
    background: rgba(96, 165, 250, 0.1);
    border-bottom-color: #60a5fa;
  }

  .export-card,
  .paste-area,
  .info-card,
  .action-card {
    background: #1e293b;
    border-color: #334155;
  }

  .export-card:hover,
  .action-card:hover {
    border-color: #3b82f6;
  }

  .card-content h5,
  .info-title,
  .action-content h5 {
    color: #f1f5f9;
  }

  .card-content p,
  .upload-text,
  .store-name {
    color: #94a3b8;
  }

  .paste-textarea {
    background: #0f172a;
    border-color: #334155;
    color: #e2e8f0;
  }

  .info-header {
    background: #334155;
    border-bottom-color: #475569;
  }
}
</style>
