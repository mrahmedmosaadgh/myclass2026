<template>
  <Head>
    <title>My Schedule App V3 - Advanced Timing Settings</title>
    <meta name="description" content="Advanced schedule app with timing settings, offline capabilities, and PWA installation.">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/my-fly-schedule-app/v3/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/my-fly-schedule-app/v3/icon.svg">
  </Head>

  <div class="standalone-schedule-app-v3">
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
            <h1 class="app-title">⚙️ SCHEDULE V3</h1>
            <p class="app-subtitle">ADVANCED • OFFLINE • TIMING</p>
          </div>
        </div>
        
        <div class="status-section">
          <div class="status-indicators">
            <span class="status-badge" :class="serviceWorkerStatus">
              <span class="status-icon">{{ getStatusIcon() }}</span>
              {{ serviceWorkerStatus }}
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
          
          <a href="#" @click.prevent="openTimingSettings" class="menu-item">
            <span class="menu-item-icon">⏰</span>
            <span class="menu-item-text">Timing Settings</span>
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
          <p class="app-version">Version 3.0</p>
          <p class="app-copyright">© 2026 Schedule App</p>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="app-main" :class="{ 'with-menu': showMenu }">
      <!-- Timing Settings Section -->
      <section v-if="showTimingSettings" class="timing-section">
        <div class="section-header">
          <h2>⏰ Timing Settings</h2>
          <button @click="showTimingSettings = false" class="close-section-btn">×</button>
        </div>
        
        <StageDayTimingManager
          v-model="timingData"
          :stage="currentStage"
          :day="currentDay"
          @close="showTimingSettings = false"
          @update:modelValue="handleTimingUpdate"
        />
      </section>

      <!-- Schedule View Section -->
      <section v-else class="schedule-section">
        <div class="section-header">
          <h2>📅 Schedule View</h2>
          <div class="view-controls">
            <select v-model="currentStage" class="stage-selector">
              <option value="">All Stages</option>
              <option value="prim">Primary</option>
              <option value="middle">Middle</option>
              <option value="sec">Secondary</option>
            </select>
            
            <select v-model="currentDay" class="day-selector">
              <option value="">All Days</option>
              <option value="d1">Day 1</option>
              <option value="d2">Day 2</option>
              <option value="d3">Day 3</option>
              <option value="d4">Day 4</option>
              <option value="d5">Day 5</option>
              <option value="d6">Day 6</option>
            </select>
            
            <button @click="openTimingSettings" class="timing-btn">
              ⏰ Timing
            </button>
          </div>
        </div>
        
        <MyTableScheduleV2 
          :stage="currentStage"
          :day="currentDay"
          :timing-data="timingData"
        />
      </section>

      <!-- Floating Action Button -->
      <div class="fab-container">
        <button 
          @click="toggleFabMenu" 
          class="fab-main"
          :class="{ active: fabMenuOpen }"
        >
          <span class="fab-icon">+</span>
        </button>
        
        <div class="fab-menu" :class="{ active: fabMenuOpen }">
          <button @click="openTimingSettings" class="fab-item timing">
            <span class="fab-item-icon">⏰</span>
            <span class="fab-item-label">Timing</span>
          </button>
          
          <button @click="exportData" class="fab-item export">
            <span class="fab-item-icon">📥</span>
            <span class="fab-item-label">Export</span>
          </button>
          
          <button @click="refreshData" class="fab-item refresh">
            <span class="fab-item-icon">🔄</span>
            <span class="fab-item-label">Refresh</span>
          </button>
        </div>
      </div>
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
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import MyTableScheduleV2 from './MyTableScheduleV2.vue';
import StageDayTimingManager from './components/StageDayTimingManager.vue';

// Component state
const showMenu = ref(false);
const showTimingSettings = ref(false);
const fabMenuOpen = ref(false);
const isScrolled = ref(false);
const isOnline = ref(navigator.onLine);
const canInstall = ref(false);
const isInstalled = ref(false);
const deferredPrompt = ref(null);

// Schedule state
const currentStage = ref('');
const currentDay = ref('');
const timingData = ref({
  default: [
    { id: 1, title: 'Period 1', type: 'lesson', start: '09:00', end: '09:30' },
    { id: 2, title: 'Period 2', type: 'lesson', start: '09:30', end: '10:00' },
    { id: 'b1', title: 'First Break', type: 'break', start: '10:00', end: '10:30' },
    { id: 3, title: 'Period 3', type: 'lesson', start: '10:30', end: '11:00' },
    { id: 4, title: 'Period 4', type: 'lesson', start: '11:00', end: '11:30' },
    { id: 'b2', title: 'Second Break', type: 'break', start: '11:30', end: '12:00' },
    { id: 5, title: 'Period 5', type: 'lesson', start: '12:00', end: '12:25' },
    { id: 6, title: 'Period 6', type: 'lesson', start: '12:25', end: '12:50' }
  ],
  overrides: {}
});

// Toast notifications
const toasts = ref([]);

// Computed properties
const manifestHref = computed(() => '/my-fly-schedule-app/v3/manifest.webmanifest');
const serviceWorkerStatus = ref('checking');

// Methods
const toggleMenu = () => {
  showMenu.value = !showMenu.value;
};

const toggleFabMenu = () => {
  fabMenuOpen.value = !fabMenuOpen.value;
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
  showMenu.value = false;
};

const openTimingSettings = () => {
  showTimingSettings.value = true;
  showMenu.value = false;
  fabMenuOpen.value = false;
};

const openDataManager = () => {
  showMenu.value = false;
  showToast('Data Manager coming soon!', 'info');
};

const openSettings = () => {
  showMenu.value = false;
  showToast('Settings coming soon!', 'info');
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
  fabMenuOpen.value = false;
};

const refreshData = () => {
  // Load data from localStorage
  const savedTimingData = localStorage.getItem('schedule-v3-timing-data');
  if (savedTimingData) {
    timingData.value = JSON.parse(savedTimingData);
  }
  
  showToast('Data refreshed!', 'success');
  fabMenuOpen.value = false;
};

const showAbout = () => {
  showMenu.value = false;
  showToast('Schedule App V3 - Advanced timing settings with offline support', 'info');
};

const handleTimingUpdate = (newTimingData) => {
  timingData.value = newTimingData;
  localStorage.setItem('schedule-v3-timing-data', JSON.stringify(newTimingData));
  showToast('Timing settings saved!', 'success');
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
    checking: '⏳',
    installed: '✅',
    failed: '❌',
    updated: '🔄'
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
  if ('serviceWorker' in navigator) {
    try {
      const registration = await navigator.serviceWorker.register('/my-fly-schedule-app/v3/sw.js');
      console.log('[V3] SW registered:', registration);
      
      // Check for updates
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
      console.error('[V3] SW registration failed:', error);
      serviceWorkerStatus.value = 'failed';
    }
  }
};

// Event listeners
const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
};

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

// Lifecycle
onMounted(() => {
  // Load saved data
  const savedTimingData = localStorage.getItem('schedule-v3-timing-data');
  if (savedTimingData) {
    timingData.value = JSON.parse(savedTimingData);
  }
  
  // Check if already installed
  if (window.matchMedia('(display-mode: standalone)').matches) {
    isInstalled.value = true;
  }
  
  // Register service worker
  registerServiceWorker();
  
  // Add event listeners
  window.addEventListener('scroll', handleScroll);
  window.addEventListener('online', handleOnline);
  window.addEventListener('offline', handleOffline);
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
  
  // Close menus when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.slide-menu') && !e.target.closest('.menu-btn')) {
      showMenu.value = false;
    }
    if (!e.target.closest('.fab-container')) {
      fabMenuOpen.value = false;
    }
  });
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
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

/* Header Styles */
.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(148, 163, 184, 0.1);
  transition: all 0.3s ease;
}

.app-header.compact {
  padding: 0.5rem 0;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  max-width: 100%;
  margin: 0 auto;
}

.brand-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.menu-btn {
  background: rgba(59, 130, 246, 0.2);
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: #60a5fa;
  padding: 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.menu-btn:hover,
.menu-btn.active {
  background: rgba(59, 130, 246, 0.3);
  border-color: #60a5fa;
}

.menu-icon {
  font-size: 1.2rem;
}

.brand-info h1 {
  font-size: 1.2rem;
  font-weight: 700;
  margin: 0;
  color: #f8fafc;
}

.brand-info p {
  font-size: 0.75rem;
  margin: 0;
  color: #94a3b8;
}

.status-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.status-indicators {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-badge {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
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
  font-size: 0.875rem;
}

.install-btn {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.install-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.installed-badge {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid rgba(16, 185, 129, 0.3);
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

/* Main Content */
.app-main {
  min-height: calc(100vh - 80px);
  padding: 1rem;
  transition: margin-left 0.3s ease;
}

.app-main.with-menu {
  margin-left: 300px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(30, 41, 59, 0.5);
  border-radius: 12px;
  backdrop-filter: blur(10px);
}

.section-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.close-section-btn {
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

.close-section-btn:hover {
  background: rgba(148, 163, 184, 0.1);
  color: #f8fafc;
}

.view-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.stage-selector,
.day-selector {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  color: #f8fafc;
  padding: 0.5rem;
  border-radius: 8px;
  font-size: 0.875rem;
}

.timing-btn {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  border: 1px solid rgba(139, 92, 246, 0.3);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(139, 92, 246, 0.2);
}

.timing-btn:hover {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  border-color: #8b5cf6;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

/* Floating Action Button */
.fab-container {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 90;
}

.fab-main {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fab-main:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
}

.fab-main.active {
  transform: rotate(45deg);
}

.fab-menu {
  position: absolute;
  bottom: 70px;
  right: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  opacity: 0;
  pointer-events: none;
  transition: all 0.3s ease;
}

.fab-menu.active {
  opacity: 1;
  pointer-events: auto;
}

.fab-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: rgba(30, 41, 59, 0.95);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 24px;
  color: #f8fafc;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.fab-item:hover {
  background: rgba(59, 130, 246, 0.2);
  border-color: #60a5fa;
  transform: translateX(-4px);
}

.fab-item-icon {
  font-size: 1rem;
}

.fab-item-label {
  font-weight: 500;
}

/* Offline Indicator */
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
    padding: 0.75rem;
  }
  
  .brand-info h1 {
    font-size: 1rem;
  }
  
  .brand-info p {
    font-size: 0.625rem;
  }
  
  .view-controls {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .stage-selector,
  .day-selector {
    font-size: 0.75rem;
    padding: 0.375rem;
  }
  
  .fab-container {
    bottom: 1rem;
    right: 1rem;
  }
  
  .fab-main {
    width: 48px;
    height: 48px;
    font-size: 1.25rem;
  }
  
  .fab-menu {
    bottom: 60px;
  }
  
  .fab-item {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
  }
  
  .toast {
    min-width: 250px;
    max-width: calc(100vw - 2rem);
  }
  
  .app-main.with-menu {
    margin-left: 0;
  }
  
  .slide-menu {
    width: 100%;
    left: -100%;
  }
}

@media (max-width: 480px) {
  .section-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .view-controls {
    justify-content: center;
  }
}
</style>
