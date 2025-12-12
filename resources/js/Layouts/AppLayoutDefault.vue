<script setup>
/**
 * AppLayoutDefault.vue
 *
 * Main application layout that provides the structure for all pages.
 * Features:
 * - Responsive header with navigation
 * - SidebarMenu component for navigation
 * - Dark mode toggle
 * - Language switcher
 * - Notification system
 * - Global search
 * - Breadcrumb navigation
 */
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import InertiaLinkWrapper from '@/Components/InertiaLinkWrapper.vue';
import { useI18n } from 'vue-i18n';

// Components
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import SidebarMenu from '@/Layouts/comp/SidebarMenu.vue';
import DateTimeDisplay from '@/Layouts/comp/date/date_time.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import ChatNotificationListener from '@/Components/Chat/NotificationListener.vue';
import PrivateChatNotificationListener from '@/Components/PrivateChat/NotificationListener.vue';
import BreadcrumbsNav from '@/Components/BreadcrumbsNav.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import NetworkStatusIndicator from '@/Components/NetworkStatusIndicator.vue';

// Store
import { useAppStore } from '@/Stores/AppStore';
const appStore = useAppStore();

// Props and emits
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
const currentLocale = computed(() => locale.value);

// Import dark mode composable
import { useDarkMode } from '@/composables/useDarkMode';
const { isDarkMode, toggleDarkMode } = useDarkMode();

// State
const globalSearch = ref(null);

// User information
const user = computed(() => page.props?.auth?.user);

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

const isActiveRoute = (name) => {
  try {
    return typeof window !== 'undefined'
      && typeof window.route === 'function'
      && window.route().current(name);
  } catch {
    return false;
  }
};


</script>

<template>
      <!-- Global Network Status Indicator -->
 
     
     
      <div class=" no-print " > 
        <NetworkStatusIndicator />
      </div>
  <div class="app-layout">
    <!-- Page title and meta -->
    <Head :title="title" />

    <!-- System banner for notifications -->
    <Banner />

    <!-- Main container with flex layout instead of q-layout -->
    <div class="layout-container">
      <!-- Header/Navbar -->
      <header class="header">
        <div class="header-content">
          <!-- Left section: Logo -->
          <div class="header-left">
            <Link :href="getRoute('dashboard')" class="logo-link">
              <ApplicationMark class="logo" />
            </Link>
          </div>

          <!-- Center section: Date/time and breadcrumbs -->
          <div class="header-center">
            <DateTimeDisplay class="date-time" />
             <BreadcrumbsNav class="breadcrumbs" />
          </div>

          <!-- Right section: Actions and user menu -->
          <div class="header-right">
            <!-- Global Search -->
            <GlobalSearch ref="globalSearch" class="search-button" />


            <!-- Private Chat Link -->
            <InertiaLinkWrapper
              v-if="user && hasRoute('private-chat.index')"
              :href="getRoute('private-chat.index')"
            >
              <q-btn
                flat
                dense
                :color="isActiveRoute('private-chat.index') ? 'primary' : 'grey'"
                :class="{ 'active-nav-link': isActiveRoute('private-chat.index') }"
                class="chat-button"
              >
                <q-icon name="chat" size="sm" class="q-mr-xs" />
                <span class="text-sm hide-on-mobile">{{ t('common.chat') }}</span>
              </q-btn>
            </InertiaLinkWrapper>

            <!-- Dark Mode Toggle -->
            <q-btn
              flat
              dense
              round
              @click="toggleDarkMode"
              :icon="isDarkMode ? 'light_mode' : 'dark_mode'"
              :title="isDarkMode ? t('common.switchToLight') : t('common.switchToDark')"
              class="dark-mode-toggle"
            />

            <!-- Notification Bell -->
            <NotificationBell
              v-if="user"
              :user-id="user.id"
              class="notification-bell"
            />

            <!-- User Menu -->
            <SidebarMenu v-if="user" class="user-menu" />
          </div>
        </div>
      </header>

      <!-- Main content area -->
      <div class="main-container">
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
      </div>

      <!-- Footer -->
      <footer v-if="props.showFooter" class="footer">
        <div class="footer-content">
          <p class="copyright">
            © {{ new Date().getFullYear() }} MyClass. {{ t('common.allRightsReserved') }}
          </p>
        </div>
      </footer>
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




  </div>
</template>

<style scoped>
/* Layout structure */
.layout-container {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: var(--background-color);
}

.main-container {
  display: block;
  flex: 1;
  background-color: var(--background-color);
}

/* Header styles */
.header {
  /* background-color: white; */
  border-bottom: 1px solid var(--border-color);
  padding: 0 1rem;
  height: 64px;
  /* position: sticky; */
  top: 0;
  /* z-index: 100; */
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
}

.header-left, .header-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.header-center {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo {
  height: 36px;
  width: auto;
}



/* Content area */
.content {
  flex: 1;
  padding: 1rem;
  overflow-y: auto;
  background-color: var(--background-color);
  color: var(--text-color);
}

.page-header {
  margin-bottom: 1rem;
}

/* Footer */
.footer {
  background-color: white;
  border-top: 1px solid var(--border-color);
  padding: 1rem;
}

.footer-content {
  max-width: 1400px;
  margin: 0 auto;
  text-align: center;
}

/* Active navigation styles */
.active-nav-item {
  background-color: rgba(25, 118, 210, 0.1);
  color: var(--q-primary);
}

.active-nav-link {
  background-color: rgba(25, 118, 210, 0.1);
}

/* Responsive utilities */
@media (max-width: 600px) {
  .hide-on-mobile {
    display: none;
  }

  .date-time {
    transform: scale(0.8);
  }
}

/* Dark mode */
:global(.dark) .layout-container {
  background-color: var(--background-color, #121212);
}

:global(.dark) .header,
:global(.dark) .footer {
  background-color: #1d1d1d;
  border-color: var(--border-color, #333);
  color: var(--text-color, #f5f5f5);
}

:global(.dark) .main-container {
  background-color: var(--background-color, #121212);
}

:global(.dark) .content {
  background-color: var(--background-color, #121212);
  color: var(--text-color, #f5f5f5);
}

:global(.dark) .page-content {
  color: var(--text-color, #f5f5f5);
}

:global(.dark) .active-nav-item {
  background-color: rgba(64, 158, 255, 0.2);
}

/* RTL support */
:global([dir="rtl"]) .header-left,
:global([dir="rtl"]) .header-right {
  flex-direction: row-reverse;
}

:global([dir="rtl"]) .q-item__section--avatar {
  padding-right: 0;
  padding-left: 16px;
}

/* Link styles */
.logo-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
}


</style>
