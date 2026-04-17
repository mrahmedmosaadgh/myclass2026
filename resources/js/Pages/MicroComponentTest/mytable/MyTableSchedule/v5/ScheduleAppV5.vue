<template>
  <Head>
    <title>My Schedule App V5</title>
    <meta name="description" content="Schedule App V5 - Authenticated timeline with sync across devices.">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/my-fly-schedule-app/v5/icon.png" type="image/png">
    <link rel="apple-touch-icon" href="/my-fly-schedule-app/v5/icon.png">
  </Head>

  <div class="schedule-app-v5">
    <!-- Authentication Check -->
    <div v-if="!isAuthenticated" class="auth-required">
      <div class="auth-message">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
          <polyline points="10 17 15 12 10 7"></polyline>
          <line x1="15" y1="12" x2="3" y2="12"></line>
        </svg>
        <h2>Authentication Required</h2>
        <p>Please sign in to access your timeline and sync data across devices.</p>
        <a href="/timeline/login" class="btn-auth">
          Sign In to Continue
        </a>
      </div>
    </div>

    <!-- Main App (Authenticated) -->
    <div v-else>
      <!-- User Profile Header -->
      <UserProfile />

      <AppHeader
        :show-menu="showMenu"
        :can-install="canInstall"
        :is-installed="isInstalled"
        @toggle-menu="toggleMenu"
        @install="handleInstall"
      />

      <main class="app-main" :class="{ 'menu-open': showMenu }">
        <ScheduleViewer v-if="store.isInitialized.value" />
        <div v-else class="loading-screen">
          <div class="loading-spinner"></div>
          <p>Loading schedule...</p>
        </div>
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
        ^
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { createAppStore, provideAppStore } from './composables/useAppStore';
import { useTimelineAuth } from './composables/useTimelineAuth.js';
import { useTimelineSync } from './composables/useTimelineSync.js';
import AppHeader from './components/AppHeader.vue';
import ScheduleViewer from './ScheduleViewer.vue';
import SlideMenu from './components/SlideMenu.vue';
import UserProfile from './components/UserProfile.vue';

// ── Create & provide the central store ──
const store = createAppStore();
provideAppStore(store);

// ── PWA state ──
const manifestHref = '/my-fly-schedule-app/v5/manifest.webmanifest';
const canInstall = ref(false);
const isInstalled = ref(false);
let deferredPrompt = null;

// ── Authentication & Sync
const { isAuthenticated, user, isLoading: authLoading } = useTimelineAuth();
const { loadTimelineData, isOnline } = useTimelineSync();

// Computed properties
const authRequired = computed(() => !isAuthenticated.value && !authLoading.value);

// Load timeline data when authenticated
watch(isAuthenticated, async (authenticated) => {
  if (authenticated) {
    await loadTimelineData();
  }
}, { immediate: true });

// ── UI state ──
const showMenu = ref(false);
const showScrollToTop = ref(false);
const isScrolled = ref(false);

const toggleMenu = () => {
  showMenu.value = !showMenu.value;
};

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
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

// ── Lifecycle ──
onMounted(async () => {
  await store.initialize();

  window.addEventListener('scroll', handleScroll, { passive: true });
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

  if (window.matchMedia('(display-mode: standalone)').matches) {
    isInstalled.value = true;
  }

  // Register service worker
  if ('serviceWorker' in navigator && window.location.pathname.startsWith('/my-fly-schedule-app/v5')) {
    try {
      const reg = await navigator.serviceWorker.register('/my-fly-schedule-app-v5-sw.js', {
        scope: '/my-fly-schedule-app/v5'
      });
      console.log('[V5 SW] Registered:', reg.scope);
    } catch (e) {
      console.warn('[V5 SW] Registration failed:', e);
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
.schedule-app-v5 {
  min-height: 100vh;
  background: #f8fafc;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Authentication Required Screen */
.auth-required {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.auth-message {
  background: white;
  border-radius: 16px;
  padding: 3rem;
  text-align: center;
  max-width: 480px;
  width: 100%;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.auth-message svg {
  width: 4rem;
  height: 4rem;
  color: #667eea;
  margin-bottom: 1.5rem;
}

.auth-message h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 1rem 0;
}

.auth-message p {
  font-size: 1rem;
  color: #6b7280;
  margin: 0 0 2rem 0;
  line-height: 1.6;
}

.btn-auth {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-auth:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
}

.app-main {
  padding-top: 60px;
  transition: filter 0.3s ease;
  min-height: calc(100vh - 60px);
}

.app-main.menu-open {
  filter: blur(2px);
  pointer-events: none;
}

.loading-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 1rem;
  color: #64748b;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

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

@media (prefers-color-scheme: dark) {
  .schedule-app-v5 {
    background: #0f172a;
    color: #f1f5f9;
  }
}
</style>
