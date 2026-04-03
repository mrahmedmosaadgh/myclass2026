<template>
  <div class="menu-settings">
    <h3 class="section-title">Settings</h3>
    <p class="section-desc">App preferences and maintenance.</p>

    <!-- Notifications -->
    <div class="setting-group">
      <h4 class="group-title">Notifications</h4>
      <div class="setting-row">
        <div class="setting-info">
          <span class="setting-label">Enable Notifications</span>
          <span class="setting-desc">Period change reminders</span>
        </div>
        <button
          class="toggle-btn"
          :class="{ active: notificationsEnabled }"
          @click="toggleNotifications"
        >
          <span class="toggle-dot"></span>
        </button>
      </div>
    </div>

    <!-- Color Scheme -->
    <div class="setting-group">
      <h4 class="group-title">Table View Colors</h4>
      <div class="setting-row">
        <div class="setting-info">
          <span class="setting-label">Color Scheme</span>
          <span class="setting-desc">Choose color palette for classrooms</span>
        </div>
        <select v-model="colorScheme" @change="updateColorScheme" class="color-select">
          <option value="default">Default</option>
          <option value="pastel">Pastel</option>
          <option value="vibrant">Vibrant</option>
          <option value="monochrome">Monochrome</option>
        </select>
      </div>
    </div>

    <!-- Test Time Override -->
    <div class="setting-group">
      <h4 class="group-title">Test Time Override</h4>
      <div class="setting-row">
        <div class="setting-info">
          <span class="setting-label">Enable Test Time</span>
          <span class="setting-desc">Override current time for testing</span>
        </div>
        <button
          class="toggle-btn"
          :class="{ active: store.testTimeEnabled.value }"
          @click="toggleTestTime"
        >
          <span class="toggle-dot"></span>
        </button>
      </div>

      <div v-if="store.testTimeEnabled.value" class="test-config">
        <div class="test-row">
          <label class="test-label">Day</label>
          <select v-model="testDayIndex" @change="updateTestTime" class="test-select">
            <option value="0">Sunday</option>
            <option value="1">Monday</option>
            <option value="2">Tuesday</option>
            <option value="3">Wednesday</option>
            <option value="4">Thursday</option>
            <option value="5">Friday</option>
            <option value="6">Saturday</option>
          </select>
        </div>
        <div class="test-row">
          <label class="test-label">Time</label>
          <input
            v-model="testTimeValue"
            type="time"
            @change="updateTestTime"
            class="test-input"
          />
        </div>
      </div>
    </div>

    <!-- Cloud Sync -->
    <div class="setting-group">
      <h4 class="group-title">Cloud Sync</h4>
      <div class="setting-row">
        <div class="setting-info">
          <span class="setting-label">Status</span>
          <span class="setting-desc">{{ syncStatusDesc }}</span>
        </div>
        <span class="sync-badge" :class="store.syncStatus.value">
          {{ store.syncStatus.value }}
        </span>
      </div>
      <div class="setting-row">
        <div class="setting-info">
          <span class="setting-label">Force Sync Now</span>
          <span class="setting-desc">Push current data to server</span>
        </div>
        <button class="action-btn" @click="forceSync" :disabled="!store.isOnline.value">
          🔄 Sync
        </button>
      </div>
    </div>

    <!-- Maintenance -->
    <div class="setting-group">
      <h4 class="group-title">Maintenance</h4>
      <div class="setting-row">
        <div class="setting-info">
          <span class="setting-label">Clear Cache</span>
          <span class="setting-desc">Clear browser cache and reload</span>
        </div>
        <button class="action-btn danger" @click="clearCache">
          🗑 Clear
        </button>
      </div>
      <div class="setting-row">
        <div class="setting-info">
          <span class="setting-label">Reset App</span>
          <span class="setting-desc">Delete all local data and reload</span>
        </div>
        <button class="action-btn danger" @click="resetApp">
          🔄 Reset
        </button>
      </div>
    </div>

    <!-- Storage Info -->
    <div class="setting-group">
      <h4 class="group-title">Storage</h4>
      <div class="storage-info">
        <div class="storage-row">
          <span class="storage-label">IndexedDB</span>
          <span class="storage-value">{{ storageInfo?.dbName || 'Loading...' }}</span>
        </div>
        <div v-if="storageInfo" class="storage-row">
          <span class="storage-label">Total Size</span>
          <span class="storage-value">{{ formatSize(totalSize) }}</span>
        </div>
      </div>
    </div>

    <!-- Status Messages -->
    <div v-if="statusMessage" class="status-msg" :class="statusType">
      {{ statusMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();

const notificationsEnabled = ref(false);
const statusMessage = ref('');
const statusType = ref('info');
const storageInfo = ref(null);
const testDayIndex = ref(0);
const testTimeValue = ref('09:00');
const colorScheme = ref('default');

const syncStatusDesc = computed(() => {
  if (!store.isOnline.value) return 'Offline';
  switch (store.syncStatus.value) {
    case 'syncing': return 'Saving to cloud...';
    case 'synced': return 'All changes saved';
    case 'error': return 'Last sync failed';
    case 'offline': return 'Offline mode';
    default: return 'Ready';
  }
});

const totalSize = computed(() => {
  if (!storageInfo.value) return 0;
  return Object.values(storageInfo.value.stores).reduce((sum, s) => sum + s.sizeBytes, 0);
});

const formatSize = (bytes) => {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const showStatus = (msg, type = 'info') => {
  statusMessage.value = msg;
  statusType.value = type;
  setTimeout(() => { statusMessage.value = ''; }, 4000);
};

const toggleNotifications = async () => {
  if (!('Notification' in window)) {
    showStatus('Notifications not supported', 'error');
    return;
  }

  if (Notification.permission === 'granted') {
    notificationsEnabled.value = !notificationsEnabled.value;
    await store.db.saveSetting('notificationsEnabled', notificationsEnabled.value);
    showStatus(notificationsEnabled.value ? 'Notifications enabled' : 'Notifications disabled');
  } else if (Notification.permission === 'denied') {
    showStatus('Notifications blocked by browser', 'error');
  } else {
    const perm = await Notification.requestPermission();
    if (perm === 'granted') {
      notificationsEnabled.value = true;
      await store.db.saveSetting('notificationsEnabled', true);
      showStatus('Notifications enabled');
    } else {
      showStatus('Notifications denied', 'error');
    }
  }
};

const toggleTestTime = () => {
  const enabled = !store.testTimeEnabled.value;
  store.setTestTimeConfig({
    enabled,
    dayIndex: testDayIndex.value,
    timeValue: testTimeValue.value
  });
};

const updateTestTime = () => {
  store.setTestTimeConfig({
    enabled: true,
    dayIndex: testDayIndex.value,
    timeValue: testTimeValue.value
  });
};

const forceSync = async () => {
  if (!store.isOnline.value) {
    showStatus('Cannot sync: offline', 'error');
    return;
  }
  await store.pushCloudSnapshot();
  showStatus('Sync triggered', 'success');
};

const clearCache = async () => {
  if ('caches' in window) {
    const cacheNames = await caches.keys();
    for (const name of cacheNames) {
      await caches.delete(name);
    }
  }
  showStatus('Cache cleared. Reloading...', 'info');
  setTimeout(() => window.location.reload(), 1000);
};

const resetApp = async () => {
  if (!confirm('Reset app? All data will be deleted.')) return;
  try {
    await store.db.clear('timings');
    await store.db.clear('personalSchedule');
    await store.db.clear('schoolTimetable');
    await store.db.clear('appSettings');
    await store.db.clear('syncQueue');
    showStatus('App reset. Reloading...', 'info');
    setTimeout(() => window.location.reload(), 1000);
  } catch (e) {
    showStatus('Reset failed: ' + e.message, 'error');
  }
};

const updateColorScheme = async () => {
  try {
    await store.db.saveSetting('colorScheme', colorScheme.value);
    showStatus('Color scheme updated', 'success');
  } catch (e) {
    showStatus('Failed to save color scheme', 'error');
  }
};

watch(() => store.testTimeEnabled.value, (val) => {
  testDayIndex.value = store.testDayIndex.value;
  testTimeValue.value = store.testTimeValue.value;
}, { immediate: true });

onMounted(async () => {
  const notifSetting = await store.db.getSetting('notificationsEnabled');
  notificationsEnabled.value = !!notifSetting;
  const colorSetting = await store.db.getSetting('colorScheme');
  colorScheme.value = colorSetting || 'default';
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

.setting-group {
  margin-bottom: 1.5rem;
}

.group-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 0.75rem 0;
}

.setting-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.setting-row:last-child { border-bottom: none; }

.setting-info {
  flex: 1;
}

.setting-label {
  display: block;
  font-weight: 600;
  font-size: 0.85rem;
  color: #e2e8f0;
}

.setting-desc {
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 0.15rem;
}

.toggle-btn {
  width: 48px;
  height: 24px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.05);
  cursor: pointer;
  position: relative;
  transition: all 0.2s;
  flex-shrink: 0;
}

.toggle-btn.active {
  background: rgba(59, 130, 246, 0.3);
  border-color: rgba(59, 130, 246, 0.5);
}

.toggle-dot {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #94a3b8;
  transition: all 0.2s;
}

.toggle-btn.active .toggle-dot {
  left: 26px;
  background: #60a5fa;
}

.action-btn {
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  min-height: 32px;
  min-width: 80px;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.action-btn.danger {
  border-color: rgba(239, 68, 68, 0.3);
  background: rgba(239, 68, 68, 0.1);
}

.action-btn.danger:hover {
  background: rgba(239, 68, 68, 0.2);
}

.color-select {
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  min-height: 32px;
  min-width: 120px;
}

.color-select:hover {
  background: rgba(255, 255, 255, 0.1);
}

.color-select option {
  background: #1e293b;
  color: #e2e8f0;
}

.sync-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sync-badge.synced { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.sync-badge.syncing { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
.sync-badge.error { background: rgba(239, 68, 68, 0.2); color: #f87171; }
.sync-badge.offline { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

.test-config {
  margin-top: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.test-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.test-label {
  flex: 0 0 40px;
  font-size: 0.8rem;
  color: #94a3b8;
}

.test-select,
.test-input {
  flex: 1;
  padding: 0.35rem 0.5rem;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: #e2e8f0;
  font-size: 0.8rem;
}

.storage-info {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  padding: 0.75rem;
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

.status-msg {
  margin-top: 1rem;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  font-size: 0.8rem;
  text-align: center;
}

.status-msg.success { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.status-msg.error { background: rgba(239, 68, 68, 0.2); color: #f87171; }
.status-msg.info { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
</style>
