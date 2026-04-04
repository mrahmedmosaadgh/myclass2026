<template>
  <Head>
    <title>My Schedule App V7</title>
    <meta name="description" content="Schedule App V7 — authenticated schedule app with cloud storage and real-time sync.">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/my-fly-schedule-app/v7/icon.png" type="image/png">
    <link rel="apple-touch-icon" href="/my-fly-schedule-app/v7/icon.png">
  </Head>

  <div class="schedule-app-v7">
    <!-- Authentication Check -->
    <div v-if="!isAuthenticated" class="auth-required">
      <div class="auth-card">
        <div class="auth-icon">🔒</div>
        <h2>Authentication Required</h2>
        <p>Please log in to access your personal schedule.</p>
        <button @click="goToLogin" class="login-btn">
          Go to Login
        </button>
      </div>
    </div>

    <!-- Main App (when authenticated) -->
    <template v-else>
      <AppHeader
        :show-menu="showMenu"
        :can-install="canInstall"
        :is-installed="isInstalled"
        :user="user"
        :sync-status="syncStatus"
        @toggle-menu="toggleMenu"
        @install="handleInstall"
        @sync-now="syncNow"
      />

      <main class="app-main" :class="{ 'menu-open': showMenu }">
        <div v-if="loading" class="loading-screen">
          <div class="loading-spinner"></div>
          <p>{{ loadingMessage }}</p>
        </div>
        
        <div v-else-if="error" class="error-screen">
          <div class="error-icon">⚠️</div>
          <h3>Something went wrong</h3>
          <p>{{ error }}</p>
          <button @click="retry" class="retry-btn">Try Again</button>
        </div>

        <ScheduleViewer v-else-if="store.isInitialized.value" />
      </main>

      <SlideMenu
        :open="showMenu"
        @close="showMenu = false"
      />

      <button
        v-if="showScrollToTop"
        @click="scrollToTop"
        class="fab-scroll-top"
        aria-label="Scroll to top"
      >
        ↑
      </button>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { createAppStore, provideAppStore } from './composables/useAppStore';
import { useAuth } from './composables/useAuth';
import { useCloudSync } from './composables/useCloudSync';
import AppHeader from './components/AppHeader.vue';
import SlideMenu from './components/SlideMenu.vue';
import ScheduleViewer from './ScheduleViewer.vue';

// ── Authentication ──
const { user, isAuthenticated, checkAuth } = useAuth();

// ── Create & provide the central store ──
const store = createAppStore();
provideAppStore(store);

// ── Cloud Sync ──
const { syncStatus, syncData, saveData, loadData } = useCloudSync();

// ── PWA state ──
const manifestHref = '/my-fly-schedule-app/v7/manifest.webmanifest';
const canInstall = ref(false);
const isInstalled = ref(false);
let deferredPrompt = null;

// ── UI state ──
const showMenu = ref(false);
const showScrollToTop = ref(false);
const loading = ref(false);
const loadingMessage = ref('Loading...');
const error = ref('');

// ── Computed ──
const isScrolled = computed(() => window.scrollY > 50);

// ── Methods ──
const toggleMenu = () => {
  showMenu.value = !showMenu.value;
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleScroll = () => {
  showScrollToTop.value = window.scrollY > 300;
};

const handleInstall = async () => {
  if (!deferredPrompt) return;
  deferredPrompt.prompt();
  const { outcome } = await deferredPrompt.userChoice;
  if (outcome === 'accepted') {
    isInstalled.value = true;
  }
  deferredPrompt = null;
  canInstall.value = false;
};

const handleBeforeInstallPrompt = (e) => {
  e.preventDefault();
  deferredPrompt = e;
  canInstall.value = true;
};

const goToLogin = () => {
  router.visit('/login');
};

const retry = async () => {
  error.value = '';
  await initializeApp();
};

const syncNow = async () => {
  await syncData();
};

const initializeApp = async () => {
  loading.value = true;
  loadingMessage.value = 'Checking authentication...';
  
  try {
    // Check if user is authenticated
    await checkAuth();
    
    if (!isAuthenticated.value) {
      loading.value = false;
      return;
    }

    loadingMessage.value = 'Loading your schedule data...';
    
    // Initialize the store
    await store.initialize();
    
    // Load user data from server
    loadingMessage.value = 'Syncing with cloud...';
    await loadData();
    
    loading.value = false;
    
  } catch (err) {
    console.error('App initialization failed:', err);
    error.value = err.message || 'Failed to initialize app';
    loading.value = false;
  }
};

// ── Auto-save on data changes ──
const setupAutoSave = () => {
  // Watch for data changes and auto-save
  const saveTimer = ref(null);
  
  // This would be connected to your store's data changes
  // For now, we'll save every 30 seconds
  saveTimer.value = setInterval(async () => {
    if (isAuthenticated.value && store.isInitialized.value) {
      try {
        await saveData();
      } catch (err) {
        console.error('Auto-save failed:', err);
      }
    }
  }, 30000);
  
  onUnmounted(() => {
    if (saveTimer.value) {
      clearInterval(saveTimer.value);
    }
  });
};

// ── Lifecycle ──
onMounted(async () => {
  await initializeApp();
  setupAutoSave();

  window.addEventListener('scroll', handleScroll, { passive: true });
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

  if (window.matchMedia('(display-mode: standalone)').matches) {
    isInstalled.value = true;
  }

  // Register service worker
  if ('serviceWorker' in navigator && window.location.pathname.startsWith('/my-fly-schedule-app/ver7')) {
    try {
      const reg = await navigator.serviceWorker.register('/my-fly-schedule-app/v7-sw.js', {
        scope: '/my-fly-schedule-app/ver7'
      });
      console.log('[V7 SW] Registered:', reg.scope);
    } catch (e) {
      console.warn('[V7 SW] Registration failed:', e);
    }
  }
});

onUnmounted(() => {
  store.destroy();
  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
});
</script>

<style scoped>
.schedule-app-v7 {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f1f5f9;
}

/* Auth Required Screen */
.auth-required {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 1rem;
}

.auth-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  max-width: 400px;
  width: 100%;
}

.auth-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.auth-card h2 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.auth-card p {
  margin: 0 0 2rem 0;
  opacity: 0.8;
  line-height: 1.6;
}

.login-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.login-btn:hover {
  background: #2563eb;
}

/* Main App */
.app-main {
  padding-top: 60px;
  transition: filter 0.3s ease;
  min-height: calc(100vh - 60px);
}

.app-main.menu-open {
  filter: blur(2px);
  pointer-events: none;
}

/* Loading Screen */
.loading-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 1rem;
  color: #cbd5e1;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(59, 130, 246, 0.2);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error Screen */
.error-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 1rem;
  text-align: center;
  padding: 2rem;
}

.error-icon {
  font-size: 3rem;
}

.error-screen h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.error-screen p {
  margin: 0 0 2rem 0;
  opacity: 0.8;
  max-width: 400px;
}

.retry-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.75rem 2rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.retry-btn:hover {
  background: #dc2626;
}

/* FAB */
.fab-scroll-top {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #3b82f6;
  color: white;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
  z-index: 50;
  transition: transform 0.2s, box-shadow 0.2s;
}

.fab-scroll-top:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5);
}

/* Responsive */
@media (max-width: 768px) {
  .auth-card {
    margin: 1rem;
    padding: 1.5rem;
  }
  
  .error-screen {
    padding: 1rem;
  }
}
</style>
