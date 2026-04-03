<template>
  <Head>
    <title>My Schedule App V4 - Offline Auto-Save</title>
    <meta name="description" content="Advanced schedule app with offline auto-save, user folder persistence, and enhanced PWA features.">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/my-fly-schedule-app/v4/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/my-fly-schedule-app/v4/icon.svg">
  </Head>

  <div class="standalone-schedule-app-v2">
    <!-- Mobile-Optimized Header -->
    <header class="app-header" :class="{ 'compact': isScrolled }">
      <div class="header-content">
        <div class="brand-section">
          <button 
            @click="toggleMenu" 
            class="menu-btn"
            :class="{ active: showMenu }"
            aria-label="Toggle menu"
          >
            <span class="menu-icon">☰</span>
          </button>
          
          <div class="brand-info">
            <h1 class="app-title">📅 SCHEDULE V4</h1>
            <p class="app-subtitle">OFFLINE • AUTO-SAVE • USER FOLDER</p>
          </div>
        </div>
        
        <div class="status-section">
          <div class="status-indicators">
            <span class="status-badge" :class="serviceWorkerStatus">
              <span class="status-icon">{{ getStatusIcon() }}</span>
              {{ serviceWorkerStatus }}
            </span>
            
            <span class="status-badge" :class="autoSaveStatus">
              <span class="status-icon">{{ getAutoSaveIcon() }}</span>
              {{ autoSaveStatus }}
            </span>
            
            <span v-if="isOnline" class="online-indicator">🟢</span>
            <span v-else class="offline-indicator">🔴</span>
          </div>
          
          <button 
            v-if="canInstall && !isInstalled" 
            @click="handleInstall"
            class="install-btn"
          >
            📲 Install
          </button>
          
          <span v-if="isInstalled" class="installed-badge">✓ Installed</span>
        </div>
      </div>
      
      <!-- Slide-out Menu -->
      <div class="slide-menu" :class="{ active: showMenu }">
        <div class="menu-header">
          <h3>Menu</h3>
          <button @click="toggleMenu" class="close-menu-btn">×</button>
        </div>
        
        <nav class="menu-nav">
          <a href="#" @click.prevent="scrollToTop" class="menu-item">
            <span class="menu-item-icon">🏠</span>
            <span class="menu-item-text">Home</span>
          </a>
          
          <a href="#" @click.prevent="openViewSelector" class="menu-item">
            <span class="menu-item-icon">👁️</span>
            <span class="menu-item-text">Schedule View</span>
          </a>
          
          <a href="#" @click.prevent="openTimingSelector" class="menu-item">
            <span class="menu-item-icon">⏰</span>
            <span class="menu-item-text">Choose Timing</span>
          </a>
          
          <a href="#" @click.prevent="openTimeOverride" class="menu-item">
            <span class="menu-item-icon">🕐</span>
            <span class="menu-item-text">Test Time Override</span>
          </a>
          
          <a href="#" @click.prevent="openDataManager" class="menu-item">
            <span class="menu-item-icon">📁</span>
            <span class="menu-item-text">Data Manager</span>
          </a>
          
          <a href="#" @click.prevent="openSettings" class="menu-item">
            <span class="menu-item-icon">⚙️</span>
            <span class="menu-item-text">Settings</span>
          </a>
          
          <a href="#" @click.prevent="exportData" class="menu-item">
            <span class="menu-item-icon">📥</span>
            <span class="menu-item-text">Quick Export</span>
          </a>
          
          <a href="#" @click.prevent="showAbout" class="menu-item">
            <span class="menu-item-icon">ℹ️</span>
            <span class="menu-item-text">About</span>
          </a>
        </nav>
        
        <div class="menu-footer">
          <p class="app-version">Version 4.0</p>
          <p class="app-copyright">© 2026 Schedule App</p>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="app-main" :class="{ 'with-menu': showMenu }">
      <MyTableScheduleV4 @data-changed="handleDataChange" />
    </main>

    <!-- Floating Action Button -->
    <button
      v-if="showScrollToTop"
      @click="scrollToTop"
      class="fab-scroll-top"
      aria-label="Scroll to top"
    >
      ↑
    </button>

    <!-- Settings Modal -->
    <div v-if="showSettingsModal" class="modal-overlay" @click="closeSettings">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Settings</h3>
          <button @click="closeSettings" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="setting-item">
            <label class="setting-label">Enable Notifications</label>
            <button 
              @click="requestNotificationPermission"
              class="setting-btn"
              :disabled="!showNotifyBtn"
            >
              {{ showNotifyBtn ? 'Enable' : 'Enabled' }}
            </button>
          </div>
          
          <div class="setting-item">
            <label class="setting-label">Clear Cache</label>
            <button @click="clearCache" class="setting-btn danger">
              Clear
            </button>
          </div>
          
          <div class="setting-item">
            <label class="setting-label">Reset App</label>
            <button @click="resetApp" class="setting-btn danger">
              Reset
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Data Manager Modal -->
    <div v-if="showDataManagerModal" class="modal-overlay large" @click="closeDataManager">
      <div class="modal-content large" @click.stop>
        <div class="modal-header">
          <h3>📁 Data Manager</h3>
          <button @click="closeDataManager" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <DataManager @notification="handleNotification" />
        </div>
      </div>
    </div>

    <!-- About Modal -->
    <div v-if="showAboutModal" class="modal-overlay" @click="closeAbout">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>About Schedule App V4</h3>
          <button @click="closeAbout" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="about-content">
            <div class="app-logo">📅</div>
            <h4>Schedule App V4</h4>
            <p>Advanced schedule management with offline auto-save and user folder persistence.</p>
            
            <div class="features-list">
              <h5>Features:</h5>
              <ul>
                <li>📱 Mobile-optimized interface</li>
                <li>🔄 Multiple view modes (Card, Table, List, School)</li>
                <li>🏫 Complete school timetable</li>
                <li>🔔 Push notifications</li>
                <li>💾 Offline functionality</li>
                <li>🔄 Auto-save to user folder</li>
                <li>📥 Data export/import</li>
                <li>☁️ Cloud synchronization</li>
                <li>📊 Backup management</li>
              </ul>
            </div>
            
            <div class="app-info">
              <p><strong>Version:</strong> 4.0.0</p>
              <p><strong>Build:</strong> Offline Auto-Save</p>
              <p><strong>Platform:</strong> Progressive Web App</p>
              <p><strong>URL:</strong> https://qudratpro.com/my-schedule-app/v4</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!-- View Selector Modal -->
    <div v-if="showViewSelectorModal" class="modal-overlay" @click="closeViewSelector">
      <div class="modal-content view-selector-modal" @click.stop>
        <div class="modal-header">
          <h3>Choose Schedule View</h3>
          <button @click="closeViewSelector" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="view-options">
            <button
              v-for="view in viewOptions"
              :key="view.id"
              @click="selectView(view.id)"
              :class="['view-option-btn', { active: currentView === view.id }]"
            >
              <div class="view-option-icon">{{ view.icon }}</div>
              <div class="view-option-content">
                <h4 class="view-option-title">{{ view.title }}</h4>
                <p class="view-option-description">{{ view.description }}</p>
                <div class="view-option-features">
                  <span v-for="feature in view.features" :key="feature" class="feature-tag">
                    {{ feature }}
                  </span>
                </div>
              </div>
              <div class="view-option-status">
                <span v-if="currentView === view.id" class="current-indicator">✓ Current</span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Timing Selector Modal -->
    <div v-if="showTimingSelectorModal" class="modal-overlay" @click="closeTimingSelector">
      <div class="modal-content timing-selector-modal" @click.stop>
        <div class="modal-header">
          <h3>Choose Timing Settings</h3>
          <button @click="closeTimingSelector" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="timing-selection">
            <!-- Stage Selection -->
            <div class="selection-section">
              <h4 class="section-title">
                <span class="section-icon">🏫</span>
                Select Stage
              </h4>
              <div class="stage-grid">
                <button
                  v-for="stage in stageOptions"
                  :key="stage"
                  @click="selectedStage = stage"
                  :class="['stage-btn', { active: selectedStage === stage }]"
                >
                  {{ stage }}
                </button>
              </div>
            </div>

            <!-- Day Selection -->
            <div class="selection-section">
              <h4 class="section-title">
                <span class="section-icon">📅</span>
                Select Day
              </h4>
              <div class="day-grid">
                <button
                  v-for="day in dayOptions"
                  :key="day"
                  @click="selectedDay = day"
                  :class="['day-btn', { active: selectedDay === day }]"
                >
                  {{ day }}
                </button>
              </div>
            </div>

            <!-- Current Selection Display -->
            <div class="current-selection">
              <h4 class="section-title">
                <span class="section-icon">✅</span>
                Current Selection
              </h4>
              <div class="selection-display">
                <div class="selection-item">
                  <span class="selection-label">Stage:</span>
                  <span class="selection-value">{{ selectedStage }}</span>
                </div>
                <div class="selection-item">
                  <span class="selection-label">Day:</span>
                  <span class="selection-value">{{ selectedDay }}</span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="timing-actions">
              <button @click="resetTimingSelection" class="action-btn secondary">
                🔄 Reset to Default
              </button>
              <button @click="saveTimingSelection" class="action-btn primary">
                💾 Save Selection
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Time Override Modal -->
    <div v-if="showTimeOverrideModal" class="modal-overlay" @click="closeTimeOverride">
      <div class="modal-content time-override-modal" @click.stop>
        <div class="modal-header">
          <h3>Test Time Override</h3>
          <button @click="closeTimeOverride" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="time-override-content">
            <!-- Current Status -->
            <div class="override-status">
              <div class="status-indicator" :class="{ active: isTimeOverrideActive }">
                <span class="status-icon">{{ isTimeOverrideActive ? '🔴' : '⚪' }}</span>
                <span class="status-text">
                  {{ isTimeOverrideActive ? 'Override Active' : 'Using Real Time' }}
                </span>
              </div>
            </div>

            <!-- Real Time Display -->
            <div class="time-display-section">
              <h4 class="section-title">
                <span class="section-icon">🕐</span>
                Current Real Time
              </h4>
              <div class="time-display">
                <div class="time-value">{{ formatTime(currentRealTime) }}</div>
                <div class="time-date">{{ formatDate(currentRealTime) }}</div>
              </div>
            </div>

            <!-- Override Time Input -->
            <div class="override-input-section">
              <h4 class="section-title">
                <span class="section-icon">⏰</span>
                Override Time
              </h4>
              <div class="time-input-grid">
                <div class="input-group">
                  <label class="input-label">Date</label>
                  <input
                    v-model="overrideDate"
                    type="date"
                    class="time-input"
                    :max="maxDate"
                  />
                </div>
                <div class="input-group">
                  <label class="input-label">Time</label>
                  <input
                    v-model="overrideTime"
                    type="time"
                    class="time-input"
                  />
                </div>
              </div>
            </div>

            <!-- Quick Time Options -->
            <div class="quick-time-section">
              <h4 class="section-title">
                <span class="section-icon">⚡</span>
                Quick Time Options
              </h4>
              <div class="quick-time-grid">
                <button
                  v-for="option in quickTimeOptions"
                  :key="option.id"
                  @click="setQuickTime(option)"
                  class="quick-time-btn"
                >
                  <span class="quick-time-icon">{{ option.icon }}</span>
                  <span class="quick-time-label">{{ option.label }}</span>
                  <span class="quick-time-desc">{{ option.description }}</span>
                </button>
              </div>
            </div>

            <!-- Override Time Display -->
            <div v-if="overrideDateTime" class="override-time-display">
              <h4 class="section-title">
                <span class="section-icon">🎯</span>
                Override Time Will Be
              </h4>
              <div class="time-display override">
                <div class="time-value">{{ formatTime(overrideDateTime) }}</div>
                <div class="time-date">{{ formatDate(overrideDateTime) }}</div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="time-override-actions">
              <button
                v-if="isTimeOverrideActive"
                @click="disableTimeOverride"
                class="action-btn danger"
              >
                ⏹️ Disable Override
              </button>
              <button
                @click="enableTimeOverride"
                :disabled="!overrideDateTime"
                class="action-btn primary"
              >
                ▶️ Enable Override
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MyTableScheduleV4 from './MyTableScheduleV4.vue';
import DataManager from './components/DataManager.vue';
import { useDataImportExport } from './composables/useDataImportExport.js';

// State
const serviceWorkerStatus = ref('idle');
const autoSaveStatus = ref('idle');
const canInstall = ref(false);
const isInstalled = ref(false);
const showMenu = ref(false);
const showSettingsModal = ref(false);
const showAboutModal = ref(false);
const showDataManagerModal = ref(false);
const showViewSelectorModal = ref(false);
const showTimingSelectorModal = ref(false);
const showTimeOverrideModal = ref(false);
const isScrolled = ref(false);
const showScrollToTop = ref(false);
const isOnline = ref(navigator.onLine);
const showNotifyBtn = ref(false);
const currentView = ref('card');
const selectedStage = ref('Primary 1');
const selectedDay = ref('Monday');
const overrideDate = ref('');
const overrideTime = ref('');

const currentRealTime = ref(new Date());
let deferredPrompt = null;
let scrollTimeout = null;
let realTimeInterval = null;

const { exportAllData } = useDataImportExport();

const manifestHref = computed(() => '/my-fly-schedule-app/v4/manifest.webmanifest');

// Computed properties
const isTimeOverrideActive = computed(() => {
  return localStorage.getItem('schedule-v4-time-override') !== null;
});

const overrideDateTime = computed(() => {
  if (overrideDate.value && overrideTime.value) {
    return new Date(`${overrideDate.value}T${overrideTime.value}`);
  }
  return null;
});

const maxDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

// Stage and Day options
const stageOptions = [
  'KG 1', 'KG 2', 'Primary 1', 'Primary 2', 'Primary 3', 
  'Primary 4', 'Primary 5', 'Primary 6', 'Prep 1', 'Prep 2', 'Prep 3'
];

const dayOptions = [
  'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
];

// Quick time options
const quickTimeOptions = [
  {
    id: 'now',
    icon: '🕐',
    label: 'Now',
    description: 'Current time'
  },
  {
    id: 'morning',
    icon: '🌅',
    label: 'Morning',
    description: '8:00 AM'
  },
  {
    id: 'start',
    icon: '🏫',
    label: 'School Start',
    description: '7:30 AM'
  },
  {
    id: 'first',
    icon: '1️⃣',
    label: 'First Period',
    description: '8:00 AM'
  },
  {
    id: 'break',
    icon: '☕',
    label: 'Break Time',
    description: '10:00 AM'
  },
  {
    id: 'lunch',
    icon: '🍽️',
    label: 'Lunch Time',
    description: '12:00 PM'
  },
  {
    id: 'afternoon',
    icon: '🌇',
    label: 'Afternoon',
    description: '2:00 PM'
  },
  {
    id: 'end',
    icon: '🏁',
    label: 'School End',
    description: '3:00 PM'
  }
];

// View options for the selector
const viewOptions = [
  {
    id: 'card',
    icon: '🎴',
    title: 'Card View',
    description: 'Visual cards with detailed information for each period',
    features: ['Visual', 'Detailed', 'Mobile Friendly']
  },
  {
    id: 'table',
    icon: '📊',
    title: 'Table View',
    description: 'Compact table layout for quick overview',
    features: ['Compact', 'Overview', 'Printable']
  },
  {
    id: 'list',
    icon: '📋',
    title: 'List View',
    description: 'Simple list format for easy scanning',
    features: ['Simple', 'Scannable', 'Minimal']
  },
  {
    id: 'master',
    icon: '🏫',
    title: 'School View',
    description: 'Complete school timetable with all stages and teachers',
    features: ['Complete', 'Administrative', 'All Data']
  }
];

// Methods
const getStatusIcon = () => {
  switch (serviceWorkerStatus.value) {
    case 'ready': return '✅';
    case 'registered': return '🔄';
    case 'error': return '❌';
    case 'unsupported': return '⚠️';
    default: return '⏳';
  }
};

const getAutoSaveIcon = () => {
  switch (autoSaveStatus.value) {
    case 'saving': return '💾';
    case 'saved': return '✅';
    case 'error': return '❌';
    case 'offline': return '📴';
    default: return '⏳';
  }
};

const toggleMenu = () => {
  showMenu.value = !showMenu.value;
  
  // Haptic feedback on mobile
  if (navigator.vibrate) {
    navigator.vibrate(50);
  }
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
  showMenu.value = false;
};

const openDataManager = () => {
  showDataManagerModal.value = true;
  showMenu.value = false;
};

const closeDataManager = () => {
  showDataManagerModal.value = false;
};

const openSettings = () => {
  showSettingsModal.value = true;
  showMenu.value = false;
};

const closeSettings = () => {
  showSettingsModal.value = false;
  showMenu.value = false;
};

const showAbout = () => {
  showAboutModal.value = true;
  showMenu.value = false;
};

const closeAbout = () => {
  showAboutModal.value = false;
  showMenu.value = false;
};

const openViewSelector = () => {
  showViewSelectorModal.value = true;
  showMenu.value = false;
  
  // Load current view from localStorage
  const savedView = localStorage.getItem('schedule-v4-current-view');
  if (savedView) {
    currentView.value = savedView;
  }
};

const closeViewSelector = () => {
  showViewSelectorModal.value = false;
};

const selectView = (viewId) => {
  currentView.value = viewId;
  localStorage.setItem('schedule-v4-current-view', viewId);
  
  // Emit event to child component to change view
  const event = new CustomEvent('view-change', { detail: { view: viewId } });
  window.dispatchEvent(event);
  
  // Show notification
  const selectedView = viewOptions.find(v => v.id === viewId);
  handleNotification('View Changed', `Switched to ${selectedView.title}`);
  
  closeViewSelector();
};

const openTimingSelector = () => {
  showTimingSelectorModal.value = true;
  showMenu.value = false;
  
  // Load current timing selection from localStorage
  const savedStage = localStorage.getItem('schedule-v4-selected-stage');
  const savedDay = localStorage.getItem('schedule-v4-selected-day');
  
  if (savedStage && stageOptions.includes(savedStage)) {
    selectedStage.value = savedStage;
  }
  if (savedDay && dayOptions.includes(savedDay)) {
    selectedDay.value = savedDay;
  }
};

const closeTimingSelector = () => {
  showTimingSelectorModal.value = false;
};

const resetTimingSelection = () => {
  selectedStage.value = 'Primary 1';
  selectedDay.value = 'Monday';
  
  // Clear saved preferences
  localStorage.removeItem('schedule-v4-selected-stage');
  localStorage.removeItem('schedule-v4-selected-day');
  
  // Emit event to child component
  const event = new CustomEvent('timing-change', { 
    detail: { stage: selectedStage.value, day: selectedDay.value, reset: true } 
  });
  window.dispatchEvent(event);
  
  handleNotification('Timing Reset', 'Reset to default timing settings');
};

const saveTimingSelection = () => {
  // Save to localStorage
  localStorage.setItem('schedule-v4-selected-stage', selectedStage.value);
  localStorage.setItem('schedule-v4-selected-day', selectedDay.value);
  
  // Emit event to child component
  const event = new CustomEvent('timing-change', { 
    detail: { stage: selectedStage.value, day: selectedDay.value, reset: false } 
  });
  window.dispatchEvent(event);
  
  // Show notification
  handleNotification('Timing Saved', `Stage: ${selectedStage.value}, Day: ${selectedDay.value}`);
  
  closeTimingSelector();
};

const openTimeOverride = () => {
  showTimeOverrideModal.value = true;
  showMenu.value = false;
  
  // Load current override if exists
  const savedOverride = localStorage.getItem('schedule-v4-time-override');
  if (savedOverride) {
    const overrideDate = new Date(savedOverride);
    overrideDate.value = overrideDate.toISOString().split('T')[0];
    overrideTime.value = overrideDate.toTimeString().slice(0, 5);
  } else {
    // Set default to current time
    const now = new Date();
    overrideDate.value = now.toISOString().split('T')[0];
    overrideTime.value = now.toTimeString().slice(0, 5);
  }
  
  // Start real-time clock
  startRealTimeClock();
};

const closeTimeOverride = () => {
  showTimeOverrideModal.value = false;
  stopRealTimeClock();
};

const startRealTimeClock = () => {
  currentRealTime.value = new Date();
  realTimeInterval = setInterval(() => {
    currentRealTime.value = new Date();
  }, 1000);
};

const stopRealTimeClock = () => {
  if (realTimeInterval) {
    clearInterval(realTimeInterval);
    realTimeInterval = null;
  }
};

const setQuickTime = (option) => {
  const now = new Date();
  const date = now.toISOString().split('T')[0];
  
  switch (option.id) {
    case 'now':
      overrideDate.value = date;
      overrideTime.value = now.toTimeString().slice(0, 5);
      break;
    case 'morning':
      overrideDate.value = date;
      overrideTime.value = '08:00';
      break;
    case 'start':
      overrideDate.value = date;
      overrideTime.value = '07:30';
      break;
    case 'first':
      overrideDate.value = date;
      overrideTime.value = '08:00';
      break;
    case 'break':
      overrideDate.value = date;
      overrideTime.value = '10:00';
      break;
    case 'lunch':
      overrideDate.value = date;
      overrideTime.value = '12:00';
      break;
    case 'afternoon':
      overrideDate.value = date;
      overrideTime.value = '14:00';
      break;
    case 'end':
      overrideDate.value = date;
      overrideTime.value = '15:00';
      break;
  }
};

const enableTimeOverride = () => {
  if (overrideDateTime.value) {
    const overrideString = overrideDateTime.value.toISOString();
    localStorage.setItem('schedule-v4-time-override', overrideString);
    
    // Emit event to child components
    const event = new CustomEvent('time-override-change', { 
      detail: { 
        isActive: true, 
        overrideTime: overrideDateTime.value,
        realTime: currentRealTime.value
      } 
    });
    window.dispatchEvent(event);
    
    handleNotification('Time Override Enabled', `Override set to ${formatTime(overrideDateTime.value)}`);
    closeTimeOverride();
  }
};

const disableTimeOverride = () => {
  localStorage.removeItem('schedule-v4-time-override');
  
  // Emit event to child components
  const event = new CustomEvent('time-override-change', { 
    detail: { 
      isActive: false, 
      overrideTime: null,
      realTime: currentRealTime.value
    } 
  });
  window.dispatchEvent(event);
  
  handleNotification('Time Override Disabled', 'Now using real time');
  closeTimeOverride();
};

const formatTime = (date) => {
  return date.toLocaleTimeString('en-US', { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: true 
  });
};

const formatDate = (date) => {
  return date.toLocaleDateString('en-US', { 
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const handleDataImport = (event) => {
  const { target } = event.detail;
  
  // Handle different types of imported data
  switch (target) {
    case 'personal_schedule':
      // Move imported personal schedule data to the correct keys
      const importedSchedule = localStorage.getItem('imported-personal-schedule');
      const importedTimings = localStorage.getItem('imported-personal-timings');
      
      if (importedSchedule) {
        localStorage.setItem('schedule-v4-personal-schedule', importedSchedule);
        localStorage.removeItem('imported-personal-schedule');
      }
      
      if (importedTimings) {
        localStorage.setItem('schedule-v4-personal-timings', importedTimings);
        localStorage.removeItem('imported-personal-timings');
      }
      
      handleNotification('Import Complete', 'Personal schedule data has been imported and applied');
      break;
      
    case 'school_timetable':
      // Move imported school timetable data to the correct keys
      const importedSchoolTimetable = localStorage.getItem('imported-school-timetable');
      
      if (importedSchoolTimetable) {
        localStorage.setItem('schedule-v4-school-timetable', importedSchoolTimetable);
        localStorage.removeItem('imported-school-timetable');
      }
      
      handleNotification('Import Complete', 'School timetable data has been imported and applied');
      break;
      
    case 'stage_day_timings':
      // Move imported timing data to the correct keys
      const importedStageTimings = localStorage.getItem('imported-stage-day-timings');
      
      if (importedStageTimings) {
        localStorage.setItem('schedule-v4-stage-timings', importedStageTimings);
        localStorage.removeItem('imported-stage-day-timings');
      }
      
      handleNotification('Import Complete', 'Timing data has been imported and applied');
      break;
      
    case 'app_settings':
      // Move imported settings to the correct keys
      const importedSettings = localStorage.getItem('imported-app-settings');
      
      if (importedSettings) {
        localStorage.setItem('schedule-v4-app-settings', importedSettings);
        localStorage.removeItem('imported-app-settings');
      }
      
      // Reload view and timing settings if they were imported
      const savedView = localStorage.getItem('schedule-v4-current-view');
      if (savedView) {
        currentView.value = savedView;
      }
      
      const savedStage = localStorage.getItem('schedule-v4-selected-stage');
      const savedDay = localStorage.getItem('schedule-v4-selected-day');
      if (savedStage && savedDay) {
        selectedStage.value = savedStage;
        selectedDay.value = savedDay;
      }
      
      handleNotification('Import Complete', 'App settings have been imported and applied');
      break;
  }
  
  // Emit a global refresh event to notify child components
  window.dispatchEvent(new CustomEvent('data-refresh-required', { 
    detail: { source: 'import', target } 
  }));
};

const clearCache = () => {
  if ('caches' in window) {
    caches.keys().then(cacheNames => {
      cacheNames.forEach(cacheName => {
        caches.delete(cacheName);
      });
    });
  }
  localStorage.clear();
  alert('Cache cleared successfully!');
  closeSettings();
};

const resetApp = () => {
  if (confirm('Are you sure you want to reset the app? This will clear all data.')) {
    clearCache();
    location.reload();
  }
};

const exportData = async () => {
  const result = await exportAllData('qudratpro');
  if (result.success) {
    handleNotification('Export Complete', `Created ${result.files.length} backup files`);
  } else {
    handleNotification('Export Failed', result.error || 'Unable to export backup');
  }

  showMenu.value = false;
};

const handleDataChange = (data) => {
  // Handle data changes from child component
  console.log('Data changed in StandaloneScheduleAppV4:', data);
};

// Service Worker Registration
const registerServiceWorker = async () => {
  if (!('serviceWorker' in navigator)) {
    serviceWorkerStatus.value = 'unsupported';
    return;
  }

  try {
    serviceWorkerStatus.value = 'registering';
    
    // First, unregister any existing service workers to avoid conflicts
    const registrations = await navigator.serviceWorker.getRegistrations();
    for (const registration of registrations) {
      if (registration.scope.includes('/my-schedule-app/') || registration.scope.includes('/my-fly-schedule-app/')) {
        await registration.unregister();
        console.log('[SW] Unregistered existing service worker:', registration.scope);
      }
    }
    
    // Now register the new service worker
    const registration = await navigator.serviceWorker.register('/my-fly-schedule-app-v4-sw.js', {
      scope: '/my-fly-schedule-app/v4'
    });
    serviceWorkerStatus.value = registration.active ? 'ready' : 'registered';
    autoSaveStatus.value = 'ready';
  } catch (error) {
    console.error('Failed to register service worker:', error);
    serviceWorkerStatus.value = 'error';
    autoSaveStatus.value = 'error';
  }
};

// Install Prompt Setup
const setupInstallPrompt = () => {
  // Check if already installed
  if (window.matchMedia('(display-mode: standalone)').matches) {
    isInstalled.value = true;
    return;
  }

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    canInstall.value = true;
  });

  window.addEventListener('appinstalled', () => {
    isInstalled.value = true;
    canInstall.value = false;
    deferredPrompt = null;
  });
};

const handleInstall = async () => {
  if (!deferredPrompt) {
    return;
  }

  deferredPrompt.prompt();
  const { outcome } = await deferredPrompt.userChoice;
  
  if (outcome === 'accepted') {
    console.log('User accepted the install prompt');
  }
  
  deferredPrompt = null;
  canInstall.value = false;
};

// Notification Setup
const checkNotificationStatus = () => {
  if ("Notification" in window) {
    if (Notification.permission === "default") {
      showNotifyBtn.value = true;
    }
  }
};

const requestNotificationPermission = () => {
  if ("Notification" in window) {
    Notification.requestPermission().then(permission => {
      if (permission === "granted") {
        showNotifyBtn.value = false;
        new Notification("Notifications Enabled", {
          body: "You will receive schedule notifications!",
          icon: "/my-fly-schedule-app/v4/icon.svg"
        });
      }
    });
  }
};

const handleNotification = (title, body) => {
  if ("Notification" in window && Notification.permission === "granted") {
    new Notification(title, {
      body: body,
      icon: "/my-fly-schedule-app/v4/icon.svg",
      vibrate: [200, 100, 200]
    });
  }
};

// Scroll handling
const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
  showScrollToTop.value = window.scrollY > 300;
  
  // Clear existing timeout
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }
  
  // Set new timeout to hide scroll-to-top button after scrolling stops
  scrollTimeout = setTimeout(() => {
    showScrollToTop.value = false;
  }, 3000);
};

// Online/Offline handling
const handleOnlineStatus = () => {
  isOnline.value = navigator.onLine;
  if (isOnline.value) {
    autoSaveStatus.value = 'ready';
  } else {
    autoSaveStatus.value = 'offline';
  }
};

// Lifecycle
onMounted(() => {
  registerServiceWorker();
  setupInstallPrompt();
  checkNotificationStatus();
  handleOnlineStatus();
  
  // Add event listeners for data import
  window.addEventListener('schedule-v2-data-imported', handleDataImport);
  
  // Add online/offline listeners
  window.addEventListener('online', handleOnlineStatus);
  window.addEventListener('offline', handleOnlineStatus);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('online', handleOnlineStatus);
  window.removeEventListener('offline', handleOnlineStatus);
  window.removeEventListener('schedule-v2-data-imported', handleDataImport);
  
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }
  
  // Clean up real-time clock
  stopRealTimeClock();
});
</script>

<style scoped>
.standalone-schedule-app-v2 {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #f1f5f9;
  font-family: 'Segoe UI', system-ui, sans-serif;
  position: relative;
}

/* Header Styles */
.app-header {
  position: relative;
  z-index: 100;
  background: rgba(15, 23, 42, 0.95);
  border-bottom: 1px solid rgba(59, 130, 246, 0.3);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.app-header.compact {
  backdrop-filter: blur(20px);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  gap: 1rem;
}

.brand-section {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex: 1;
  min-width: 0;
}

.menu-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: rgba(59, 130, 246, 0.2);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 8px;
  color: #60a5fa;
  cursor: pointer;
  transition: all 0.3s ease;
}

.menu-btn:hover,
.menu-btn.active {
  background: rgba(59, 130, 246, 0.3);
  border-color: #60a5fa;
}

.menu-icon {
  font-size: 1.25rem;
  font-weight: bold;
}

.brand-info {
  min-width: 0;
  flex: 1;
}

.app-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #60a5fa;
  margin: 0;
  letter-spacing: 0.05em;
  text-shadow: 0 0 20px rgba(96, 165, 250, 0.3);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.app-subtitle {
  font-size: 0.58rem;
  color: #94a3b8;
  margin: 0.125rem 0 0 0;
  letter-spacing: 0.1em;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.status-section {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.status-indicators {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.status-badge {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.3rem 0.55rem;
  border-radius: 6px;
  font-size: 0.58rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  background: rgba(71, 85, 105, 0.5);
  color: #cbd5e1;
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.status-badge.ready {
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
  border-color: rgba(74, 222, 128, 0.4);
}

.status-badge.registered {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border-color: rgba(96, 165, 250, 0.4);
}

.status-badge.error {
  background: rgba(239, 68, 68, 0.2);
  color: #f87171;
  border-color: rgba(248, 113, 113, 0.4);
}

.status-icon {
  font-size: 0.75rem;
}

.online-indicator,
.offline-indicator {
  font-size: 0.75rem;
}

.install-btn {
  padding: 0.45rem 0.8rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.68rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  white-space: nowrap;
}

.install-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.installed-badge {
  padding: 0.3rem 0.55rem;
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
  border: 1px solid rgba(74, 222, 128, 0.4);
  border-radius: 6px;
  font-size: 0.58rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

/* Slide Menu */
.slide-menu {
  position: fixed;
  top: 0;
  left: -100%;
  width: 280px;
  height: 100vh;
  background: rgba(15, 23, 42, 0.98);
  border-right: 1px solid rgba(59, 130, 246, 0.3);
  backdrop-filter: blur(10px);
  transition: left 0.3s ease;
  z-index: 200;
  display: flex;
  flex-direction: column;
}

.slide-menu.active {
  left: 0;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1rem;
  border-bottom: 1px solid rgba(59, 130, 246, 0.3);
}

.menu-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #f1f5f9;
  margin: 0;
}

.close-menu-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.25rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.close-menu-btn:hover {
  background: rgba(148, 163, 184, 0.2);
  color: #f1f5f9;
}

.menu-nav {
  flex: 1;
  padding: 1rem;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  color: #cbd5e1;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.3s ease;
  margin-bottom: 0.5rem;
}

.menu-item:hover {
  background: rgba(59, 130, 246, 0.2);
  color: #f1f5f9;
}

.menu-item-icon {
  font-size: 1.25rem;
  width: 24px;
  text-align: center;
}

.menu-item-text {
  font-weight: 500;
}

.menu-footer {
  padding: 1rem;
  border-top: 1px solid rgba(59, 130, 246, 0.3);
  text-align: center;
}

.app-version,
.app-copyright {
  font-size: 0.65rem;
  color: #64748b;
  margin: 0.25rem 0;
}

/* Main Content */
.app-main {
  flex: 1;
  transition: filter 0.3s ease;
  min-height: calc(100vh - 80px); /* Account for header only */
}

.app-main.with-menu {
  filter: blur(2px);
  pointer-events: none;
}

/* Floating Action Button */
.fab-scroll-top {
  position: fixed;
  bottom: 20px;
  right: 1rem;
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 1.25rem;
  font-weight: bold;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  transition: all 0.3s ease;
  z-index: 40;
}

.fab-scroll-top:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 300;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem 1rem calc(1rem + env(safe-area-inset-bottom, 0px));
}

.modal-content {
  background: #1e293b;
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 12px;
  padding: 1.5rem;
  max-width: 400px;
  width: 100%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-content.large {
  max-width: 90vw;
  max-height: 90vh;
  width: 800px;
}

.modal-overlay.large {
  padding: 1rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #f1f5f9;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #64748b;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.25rem;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(100, 116, 139, 0.2);
  color: #f1f5f9;
}

.modal-body {
  color: #cbd5e1;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 0;
  border-bottom: 1px solid rgba(59, 130, 246, 0.2);
}

.setting-label {
  font-weight: 500;
}

.setting-btn {
  padding: 0.5rem 1rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.setting-btn:hover:not(:disabled) {
  background: #2563eb;
}

.setting-btn:disabled {
  background: #475569;
  cursor: not-allowed;
}

.setting-btn.danger {
  background: #dc2626;
}

.setting-btn.danger:hover:not(:disabled) {
  background: #b91c1c;
}

/* About Modal */
.about-content {
  text-align: center;
}

.app-logo {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.about-content h4 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #f1f5f9;
  margin: 0 0 0.5rem 0;
}

.about-content p {
  color: #94a3b8;
  margin-bottom: 1.5rem;
}

.features-list {
  text-align: left;
  margin-bottom: 1.5rem;
}

.features-list h5 {
  color: #f1f5f9;
  margin-bottom: 0.5rem;
}

.features-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.features-list li {
  padding: 0.25rem 0;
  color: #cbd5e1;
}

.app-info {
  text-align: left;
  background: rgba(59, 130, 246, 0.1);
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.app-info p {
  margin: 0.25rem 0;
  color: #cbd5e1;
}

/* Mobile Optimizations */
@media (max-width: 480px) {
  .header-content {
    padding: 0.5rem 0.75rem;
    gap: 0.5rem;
  }

  .brand-section {
    gap: 0.5rem;
  }

  .menu-btn {
    width: 36px;
    height: 36px;
  }
  
  .app-title {
    font-size: 0.95rem;
  }
  
  .app-subtitle {
    display: none;
  }

  .status-section {
    gap: 0.35rem;
  }

  .status-badge {
    padding: 0.22rem 0.45rem;
    font-size: 0.52rem;
  }

  .status-icon {
    font-size: 0.62rem;
  }

  .online-indicator,
  .offline-indicator {
    font-size: 0.62rem;
  }
  
  .slide-menu {
    width: 100%;
    left: -100%;
  }
  
  .modal-overlay,
  .modal-overlay.large {
    align-items: flex-end;
    padding: 0.75rem 0.75rem calc(0.75rem + env(safe-area-inset-bottom, 0px));
  }

  .modal-content,
  .modal-content.large {
    max-height: calc(100vh - 1.5rem - env(safe-area-inset-bottom, 0px));
    width: 100%;
    margin-bottom: 0;
  }
  
  .fab-scroll-top {
    bottom: 20px;
    right: 0.75rem;
    width: 44px;
    height: 44px;
  }
}

/* Touch feedback */
.menu-btn:active,
.install-btn:active,
.fab-scroll-top:active {
  transform: scale(0.95);
}

/* Focus styles for accessibility */
.menu-btn:focus-visible,
.install-btn:focus-visible,
.fab-scroll-top:focus-visible,
.close-btn:focus-visible {
  outline: 2px solid #60a5fa;
  outline-offset: 2px;
}

/* View Selector Modal */
.view-selector-modal {
  max-width: 600px;
  width: 90%;
}

.view-options {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.view-option-btn {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: #1e293b;
  border: 2px solid #334155;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: left;
  width: 100%;
}

.view-option-btn:hover {
  border-color: #3b82f6;
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.view-option-btn.active {
  border-color: #10b981;
  background: #059669;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.view-option-icon {
  font-size: 2.5rem;
  opacity: 0.9;
  flex-shrink: 0;
}

.view-option-content {
  flex: 1;
  min-width: 0;
}

.view-option-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0 0 0.5rem 0;
}

.view-option-description {
  font-size: 0.875rem;
  color: #94a3b8;
  margin: 0 0 0.75rem 0;
  line-height: 1.4;
}

.view-option-features {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.feature-tag {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  background: rgba(59, 130, 246, 0.2);
  color: #93c5fd;
  border-radius: 999px;
  font-weight: 500;
}

.view-option-btn.active .feature-tag {
  background: rgba(16, 185, 129, 0.2);
  color: #86efac;
}

.view-option-status {
  flex-shrink: 0;
}

.current-indicator {
  font-size: 0.875rem;
  font-weight: 600;
  color: #10b981;
  background: rgba(16, 185, 129, 0.2);
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

/* Mobile optimizations for view selector */
@media (max-width: 640px) {
  .view-option-btn {
    padding: 1rem;
    gap: 0.75rem;
  }
  
  .view-option-icon {
    font-size: 2rem;
  }
  
  .view-option-title {
    font-size: 1rem;
  }
  
  .view-option-description {
    font-size: 0.8rem;
  }
  
  .view-option-features {
    gap: 0.375rem;
  }
  
  .feature-tag {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
  }
  
  .current-indicator {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }
}

/* Timing Selector Modal */
.timing-selector-modal {
  max-width: 500px;
  width: 90%;
}

.timing-selection {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.selection-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0;
}

.section-icon {
  font-size: 1.25rem;
}

.stage-grid,
.day-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 0.75rem;
}

.stage-btn,
.day-btn {
  padding: 0.75rem 1rem;
  background: #1e293b;
  border: 2px solid #334155;
  border-radius: 8px;
  color: #cbd5e1;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.stage-btn:hover,
.day-btn:hover {
  border-color: #3b82f6;
  background: #2563eb;
  color: white;
  transform: translateY(-1px);
}

.stage-btn.active,
.day-btn.active {
  border-color: #10b981;
  background: #059669;
  color: white;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.current-selection {
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 12px;
  padding: 1rem;
}

.selection-display {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.selection-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
}

.selection-label {
  font-weight: 500;
  color: #94a3b8;
}

.selection-value {
  font-weight: 600;
  color: #f1f5f9;
  background: rgba(59, 130, 246, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
}

.timing-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.action-btn {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.action-btn.secondary {
  background: #475569;
  color: white;
}

.action-btn.secondary:hover {
  background: #334155;
}

.action-btn.primary {
  background: #3b82f6;
  color: white;
}

.action-btn.primary:hover {
  background: #2563eb;
}

/* Mobile optimizations for timing selector */
@media (max-width: 640px) {
  .timing-selector-modal {
    width: 95%;
  }
  
  .stage-grid,
  .day-grid {
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 0.5rem;
  }
  
  .stage-btn,
  .day-btn {
    padding: 0.625rem 0.75rem;
    font-size: 0.875rem;
  }
  
  .section-title {
    font-size: 1rem;
  }
  
  .timing-actions {
    flex-direction: column;
  }
  
  .action-btn {
    padding: 0.75rem 1rem;
  }
}

/* Time Override Modal */
.time-override-modal {
  max-width: 600px;
  width: 90%;
}

.time-override-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.override-status {
  text-align: center;
  padding: 1rem;
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 12px;
}

.status-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-size: 1.125rem;
  font-weight: 600;
}

.status-indicator.active {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
  border-color: rgba(239, 68, 68, 0.3);
}

.status-icon {
  font-size: 1.25rem;
}

.time-display-section,
.override-input-section,
.quick-time-section,
.override-time-display {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.time-display {
  text-align: center;
  padding: 1.5rem;
  background: #1e293b;
  border: 2px solid #334155;
  border-radius: 12px;
}

.time-display.override {
  border-color: #3b82f6;
  background: rgba(59, 130, 246, 0.1);
}

.time-value {
  font-size: 2rem;
  font-weight: 700;
  color: #60a5fa;
  margin-bottom: 0.5rem;
}

.time-date {
  font-size: 1rem;
  color: #94a3b8;
}

.time-input-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.input-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #cbd5e1;
}

.time-input {
  padding: 0.75rem;
  background: #1e293b;
  border: 2px solid #334155;
  border-radius: 8px;
  color: #f1f5f9;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.time-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.quick-time-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.75rem;
}

.quick-time-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: #1e293b;
  border: 2px solid #334155;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.quick-time-btn:hover {
  border-color: #3b82f6;
  background: #2563eb;
  transform: translateY(-2px);
}

.quick-time-icon {
  font-size: 1.5rem;
}

.quick-time-label {
  font-weight: 600;
  color: #f1f5f9;
  font-size: 0.875rem;
}

.quick-time-desc {
  font-size: 0.75rem;
  color: #94a3b8;
}

.time-override-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.action-btn.danger {
  background: #ef4444;
  color: white;
}

.action-btn.danger:hover {
  background: #dc2626;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Mobile optimizations for time override */
@media (max-width: 640px) {
  .time-override-modal {
    width: 95%;
  }
  
  .time-input-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }
  
  .quick-time-grid {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.5rem;
  }
  
  .quick-time-btn {
    padding: 0.75rem;
  }
  
  .time-value {
    font-size: 1.5rem;
  }
  
  .time-override-actions {
    flex-direction: column;
  }
  
  .action-btn {
    padding: 0.75rem 1rem;
  }
}
</style>
