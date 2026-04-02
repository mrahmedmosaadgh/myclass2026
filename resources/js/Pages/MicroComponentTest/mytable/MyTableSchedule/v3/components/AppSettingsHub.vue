<template>
  <div class="app-settings-hub">
    <!-- Header -->
    <div class="hub-header">
      <div class="header-info">
        <h2 class="hub-title">⚙️ App Settings</h2>
        <p class="hub-subtitle">Manage your application preferences and configurations</p>
      </div>
      <button @click="$emit('close')" class="close-btn">×</button>
    </div>

    <!-- Main Tabs -->
    <div class="main-tabs">
      <button
        v-for="tab in mainTabs"
        :key="tab.id"
        @click="selectMainTab(tab.id)"
        :class="['main-tab', { active: activeMainTab === tab.id }]"
      >
        <span class="tab-icon">{{ tab.icon }}</span>
        <span class="tab-label">{{ tab.label }}</span>
        <span v-if="tab.badge" class="tab-badge">{{ tab.badge }}</span>
      </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Timing Settings Tab -->
      <div v-if="activeMainTab === 'timing'" class="timing-tab">
        <div class="tab-description">
          <h3>⏰ Timing Settings</h3>
          <p>Configure custom timing for Primary stage and manage schedule periods</p>
        </div>

        <!-- Timing Sub-tabs -->
        <div class="sub-tabs">
          <button
            v-for="subTab in timingSubTabs"
            :key="subTab.id"
            @click="selectTimingSubTab(subTab.id)"
            :class="['sub-tab', { active: activeTimingSubTab === subTab.id }]"
          >
            {{ subTab.label }}
          </button>
        </div>

        <!-- Global Timing Sub-tab -->
        <div v-if="activeTimingSubTab === 'global'" class="global-timing">
          <div class="timing-editor">
            <div class="timing-list">
              <div
                v-for="(period, index) in globalTimings"
                :key="period.id || index"
                class="timing-item"
              >
                <div class="period-info">
                  <input
                    v-model="period.title"
                    type="text"
                    class="period-title"
                    placeholder="Period title"
                  />
                  <select v-model="period.type" class="period-type">
                    <option value="lesson">Lesson</option>
                    <option value="break">Break</option>
                    <option value="activity">Activity</option>
                  </select>
                </div>
                
                <div class="time-range">
                  <input
                    v-model="period.start"
                    type="time"
                    class="time-input"
                  />
                  <span class="time-separator">−</span>
                  <input
                    v-model="period.end"
                    type="time"
                    class="time-input"
                  />
                </div>
                
                <div class="period-actions">
                  <button @click="movePeriodUp(index)" class="action-btn" :disabled="index === 0">↑</button>
                  <button @click="movePeriodDown(index)" class="action-btn" :disabled="index === globalTimings.length - 1">↓</button>
                  <button @click="removePeriod(index)" class="action-btn danger">×</button>
                </div>
              </div>
            </div>
            
            <div class="timing-actions">
              <button @click="addNewPeriod" class="add-btn">+ Add Period</button>
              <button @click="resetToDefault" class="reset-btn">Reset to Default</button>
            </div>
          </div>
        </div>

        <!-- Day-specific Timing Sub-tab -->
        <div v-else-if="activeTimingSubTab === 'days'" class="days-timing">
          <div class="day-selector">
            <label>Select Day:</label>
            <select v-model="selectedDay" class="day-select">
              <option value="">All Days</option>
              <option value="d1">Day 1</option>
              <option value="d2">Day 2</option>
              <option value="d3">Day 3</option>
              <option value="d4">Day 4</option>
              <option value="d5">Day 5</option>
              <option value="d6">Day 6</option>
            </select>
          </div>

          <div v-if="selectedDay" class="day-timing-editor">
            <div class="timing-list">
              <div
                v-for="(period, index) in dayTimings"
                :key="period.id || index"
                class="timing-item"
              >
                <div class="period-info">
                  <input
                    v-model="period.title"
                    type="text"
                    class="period-title"
                    placeholder="Period title"
                  />
                  <select v-model="period.type" class="period-type">
                    <option value="lesson">Lesson</option>
                    <option value="break">Break</option>
                    <option value="activity">Activity</option>
                  </select>
                </div>
                
                <div class="time-range">
                  <input
                    v-model="period.start"
                    type="time"
                    class="time-input"
                  />
                  <span class="time-separator">−</span>
                  <input
                    v-model="period.end"
                    type="time"
                    class="time-input"
                  />
                </div>
                
                <div class="period-actions">
                  <button @click="moveDayPeriodUp(index)" class="action-btn" :disabled="index === 0">↑</button>
                  <button @click="moveDayPeriodDown(index)" class="action-btn" :disabled="index === dayTimings.length - 1">↓</button>
                  <button @click="removeDayPeriod(index)" class="action-btn danger">×</button>
                </div>
              </div>
            </div>
            
            <div class="timing-actions">
              <button @click="addNewDayPeriod" class="add-btn">+ Add Period</button>
              <button @click="clearDayTiming" class="reset-btn">Clear Day Timing</button>
            </div>
          </div>
        </div>

        <!-- Quick Templates Sub-tab -->
        <div v-else-if="activeTimingSubTab === 'templates'" class="templates">
          <div class="template-grid">
            <div
              v-for="template in timingTemplates"
              :key="template.id"
              class="template-card"
              @click="applyTemplate(template)"
            >
              <div class="template-icon">{{ template.icon }}</div>
              <h4 class="template-name">{{ template.name }}</h4>
              <p class="template-description">{{ template.description }}</p>
              <div class="template-periods">{{ template.periods.length }} periods</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Display Settings Tab -->
      <div v-else-if="activeMainTab === 'display'" class="display-settings">
        <div class="tab-description">
          <h3>🎨 Display Settings</h3>
          <p>Customize the visual appearance and layout of the app</p>
        </div>

        <div class="settings-grid">
          <div class="setting-group">
            <h4>Theme</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="radio" name="theme" value="dark" v-model="displaySettings.theme" />
                <span>Dark Theme</span>
              </label>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="radio" name="theme" value="light" v-model="displaySettings.theme" />
                <span>Light Theme</span>
              </label>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="radio" name="theme" value="auto" v-model="displaySettings.theme" />
                <span>Auto (System)</span>
              </label>
            </div>
          </div>

          <div class="setting-group">
            <h4>Layout</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="displaySettings.compactMode" />
                <span>Compact Mode</span>
              </label>
              <p class="setting-help">Reduce spacing and use smaller elements</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="displaySettings.showGrid" />
                <span>Show Grid Lines</span>
              </label>
              <p class="setting-help">Display grid lines in schedule view</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="displaySettings.showTimeLabels" />
                <span>Show Time Labels</span>
              </label>
              <p class="setting-help">Display time labels on schedule periods</p>
            </div>
          </div>

          <div class="setting-group">
            <h4>Colors</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="displaySettings.colorCodeTypes" />
                <span>Color Code Period Types</span>
              </label>
              <p class="setting-help">Use different colors for lessons, breaks, activities</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="displaySettings.gradientEffects" />
                <span>Gradient Effects</span>
              </label>
              <p class="setting-help">Enable gradient backgrounds and effects</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications Tab -->
      <div v-else-if="activeMainTab === 'notifications'" class="notification-settings">
        <div class="tab-description">
          <h3>🔔 Notifications</h3>
          <p>Manage alerts, reminders, and system notifications</p>
        </div>

        <div class="settings-grid">
          <div class="setting-group">
            <h4>Schedule Notifications</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="notificationSettings.periodAlerts" />
                <span>Period Change Alerts</span>
              </label>
              <p class="setting-help">Get notified when periods are about to start/end</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="notificationSettings.breakReminders" />
                <span>Break Reminders</span>
              </label>
              <p class="setting-help">Remind when breaks are ending</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="notificationSettings.dailySummary" />
                <span>Daily Schedule Summary</span>
              </label>
              <p class="setting-help">Receive daily schedule overview</p>
            </div>
          </div>

          <div class="setting-group">
            <h4>System Notifications</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="notificationSettings.updateAlerts" />
                <span>App Updates</span>
              </label>
              <p class="setting-help">Notify about app updates and new features</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="notificationSettings.backupReminders" />
                <span>Backup Reminders</span>
              </label>
              <p class="setting-help">Remind to backup schedule data</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="notificationSettings.errorReports" />
                <span>Error Reports</span>
              </label>
              <p class="setting-help">Notify about system errors and issues</p>
            </div>
          </div>

          <div class="setting-group">
            <h4>Notification Methods</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="notificationSettings.browserNotifications" />
                <span>Browser Notifications</span>
              </label>
              <p class="setting-help">Use system browser notifications</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="notificationSettings.soundAlerts" />
                <span>Sound Alerts</span>
              </label>
              <p class="setting-help">Play sound for important notifications</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Data & Sync Tab -->
      <div v-else-if="activeMainTab === 'data'" class="data-settings">
        <div class="tab-description">
          <h3>📁 Data & Sync</h3>
          <p>Manage data storage, backup, and synchronization</p>
        </div>

        <div class="data-grid">
          <div class="data-section">
            <h4>Export Data</h4>
            <p>Download your schedule data as backup</p>
            <button @click="exportTimings" class="export-btn">
              📥 Export Schedule Data
            </button>
          </div>

          <div class="data-section">
            <h4>Import Data</h4>
            <p>Restore schedule from backup file</p>
            <input
              type="file"
              ref="fileInput"
              accept=".json"
              @change="handleFileImport"
              style="display: none"
            />
            <button @click="$refs.fileInput.click()" class="import-btn">
              📤 Import from File
            </button>
          </div>

          <div class="data-section">
            <h4>Clear Data</h4>
            <p>Reset all schedule data to defaults</p>
            <button @click="confirmClearData" class="clear-btn">
              🗑️ Clear All Data
            </button>
          </div>

          <div class="data-section">
            <h4>Cloud Sync</h4>
            <p>Synchronize data across devices (coming soon)</p>
            <button @click="showToast('Cloud sync coming soon!', 'info')" class="sync-btn" disabled>
              ☁️ Enable Cloud Sync
            </button>
          </div>
        </div>

        <div class="storage-info">
          <h4>Storage Information</h4>
          <div class="storage-stats">
            <div class="stat-item">
              <span class="stat-label">Schedule Entries:</span>
              <span class="stat-value">{{ storageInfo.scheduleEntries }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Timing Overrides:</span>
              <span class="stat-value">{{ storageInfo.timingOverrides }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Storage Used:</span>
              <span class="stat-value">{{ storageInfo.storageUsed }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Last Backup:</span>
              <span class="stat-value">{{ storageInfo.lastBackup }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Advanced Tab -->
      <div v-else-if="activeMainTab === 'advanced'" class="advanced-settings">
        <div class="tab-description">
          <h3>🔧 Advanced Settings</h3>
          <p>Advanced configuration and developer options</p>
        </div>

        <div class="settings-grid">
          <div class="setting-group">
            <h4>Performance</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="advancedSettings.hardwareAcceleration" />
                <span>Hardware Acceleration</span>
              </label>
              <p class="setting-help">Use GPU acceleration for animations</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="advancedSettings.lazyLoading" />
                <span>Lazy Loading</span>
              </label>
              <p class="setting-help">Load schedule data on demand</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="advancedSettings.caching" />
                <span>Enable Caching</span>
              </label>
              <p class="setting-help">Cache schedule data for faster access</p>
            </div>
          </div>

          <div class="setting-group">
            <h4>Debug</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="advancedSettings.debugMode" />
                <span>Debug Mode</span>
              </label>
              <p class="setting-help">Show debug information and logs</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="advancedSettings.verboseLogging" />
                <span>Verbose Logging</span>
              </label>
              <p class="setting-help">Enable detailed console logging</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="advancedSettings.performanceMetrics" />
                <span>Performance Metrics</span>
              </label>
              <p class="setting-help">Track and display performance data</p>
            </div>
          </div>

          <div class="setting-group">
            <h4>Experimental</h4>
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="advancedSettings.betaFeatures" />
                <span>Beta Features</span>
              </label>
              <p class="setting-help">Enable experimental features</p>
            </div>
            
            <div class="setting-item">
              <label class="setting-toggle">
                <input type="checkbox" v-model="advancedSettings.aiSuggestions" />
                <span>AI Suggestions</span>
              </label>
              <p class="setting-help">Get AI-powered schedule suggestions</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Actions -->
    <div class="hub-footer">
      <button @click="saveAllSettings" class="save-btn" :disabled="!hasChanges">
        💾 Save All Changes
      </button>
      <button @click="$emit('close')" class="cancel-btn">
        Cancel
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  modelValue: { type: Object, required: true }
});

const emit = defineEmits(['update:modelValue', 'close']);

// Component state
const activeMainTab = ref('timing');
const activeTimingSubTab = ref('global');
const selectedDay = ref('');
const hasChanges = ref(false);

// Main tabs configuration
const mainTabs = [
  { id: 'timing', label: 'Timing', icon: '⏰', badge: 'Primary' },
  { id: 'display', label: 'Display', icon: '🎨' },
  { id: 'notifications', label: 'Notifications', icon: '🔔' },
  { id: 'data', label: 'Data & Sync', icon: '📁' },
  { id: 'advanced', label: 'Advanced', icon: '🔧' }
];

// Timing sub-tabs
const timingSubTabs = [
  { id: 'global', label: 'Global Timing' },
  { id: 'days', label: 'Day Specific' },
  { id: 'templates', label: 'Quick Templates' }
];

// Timing templates
const timingTemplates = [
  {
    id: 'standard',
    name: 'Standard Primary',
    description: '6 periods with 2 breaks',
    icon: '📚',
    periods: [
      { title: 'Period 1', type: 'lesson', start: '09:00', end: '09:40' },
      { title: 'Period 2', type: 'lesson', start: '09:40', end: '10:20' },
      { title: 'First Break', type: 'break', start: '10:20', end: '10:40' },
      { title: 'Period 3', type: 'lesson', start: '10:40', end: '11:20' },
      { title: 'Period 4', type: 'lesson', start: '11:20', end: '12:00' },
      { title: 'Second Break', type: 'break', start: '12:00', end: '12:20' },
      { title: 'Period 5', type: 'lesson', start: '12:20', end: '13:00' },
      { title: 'Period 6', type: 'lesson', start: '13:00', end: '13:40' }
    ]
  },
  {
    id: 'compact',
    name: 'Compact Schedule',
    description: 'Shorter periods, quick transitions',
    icon: '⚡',
    periods: [
      { title: 'Period 1', type: 'lesson', start: '09:00', end: '09:30' },
      { title: 'Period 2', type: 'lesson', start: '09:30', end: '10:00' },
      { title: 'Quick Break', type: 'break', start: '10:00', end: '10:15' },
      { title: 'Period 3', type: 'lesson', start: '10:15', end: '10:45' },
      { title: 'Period 4', type: 'lesson', start: '10:45', end: '11:15' },
      { title: 'Lunch Break', type: 'break', start: '11:15', end: '11:45' },
      { title: 'Period 5', type: 'lesson', start: '11:45', end: '12:15' },
      { title: 'Period 6', type: 'lesson', start: '12:15', end: '12:45' }
    ]
  },
  {
    id: 'extended',
    name: 'Extended Learning',
    description: 'Longer periods for deep learning',
    icon: '🔬',
    periods: [
      { title: 'Period 1', type: 'lesson', start: '09:00', end: '09:50' },
      { title: 'Period 2', type: 'lesson', start: '09:50', end: '10:40' },
      { title: 'Long Break', type: 'break', start: '10:40', end: '11:00' },
      { title: 'Period 3', type: 'lesson', start: '11:00', end: '11:50' },
      { title: 'Period 4', type: 'lesson', start: '11:50', end: '12:40' },
      { title: 'Lunch Break', type: 'break', start: '12:40', end: '13:10' },
      { title: 'Period 5', type: 'lesson', start: '13:10', end: '14:00' }
    ]
  }
];

// Settings state
const displaySettings = ref({
  theme: 'dark',
  compactMode: false,
  showGrid: true,
  showTimeLabels: true,
  colorCodeTypes: true,
  gradientEffects: true
});

const notificationSettings = ref({
  periodAlerts: false,
  breakReminders: false,
  dailySummary: false,
  updateAlerts: true,
  backupReminders: true,
  errorReports: true,
  browserNotifications: false,
  soundAlerts: false
});

const advancedSettings = ref({
  hardwareAcceleration: true,
  lazyLoading: true,
  caching: true,
  debugMode: false,
  verboseLogging: false,
  performanceMetrics: false,
  betaFeatures: false,
  aiSuggestions: false
});

const storageInfo = ref({
  scheduleEntries: 0,
  timingOverrides: 0,
  storageUsed: '0 KB',
  lastBackup: 'Never'
});

// Reactive data
const globalTimings = ref([]);
const dayTimings = ref([]);

// Computed properties
const timingsData = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

// Methods
const selectMainTab = (tabId) => {
  activeMainTab.value = tabId;
};

const selectTimingSubTab = (subTabId) => {
  activeTimingSubTab.value = subTabId;
};

// Timing methods
const addNewPeriod = () => {
  const newId = Math.max(...globalTimings.value.map(p => typeof p.id === 'number' ? p.id : 0), 0) + 1;
  globalTimings.value.push({
    id: newId,
    title: `Period ${newId}`,
    type: 'lesson',
    start: '09:00',
    end: '09:30'
  });
  hasChanges.value = true;
};

const removePeriod = (index) => {
  globalTimings.value.splice(index, 1);
  hasChanges.value = true;
};

const movePeriodUp = (index) => {
  if (index > 0) {
    [globalTimings.value[index], globalTimings.value[index - 1]] = 
    [globalTimings.value[index - 1], globalTimings.value[index]];
    hasChanges.value = true;
  }
};

const movePeriodDown = (index) => {
  if (index < globalTimings.value.length - 1) {
    [globalTimings.value[index], globalTimings.value[index + 1]] = 
    [globalTimings.value[index + 1], globalTimings.value[index]];
    hasChanges.value = true;
  }
};

const addNewDayPeriod = () => {
  const newId = Math.max(...dayTimings.value.map(p => typeof p.id === 'number' ? p.id : 0), 0) + 1;
  dayTimings.value.push({
    id: newId,
    title: `Period ${newId}`,
    type: 'lesson',
    start: '09:00',
    end: '09:30'
  });
  hasChanges.value = true;
};

const removeDayPeriod = (index) => {
  dayTimings.value.splice(index, 1);
  hasChanges.value = true;
};

const moveDayPeriodUp = (index) => {
  if (index > 0) {
    [dayTimings.value[index], dayTimings.value[index - 1]] = 
    [dayTimings.value[index - 1], dayTimings.value[index]];
    hasChanges.value = true;
  }
};

const moveDayPeriodDown = (index) => {
  if (index < dayTimings.value.length - 1) {
    [dayTimings.value[index], dayTimings.value[index + 1]] = 
    [dayTimings.value[index + 1], dayTimings.value[index]];
    hasChanges.value = true;
  }
};

const clearDayTiming = () => {
  dayTimings.value = [];
  hasChanges.value = true;
};

const resetToDefault = () => {
  globalTimings.value = JSON.parse(JSON.stringify(timingTemplates[0].periods));
  hasChanges.value = true;
};

const applyTemplate = (template) => {
  globalTimings.value = JSON.parse(JSON.stringify(template.periods));
  hasChanges.value = true;
  showToast(`Applied "${template.name}" template`, 'success');
};

// Data management methods
const exportTimings = () => {
  const data = {
    timingData: timingsData.value,
    displaySettings: displaySettings.value,
    notificationSettings: notificationSettings.value,
    advancedSettings: advancedSettings.value,
    exportDate: new Date().toISOString()
  };
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `app-settings-${new Date().toISOString().split('T')[0]}.json`;
  a.click();
  URL.revokeObjectURL(url);
  
  showToast('Settings exported successfully!', 'success');
};

const handleFileImport = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const reader = new FileReader();
  reader.onload = (e) => {
    try {
      const data = JSON.parse(e.target.result);
      if (data.timingData) timingsData.value = data.timingData;
      if (data.displaySettings) displaySettings.value = { ...displaySettings.value, ...data.displaySettings };
      if (data.notificationSettings) notificationSettings.value = { ...notificationSettings.value, ...data.notificationSettings };
      if (data.advancedSettings) advancedSettings.value = { ...advancedSettings.value, ...data.advancedSettings };
      hasChanges.value = true;
      showToast('Settings imported successfully!', 'success');
    } catch (error) {
      showToast('Invalid file format', 'error');
    }
  };
  reader.readAsText(file);
};

const confirmClearData = () => {
  if (confirm('Are you sure you want to clear all data? This action cannot be undone.')) {
    globalTimings.value = [];
    dayTimings.value = [];
    timingsData.value = { default: [], overrides: {} };
    hasChanges.value = true;
    showToast('All data cleared', 'warning');
  }
};

const saveAllSettings = () => {
  const newTimingsData = {
    ...timingsData.value,
    default: globalTimings.value,
    overrides: {
      ...timingsData.value.overrides,
      prim: {
        default: globalTimings.value,
        days: {
          ...(timingsData.value.overrides?.prim?.days || {}),
          ...(selectedDay.value && { [selectedDay.value]: dayTimings.value })
        }
      }
    }
  };
  
  emit('update:modelValue', newTimingsData);
  
  // Save all settings to localStorage
  localStorage.setItem('app-settings-hub', JSON.stringify({
    globalTimings: globalTimings.value,
    dayTimings: dayTimings.value,
    displaySettings: displaySettings.value,
    notificationSettings: notificationSettings.value,
    advancedSettings: advancedSettings.value
  }));
  
  hasChanges.value = false;
  showToast('All settings saved successfully!', 'success');
  emit('close');
};

const updateStorageInfo = () => {
  storageInfo.value = {
    scheduleEntries: globalTimings.value.length,
    timingOverrides: Object.keys(timingsData.value.overrides || {}).length,
    storageUsed: `${Math.round(JSON.stringify({
      globalTimings: globalTimings.value,
      displaySettings: displaySettings.value,
      notificationSettings: notificationSettings.value,
      advancedSettings: advancedSettings.value
    }).length / 1024)} KB`,
    lastBackup: localStorage.getItem('last-backup-date') || 'Never'
  };
};

const showToast = (message, type = 'info') => {
  // Simple toast implementation - you might want to replace with a proper toast component
  console.log(`[${type.toUpperCase()}] ${message}`);
};

// Watchers
watch(selectedDay, (newDay) => {
  if (newDay) {
    const existingDayTiming = timingsData.value.overrides?.prim?.days?.[newDay];
    dayTimings.value = existingDayTiming 
      ? JSON.parse(JSON.stringify(existingDayTiming))
      : JSON.parse(JSON.stringify(globalTimings.value));
  }
});

// Watch all settings for changes
watch([globalTimings, dayTimings, displaySettings, notificationSettings, advancedSettings], () => {
  hasChanges.value = true;
}, { deep: true });

// Initialize on mount
onMounted(() => {
  // Load current timings
  globalTimings.value = JSON.parse(JSON.stringify(timingsData.value.default || timingTemplates[0].periods));
  
  // Load saved settings from localStorage
  const savedSettings = localStorage.getItem('app-settings-hub');
  if (savedSettings) {
    try {
      const parsed = JSON.parse(savedSettings);
      if (parsed.displaySettings) displaySettings.value = { ...displaySettings.value, ...parsed.displaySettings };
      if (parsed.notificationSettings) notificationSettings.value = { ...notificationSettings.value, ...parsed.notificationSettings };
      if (parsed.advancedSettings) advancedSettings.value = { ...advancedSettings.value, ...parsed.advancedSettings };
    } catch (error) {
      console.error('Failed to load saved settings:', error);
    }
  }
  
  updateStorageInfo();
});
</script>

<style scoped>
.app-settings-hub {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  max-width: 900px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.hub-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.header-info h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.hub-subtitle {
  margin: 0;
  font-size: 0.875rem;
  opacity: 0.9;
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.25rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.main-tabs {
  display: flex;
  padding: 1rem 1.5rem 0;
  gap: 0.5rem;
  border-bottom: 1px solid #e2e8f0;
  overflow-x: auto;
}

.main-tab {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  font-weight: 600;
  color: #64748b;
  transition: all 0.3s ease;
  white-space: nowrap;
  position: relative;
}

.main-tab:hover {
  color: #475569;
  background: #f8fafc;
}

.main-tab.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

.tab-icon {
  font-size: 1rem;
}

.tab-label {
  font-size: 0.875rem;
}

.tab-badge {
  background: #ef4444;
  color: white;
  font-size: 0.625rem;
  padding: 0.125rem 0.375rem;
  border-radius: 10px;
  font-weight: 600;
}

.tab-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.tab-description {
  margin-bottom: 1.5rem;
}

.tab-description h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.tab-description p {
  color: #64748b;
  font-size: 0.875rem;
  margin: 0;
}

.sub-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  overflow-x: auto;
}

.sub-tab {
  padding: 0.5rem 1rem;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  font-weight: 500;
  color: #64748b;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.sub-tab:hover {
  color: #475569;
}

.sub-tab.active {
  color: #3b82f6;
  border-bottom-color: #3b82f6;
}

/* Timing Styles */
.timing-editor {
  max-width: 600px;
}

.timing-list {
  margin-bottom: 1.5rem;
}

.timing-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  margin-bottom: 0.75rem;
}

.period-info {
  flex: 1;
  display: flex;
  gap: 0.5rem;
}

.period-title {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
}

.period-type {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
}

.time-range {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.time-input {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  width: 80px;
}

.time-separator {
  font-weight: 600;
  color: #64748b;
}

.period-actions {
  display: flex;
  gap: 0.25rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  color: #64748b;
  transition: all 0.3s ease;
}

.action-btn:hover:not(:disabled) {
  background: #f8fafc;
  border-color: #9ca3af;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.action-btn.danger {
  color: #dc2626;
  border-color: #fca5a5;
}

.action-btn.danger:hover:not(:disabled) {
  background: #fef2f2;
  border-color: #f87171;
}

.timing-actions {
  display: flex;
  gap: 1rem;
}

.add-btn,
.reset-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.add-btn {
  background: #10b981;
  color: white;
}

.add-btn:hover {
  background: #059669;
}

.reset-btn {
  background: #f1f5f9;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.reset-btn:hover {
  background: #e2e8f0;
}

.day-selector {
  margin-bottom: 1.5rem;
}

.day-selector label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.5rem;
}

.day-select {
  width: 200px;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
}

.template-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.template-card {
  padding: 1.5rem;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
}

.template-card:hover {
  border-color: #3b82f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.template-icon {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

.template-name {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.template-description {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 1rem 0;
}

.template-periods {
  font-size: 0.75rem;
  color: #3b82f6;
  font-weight: 600;
}

/* Settings Grid */
.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.setting-group {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.setting-group h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1rem 0;
}

.setting-item {
  margin-bottom: 1rem;
}

.setting-toggle {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  font-weight: 500;
  color: #374151;
}

.setting-help {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 0.25rem 0 0 2rem;
}

/* Data Settings */
.data-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.data-section {
  padding: 1.5rem;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  text-align: center;
}

.data-section h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.data-section p {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 1rem 0;
}

.export-btn,
.import-btn,
.clear-btn,
.sync-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.export-btn {
  background: #10b981;
  color: white;
}

.import-btn {
  background: #3b82f6;
  color: white;
}

.clear-btn {
  background: #ef4444;
  color: white;
}

.sync-btn {
  background: #6b7280;
  color: white;
}

.export-btn:hover {
  background: #059669;
}

.import-btn:hover {
  background: #2563eb;
}

.clear-btn:hover {
  background: #dc2626;
}

.storage-info {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.storage-info h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 1rem 0;
}

.storage-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e2e8f0;
}

.stat-label {
  font-size: 0.875rem;
  color: #64748b;
}

.stat-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1e293b;
}

.hub-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.save-btn,
.cancel-btn {
  flex: 1;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.save-btn {
  background: #3b82f6;
  color: white;
}

.save-btn:hover:not(:disabled) {
  background: #2563eb;
}

.save-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.cancel-btn {
  background: white;
  color: #334155;
  border: 1px solid #e2e8f0;
}

.cancel-btn:hover {
  background: #f8fafc;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .app-settings-hub {
    max-height: 100vh;
    border-radius: 0;
  }
  
  .hub-header {
    padding: 1rem;
  }
  
  .main-tabs {
    padding: 1rem;
    gap: 0.25rem;
  }
  
  .main-tab {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
  }
  
  .tab-content {
    padding: 1rem;
  }
  
  .timing-item {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }
  
  .period-info {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .time-range {
    justify-content: center;
  }
  
  .period-actions {
    justify-content: center;
  }
  
  .settings-grid {
    grid-template-columns: 1fr;
  }
  
  .data-grid {
    grid-template-columns: 1fr;
  }
  
  .storage-stats {
    grid-template-columns: 1fr;
  }
}
</style>
