<script setup>
/**
 * AppLayoutDefault.vue
 *
 * Clean, minimalist layout with persistent vertical sidebar
 * Features:
 * - Single FAB (Floating Action Button) to access everything
 * - Persistent vertical sidebar with all navigation
 * - Responsive design
 * - Dark mode support
 */
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import InertiaLinkWrapper from '@/Components/InertiaLinkWrapper.vue';
import { useI18n } from 'vue-i18n';
import { useQuasar } from 'quasar';

// Components
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import ChatNotificationListener from '@/Components/Chat/NotificationListener.vue';
import PrivateChatNotificationListener from '@/Components/PrivateChat/NotificationListener.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import ChatbotWidget from '@/Components/Chatbot/ChatbotWidget.vue';
import VerticalSidebar from '@/Layouts/comp/VerticalSidebar.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import ToolsSwitcherPanel from '@/Components/ToolsSwitcherPanel.vue';
import ConfirmDropdownBtn from '@/Components/ConfirmDropdownBtn.vue';

// Store
import { useAppStore } from '@/Stores/AppStore';
const appStore = useAppStore();

// Check if running in local environment
const isLocalEnvironment = computed(() => {
  const hostname = window.location.hostname;
  return hostname === 'localhost' || hostname === '127.0.0.1' || hostname.includes('.local');
});

// Props
const props = defineProps({
  title: String,
  showFooter: {
    type: Boolean,
    default: false
  }
});

// Composables
const { t, locale } = useI18n();
const page = usePage();
const q = useQuasar();

// Import dark mode composable
import { useDarkMode } from '@/composables/useDarkMode';
const { isDarkMode, toggleDarkMode } = useDarkMode();

// State
const sidebarOpen = ref(false);
const globalSearch = ref(null);

// User information
const user = computed(() => page.props?.auth?.user);

// Chatbot reference
const chatbotRef = ref(null);

const toggleChat = () => {
  if (chatbotRef.value) {
    chatbotRef.value.toggle();
  }
};

// Toggle sidebar
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

// Close sidebar
const closeSidebar = () => {
  sidebarOpen.value = false;
};

// Ziggy Route Helpers
const hasRoute = (name) => {
  try {
    return typeof window !== 'undefined'
      && typeof window.route === 'function'
      && window.route().has(name);
  } catch {
    return false;
  }
};

const getRoute = (name, params = {}) => {
  try {
    return hasRoute(name) ? window.route(name, params) : '#';
  } catch {
    return '#';
  }
};

const logout = () => {
  if (hasRoute('logout')) {
    router.post(window.route('logout'));
  }
};

// Clear offline data function
const clearingData = ref(false);
const handleClearOfflineData = async () => {
  clearingData.value = true;
  console.log('🗑️ Starting offline data cleanup...');

  try {
    // Clear IndexedDB (Dexie database)
    try {
      const { db } = await import('@/offline/dexie.js');
      await db.delete();
      console.log('✅ IndexedDB cleared');
    } catch (error) {
      console.warn('⚠️ Could not clear IndexedDB:', error);
    }

    // Clear localStorage items related to offline functionality
    const offlineKeys = [];
    for (let i = 0; i < localStorage.length; i++) {
      const key = localStorage.key(i);
      if (key && (
        key.startsWith('offline_') ||
        key.startsWith('sync_') ||
        key.startsWith('network_') ||
        key.includes('queue') ||
        key.includes('cache') ||
        key.includes('lesson') ||
        key.includes('student') ||
        key.includes('grade')
      )) {
        offlineKeys.push(key);
      }
    }

    offlineKeys.forEach(key => {
      localStorage.removeItem(key);
      console.log(`🗑️ Removed localStorage: ${key}`);
    });

    // Clear sessionStorage
    sessionStorage.clear();
    console.log('✅ SessionStorage cleared');

    // Clear any cached service worker data
    if ('caches' in window) {
      const cacheNames = await caches.keys();
      await Promise.all(
        cacheNames.map(cacheName => {
          console.log(`🗑️ Clearing cache: ${cacheName}`);
          return caches.delete(cacheName);
        })
      );
      console.log('✅ Service Worker caches cleared');
    }

    // Reset sync queue
    try {
      const { clearQueue } = await import('@/offline/syncQueue.js');
      await clearQueue();
      console.log('✅ Sync queue cleared');
    } catch (error) {
      console.warn('⚠️ Could not clear sync queue:', error);
    }

    // Show success message
    q.notify({
      type: 'positive',
      message: '✅ All offline data has been cleared successfully!',
      caption: 'The page will reload to reset the application state.',
      timeout: 3000,
      position: 'top'
    });

    // Reload the page to reset everything
    setTimeout(() => {
      window.location.reload();
    }, 1000);

  } catch (error) {
    console.error('❌ Error clearing offline data:', error);
    q.notify({
      type: 'negative',
      message: '❌ Error clearing offline data',
      caption: 'Please check the console for details.',
      timeout: 5000,
      position: 'top'
    });
  } finally {
    clearingData.value = false;
  }
};
</script>

<template>


  <div class="app-layout">
    <!-- Page title and meta -->
    <Head :title="title" />

    <!-- System banner for notifications -->
    <!-- <Banner /> -->

    <!-- Fixed Top-Left User Status Widget -->
    <div class="fixed-status-widget" v-if="user">
      <!-- User Profile Trigger -->
      <div class="status-avatar">
        <img v-if="user.profile_photo_url" :src="user.profile_photo_url" :alt="user.name" />
        <span v-else>{{ user.name.charAt(0) }}</span>
      </div>
      <div class="status-name">{{ user.name }}</div>

      <!-- Dropdown Menu -->
       
      <q-menu>
        <q-list style="min-width: 150px w-24">
          <InertiaLinkWrapper :href="getRoute('profile.show')">
            <q-item clickable v-close-popup>
              <q-item-section avatar>
                <q-icon name="person" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ t('common.profile') || 'Profile' }}</q-item-label>
              </q-item-section>
            </q-item>
          </InertiaLinkWrapper>

          <q-separator />

          <q-item>
            <q-item-section>
              <LanguageSwitcher />
            </q-item-section>
          </q-item>

          <q-item>
            <q-item-section>
              <ToolsSwitcherPanel />
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item clickable v-close-popup @click="logout">
            <q-item-section avatar>
              <q-icon name="logout" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ t('common.logout') || 'Logout' }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-menu>
    </div>

    <!-- Clear Offline Data Button (Local Only) -->
    <div v-if="isLocalEnvironment" class="fixed-clear-data-btn">
      <ConfirmDropdownBtn
        label="Clear Offline"
        icon="delete_sweep"
        color="negative"
        title="Clear Offline Data"
        message="⚠️ This will clear ALL offline data including cached lessons, students, grades, pending sync queue, local storage data, and IndexedDB data. This action cannot be undone."
        confirm-label="Clear Data"
        confirm-icon="delete_sweep"
        confirm-color="negative"
        :loading="clearingData"
        dense
        no-caps
        @confirm="handleClearOfflineData"
      />
    </div>

    <!-- Main container -->
    <div class="layout-container">
      <!-- Floating Action Button (FAB) - Hidden when sidebar is open -->
       <q-btn
        v-if="!sidebarOpen"
        fab
        color="white"
        text-color="primary"
        class="fab-menu "
        @click="toggleSidebar"
        size="lg"
      >
        <ApplicationMark class="app-icon-fab" />
        
        <q-tooltip anchor="center left" self="center right" :offset="[10, 0]">
          {{ t('common.menu') }}
        </q-tooltip>
      </q-btn>
      
      <!-- Vertical Sidebar -->
      <VerticalSidebar
        v-model="sidebarOpen"
        :user="user"
        :is-dark-mode="isDarkMode"
        @toggle-dark-mode="toggleDarkMode"
        @close="closeSidebar"
        @toggle-chat="toggleChat"
      />

      <!-- Main content area with dynamic margin -->
      <div class="main-container" :class="{ 'sidebar-open': sidebarOpen }">
        <!-- Main content -->
        <main class="content">
          <!-- Header Slot -->
          <div class="page-header" v-if="$slots.header">
            <slot name="header" />
          </div>

          <!-- Main Content Slot -->
          <div class="page-content">
            <slot />
          </div>
        </main>

        <!-- Footer -->
        <footer v-if="props.showFooter" class="footer">
          <div class="footer-content">
            <p class="copyright">
              © {{ new Date().getFullYear() }} MyClass. {{ t('common.allRightsReserved') }}
            </p>
          </div>
        </footer>
      </div>
    </div>

    <!-- Chat Notification Listeners -->
    <ChatNotificationListener
      v-if="user"
      :user-id="user.id"
    />
    <PrivateChatNotificationListener
      v-if="user"
      :user-id="user.id"
    />

    <ChatbotWidget ref="chatbotRef" custom-trigger />
  </div>
</template>

<style scoped>
/* Layout structure */
/* Layout structure */
.fixed-status-widget {
  /* position: block; */
  width: fit-content;
  top: 40px;
  left: 4px;
  /* z-index: 10000; */
  display: flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(8px);
  padding: 2px 8px;
  border-radius: 16px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  border: 1px solid rgba(0,0,0,0.05);
  cursor: pointer;
}

.status-dot {
  width: 6px;
  height: 6px;
  background-color: #22c55e;
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
}

.status-avatar {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  overflow: hidden;
  background: #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 600;
  color: #4b5563;
}

.status-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.status-name {
  font-size: 11px;
  font-weight: 500;
  color: #1f2937;
  white-space: nowrap;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
}

:global(.dark) .fixed-status-widget {
  background: rgba(30, 30, 30, 0.9);
  border-color: rgba(255,255,255,0.1);
}

:global(.dark) .status-name {
  color: #f3f4f6;
}

/* Fixed Clear Offline Data Button */
.fixed-clear-data-btn {
  position: fixed;
  top: 80px;
  left: 4px;
  z-index: 1999;
}

.fixed-clear-data-btn .q-btn {
  background: rgba(239, 68, 68, 0.9);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
  padding: 4px 8px;
  min-height: 28px;
  transition: all 0.2s ease;
}

.fixed-clear-data-btn .q-btn:hover {
  background: rgba(220, 38, 38, 0.95);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

:global(.dark) .fixed-clear-data-btn .q-btn {
  background: rgba(220, 38, 38, 0.9);
  border-color: rgba(220, 38, 38, 0.4);
}

:global(.dark) .fixed-clear-data-btn .q-btn:hover {
  background: rgba(200, 30, 30, 0.95);
}

.layout-container {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background-color: var(--background-color);
  position: relative;
}

/* Floating Action Button - Modern & Professional */
.fab-menu {
  position: fixed;
    top: 2px;
    right: 2px; 
    left: auto;
    width: 28px;  /* Slightly smaller on mobile */
    height: 28px;
  z-index: 2000;
  
  /* Modern styling overrides */
  width: 50px; /* Reduced from 64px */
  height: 50px; /* Reduced from 64px */
  border-radius: 50%;
  background: white; /* Clean white background */
  
  /* Premium soft shadow */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12), 
              0 2px 4px rgba(0, 0, 0, 0.04);
  
  /* Smooth transitions */
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  border: 1px solid rgba(255, 255, 255, 0.8);
  
  /* Ensure no padding on the button prevents the icon from growing */
  padding: 0 !important;
  
  /* Clip content to circle */
  overflow: hidden;
}

.fab-menu:hover {
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15), 
              0 4px 12px rgba(0, 0, 0, 0.08);
}

.fab-menu:active {
  transform: translateY(1px) scale(0.96);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Main container */
.main-container {
  display: flex;
  flex-direction: column;
  transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin-right: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

/* Content area */
.content {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
  background-color: var(--background-color);
  color: var(--text-color);
  padding-top: 4rem; /* Increased top spacing */
}

.page-header {
  margin-bottom: 0.5rem; /* Minimized margin */
}

.page-content {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Footer */
.footer {
  background-color: var(--background-color);
  border-top: 1px solid var(--border-color);
  padding: 1rem;
  margin-top: auto;
}

.footer-content {
  max-width: 1400px;
  margin: 0 auto;
  text-align: center;
}

/* Dark mode */
:global(.dark) .layout-container {
  background-color: var(--background-color, #121212);
}

:global(.dark) .footer {
  background-color: #1d1d1d;
  border-color: var(--border-color, #333);
  color: var(--text-color, #f5f5f5);
}

:global(.dark) .content {
  background-color: var(--background-color, #121212);
  color: var(--text-color, #f5f5f5);
}

/* Ensure the icon or custom mark inside the FAB is perfectly centered and large */
.fab-menu :deep(.app-icon-fab),
.fab-menu :deep(img.app-icon-fab),
.fab-menu .q-icon {
  display: block;
  margin: 0 auto;
  width: 48px !important; /* Force larger size */
  height: 48px !important; /* Force larger size */
  max-width: none !important;
  max-height: none !important;
  object-fit: contain;
  object-position: center;
}

/* Mobile responsiveness */
@media (max-width: 600px) {
  .content {
    padding: 0.5rem;
    padding-top: 70px;
  }

  .fab-menu {
    top: 2px;
    right: 2px; 
    left: auto;
    width: 28px;  /* Slightly smaller on mobile */
    height: 28px;
  }
  
  .app-icon-fab {
    width: 34px;
    height: 34px;
  }
}

/* Print styles */
@media print {
  .fab-menu {
    display: none;
  }
}
</style>
