<template>
  <Head>
    <title>My Schedule App V2 - Mobile Optimized</title>
    <meta name="description" content="Mobile-first schedule app with multiple view modes, school timetable, and offline capabilities.">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/my-fly-schedule-app/v2/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/my-fly-schedule-app/v2/icon.svg">
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
            <h1 class="app-title">📅 SCHEDULE V2</h1>
            <p class="app-subtitle">MOBILE • OFFLINE • MULTI-VIEW</p>
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
          <p class="app-version">Version 2.0</p>
          <p class="app-copyright">© 2026 Schedule App</p>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="app-main" :class="{ 'with-menu': showMenu }">
      <MyTableScheduleV2 />
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="bottom-nav">
      <button 
        v-for="navItem in bottomNavItems"
        :key="navItem.id"
        @click="handleBottomNavClick(navItem)"
        :class="['nav-btn', { active: activeBottomNav === navItem.id }]"
        class="nav-btn"
      >
        <span class="nav-icon">{{ navItem.icon }}</span>
        <span class="nav-label">{{ navItem.label }}</span>
      </button>
    </nav>

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
          <h3>About Schedule App V2</h3>
          <button @click="closeAbout" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="about-content">
            <div class="app-logo">📅</div>
            <h4>Schedule App V2</h4>
            <p>Mobile-first schedule management with multiple view modes.</p>
            
            <div class="features-list">
              <h5>Features:</h5>
              <ul>
                <li>📱 Mobile-optimized interface</li>
                <li>🔄 Multiple view modes (Card, Table, List, School)</li>
                <li>🏫 Complete school timetable</li>
                <li>🔔 Push notifications</li>
                <li>💾 Offline functionality</li>
                <li>⚙️ Customizable timings</li>
                <li>📥 Data export/import</li>
              </ul>
            </div>
            
            <div class="app-info">
              <p><strong>Version:</strong> 2.0.0</p>
              <p><strong>Build:</strong> Mobile Optimized</p>
              <p><strong>Platform:</strong> Progressive Web App</p>
              <p><strong>URL:</strong> https://qudratpro.com/my-schedule-app/v2</p>
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
import MyTableScheduleV2 from './MyTableScheduleV2.vue';
import DataManager from './components/DataManager.vue';
import { useDataImportExport } from './composables/useDataImportExport.js';

// State
const serviceWorkerStatus = ref('idle');
const canInstall = ref(false);
const isInstalled = ref(false);
const showMenu = ref(false);
const showSettingsModal = ref(false);
const showAboutModal = ref(false);
const showDataManagerModal = ref(false);
const isScrolled = ref(false);
const showScrollToTop = ref(false);
const isOnline = ref(navigator.onLine);
const showNotifyBtn = ref(false);
const activeBottomNav = ref('home');
let deferredPrompt = null;
let scrollTimeout = null;

const { exportAllData } = useDataImportExport();

const manifestHref = computed(() => '/my-fly-schedule-app/v2/manifest.webmanifest');

// Bottom navigation items
const bottomNavItems = [
  { id: 'home', icon: '🏠', label: 'Home' },
  { id: 'views', icon: '👁️', label: 'Views' },
  { id: 'school', icon: '🏫', label: 'School' },
  { id: 'settings', icon: '⚙️', label: 'Settings' }
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

const toggleMenu = () => {
  showMenu.value = !showMenu.value;
  
  // Haptic feedback on mobile
  if (navigator.vibrate) {
    navigator.vibrate(50);
  }
};

const handleBottomNavClick = (navItem) => {
  activeBottomNav.value = navItem.id;
  
  if (navItem.id === 'settings') {
    openSettings();
  } else if (navItem.id === 'home') {
    scrollToTop();
  }
  
  // Haptic feedback
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
    const registration = await navigator.serviceWorker.register('/my-fly-schedule-app-v2-sw.js', {
      scope: '/my-fly-schedule-app/v2'
    });
    serviceWorkerStatus.value = registration.active ? 'ready' : 'registered';
  } catch (error) {
    console.error('Failed to register service worker:', error);
    serviceWorkerStatus.value = 'error';
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
          icon: "/my-fly-schedule-app/v2/icon.svg"
        });
      }
    });
  }
};

const handleNotification = (title, body) => {
  if ("Notification" in window && Notification.permission === "granted") {
    new Notification(title, {
      body: body,
      icon: "/my-fly-schedule-app/v2/icon.svg",
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
};

// Lifecycle
onMounted(() => {
  registerServiceWorker();
  setupInstallPrompt();
  checkNotificationStatus();
  handleOnlineStatus();
  
  // Add scroll listener
  window.addEventListener('scroll', handleScroll);
  
  // Add online/offline listeners
  window.addEventListener('online', handleOnlineStatus);
  window.addEventListener('offline', handleOnlineStatus);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('online', handleOnlineStatus);
  window.removeEventListener('offline', handleOnlineStatus);
  
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }
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
  min-height: calc(100vh - 140px); /* Account for header and bottom nav */
  padding-bottom: calc(88px + env(safe-area-inset-bottom, 0px));
}

.app-main.with-menu {
  filter: blur(2px);
  pointer-events: none;
}

/* Bottom Navigation */
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(15, 23, 42, 0.95);
  border-top: 1px solid rgba(59, 130, 246, 0.3);
  backdrop-filter: blur(10px);
  display: flex;
  justify-content: space-around;
  padding: 0.5rem 0 calc(0.5rem + env(safe-area-inset-bottom, 0px));
  z-index: 50;
}

.nav-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem;
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 60px;
  font-size: 0.65rem;
  font-weight: 500;
}

.nav-btn:hover,
.nav-btn.active {
  color: #60a5fa;
}

.nav-icon {
  font-size: 1.25rem;
}

.nav-label {
  font-weight: 500;
}

/* Floating Action Button */
.fab-scroll-top {
  position: fixed;
  bottom: 80px;
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
  
  .bottom-nav {
    padding: 0.375rem 0;
  }

  .app-main {
    padding-bottom: calc(92px + env(safe-area-inset-bottom, 0px));
  }

  .bottom-nav {
    padding: 0.375rem 0 calc(0.5rem + env(safe-area-inset-bottom, 0px));
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

  .nav-btn {
    min-width: 50px;
    padding: 0.375rem;
  }
  
  .nav-icon {
    font-size: 1rem;
  }
  
  .nav-label {
    font-size: 0.6rem;
  }
  
  .fab-scroll-top {
    bottom: 70px;
    right: 0.75rem;
    width: 44px;
    height: 44px;
  }
}

/* Touch feedback */
.menu-btn:active,
.nav-btn:active,
.install-btn:active,
.fab-scroll-top:active {
  transform: scale(0.95);
}

/* Focus styles for accessibility */
.menu-btn:focus-visible,
.nav-btn:focus-visible,
.install-btn:focus-visible,
.fab-scroll-top:focus-visible,
.close-btn:focus-visible {
  outline: 2px solid #60a5fa;
  outline-offset: 2px;
}
</style>
