<template>
  <Head>
    <title>My Schedule App V3 - Advanced Timing Settings</title>
    <meta name="description" content="Advanced schedule app with timing settings, offline capabilities, and PWA installation.">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/my-schedule-app/v3/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/my-schedule-app/v3/icon.svg">
  </Head>

  <div class="standalone-schedule-app-v3">
    <!-- Fixed Header with Menu -->
    <header class="app-header compact">
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
            <h5 class="app-title">⚙️ SCHEDULE V3</h5>
            <p class="app-subtitle">ADVANCED • OFFLINE • TIMING</p>
          </div>
        </div>
        
        <div class="status-section">
          <div class="status-indicators">
            <span class="status-badge" :class="serviceWorkerStatus">
              <span class="status-icon">{{ getStatusIcon() }}</span>
            </span>
            
            <span v-if="isOnline" class="online-indicator">🟢</span>
            <span v-else class="offline-indicator">🔴</span>
          </div>
          
          <button 
            v-if="canInstall && !isInstalled" 
            @click="handleInstall"
            class="install-btn compact"
          >
            📲
          </button>
          
          <span v-if="isInstalled" class="installed-badge compact">✓</span>
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
          
          <a href="#" @click.prevent="toggleSettingsPanel" class="menu-item">
            <span class="menu-item-icon">⚙️</span>
            <span class="menu-item-text">Settings</span>
          </a>
          
          <a href="#" @click.prevent="openDataManager" class="menu-item">
            <span class="menu-item-icon">📁</span>
            <span class="menu-item-text">Data Manager</span>
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
          <p class="app-version">Version 3.0</p>
          <p class="app-copyright">© 2026 Schedule App</p>
        </div>
      </div>
    </header>

    <!-- Optimized Main Content -->
    <main class="app-main" :class="{ 'with-menu': showMenu }">
      <!-- Clean Schedule View -->
      <section class="schedule-content">
        <MyTableScheduleV2 
          :stage="currentStage"
          :day="currentDay"
          :timing-data="timingData"
        />
      </section>
      
      <!-- App Settings Panel (Lazy Loaded) -->
      <Suspense>
        <template #default>
          <AppSettingsPanel
            v-if="showSettingsPanel"
            @close="closeSettingsPanel"
            @open-app-settings="openAppSettings"
            @open-data-manager="openDataManager"
            @open-general-settings="openGeneralSettings"
            @export-data="exportData"
            @import-data="importData"
            @refresh-data="refreshData"
          />
        </template>
        <template #fallback>
          <div class="settings-loading">
            <div class="loading-spinner">⚙️</div>
            <p>Loading Settings...</p>
          </div>
        </template>
      </Suspense>
    </main>

    <!-- Offline Indicator -->
    <div v-if="!isOnline" class="offline-indicator-bar">
      <span class="offline-text">🔴 Offline Mode</span>
      <button @click="checkConnection" class="retry-btn">Retry</button>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container">
      <div 
        v-for="toast in toasts" 
        :key="toast.id"
        class="toast"
        :class="[toast.type, { 'show': toast.show }]"
      >
        <span class="toast-icon">{{ getToastIcon(toast.type) }}</span>
        <span class="toast-message">{{ toast.message }}</span>
        <button @click="removeToast(toast.id)" class="toast-close">×</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick, defineAsyncComponent } from 'vue';
import { Head } from '@inertiajs/vue3';
import MyTableScheduleV2 from '../v2/MyTableScheduleV2.vue';

// Lazy load AppSettingsPanel to reduce initial bundle size
const AppSettingsPanel = defineAsyncComponent(() => 
  import('./components/AppSettingsPanel.vue')
);

// Component state
const showMenu = ref(false);
const showSettingsPanel = ref(false);
const isOnline = ref(navigator.onLine);
const canInstall = ref(false);
const isInstalled = ref(false);
const deferredPrompt = ref(null);

// Schedule state
const currentStage = ref('');
const currentDay = ref('');

// Timing data - optimized structure
const timingData = ref({
  default: [
    { id: 1, title: 'Period 1', type: 'lesson', start: '08:00', end: '08:25' },
    { id: 2, title: 'Period 2', type: 'lesson', start: '08:25', end: '08:50' },
    { id: 3, title: 'Period 3', type: 'lesson', start: '09:00', end: '09:25' },
    { id: 4, title: 'Period 4', type: 'lesson', start: '09:25', end: '09:50' },
    { id: 5, title: 'Period 5', type: 'lesson', start: '10:00', end: '10:25' },
    { id: 6, title: 'Period 6', type: 'lesson', start: '10:25', end: '10:50' }
  ]
});

// Toast notifications
const toasts = ref([]);

// Computed properties
const manifestHref = computed(() => '/my-schedule-app/v3/manifest.webmanifest');
const serviceWorkerStatus = ref('checking');

// Methods
const toggleMenu = () => {
  showMenu.value = !showMenu.value;
};

// Settings panel methods
const toggleSettingsPanel = () => {
  showSettingsPanel.value = !showSettingsPanel.value;
  showMenu.value = false;
};

const closeSettingsPanel = () => {
  showSettingsPanel.value = false;
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
  showMenu.value = false;
};

const openAppSettings = () => {
  showSettingsPanel.value = true;
  showMenu.value = false;
};

const openDataManager = () => {
  showMenu.value = false;
  showToast('Data Manager coming soon!', 'info');
};

const openGeneralSettings = () => {
  showMenu.value = false;
  showToast('General Settings coming soon!', 'info');
};

const exportData = () => {
  const data = {
    timingData: timingData.value,
    currentStage: currentStage.value,
    currentDay: currentDay.value,
    exportDate: new Date().toISOString()
  };
  
  const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `schedule-v3-${new Date().toISOString().split('T')[0]}.json`;
  a.click();
  URL.revokeObjectURL(url);
  
  showToast('Schedule data exported successfully!', 'success');
  showMenu.value = false;
};

const importData = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = '.json';
  input.onchange = (event) => {
    const file = event.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        try {
          const data = JSON.parse(e.target.result);
          
          // Import timing data
          if (data.timingData) {
            timingData.value = data.timingData;
            localStorage.setItem('schedule-v3-timing-data', JSON.stringify(data.timingData));
          }
          
          // Import stage and day
          if (data.currentStage !== undefined) {
            currentStage.value = data.currentStage;
            localStorage.setItem('schedule-v3-selected-stage', data.currentStage);
          }
          
          if (data.currentDay !== undefined) {
            currentDay.value = data.currentDay;
            localStorage.setItem('schedule-v3-selected-day', data.currentDay);
          }
          
          showToast('Schedule data imported successfully!', 'success');
        } catch (error) {
          showToast('Error importing data. Please check the file format.', 'error');
        }
      };
      reader.readAsText(file);
    }
  };
  input.click();
};

const refreshData = () => {
  // Load data from localStorage
  const savedTimingData = localStorage.getItem('schedule-v3-timing-data');
  if (savedTimingData) {
    timingData.value = JSON.parse(savedTimingData);
  }
  
  showToast('Data refreshed!', 'success');
};

const showAbout = () => {
  showMenu.value = false;
  showToast('Schedule App V3 - Advanced timing settings with offline support', 'info');
};

const checkConnection = () => {
  if (navigator.onLine) {
    isOnline.value = true;
    showToast('Connection restored!', 'success');
  } else {
    showToast('Still offline', 'warning');
  }
};

const showToast = (message, type = 'info') => {
  const toast = {
    id: Date.now(),
    message,
    type,
    show: false
  };
  
  toasts.value.push(toast);
  
  nextTick(() => {
    toast.show = true;
  });
  
  setTimeout(() => {
    removeToast(toast.id);
  }, 3000);
};

const removeToast = (id) => {
  const toast = toasts.value.find(t => t.id === id);
  if (toast) {
    toast.show = false;
    setTimeout(() => {
      toasts.value = toasts.value.filter(t => t.id !== id);
    }, 300);
  }
};

const getToastIcon = (type) => {
  const icons = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️'
  };
  return icons[type] || 'ℹ️';
};

const getStatusIcon = () => {
  const icons = {
    checking: '🔄',
    installed: '✅',
    failed: '❌',
    updated: '🔄',
    offline: '📱'
  };
  return icons[serviceWorkerStatus.value] || '❓';
};

// PWA Installation
const handleInstall = async () => {
  if (!deferredPrompt.value) return;
  
  try {
    const result = await deferredPrompt.value.prompt();
    console.log('[V3] Install prompt result:', result);
    
    if (result.outcome === 'accepted') {
      isInstalled.value = true;
      showToast('App installed successfully!', 'success');
    }
    
    deferredPrompt.value = null;
  } catch (error) {
    console.error('[V3] Install failed:', error);
    showToast('Installation failed', 'error');
  }
};

// Service Worker Registration
const registerServiceWorker = async () => {
  if (!('serviceWorker' in navigator)) return;
  try {
    const registration = await navigator.serviceWorker.register('/my-schedule-app/v3/sw.js');
    console.log('[V3] SW registered:', registration);
    registration.addEventListener('updatefound', () => {
      const newWorker = registration.installing;
      newWorker.addEventListener('statechange', () => {
        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
          serviceWorkerStatus.value = 'updated';
          showToast('App updated! Refresh to see changes.', 'info');
        }
      });
    });
    serviceWorkerStatus.value = 'installed';
  } catch (error) {
    console.log('[V3] Running without service worker:', error.message);
    serviceWorkerStatus.value = 'offline';
  }
};

// Event listeners
const handleOnline = () => {
  isOnline.value = true;
  showToast('Back online!', 'success');
};

const handleOffline = () => {
  isOnline.value = false;
  showToast('Offline mode activated', 'warning');
};

const handleBeforeInstallPrompt = (e) => {
  e.preventDefault();
  deferredPrompt.value = e;
  canInstall.value = true;
};

// Watch for timing data changes
watch(timingData, (newData) => {
  localStorage.setItem('schedule-v3-timing-data', JSON.stringify(newData));
}, { deep: true });

// Lifecycle - Optimized for performance
onMounted(() => {
  // Load essential data first
  const savedTimingData = localStorage.getItem('schedule-v3-timing-data');
  if (savedTimingData) {
    try {
      timingData.value = JSON.parse(savedTimingData);
    } catch (e) {
      console.warn('[V3] Could not parse timing data:', e);
    }
  }
  
  // Load saved stage and day
  const savedStage = localStorage.getItem('schedule-v3-selected-stage');
  if (savedStage) {
    currentStage.value = savedStage;
  }
  
  const savedDay = localStorage.getItem('schedule-v3-selected-day');
  if (savedDay) {
    currentDay.value = savedDay;
  }
  
  // Add event listeners
  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
  
  // Check if already installed as PWA
  if (window.matchMedia('(display-mode: standalone)').matches) {
    isInstalled.value = true;
  }
  
  // Defer service worker registration
  setTimeout(() => {
    registerServiceWorker();
  }, 1000);
});

onUnmounted(() => {
  window.removeEventListener('online', handleOnline);
  window.removeEventListener('offline', handleOffline);
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
});
</script>

<style scoped>
.standalone-schedule-app-v3 {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #f8fafc;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Header Styles - Optimized */
.app-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  transition: all 0.3s ease;
  padding: 0.5rem 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
  max-width: 100%;
  margin: 0 auto;
}

.brand-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.menu-btn {
  background: rgba(59, 130, 246, 0.2);
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: #60a5fa;
  padding: 0.375rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.menu-btn:hover,
.menu-btn.active {
  background: rgba(59, 130, 246, 0.3);
  border-color: #60a5fa;
}

.menu-icon {
  font-size: 1rem;
}

.brand-info h1 {
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
  color: #f8fafc;
}

.brand-info p {
  font-size: 0.625rem;
  margin: 0;
  color: #94a3b8;
}

.status-section {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-indicators {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.status-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  font-size: 0.625rem;
  font-weight: 600;
  padding: 0;
}

.status-badge.installed {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: 1px solid rgba(16, 185, 129, 0.3);
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.status-badge.checking {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border: 1px solid rgba(245, 158, 11, 0.3);
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);
}

.status-badge.failed {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: 1px solid rgba(239, 68, 68, 0.3);
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
}

.online-indicator,
.offline-indicator {
  font-size: 0.75rem;
}

.install-btn {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  border: none;
  color: white;
  padding: 0.375rem 0.75rem;
  border-radius: 16px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.install-btn.compact {
  padding: 0.375rem;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.install-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.installed-badge {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.625rem;
  font-weight: 600;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.installed-badge.compact {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
}

/* Slide Menu */
.slide-menu {
  position: fixed;
  top: 0;
  left: -300px;
  width: 300px;
  height: 100vh;
  background: rgba(15, 23, 42, 0.98);
  backdrop-filter: blur(10px);
  border-right: 1px solid rgba(148, 163, 184, 0.1);
  transition: left 0.3s ease;
  z-index: 200;
}

.slide-menu.active {
  left: 0;
}

.menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.menu-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
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
  background: rgba(148, 163, 184, 0.1);
  color: #f8fafc;
}

.menu-nav {
  padding: 1rem 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.3s ease;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.menu-item:hover {
  background: rgba(59, 130, 246, 0.1);
  color: #60a5fa;
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
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 1.5rem;
  text-align: center;
  border-top: 1px solid rgba(148, 163, 184, 0.1);
}

.app-version,
.app-copyright {
  font-size: 0.75rem;
  color: #64748b;
  margin: 0.25rem 0;
}

/* Main Content - Optimized */
.app-main {
  min-height: calc(100vh - 60px);
  padding: 0;
  padding-top: 60px;
  transition: margin-left 0.3s ease;
}

.app-main.with-menu {
  margin-left: 300px;
}

/* Settings Loading Spinner */
.settings-loading {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #f8fafc;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 3000;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.loading-spinner {
  font-size: 3rem;
  animation: spin 2s linear infinite;
  margin-bottom: 1rem;
}

.settings-loading p {
  font-size: 1.125rem;
  color: #64748b;
  font-weight: 500;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Schedule Content */
.schedule-content {
  height: calc(100vh - 120px);
  overflow: auto;
  padding: 1rem;
}

/* Offline Indicator - Optimized */
.offline-indicator-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(239, 68, 68, 0.95);
  backdrop-filter: blur(10px);
  padding: 0.75rem 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 100;
}

.offline-text {
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.retry-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Toast Notifications */
.toast-container {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.toast {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: rgba(30, 41, 59, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 8px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  min-width: 300px;
  max-width: 400px;
  transform: translateX(100%);
  opacity: 0;
  transition: all 0.3s ease;
}

.toast.show {
  transform: translateX(0);
  opacity: 1;
}

.toast.success {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: 1px solid rgba(16, 185, 129, 0.3);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.toast.error {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: 1px solid rgba(239, 68, 68, 0.3);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

.toast.warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border: 1px solid rgba(245, 158, 11, 0.3);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.toast.info {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border: 1px solid rgba(139, 92, 246, 0.3);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
}

.toast-icon {
  font-size: 1rem;
  flex-shrink: 0;
}

.toast-message {
  flex: 1;
  font-size: 0.875rem;
  font-weight: 500;
}

.toast-close {
  background: none;
  border: none;
  color: #64748b;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.25rem;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.toast-close:hover {
  background: rgba(148, 163, 184, 0.1);
  color: #f8fafc;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .header-content {
    padding: 0.5rem;
  }
  
  .brand-info h1 {
    font-size: 0.875rem;
  }
  
  .brand-info p {
    font-size: 0.5rem;
  }
  
  .status-section {
    gap: 0.25rem;
  }
  
  .app-main {
    padding-top: 50px;
  }
  
  .schedule-content {
    height: calc(100vh - 110px);
    padding: 0.5rem;
  }
  
  .app-main.with-menu {
    margin-left: 0;
  }
  
  .slide-menu {
    width: 100%;
    left: -100%;
  }
  
  .offline-indicator-bar {
    padding: 0.5rem;
  }
  
  .offline-text,
  .retry-btn {
    font-size: 0.75rem;
  }
  
  .toast {
    min-width: 250px;
    max-width: 90vw;
  }
}

@media (max-width: 480px) {
  .schedule-content {
    height: calc(100vh - 100px);
    padding: 0.375rem;
  }
  
  .toast {
    min-width: 200px;
  }
}
</style>
