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

// Store
import { useAppStore } from '@/Stores/AppStore';
const appStore = useAppStore();

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
        <q-list style="min-width: 150px">
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

    <!-- Main container -->
    <div class="layout-container">
      <!-- Floating Action Button (FAB) - Hidden when sidebar is open -->
      <q-btn
        v-if="!sidebarOpen"
        fab
        color="white"
        text-color="primary"
        class="fab-menu"
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
  position: fixed;
  top: 4px;
  left: 4px;
  z-index: 10000;
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

.layout-container {
  display: flex;
  min-height: 100vh;
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
}

/* Content area */
.content {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
  background-color: var(--background-color);
  color: var(--text-color);
  padding-top: 1.5rem; /* Compact top spacing */
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
