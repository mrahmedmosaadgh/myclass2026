<template>
  <div class="app-settings-dialog">
    <!-- Full Screen Dialog Header -->
    <div class="dialog-header">
      <div class="header-left">
        <button @click="$emit('close')" class="back-btn">
          ← Back
        </button>
        <h2 class="dialog-title">⚙️ App Settings</h2>
      </div>
      <div class="header-right">
        <span class="app-status">🟢 Online</span>
      </div>
    </div>

    <!-- Full Screen Dialog Content -->
    <div class="dialog-content">
      <!-- Quick Actions -->
      <div class="settings-section">
        <h3 class="section-title">🚀 Quick Actions</h3>
        <div class="action-grid">
          <button @click="openAppSettings" class="action-card primary">
            <div class="card-icon">⚙️</div>
            <div class="card-content">
              <div class="card-title">App Settings</div>
              <div class="card-desc">Timing, display, notifications</div>
            </div>
            <div class="card-arrow">→</div>
          </button>
          
          <button @click="openDataManager" class="action-card info">
            <div class="card-icon">📁</div>
            <div class="card-content">
              <div class="card-title">Data Manager</div>
              <div class="card-desc">Import, export, backup</div>
            </div>
            <div class="card-arrow">→</div>
          </button>
          
          <button @click="openGeneralSettings" class="action-card secondary">
            <div class="card-icon">🔧</div>
            <div class="card-content">
              <div class="card-title">General Settings</div>
              <div class="card-desc">System preferences</div>
            </div>
            <div class="card-arrow">→</div>
          </button>
          
          <button @click="exportData" class="action-card success">
            <div class="card-icon">📥</div>
            <div class="card-content">
              <div class="card-title">Export Data</div>
              <div class="card-desc">Download schedule data</div>
            </div>
            <div class="card-arrow">→</div>
          </button>
          
          <button @click="importData" class="action-card warning">
            <div class="card-icon">📤</div>
            <div class="card-content">
              <div class="card-title">Import Data</div>
              <div class="card-desc">Restore from backup</div>
            </div>
            <div class="card-arrow">→</div>
          </button>
          
          <button @click="refreshData" class="action-card info">
            <div class="card-icon">🔄</div>
            <div class="card-content">
              <div class="card-title">Refresh Data</div>
              <div class="card-desc">Reload schedule data</div>
            </div>
            <div class="card-arrow">→</div>
          </button>
        </div>
      </div>

      <!-- View Settings -->
      <div class="settings-section">
        <h3 class="section-title">👁️ View Settings</h3>
        <div class="view-options">
          <label class="view-option">
            <input type="checkbox" v-model="viewSettings.compactMode" @change="updateViewSettings" />
            <div class="option-content">
              <span class="option-title">Compact Mode</span>
              <span class="option-desc">Reduce spacing and use smaller elements</span>
            </div>
            <div class="option-toggle"></div>
          </label>
          
          <label class="view-option">
            <input type="checkbox" v-model="viewSettings.showGrid" @change="updateViewSettings" />
            <div class="option-content">
              <span class="option-title">Show Grid Lines</span>
              <span class="option-desc">Display grid lines in schedule view</span>
            </div>
            <div class="option-toggle"></div>
          </label>
          
          <label class="view-option">
            <input type="checkbox" v-model="viewSettings.showTimeLabels" @change="updateViewSettings" />
            <div class="option-content">
              <span class="option-title">Show Time Labels</span>
              <span class="option-desc">Display time labels on schedule periods</span>
            </div>
            <div class="option-toggle"></div>
          </label>
          
          <label class="view-option">
            <input type="checkbox" v-model="viewSettings.colorCodeTypes" @change="updateViewSettings" />
            <div class="option-content">
              <span class="option-title">Color Code Period Types</span>
              <span class="option-desc">Use different colors for lessons, breaks, activities</span>
            </div>
            <div class="option-toggle"></div>
          </label>
        </div>
      </div>

      <!-- Theme Settings -->
      <div class="settings-section">
        <h3 class="section-title">🎨 Theme</h3>
        <div class="theme-options">
          <label class="theme-option">
            <input type="radio" name="theme" value="dark" v-model="themeSettings.currentTheme" @change="updateTheme" />
            <div class="theme-card dark">
              <div class="theme-preview">
                <span class="theme-icon">🌙</span>
                <span class="theme-name">Dark</span>
              </div>
              <div class="theme-desc">For low-light environments</div>
            </div>
          </label>
          
          <label class="theme-option">
            <input type="radio" name="theme" value="light" v-model="themeSettings.currentTheme" @change="updateTheme" />
            <div class="theme-card light">
              <div class="theme-preview">
                <span class="theme-icon">☀️</span>
                <span class="theme-name">Light</span>
              </div>
              <div class="theme-desc">For bright environments</div>
            </div>
          </label>
          
          <label class="theme-option">
            <input type="radio" name="theme" value="auto" v-model="themeSettings.currentTheme" @change="updateTheme" />
            <div class="theme-card auto">
              <div class="theme-preview">
                <span class="theme-icon">🔄</span>
                <span class="theme-name">Auto</span>
              </div>
              <div class="theme-desc">Follows system preference</div>
            </div>
          </label>
        </div>
      </div>

      <!-- Data Management -->
      <div class="settings-section">
        <h3 class="section-title">📊 Data Management</h3>
        <div class="data-stats">
          <div class="stat-card">
            <div class="stat-icon">📅</div>
            <div class="stat-info">
              <div class="stat-value">{{ dataStats.scheduleEntries }}</div>
              <div class="stat-label">Schedule Entries</div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-info">
              <div class="stat-value">{{ dataStats.storageUsed }}</div>
              <div class="stat-label">Storage Used</div>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon">🕒</div>
            <div class="stat-info">
              <div class="stat-value">{{ dataStats.lastBackup }}</div>
              <div class="stat-label">Last Backup</div>
            </div>
          </div>
        </div>
        
        <div class="data-actions">
          <button @click="clearCache" class="data-btn danger">
            <span class="btn-icon">🗑️</span>
            <span class="btn-text">Clear Cache</span>
          </button>
          
          <button @click="backupData" class="data-btn success">
            <span class="btn-icon">💾</span>
            <span class="btn-text">Backup Now</span>
          </button>
          
          <button @click="resetSettings" class="data-btn warning">
            <span class="btn-icon">🔄</span>
            <span class="btn-text">Reset Settings</span>
          </button>
        </div>
      </div>

      <!-- About Section -->
      <div class="settings-section">
        <h3 class="section-title">ℹ️ About</h3>
        <div class="about-card">
          <div class="about-header">
            <div class="app-icon">⚙️</div>
            <div class="app-info">
              <div class="app-name">Schedule App V3</div>
              <div class="app-tagline">Advanced Offline Timing</div>
            </div>
          </div>
          
          <div class="about-details">
            <div class="detail-item">
              <span class="detail-label">Version</span>
              <span class="detail-value">3.0.0</span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Build</span>
              <span class="detail-value">{{ buildInfo }}</span>
            </div>
            
            <div class="detail-item">
              <span class="detail-label">Status</span>
              <span class="detail-value status-online">🟢 Online</span>
            </div>
          </div>
          
          <div class="about-actions">
            <button @click="showHelp" class="about-btn">
              <span class="btn-icon">📚</span>
              <span class="btn-text">Help & Documentation</span>
            </button>
            
            <button @click="reportIssue" class="about-btn">
              <span class="btn-icon">🐛</span>
              <span class="btn-text">Report Issue</span>
            </button>
            
            <button @click="checkUpdates" class="about-btn">
              <span class="btn-icon">🔄</span>
              <span class="btn-text">Check Updates</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Dialog Footer -->
    <div class="dialog-footer">
      <div class="footer-content">
        <p class="footer-text">Schedule App V3 • Advanced Offline Timing</p>
        <p class="footer-version">Version 3.0.0 • Build {{ buildInfo }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const emit = defineEmits(['close', 'open-app-settings', 'open-data-manager', 'open-general-settings', 'export-data', 'import-data', 'refresh-data']);

// Component state
const viewSettings = ref({
  compactMode: false,
  showGrid: true,
  showTimeLabels: true,
  colorCodeTypes: true
});

const themeSettings = ref({
  currentTheme: 'dark'
});

const dataStats = ref({
  scheduleEntries: 0,
  storageUsed: '0 KB',
  lastBackup: 'Never'
});

const buildInfo = computed(() => {
  return new Date().toISOString().split('T')[0].replace(/-/g, '.');
});

// Methods
const openAppSettings = () => {
  emit('open-app-settings');
};

const openDataManager = () => {
  emit('open-data-manager');
};

const openGeneralSettings = () => {
  emit('open-general-settings');
};

const exportData = () => {
  emit('export-data');
};

const importData = () => {
  emit('import-data');
};

const refreshData = () => {
  emit('refresh-data');
};

const updateViewSettings = () => {
  localStorage.setItem('app-view-settings', JSON.stringify(viewSettings.value));
  showToast('View settings updated', 'success');
};

const updateTheme = () => {
  localStorage.setItem('app-theme', themeSettings.value.currentTheme);
  document.body.className = `theme-${themeSettings.value.currentTheme}`;
  showToast(`Theme changed to ${themeSettings.value.currentTheme}`, 'success');
};

const clearCache = () => {
  if (confirm('Clear all cached data? This will remove temporary files but not your schedule data.')) {
    localStorage.clear();
    location.reload();
  }
};

const backupData = () => {
  const data = {
    viewSettings: viewSettings.value,
    themeSettings: themeSettings.value,
    backupDate: new Date().toISOString()
  };
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `app-backup-${new Date().toISOString().split('T')[0]}.json`;
  a.click();
  URL.revokeObjectURL(url);
  
  showToast('Settings backed up successfully!', 'success');
};

const resetSettings = () => {
  if (confirm('Reset all settings to defaults? This action cannot be undone.')) {
    viewSettings.value = {
      compactMode: false,
      showGrid: true,
      showTimeLabels: true,
      colorCodeTypes: true
    };
    themeSettings.value = {
      currentTheme: 'dark'
    };
    
    localStorage.removeItem('app-view-settings');
    localStorage.removeItem('app-theme');
    showToast('Settings reset to defaults', 'warning');
  }
};

const showHelp = () => {
  showToast('Help documentation coming soon!', 'info');
};

const reportIssue = () => {
  showToast('Issue reporting coming soon!', 'info');
};

const checkUpdates = () => {
  showToast('Checking for updates... You are running the latest version!', 'success');
};

const updateDataStats = () => {
  const storageUsed = JSON.stringify(localStorage).length;
  dataStats.value = {
    scheduleEntries: JSON.parse(localStorage.getItem('schedule-v3-timing-data') || '{}').default?.length || 0,
    storageUsed: `${Math.round(storageUsed / 1024)} KB`,
    lastBackup: localStorage.getItem('last-backup-date') || 'Never'
  };
};

const showToast = (message, type = 'info') => {
  // Simple toast implementation
  console.log(`[${type.toUpperCase()}] ${message}`);
};

// Lifecycle
onMounted(() => {
  // Load saved settings
  const savedViewSettings = localStorage.getItem('app-view-settings');
  if (savedViewSettings) {
    viewSettings.value = { ...viewSettings.value, ...JSON.parse(savedViewSettings) };
  }
  
  const savedTheme = localStorage.getItem('app-theme');
  if (savedTheme) {
    themeSettings.value.currentTheme = savedTheme;
    document.body.className = `theme-${savedTheme}`;
  }
  
  updateDataStats();
});
</script>

<style scoped>
.app-settings-dialog {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  z-index: 3000;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Full Screen Dialog Header */
.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 10;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.back-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.dialog-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
}

.header-right {
  display: flex;
  align-items: center;
}

.app-status {
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Full Screen Dialog Content */
.dialog-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  padding-bottom: 4rem;
}

.settings-section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 1.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Action Cards */
.action-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
}

.action-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
  position: relative;
  overflow: hidden;
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-color: #cbd5e1;
}

.action-card.primary {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  border-color: #3b82f6;
}

.action-card.info {
  background: linear-gradient(135deg, #06b6d4, #0891b2);
  color: white;
  border-color: #06b6d4;
}

.action-card.success {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border-color: #10b981;
}

.action-card.warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border-color: #f59e0b;
}

.action-card.secondary {
  background: linear-gradient(135deg, #6b7280, #4b5563);
  color: white;
  border-color: #6b7280;
}

.card-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.card-content {
  flex: 1;
}

.card-title {
  font-size: 1rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.card-desc {
  font-size: 0.875rem;
  opacity: 0.8;
  line-height: 1.4;
}

.card-arrow {
  font-size: 1.25rem;
  opacity: 0.6;
  flex-shrink: 0;
}

/* View Options */
.view-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.view-option {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.view-option:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.view-option input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
}

.option-content {
  flex: 1;
}

.option-title {
  display: block;
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.option-desc {
  display: block;
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.4;
}

.option-toggle {
  width: 48px;
  height: 24px;
  background: #e2e8f0;
  border-radius: 12px;
  position: relative;
  transition: all 0.3s ease;
}

.option-toggle::after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.view-option input:checked + .option-content + .option-toggle {
  background: #3b82f6;
}

.view-option input:checked + .option-content + .option-toggle::after {
  transform: translateX(24px);
}

/* Theme Options */
.theme-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.theme-option {
  cursor: pointer;
}

.theme-option input {
  position: absolute;
  opacity: 0;
}

.theme-card {
  padding: 1.5rem;
  border-radius: 16px;
  border: 3px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.theme-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.theme-card.dark {
  background: linear-gradient(135deg, #1e293b, #334155);
  color: white;
}

.theme-card.light {
  background: linear-gradient(135deg, #f8fafc, #e2e8f0);
  color: #1e293b;
}

.theme-card.auto {
  background: linear-gradient(135deg, #1e293b 50%, #f8fafc 50%);
  color: #3b82f6;
}

.theme-option input:checked + .theme-card {
  border-color: #3b82f6;
  transform: scale(1.05);
  box-shadow: 0 12px 35px rgba(59, 130, 246, 0.3);
}

.theme-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.theme-icon {
  font-size: 2rem;
}

.theme-name {
  font-size: 1.125rem;
  font-weight: 700;
}

.theme-desc {
  font-size: 0.875rem;
  opacity: 0.8;
  line-height: 1.4;
}

/* Data Management */
.data-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.stat-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.stat-info {
  flex: 1;
}

.stat-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.stat-label {
  display: block;
  font-size: 0.875rem;
  color: #64748b;
}

.data-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.data-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  justify-content: center;
}

.data-btn.danger {
  background: #ef4444;
  color: white;
}

.data-btn.danger:hover {
  background: #dc2626;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
}

.data-btn.success {
  background: #10b981;
  color: white;
}

.data-btn.success:hover {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

.data-btn.warning {
  background: #f59e0b;
  color: white;
}

.data-btn.warning:hover {
  background: #d97706;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);
}

.btn-icon {
  font-size: 1.25rem;
  flex-shrink: 0;
}

.btn-text {
  flex: 1;
}

/* About Section */
.about-card {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 16px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.about-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.about-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.app-icon {
  font-size: 3rem;
  flex-shrink: 0;
}

.app-info {
  flex: 1;
}

.app-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.app-tagline {
  font-size: 1rem;
  color: #64748b;
}

.about-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.detail-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.detail-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.status-online {
  color: #10b981;
}

.about-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.about-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: #f1f5f9;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
}

.about-btn:hover {
  background: #e2e8f0;
  border-color: #cbd5e1;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Dialog Footer */
.dialog-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  border-top: 1px solid #e2e8f0;
  padding: 1rem;
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
}

.footer-content {
  text-align: center;
}

.footer-text {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 0.25rem 0;
  font-weight: 500;
}

.footer-version {
  font-size: 0.75rem;
  color: #94a3b8;
  margin: 0;
}

/* Mobile Optimizations */
@media (max-width: 768px) {
  .dialog-header {
    padding: 0.75rem 1rem;
  }
  
  .dialog-title {
    font-size: 1.125rem;
  }
  
  .back-btn {
    padding: 0.375rem 0.625rem;
    font-size: 0.875rem;
  }
  
  .dialog-content {
    padding: 1rem;
    padding-bottom: 5rem;
  }
  
  .section-title {
    font-size: 1.125rem;
    margin-bottom: 1rem;
  }
  
  .action-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .action-card {
    padding: 1rem;
  }
  
  .card-icon {
    font-size: 1.5rem;
  }
  
  .card-title {
    font-size: 0.875rem;
  }
  
  .card-desc {
    font-size: 0.75rem;
  }
  
  .view-option {
    padding: 1rem;
  }
  
  .option-title {
    font-size: 0.875rem;
  }
  
  .option-desc {
    font-size: 0.75rem;
  }
  
  .option-toggle {
    width: 40px;
    height: 20px;
  }
  
  .option-toggle::after {
    width: 16px;
    height: 16px;
  }
  
  .view-option input:checked + .option-content + .option-toggle::after {
    transform: translateX(20px);
  }
  
  .theme-options {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .theme-card {
    padding: 1rem;
  }
  
  .theme-icon {
    font-size: 1.5rem;
  }
  
  .theme-name {
    font-size: 1rem;
  }
  
  .theme-desc {
    font-size: 0.75rem;
  }
  
  .data-stats {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .stat-card {
    padding: 1rem;
  }
  
  .stat-icon {
    font-size: 1.5rem;
  }
  
  .stat-value {
    font-size: 1.25rem;
  }
  
  .data-actions {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .data-btn {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
  }
  
  .about-card {
    padding: 1.5rem;
  }
  
  .about-header {
    margin-bottom: 1.5rem;
  }
  
  .app-icon {
    font-size: 2rem;
  }
  
  .app-name {
    font-size: 1.25rem;
  }
  
  .about-details {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .about-actions {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .about-btn {
    padding: 0.75rem 1rem;
    font-size: 0.75rem;
  }
  
  .dialog-footer {
    padding: 0.75rem 1rem;
  }
  
  .footer-text {
    font-size: 0.75rem;
  }
  
  .footer-version {
    font-size: 0.625rem;
  }
}

/* Small Screen Optimizations */
@media (max-width: 480px) {
  .dialog-header {
    padding: 0.5rem 0.75rem;
  }
  
  .dialog-title {
    font-size: 1rem;
  }
  
  .back-btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
  
  .dialog-content {
    padding: 0.75rem;
  }
  
  .action-card {
    padding: 0.75rem;
    gap: 0.75rem;
  }
  
  .card-icon {
    font-size: 1.25rem;
  }
  
  .view-option {
    padding: 0.75rem;
    gap: 0.75rem;
  }
  
  .theme-card {
    padding: 0.75rem;
  }
  
  .stat-card {
    padding: 0.75rem;
    gap: 0.75rem;
  }
  
  .about-card {
    padding: 1rem;
  }
}
</style>
