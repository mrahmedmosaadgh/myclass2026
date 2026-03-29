<template>
  <Head>
    <title>My Schedule App</title>
    <meta name="description" content="Standalone offline schedule app with notifications and live tracking.">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" :href="manifestHref">
    <link rel="icon" href="/my-schedule-app/icon.svg" type="image/svg+xml">
  </Head>

  <div class="standalone-schedule-app">
    <header class="app-header">
      <div class="header-content">
        <div class="brand-section">
          <h1 class="app-title">📅 MY SCHEDULE APP</h1>
          <p class="app-subtitle">OFFLINE • INSTALLABLE • NOTIFICATIONS</p>
        </div>
        
        <div class="status-section">
          <span class="status-badge" :class="serviceWorkerStatus">
            SW: {{ serviceWorkerStatus }}
          </span>
          <button 
            v-if="canInstall && !isInstalled" 
            @click="handleInstall"
            class="install-btn"
          >
            📲 INSTALL
          </button>
          <span v-if="isInstalled" class="installed-badge">✓ INSTALLED</span>
        </div>
      </div>
    </header>

    <main class="app-main">
      <MyTableSchedule />
    </main>

    <footer class="app-footer">
      <div class="footer-info">
        <span>🔔 Push Notifications</span>
        <span>💾 Offline Storage</span>
        <span>⚡ Live Updates</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MyTableSchedule from './MyTableSchedule.vue';

const serviceWorkerStatus = ref('idle');
const canInstall = ref(false);
const isInstalled = ref(false);
let deferredPrompt = null;

const manifestHref = computed(() => '/my-schedule-app/manifest.webmanifest');

async function registerServiceWorker() {
  if (!('serviceWorker' in navigator)) {
    serviceWorkerStatus.value = 'unsupported';
    return;
  }

  try {
    serviceWorkerStatus.value = 'registering';
    const registration = await navigator.serviceWorker.register('/my-schedule-app-v2-sw.js', {
      scope: '/my-schedule-app/v2'
    });
    serviceWorkerStatus.value = registration.active ? 'ready' : 'registered';
  } catch (error) {
    console.error('Failed to register service worker:', error);
    serviceWorkerStatus.value = 'error';
  }
}

function setupInstallPrompt() {
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
}

async function handleInstall() {
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
}

onMounted(() => {
  registerServiceWorker();
  setupInstallPrompt();
});
</script>

<style scoped>
.standalone-schedule-app {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  display: flex;
  flex-direction: column;
}

.app-header {
  background: rgba(15, 23, 42, 0.95);
  border-bottom: 2px solid rgba(59, 130, 246, 0.3);
  padding: 1rem 1.5rem;
  backdrop-filter: blur(10px);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.brand-section {
  flex: 1;
  min-width: 250px;
}

.app-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #60a5fa;
  margin: 0;
  letter-spacing: 0.05em;
  text-shadow: 0 0 20px rgba(96, 165, 250, 0.3);
}

.app-subtitle {
  font-size: 0.75rem;
  color: #94a3b8;
  margin: 0.25rem 0 0 0;
  letter-spacing: 0.1em;
  font-weight: 500;
}

.status-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.status-badge {
  padding: 0.4rem 0.8rem;
  border-radius: 6px;
  font-size: 0.75rem;
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

.install-btn {
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.install-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.install-btn:active {
  transform: translateY(0);
}

.installed-badge {
  padding: 0.4rem 0.8rem;
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
  border: 1px solid rgba(74, 222, 128, 0.4);
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
}

.app-main {
  flex: 1;
  padding: 1.5rem;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}

.app-footer {
  background: rgba(15, 23, 42, 0.95);
  border-top: 2px solid rgba(59, 130, 246, 0.3);
  padding: 1rem 1.5rem;
  backdrop-filter: blur(10px);
}

.footer-info {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  gap: 2rem;
  flex-wrap: wrap;
  font-size: 0.875rem;
  color: #94a3b8;
  font-weight: 500;
}

.footer-info span {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

@media (max-width: 768px) {
  .app-header {
    padding: 0.75rem 1rem;
  }

  .app-title {
    font-size: 1.25rem;
  }

  .app-subtitle {
    font-size: 0.65rem;
  }

  .app-main {
    padding: 1rem;
  }

  .footer-info {
    gap: 1rem;
    font-size: 0.75rem;
  }
}
</style>
