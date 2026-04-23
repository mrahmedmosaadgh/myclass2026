<template>
  <Head>
    <title>My Schedule App V7</title>
    <meta name="description" content="Schedule App V7 — offline-first, view-only main app, all settings from menu.">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/my-fly-schedule-app/v7/icon.png" type="image/png">
    <link rel="apple-touch-icon" href="/my-fly-schedule-app/v7/icon.png">
  </Head>

  <div class="schedule-app-v7">
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
      ↑
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { createAppStore, provideAppStore } from './composables/useAppStore';
import AppHeader from './components/AppHeader.vue';
import SlideMenu from './components/SlideMenu.vue';
import ScheduleViewer from './ScheduleViewer.vue';

// ── Create & provide the central store ──
const store = createAppStore();
provideAppStore(store);

// ── PWA state ──
const manifestHref = '/my-fly-schedule-app/v7/manifest.webmanifest';
const canInstall = ref(false);
const isInstalled = ref(false);
let deferredPrompt = null;

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
  if ('serviceWorker' in navigator && window.location.pathname.startsWith('/my-fly-schedule-app/ver7')) {
    try {
      const reg = await navigator.serviceWorker.register('/my-fly-schedule-app-v7-sw.js', {
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
  background: #f8fafc;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
  .schedule-app-v7 {
    background: #0f172a;
    color: #f1f5f9;
  }
}
</style>
