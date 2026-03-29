<template>
  <div class="offline-storage-demo">
    <div class="demo-header">
      <h3>📦 Offline Storage Demo</h3>
      <div class="status-badges">
        <span class="badge" :class="isInitialized ? 'success' : 'warning'">
          {{ isInitialized ? '✓ DB Ready' : '⏳ Initializing...' }}
        </span>
        <span class="badge info" v-if="lastSyncTime">
          Last Sync: {{ formatTime(lastSyncTime) }}
        </span>
      </div>
    </div>

    <div class="demo-sections">
      <!-- Storage Stats -->
      <div class="demo-section">
        <h4>💾 Storage Statistics</h4>
        <button @click="loadStorageStats" class="btn-primary">
          Refresh Stats
        </button>
        <div v-if="storageStats" class="stats-grid">
          <div v-for="(info, storeName) in storageStats.stores" :key="storeName" class="stat-card">
            <div class="stat-label">{{ storeName }}</div>
            <div class="stat-value">{{ info.count }} items</div>
            <div class="stat-size">{{ formatBytes(info.size) }}</div>
          </div>
        </div>
      </div>

      <!-- Save Personal Schedule -->
      <div class="demo-section">
        <h4>👤 Personal Schedule</h4>
        <textarea 
          v-model="personalScheduleJson" 
          placeholder="Enter personal schedule JSON..."
          rows="4"
        ></textarea>
        <div class="btn-group">
          <button @click="savePersonal" class="btn-primary">Save</button>
          <button @click="loadPersonal" class="btn-secondary">Load</button>
        </div>
        <pre v-if="loadedPersonal" class="data-preview">{{ JSON.stringify(loadedPersonal, null, 2) }}</pre>
      </div>

      <!-- Save School Timetable -->
      <div class="demo-section">
        <h4>🏫 School Timetable</h4>
        <textarea 
          v-model="schoolTimetableJson" 
          placeholder="Enter school timetable JSON..."
          rows="4"
        ></textarea>
        <div class="btn-group">
          <button @click="saveSchool" class="btn-primary">Save</button>
          <button @click="loadSchool" class="btn-secondary">Load</button>
        </div>
        <pre v-if="loadedSchool" class="data-preview">{{ JSON.stringify(loadedSchool, null, 2) }}</pre>
      </div>

      <!-- Save Timings -->
      <div class="demo-section">
        <h4>⏰ Timings</h4>
        <div class="input-group">
          <select v-model="selectedStage">
            <option value="prim">Primary</option>
            <option value="middle">Middle</option>
            <option value="sec">Secondary</option>
          </select>
          <select v-model="selectedDay">
            <option value="d1">Day 1</option>
            <option value="d2">Day 2</option>
            <option value="d3">Day 3</option>
            <option value="d4">Day 4</option>
            <option value="d5">Day 5</option>
            <option value="d6">Day 6</option>
          </select>
        </div>
        <textarea 
          v-model="timingsJson" 
          placeholder="Enter timings JSON..."
          rows="4"
        ></textarea>
        <div class="btn-group">
          <button @click="saveTimingsData" class="btn-primary">Save</button>
          <button @click="loadTimingsData" class="btn-secondary">Load</button>
          <button @click="loadAllTimingsData" class="btn-secondary">Load All</button>
        </div>
        <pre v-if="loadedTimings" class="data-preview">{{ JSON.stringify(loadedTimings, null, 2) }}</pre>
      </div>

      <!-- Settings -->
      <div class="demo-section">
        <h4>⚙️ Settings</h4>
        <div class="input-group">
          <input v-model="settingKey" placeholder="Setting key" />
          <input v-model="settingValue" placeholder="Setting value" />
        </div>
        <div class="btn-group">
          <button @click="saveSettingData" class="btn-primary">Save Setting</button>
          <button @click="loadSettingData" class="btn-secondary">Load Setting</button>
          <button @click="loadAllSettingsData" class="btn-secondary">Load All</button>
        </div>
        <pre v-if="loadedSettings" class="data-preview">{{ JSON.stringify(loadedSettings, null, 2) }}</pre>
      </div>

      <!-- Export/Import -->
      <div class="demo-section">
        <h4>📤 Export / Import</h4>
        <div class="btn-group">
          <button @click="exportData" class="btn-primary">Export All Data</button>
          <button @click="downloadExport" class="btn-success" :disabled="!exportedData">
            Download JSON
          </button>
        </div>
        <div class="file-upload">
          <input 
            type="file" 
            @change="handleFileUpload" 
            accept=".json"
            ref="fileInput"
          />
          <button @click="importData" class="btn-primary" :disabled="!importFile">
            Import Data
          </button>
        </div>
        <pre v-if="exportedData" class="data-preview">{{ JSON.stringify(exportedData, null, 2).substring(0, 500) }}...</pre>
      </div>
    </div>

    <!-- Status Messages -->
    <div v-if="statusMessage" class="status-message" :class="statusType">
      {{ statusMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useScheduleOfflineStorage } from '../composables/useScheduleOfflineStorage';

const {
  isInitialized,
  lastSyncTime,
  initialize,
  savePersonalSchedule,
  getPersonalSchedule,
  saveSchoolTimetable,
  getSchoolTimetable,
  saveTimings,
  getTimings,
  getAllTimings,
  saveSetting,
  getSetting,
  getAllSettings,
  exportAllData,
  importAllData,
  getStorageStats
} = useScheduleOfflineStorage();

// State
const storageStats = ref(null);
const personalScheduleJson = ref('');
const loadedPersonal = ref(null);
const schoolTimetableJson = ref('');
const loadedSchool = ref(null);
const selectedStage = ref('prim');
const selectedDay = ref('d1');
const timingsJson = ref('');
const loadedTimings = ref(null);
const settingKey = ref('');
const settingValue = ref('');
const loadedSettings = ref(null);
const exportedData = ref(null);
const importFile = ref(null);
const fileInput = ref(null);
const statusMessage = ref('');
const statusType = ref('info');

// Methods
const showStatus = (message, type = 'info') => {
  statusMessage.value = message;
  statusType.value = type;
  setTimeout(() => {
    statusMessage.value = '';
  }, 3000);
};

const loadStorageStats = async () => {
  try {
    storageStats.value = await getStorageStats();
    showStatus('Storage stats loaded', 'success');
  } catch (error) {
    showStatus('Failed to load stats: ' + error.message, 'error');
  }
};

const savePersonal = async () => {
  try {
    const data = JSON.parse(personalScheduleJson.value);
    await savePersonalSchedule(data);
    showStatus('Personal schedule saved', 'success');
    await loadStorageStats();
  } catch (error) {
    showStatus('Failed to save: ' + error.message, 'error');
  }
};

const loadPersonal = async () => {
  try {
    loadedPersonal.value = await getPersonalSchedule();
    showStatus('Personal schedule loaded', 'success');
  } catch (error) {
    showStatus('Failed to load: ' + error.message, 'error');
  }
};

const saveSchool = async () => {
  try {
    const data = JSON.parse(schoolTimetableJson.value);
    await saveSchoolTimetable(data);
    showStatus('School timetable saved', 'success');
    await loadStorageStats();
  } catch (error) {
    showStatus('Failed to save: ' + error.message, 'error');
  }
};

const loadSchool = async () => {
  try {
    loadedSchool.value = await getSchoolTimetable();
    showStatus('School timetable loaded', 'success');
  } catch (error) {
    showStatus('Failed to load: ' + error.message, 'error');
  }
};

const saveTimingsData = async () => {
  try {
    const data = JSON.parse(timingsJson.value);
    await saveTimings(selectedStage.value, selectedDay.value, data);
    showStatus(`Timings saved for ${selectedStage.value} - ${selectedDay.value}`, 'success');
    await loadStorageStats();
  } catch (error) {
    showStatus('Failed to save: ' + error.message, 'error');
  }
};

const loadTimingsData = async () => {
  try {
    loadedTimings.value = await getTimings(selectedStage.value, selectedDay.value);
    showStatus(`Timings loaded for ${selectedStage.value} - ${selectedDay.value}`, 'success');
  } catch (error) {
    showStatus('Failed to load: ' + error.message, 'error');
  }
};

const loadAllTimingsData = async () => {
  try {
    loadedTimings.value = await getAllTimings();
    showStatus('All timings loaded', 'success');
  } catch (error) {
    showStatus('Failed to load: ' + error.message, 'error');
  }
};

const saveSettingData = async () => {
  try {
    await saveSetting(settingKey.value, settingValue.value);
    showStatus(`Setting '${settingKey.value}' saved`, 'success');
    await loadStorageStats();
  } catch (error) {
    showStatus('Failed to save: ' + error.message, 'error');
  }
};

const loadSettingData = async () => {
  try {
    const value = await getSetting(settingKey.value);
    loadedSettings.value = { [settingKey.value]: value };
    showStatus(`Setting '${settingKey.value}' loaded`, 'success');
  } catch (error) {
    showStatus('Failed to load: ' + error.message, 'error');
  }
};

const loadAllSettingsData = async () => {
  try {
    loadedSettings.value = await getAllSettings();
    showStatus('All settings loaded', 'success');
  } catch (error) {
    showStatus('Failed to load: ' + error.message, 'error');
  }
};

const exportData = async () => {
  try {
    exportedData.value = await exportAllData();
    showStatus('Data exported successfully', 'success');
  } catch (error) {
    showStatus('Failed to export: ' + error.message, 'error');
  }
};

const downloadExport = () => {
  if (!exportedData.value) return;
  
  const blob = new Blob([JSON.stringify(exportedData.value, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `schedule-backup-${Date.now()}.json`;
  a.click();
  URL.revokeObjectURL(url);
  showStatus('Download started', 'success');
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    importFile.value = file;
    showStatus(`File selected: ${file.name}`, 'info');
  }
};

const importData = async () => {
  if (!importFile.value) return;
  
  try {
    const text = await importFile.value.text();
    const data = JSON.parse(text);
    await importAllData(data);
    showStatus('Data imported successfully', 'success');
    await loadStorageStats();
    importFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
  } catch (error) {
    showStatus('Failed to import: ' + error.message, 'error');
  }
};

const formatTime = (date) => {
  if (!date) return 'Never';
  return new Date(date).toLocaleString();
};

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

onMounted(async () => {
  await initialize();
  await loadStorageStats();
});
</script>

<style scoped>
.offline-storage-demo {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.demo-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 2px solid #e5e7eb;
}

.demo-header h3 {
  margin: 0;
  font-size: 24px;
  color: #1f2937;
}

.status-badges {
  display: flex;
  gap: 8px;
}

.badge {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.badge.success {
  background: #10b981;
  color: white;
}

.badge.warning {
  background: #f59e0b;
  color: white;
}

.badge.info {
  background: #3b82f6;
  color: white;
}

.demo-sections {
  display: grid;
  gap: 20px;
}

.demo-section {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
}

.demo-section h4 {
  margin: 0 0 16px 0;
  font-size: 18px;
  color: #374151;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  margin-top: 12px;
}

.stat-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 12px;
}

.stat-label {
  font-size: 12px;
  color: #6b7280;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
}

.stat-size {
  font-size: 12px;
  color: #9ca3af;
  margin-top: 4px;
}

textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-family: 'Courier New', monospace;
  font-size: 13px;
  resize: vertical;
  margin-bottom: 12px;
}

.input-group {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.input-group input,
.input-group select {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.btn-group {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

button {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #4b5563;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background: #059669;
}

.data-preview {
  background: #1f2937;
  color: #10b981;
  padding: 12px;
  border-radius: 6px;
  font-size: 12px;
  overflow-x: auto;
  max-height: 300px;
}

.file-upload {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-top: 12px;
}

.file-upload input[type="file"] {
  flex: 1;
}

.status-message {
  position: fixed;
  bottom: 20px;
  right: 20px;
  padding: 12px 20px;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  animation: slideIn 0.3s ease-out;
}

.status-message.success {
  background: #10b981;
  color: white;
}

.status-message.error {
  background: #ef4444;
  color: white;
}

.status-message.info {
  background: #3b82f6;
  color: white;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
