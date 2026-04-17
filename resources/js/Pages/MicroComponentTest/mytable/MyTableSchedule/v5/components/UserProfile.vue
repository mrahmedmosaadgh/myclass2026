<template>
  <div class="user-profile">
    <!-- Profile Header -->
    <div class="profile-header">
      <div class="user-info">
        <div class="user-avatar">
          <img v-if="userAvatar" :src="userAvatar" :alt="displayName" />
          <div v-else class="avatar-placeholder">
            {{ displayName.charAt(0).toUpperCase() }}
          </div>
        </div>
        <div class="user-details">
          <h3>{{ displayName }}</h3>
          <p>{{ user?.email }}</p>
          <div class="user-status">
            <span class="status-dot" :class="{ online: isOnline }"></span>
            {{ isOnline ? 'Online' : 'Offline' }}
          </div>
        </div>
      </div>
      
      <div class="profile-actions">
        <button @click="showSettings = !showSettings" class="btn-icon" title="Settings">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M12 1v6m0 6v6m4.22-13.22l4.24 4.24M1.54 1.54l4.24 4.24M20.46 20.46l-4.24-4.24M1.54 20.46l4.24-4.24"></path>
          </svg>
        </button>
        <button @click="showSyncInfo = !showSyncInfo" class="btn-icon" title="Sync Info">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 4 23 10 17 10"></polyline>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
          </svg>
        </button>
        <button @click="handleLogout" class="btn-icon logout" title="Logout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </button>
      </div>
    </div>

    <!-- Sync Status -->
    <div class="sync-status" :class="{ syncing: isSyncing, offline: !isOnline }">
      <div class="sync-info">
        <svg v-if="isSyncing" class="sync-icon spinning" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M23 4v6h-6M1 20v-6h6"></path>
          <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
        </svg>
        <svg v-else-if="!isOnline" class="sync-icon offline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
          <line x1="12" y1="9" x2="12" y2="13"></line>
          <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <svg v-else class="sync-icon synced" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span class="sync-text">{{ syncStatusText }}</span>
      </div>
      
      <div class="sync-actions">
        <button v-if="needsSync" @click="syncData" class="btn-sync" :disabled="isSyncing">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 4 23 10 17 10"></polyline>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
          </svg>
          Sync Now
        </button>
      </div>
    </div>

    <!-- Settings Panel -->
    <div v-if="showSettings" class="settings-panel">
      <h4>Settings</h4>
      
      <div class="setting-group">
        <label>Theme</label>
        <select v-model="localSettings.theme" @change="saveSettings">
          <option value="light">Light</option>
          <option value="dark">Dark</option>
          <option value="auto">Auto</option>
        </select>
      </div>

      <div class="setting-group">
        <label>Language</label>
        <select v-model="localSettings.language" @change="saveSettings">
          <option value="en">English</option>
          <option value="ar">Arabic</option>
          <option value="fr">French</option>
        </select>
      </div>

      <div class="setting-group">
        <label>Timezone</label>
        <select v-model="localSettings.timezone" @change="saveSettings">
          <option value="UTC">UTC</option>
          <option value="America/New_York">Eastern Time</option>
          <option value="Europe/London">London</option>
          <option value="Asia/Dubai">Dubai</option>
        </select>
      </div>

      <div class="setting-group">
        <label class="checkbox-label">
          <input v-model="localSettings.notifications" @change="saveSettings" type="checkbox">
          <span>Enable notifications</span>
        </label>
      </div>

      <div class="setting-group">
        <label class="checkbox-label">
          <input v-model="localSettings.autoSync" @change="saveSettings" type="checkbox">
          <span>Auto-sync when online</span>
        </label>
      </div>
    </div>

    <!-- Sync Info Panel -->
    <div v-if="showSyncInfo" class="sync-info-panel">
      <h4>Sync Information</h4>
      
      <div class="info-item">
        <label>Last Sync:</label>
        <span>{{ lastSyncAt ? formatDate(lastSyncAt) : 'Never' }}</span>
      </div>

      <div class="info-item">
        <label>Device ID:</label>
        <span class="device-id">{{ deviceId }}</span>
      </div>

      <div class="info-item">
        <label>Local Data:</label>
        <span>{{ hasLocalData ? 'Available' : 'None' }}</span>
      </div>

      <div class="info-item">
        <label>Pending Changes:</label>
        <span :class="{ 'has-changes': pendingChanges }">
          {{ pendingChanges ? 'Yes' : 'No' }}
        </span>
      </div>

      <div class="info-item">
        <label>Storage Usage:</label>
        <span>{{ storageUsage }}</span>
      </div>

      <div class="sync-actions-panel">
        <button @click="forceSync" class="btn-secondary" :disabled="isSyncing">
          Force Sync
        </button>
        <button @click="clearLocalData" class="btn-danger">
          Clear Local Data
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useTimelineAuth } from '../composables/useTimelineAuth.js';
import { useTimelineSync } from '../composables/useTimelineSync.js';

const { user, timelineUser, logout, displayName } = useTimelineAuth();
const { 
  isOnline, 
  isSyncing, 
  lastSyncAt, 
  syncError, 
  pendingChanges, 
  syncStatusText, 
  hasLocalData, 
  needsSync, 
  syncData,
  getLocalSettings,
  saveLocalSettings
} = useTimelineSync();

const showSettings = ref(false);
const showSyncInfo = ref(false);
const userAvatar = ref(null);
const deviceId = ref('');
const storageUsage = ref('Calculating...');

const localSettings = ref({
  theme: 'light',
  language: 'en',
  timezone: 'UTC',
  notifications: true,
  autoSync: true
});

// Computed properties
const isAuthenticated = computed(() => !!user.value);

// Methods
async function handleLogout() {
  await logout();
  window.location.href = '/timeline/login';
}

async function saveSettings() {
  await saveLocalSettings(localSettings.value);
}

async function forceSync() {
  await syncData();
}

function clearLocalData() {
  if (confirm('Are you sure you want to clear all local data? This cannot be undone.')) {
    localStorage.removeItem('timeline_data');
    localStorage.removeItem('timeline_settings');
    localStorage.removeItem('timeline_sync_status');
    window.location.reload();
  }
}

function formatDate(date) {
  return new Intl.DateTimeFormat('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short'
  }).format(new Date(date));
}

function calculateStorageUsage() {
  try {
    const data = localStorage.getItem('timeline_data');
    const settings = localStorage.getItem('timeline_settings');
    const syncStatus = localStorage.getItem('timeline_sync_status');
    
    let totalBytes = 0;
    
    if (data) totalBytes += new Blob([data]).size;
    if (settings) totalBytes += new Blob([settings]).size;
    if (syncStatus) totalBytes += new Blob([syncStatus]).size;
    
    if (totalBytes < 1024) {
      storageUsage.value = `${totalBytes} bytes`;
    } else if (totalBytes < 1024 * 1024) {
      storageUsage.value = `${(totalBytes / 1024).toFixed(1)} KB`;
    } else {
      storageUsage.value = `${(totalBytes / (1024 * 1024)).toFixed(1)} MB`;
    }
  } catch (err) {
    storageUsage.value = 'Error calculating';
  }
}

// Initialize
onMounted(() => {
  // Load device ID
  deviceId.value = localStorage.getItem('timeline_device_id') || 'Unknown';
  
  // Load settings
  const savedSettings = getLocalSettings();
  if (savedSettings) {
    localSettings.value = { ...localSettings.value, ...savedSettings };
  }
  
  // Calculate storage usage
  calculateStorageUsage();
  
  // Update storage usage periodically
  setInterval(calculateStorageUsage, 5000);
});

// Watch for sync errors
watch(syncError, (error) => {
  if (error) {
    console.error('Sync error:', error);
  }
});
</script>

<style scoped>
.user-profile {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  margin-bottom: 1rem;
}

.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1.25rem;
}

.user-details h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
}

.user-details p {
  margin: 0 0 0.5rem 0;
  font-size: 0.875rem;
  color: #6b7280;
}

.user-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #6b7280;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #ef4444;
}

.status-dot.online {
  background: #10b981;
}

.profile-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 8px;
  background: #f3f4f6;
  color: #6b7280;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.btn-icon:hover {
  background: #e5e7eb;
  color: #374151;
}

.btn-icon.logout:hover {
  background: #fef2f2;
  color: #dc2626;
}

.btn-icon svg {
  width: 18px;
  height: 18px;
}

.sync-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 1rem;
  border: 1px solid #e5e7eb;
}

.sync-status.syncing {
  background: #eff6ff;
  border-color: #3b82f6;
}

.sync-status.offline {
  background: #fef2f2;
  border-color: #ef4444;
}

.sync-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sync-icon {
  width: 16px;
  height: 16px;
  color: #6b7280;
}

.sync-icon.spinning {
  animation: spin 1s linear infinite;
  color: #3b82f6;
}

.sync-icon.offline {
  color: #ef4444;
}

.sync-icon.synced {
  color: #10b981;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.sync-text {
  font-size: 0.875rem;
  color: #374151;
}

.btn-sync {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  background: #3b82f6;
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-sync:hover:not(:disabled) {
  background: #2563eb;
}

.btn-sync:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-sync svg {
  width: 14px;
  height: 14px;
}

.settings-panel,
.sync-info-panel {
  background: #f9fafb;
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
}

.settings-panel h4,
.sync-info-panel h4 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
}

.setting-group {
  margin-bottom: 1rem;
}

.setting-group:last-child {
  margin-bottom: 0;
}

.setting-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.setting-group select {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  background: white;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: auto;
  margin: 0;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e5e7eb;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.info-item span {
  font-size: 0.875rem;
  color: #6b7280;
}

.device-id {
  font-family: monospace;
  font-size: 0.75rem;
  background: #f3f4f6;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.has-changes {
  color: #f59e0b !important;
  font-weight: 600;
}

.sync-actions-panel {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}

.btn-secondary,
.btn-danger {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #4b5563;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

@media (max-width: 640px) {
  .profile-header {
    flex-direction: column;
    gap: 1rem;
  }
  
  .sync-status {
    flex-direction: column;
    gap: 0.75rem;
    align-items: stretch;
  }
  
  .sync-actions-panel {
    flex-direction: column;
  }
}
</style>
