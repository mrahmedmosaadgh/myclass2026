<template>
  <header class="app-header" :class="{ compact: isScrolled }">
    <div class="header-content">
      <div class="brand-section">
        <button
          @click="$emit('toggle-menu')"
          class="menu-btn"
          :class="{ active: showMenu }"
          aria-label="Toggle menu"
        >
          <span class="menu-icon">{{ showMenu ? '✕' : '☰' }}</span>
        </button>

        <div class="brand-info">
          <h1 class="app-title">📅 SCHEDULE V7</h1>
          <p class="app-subtitle">OFFLINE-FIRST • VIEW-ONLY</p>
        </div>
      </div>

      <div class="status-section">
        <span class="status-badge" :class="store.syncStatus.value">
          {{ syncIcon }} {{ store.syncStatus.value }}
        </span>

        <span v-if="store.isOnline.value" class="online-dot online" title="Online">🟢</span>
        <span v-else class="online-dot offline" title="Offline">🔴</span>

        <button
          v-if="canInstall && !isInstalled"
          @click="$emit('install')"
          class="install-btn"
        >
          📲
        </button>

        <span v-if="isInstalled" class="installed-badge" title="Installed">✓</span>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useAppStore } from '../composables/useAppStore';

defineProps({
  showMenu: Boolean,
  canInstall: Boolean,
  isInstalled: Boolean
});

defineEmits(['toggle-menu', 'install']);

const store = useAppStore();
const isScrolled = ref(false);

const syncIcon = computed(() => {
  switch (store.syncStatus.value) {
    case 'syncing': return '💾';
    case 'synced': return '✅';
    case 'error': return '❌';
    case 'offline': return '📴';
    default: return '⏳';
  }
});

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.app-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: white;
  transition: all 0.3s ease;
  padding: env(safe-area-inset-top, 0) 0 0;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  max-width: 1200px;
  margin: 0 auto;
}

.app-header.compact .header-content {
  padding: 0.5rem 1rem;
}

.brand-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.menu-btn {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: none;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s;
}

.menu-btn:hover,
.menu-btn.active {
  background: rgba(255, 255, 255, 0.2);
}

.app-title {
  font-size: 1rem;
  font-weight: 700;
  margin: 0;
  letter-spacing: 0.5px;
}

.app-subtitle {
  font-size: 0.625rem;
  opacity: 0.7;
  margin: 0;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.status-section {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-badge {
  font-size: 0.65rem;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.1);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.synced { background: rgba(16, 185, 129, 0.3); }
.status-badge.syncing { background: rgba(59, 130, 246, 0.3); }
.status-badge.error { background: rgba(239, 68, 68, 0.3); }
.status-badge.offline { background: rgba(245, 158, 11, 0.3); }

.online-dot {
  font-size: 0.6rem;
}

.install-btn {
  background: rgba(59, 130, 246, 0.3);
  border: 1px solid rgba(59, 130, 246, 0.5);
  color: white;
  border-radius: 8px;
  padding: 0.3rem 0.5rem;
  font-size: 0.85rem;
  cursor: pointer;
  min-height: 32px;
  min-width: 32px;
}

.installed-badge {
  color: #10b981;
  font-weight: 700;
  font-size: 0.85rem;
}

@media (max-width: 400px) {
  .app-title { font-size: 0.85rem; }
  .status-badge { display: none; }
}
</style>
