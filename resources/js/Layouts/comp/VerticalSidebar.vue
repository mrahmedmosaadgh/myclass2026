<script setup>
/**
 * VerticalSidebar.vue
 * 
 * Persistent vertical sidebar with all navigation elements
 * Features:
 * - Logo at top
 * - Action icons (Search, Chat, Theme, Notifications)
 * - Role-based navigation menu
 * - User profile at bottom
 * - Responsive design
 */
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useAppStore } from '@/Stores/AppStore';
import { useStringHelpers } from '@/composables/useStringHelpers';
import InertiaLinkWrapper from '@/Components/InertiaLinkWrapper.vue';
import { useMenuStore } from '@/Stores/useMenuStore';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import ToolsSwitcherPanel from '@/Components/ToolsSwitcherPanel.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import { useI18n } from 'vue-i18n';

// Props
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    required: true
  },
  isDarkMode: {
    type: Boolean,
    default: false
  }
});

// Emits
const emit = defineEmits(['update:modelValue', 'toggleDarkMode', 'close', 'toggleChat']);

// Stores
const Ap = useAppStore();
const menuStore = useMenuStore();
const { capitalizeFirst } = useStringHelpers();
const { t, locale } = useI18n();

// Computed
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

// Chat Handler
const handleChatToggle = () => {
    emit('toggleChat');
    // Optional: Close sidebar on mobile
    if (window.innerWidth < 1024) {
        emit('close');
    }
};

// State
const selectedSchool_data = ref({});
const selectedSchool_id = ref(null);
const globalSearch = ref(null);
const searchTerm = ref('');
const expandedMenus = ref(new Set());

// User roles
const userRoles = computed(() => props.user?.roles || []);
const availableRoles = ref(userRoles.value);

// Initialize selected role from localStorage or default
const selectedRole = ref(
  localStorage.getItem('selectedRole') ||
  (userRoles.value.length > 0 ? userRoles.value[0] : 'guest')
);

const originalRole = ref(null);
const isPreviewMode = ref(false);

// School management
const onSchoolSelected = (value1) => {
  selectedSchool_data.value = value1;
  Ap.selectedSchool_data = selectedSchool_data.value;
  localStorage.setItem('school_id', value1.id);
};

const loadSchool = () => {
  const School_id = localStorage.getItem('school_id');
  let foundId = null;
  const userSchools = usePage()?.props?.auth?.user?.schools || [];

  if (School_id) {
    foundId = parseInt(School_id);
  } else {
    const teacherSchoolId = usePage()?.props?.auth?.user?.teacher?.School_id;
    const studentSchoolId = usePage()?.props?.auth?.user?.student?.School_id;
    
    if (teacherSchoolId) foundId = teacherSchoolId;
    else if (studentSchoolId) foundId = studentSchoolId;
  }

  // Default to first school if no specific ID found
  if (!foundId && userSchools.length > 0) {
    foundId = userSchools[0].id;
  }

  if (foundId) {
    // Validate foundId against available schools
    let school = userSchools.find(s => s.id === foundId);
    
    // Fallback if ID invalid
    if (!school && userSchools.length > 0) {
      school = userSchools[0];
      foundId = school.id;
    }

    if (school) {
      selectedSchool_id.value = foundId;
      selectedSchool_data.value = school;
      Ap.selectedSchool_data = school;
      localStorage.setItem('school_id', foundId);
    }
  }
};

// Lifecycle
onMounted(() => {
  loadSchool();
  fetchMenusForRole(selectedRole.value);
  window.addEventListener('admin:preview-role-changed', handlePreviewRoleChange);
});

onUnmounted(() => {
  window.removeEventListener('admin:preview-role-changed', handlePreviewRoleChange);
});

// Preview role handling
const handlePreviewRoleChange = (event) => {
  const previewRole = event.detail.role;
  
  if (previewRole) {
    if (!originalRole.value) {
      originalRole.value = selectedRole.value;
    }
    isPreviewMode.value = true;
    selectedRole.value = previewRole;
  } else {
    if (originalRole.value) {
      selectedRole.value = originalRole.value;
      originalRole.value = null;
    }
    isPreviewMode.value = false;
  }
};

// Watch selected role
watch(selectedRole, (newRole) => {
  if (!isPreviewMode.value) {
    localStorage.setItem('selectedRole', newRole);
  }
  fetchMenusForRole(newRole);
  document.dispatchEvent(new CustomEvent('role-changed', {
    detail: { role: newRole }
  }));
});

// Fetch menus
async function fetchMenusForRole(role, force = false) {
  expandedMenus.value.clear();
  await menuStore.fetchMenus(role, true);
}

// Permissions
const canSwitchRoles = computed(() => {
  return userRoles.value.some(role => ['admin', 'superadmin'].includes(role));
});

const canManageMenus = computed(() => {
  const roles = userRoles.value || [];
  const permissions = props.user?.permissions || [];
  return roles.includes('admin') || roles.includes('superadmin') || permissions.includes('manage-menus');
});

// Menu items
const menuItems = computed(() => {
  return menuStore.getMenusForRole(selectedRole.value);
});

// Localization
const getLocalizedTitle = (item) => {
  if (!item) return '';
  if (locale.value === 'ar' && item.label_ar) {
    return item.label_ar;
  }
  return item.title;
};

// Filtered menu items
const filteredMenuItems = computed(() => {
  if (!searchTerm.value || !searchTerm.value.trim()) {
    return menuItems.value;
  }
  
  const lower = searchTerm.value.toLowerCase();
  
  const filter = (items) => {
    return items.reduce((acc, item) => {
      const title = getLocalizedTitle(item);
      const matches = title.toLowerCase().includes(lower);
      
      let childrenMatch = [];
      if (item.children && item.children.length) {
        childrenMatch = filter(item.children);
      }
      
      if (matches || childrenMatch.length > 0) {
        acc.push({
          ...item,
          children: childrenMatch
        });
      }
      
      return acc;
    }, []);
  };
  
  return filter(menuItems.value);
});

// Route helpers
const currentRouteParams = computed(() => {
  const params = {};
  if (selectedSchool_data.value?.slug) {
    params.school_slug = selectedSchool_data.value.slug;
    params.school = selectedSchool_data.value.slug;
  } else if (selectedSchool_data.value?.id) {
    params.school_slug = selectedSchool_data.value.id;
    params.school = selectedSchool_data.value.id;
  }
  return params;
});

const hasRoute = (name) => {
  try {
    if (typeof window === 'undefined' || typeof window.route === 'undefined') return false;
    return window.route().has(name);
  } catch (error) {
    return false;
  }
};

const isRouteMissing = (path) => {
  if (!path) return true;
  if (path === '#' || path === '/route-not-found') return true;
  if (typeof path === 'string' && path.startsWith('/') && !path.includes('.')) {
    return false;
  }
  return !hasRoute(path);
};

const safeRoute = (path, params = {}) => {
  try {
    if (!path) return '#';
    if (typeof path === 'string' && (path.startsWith('/') || path.startsWith('http'))) {
      return path;
    }
    if (!hasRoute(path)) {
      return typeof path === 'string' && path.startsWith('/') ? path : '#';
    }
    const mergedParams = { ...currentRouteParams.value, ...params };
    try {
      return window.route(path, mergedParams);
    } catch (routeError) {
      return '#';
    }
  } catch (error) {
    return '#';
  }
};

const isItemActive = (item) => {
  if (!item || !item.to) return false;
  if (hasRoute(item.to)) {
    try {
      return route().current(item.to);
    } catch (e) {
      return false;
    }
  }
  if (typeof item.to === 'string' && item.to.startsWith('/')) {
    return usePage().url === item.to || usePage().url.startsWith(item.to + '/');
  }
  return false;
};

const getDefaultIcon = (icon) => {
  if (!icon || typeof icon !== 'string') {
    return 'help_outline';
  }
  return icon;
};

// Logout
const logout = () => {
  if (hasRoute('logout')) {
    router.post(window.route('logout'));
  }
};

// Close sidebar
const closeSidebar = () => {
  emit('close');
};

/* --- Recent Pages History --- */
const recentPages = ref([]);

// Helper to find matching menu item for a URL
const findMenuItemForUrl = (items, currentUrl) => {
  if (!items || !items.length) return null;
  if (!currentUrl) return null;

  // Strip query string and hash from currentUrl for comparison
  const currentPath = currentUrl.split('?')[0].split('#')[0].replace(/\/$/, '');
  
  for (const item of items) {
    if (item.to) {
       let itemPath = item.to;
       
       // Resolve route name to path if possible
       if (typeof window.route === 'function' && !itemPath.startsWith('/') && !itemPath.startsWith('http')) {
           try {
               const fullUrl = window.route(item.to);
               const urlObj = new URL(fullUrl);
               itemPath = urlObj.pathname; 
           } catch(e) {}
       }

       // Normalize: ensure leading slash and remove trailing slash
       if (!itemPath.startsWith('/') && !itemPath.startsWith('http')) itemPath = '/' + itemPath;
       const normalizedItemPath = itemPath.replace(/\/$/, '');
       
       // Compare paths
       // 1. Exact match (ignoring query/hash)
       if (normalizedItemPath === currentPath) return item;
       
       // 2. Prefix match (e.g. /users matching /users/1, but NOT /users match /users-list)
       // We strictly check for '/' separator to avoid partial word matches
       if (normalizedItemPath !== '' && currentPath.startsWith(normalizedItemPath + '/')) return item;
    }
    
    if (item.children) {
      const found = findMenuItemForUrl(item.children, currentUrl);
      if (found) return found;
    }
  }
  return null;
};

const trackPageVisit = (url) => {
  if (!url || url.includes('login') || !menuItems.value.length) return;

  const item = findMenuItemForUrl(menuItems.value, url);
  if (!item) return;

  const title = getLocalizedTitle(item);
  if (!title) return;

  // Remove existing entry to move it to top (Recency)
  const existingIndex = recentPages.value.findIndex(p => p.to === item.to);
  if (existingIndex !== -1) {
     recentPages.value.splice(existingIndex, 1);
  }

  // Add to start
  recentPages.value.unshift({
    to: item.to,
    title: title,
    timestamp: Date.now()
  });

  // Keep last 5
  if (recentPages.value.length > 5) {
     recentPages.value = recentPages.value.slice(0, 5);
  }
  
  try {
    localStorage.setItem('user_recent_pages', JSON.stringify(recentPages.value));
  } catch (e) {}
};

onMounted(() => {
  try {
    const stored = localStorage.getItem('user_recent_pages');
    if (stored) recentPages.value = JSON.parse(stored);
  } catch (e) {}
});

watch(
  [() => usePage().url, menuItems],
  ([newUrl, items]) => {
    if (newUrl && items && items.length > 0) {
       trackPageVisit(newUrl);
    }
  },
  { immediate: true }
);

// Toggle dark mode
const handleToggleDarkMode = () => {
  emit('toggleDarkMode');
};
</script>

<template>
  <!-- Sidebar Overlay (Mobile) -->
  <div
    v-if="isOpen"
    class="sidebar-overlay"
    @click="closeSidebar"
  ></div>

  <!-- Sidebar -->
  <aside
    class="sidebar"
    :class="{ 'sidebar-open': isOpen }"
  >
    <!-- Sidebar Header: Reorganized with all controls -->
    <div class="sidebar-header-new">
      <!-- Top Row: Status, Logo, Name, School, Close -->
      <div class="header-top-row">
        <!-- Left Group: Logo Only -->
        <div class="header-left-group">
          <Link :href="safeRoute('dashboard')" class="logo-link-compact" @click="closeSidebar">
            <ApplicationMark class="logo-compact" />
          </Link>
        </div>
        
        <!-- School Selector (if multiple schools available) -->
        <q-btn-dropdown
          v-if="$page?.props?.auth?.user?.schools && $page.props.auth.user.schools.length > 1"
          flat
          dense
          :label="selectedSchool_data?.name || 'School'"
          color="primary"
          class="school-selector-btn"
          size="sm"
          no-caps
        >
          <q-list>
            <q-item
              v-for="school in $page?.props?.auth?.user?.schools"
              :key="school.id"
              clickable
              v-close-popup
              @click="onSchoolSelected(school)"
              :active="selectedSchool_id === school.id"
              active-class="text-primary bg-blue-50"
            >
              <q-item-section>
                <q-item-label>{{ school?.name }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
        
        <!-- Single School Display -->
        <div 
          v-else-if="$page?.props?.auth?.user?.schools && $page.props.auth.user.schools.length === 1" 
          class="school-name-single"
        >
           {{ selectedSchool_data?.name || $page.props.auth.user.schools[0].name }}
        </div>
        
        <!-- Close Button -->
        <q-btn
          flat
          dense
          round
          icon="close"
          color="grey-7"
          class="close-btn"
          @click="closeSidebar"
        >
          <q-tooltip>{{ t('common.close') || 'Close' }}</q-tooltip>
        </q-btn>
      </div>

      <!-- Role Menu & Controls Row -->
      <div v-if="canSwitchRoles" class="header-controls-row">
        <!-- Role Dropdown (Compact) -->
        <q-btn-dropdown
          flat
          :label="capitalizeFirst(selectedRole)"
          color="primary"
          class="role-btn-compact"
          dense
          size="sm"
        >
          <q-list>
            <q-item
              v-for="role in availableRoles"
              :key="role"
              clickable
              v-close-popup
              @click="selectedRole = role"
              :active="selectedRole === role"
              active-class="text-primary bg-blue-50"
            >
              <q-item-section>
                <q-item-label>{{ capitalizeFirst(role) }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>

        <!-- Year & Semester Buttons (Placeholder - adjust based on your data) -->
        <q-btn
          flat
          dense
          size="sm"
          color="primary"
          class="year-btn"
        >
          2024/2025
          <q-tooltip>Active Year</q-tooltip>
        </q-btn>
        
        <q-btn
          flat
          dense
          size="sm"
          color="primary"
          class="semester-btn"
        >
          Semester 1
          <q-tooltip>Active Semester</q-tooltip>
        </q-btn>
      </div>

      <!-- Action Icons Row -->
      <div class="action-icons-compact">


        <!-- Global Search -->
        <q-btn
          flat
          dense
          round
          icon="search"
          color="grey-7"
          class="action-btn-sm"
          @click="$refs.globalSearchDialog?.show()"
        >
          <q-tooltip>{{ t('common.search') }}</q-tooltip>
        </q-btn>

        <!-- Chat Helper -->
        <q-btn
          flat
          dense
          round
          icon="support_agent"
          color="primary"
          class="action-btn-sm"
          @click="handleChatToggle"
        >
          <q-tooltip>{{ t('common.chatHelp') || 'Help Center' }}</q-tooltip>
        </q-btn>

        <!-- Private Chat -->
        <InertiaLinkWrapper
          v-if="user && hasRoute('private-chat.index')"
          :href="safeRoute('private-chat.index')"
        >
          <q-btn
            flat
            dense
            round
            icon="chat"
            :color="route().current('private-chat.index') ? 'primary' : 'grey-7'"
            class="action-btn-sm"
          >
            <q-tooltip>{{ t('common.chat') }}</q-tooltip>
          </q-btn>
        </InertiaLinkWrapper>

        <!-- Dark Mode Toggle -->
        <q-btn
          flat
          dense
          round
          :icon="isDarkMode ? 'light_mode' : 'dark_mode'"
          color="grey-7"
          class="action-btn-sm"
          @click="handleToggleDarkMode"
        >
          <q-tooltip>{{ isDarkMode ? t('common.switchToLight') : t('common.switchToDark') }}</q-tooltip>
        </q-btn>

        <!-- History Menu -->
        <q-btn flat dense round icon="history" color="grey-7" class="action-btn-sm">
           <q-tooltip>History</q-tooltip>
           <q-menu>
              <div class="column q-py-xs" style="min-width: 220px">
                  <div class="text-caption text-grey-7 q-px-md q-py-xs text-uppercase text-weight-bold">Recent History</div>
                  <q-separator class="q-mb-xs" />
                  
                  <div v-if="recentPages.length === 0" class="q-px-md q-py-sm text-grey-6 text-caption text-center">
                     No pages visited yet
                  </div>

                  <template v-else>
                     <InertiaLinkWrapper 
                       v-for="(page, idx) in recentPages" 
                       :key="idx" 
                       :href="safeRoute(page.to)"
                     >
                       <q-item clickable v-close-popup dense class="q-px-md">
                          <q-item-section avatar style="min-width: 0; padding-right: 8px;">
                             <q-icon name="schedule" size="xs" color="grey-6" />
                          </q-item-section>
                          <q-item-section>
                             <q-item-label class="text-body2">{{ page.title }}</q-item-label>
                          </q-item-section>
                       </q-item>
                     </InertiaLinkWrapper>
                  </template>
              </div>
           </q-menu>
        </q-btn>

        <!-- Notification Bell -->
        <NotificationBell
          v-if="user"
          :user-id="user.id"
          class="action-btn-notification-sm"
        />
      </div>
    </div>

    <!-- Divider -->
    <q-separator class="header-divider" />

    <!-- Search Input (Compact) -->
    <div class="search-section-compact">
      <q-input
        v-model="searchTerm"
        dense
        outlined
        :placeholder="t('common.search') || 'Search...'"
        clearable
        class="search-input-compact"
      >
        <template v-slot:prepend>
          <q-icon name="search" size="xs" />
        </template>
      </q-input>
    </div>

    <!-- Navigation Menu -->
    <q-scroll-area class="menu-scroll-area">
      <q-list padding class="navigation-menu">
        <template v-for="(item, index) in filteredMenuItems" :key="index">
          <!-- Menu item with children (submenu) -->
          <template v-if="item.children && item.children.length">
            <q-expansion-item
              :icon="getDefaultIcon(item.icon)"
              :label="getLocalizedTitle(item)"
              :disable="item.inactive"
              :default-opened="expandedMenus.has(index) || !!searchTerm || item.children.some(child => isItemActive(child))"
              @update:model-value="(val) => val ? expandedMenus.add(index) : expandedMenus.delete(index)"
              :header-class="{ 'text-primary bg-blue-50': item.children.some(child => isItemActive(child)) }"
            >
              <q-list class="submenu">
                <template v-for="(childItem, childIndex) in item.children" :key="childIndex">
                  <InertiaLinkWrapper
                    v-if="childItem && !childItem.disabled && !childItem.inactive && !isRouteMissing(childItem?.to)"
                    :href="safeRoute(childItem?.to)"
                    @click="closeSidebar"
                  >
                    <q-item
                      clickable
                      v-ripple
                      tag="div"
                      :active="isItemActive(childItem)"
                      active-class="text-primary bg-blue-100"
                    >
                      <q-item-section avatar>
                        <q-icon :name="getDefaultIcon(childItem.icon)" size="sm" />
                      </q-item-section>
                      <q-item-section>
                        <q-item-label class="menu-label">{{ getLocalizedTitle(childItem) }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </InertiaLinkWrapper>
                </template>
              </q-list>
            </q-expansion-item>
          </template>

          <!-- Regular menu item without children -->
          <InertiaLinkWrapper
            v-else-if="item && !item.disabled && !item.inactive && !isRouteMissing(item?.to)"
            :href="safeRoute(item?.to)"
            @click="closeSidebar"
          >
            <q-item
              clickable
              v-ripple
              tag="div"
              :active="isItemActive(item)"
              active-class="text-primary bg-blue-100"
            >
              <q-item-section avatar>
                <q-icon :name="getDefaultIcon(item.icon)" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="menu-label">{{ getLocalizedTitle(item) }}</q-item-label>
              </q-item-section>
            </q-item>
          </InertiaLinkWrapper>
        </template>
      </q-list>
    </q-scroll-area>

    <!-- Hidden Global Search Dialog -->
    <q-dialog ref="globalSearchDialog">
      <GlobalSearch />
    </q-dialog>
  </aside>
</template>

<style scoped>
/* Sidebar Overlay (Mobile) */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1500;
  /* Display handled by v-if */
}

@media (max-width: 1023px) {
  .sidebar-overlay {
    display: block;
  }
}

/* Sidebar */
.sidebar {
  position: fixed;
  top: 0;
  right: 0; /* Changed from left to right */
  width: 280px;
  height: 100vh;
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
  border-left: 1px solid #e0e0e0; /* Changed border-right to border-left */
  display: flex;
  flex-direction: column;
  z-index: 1600;
  transform: translateX(100%); /* Changed from -100% to 100% */
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: -2px 0 8px rgba(0, 0, 0, 0.1); /* Shadow to left */
}

.sidebar-open {
  transform: translateX(0);
}

/* New Compact Header */
.sidebar-header-new {
  padding: 0.75rem;
  border-bottom: 1px solid #e0e0e0;
  background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
}

.header-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.logo-link-compact {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: inherit;
  transition: opacity 0.2s;
}

.logo-link-compact:hover {
  opacity: 0.8;
}

.logo-compact {
  height: 32px;
  width: auto;
}

.close-btn {
  transition: all 0.2s;
}

.close-btn:hover {
  transform: scale(1.1);
  background-color: rgba(0, 0, 0, 0.05);
}

.header-left-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
  min-width: 0;
}

.status-dot-indicator {
  width: 8px;
  height: 8px;
  background-color: #22c55e;
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
}

.header-user-name {
  color: #333;
}

.school-name-display {
  font-size: 0.875rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
  padding: 0.25rem 0.5rem;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 4px;
  text-align: center;
}

.header-controls-row {
  display: flex;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
}

.role-btn-compact,
.year-btn,
.semester-btn {
  flex: 1;
  min-width: 70px;
  font-size: 0.75rem;
}

.action-icons-compact {
  display: flex;
  justify-content: space-around;
  align-items: center;
  gap: 0.25rem;
  padding-top: 0.5rem;
}

.action-btn-sm {
  transition: all 0.2s;
  width: 32px;
  height: 32px;
}

.action-btn-sm:hover {
  transform: scale(1.1);
  background-color: rgba(0, 0, 0, 0.05);
}

.action-btn-notification-sm {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Divider */
.header-divider {
  margin: 0;
}

/* Role Section */
.role-section {
  padding: 1rem;
}

.role-label {
  font-size: 0.75rem;
  color: #666;
  margin-bottom: 0.5rem;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.role-dropdown {
  width: 100%;
}

/* Search Section */
.search-section {
  padding: 0 1rem 1rem 1rem;
}

.search-input {
  width: 100%;
}

/* Menu Scroll Area */
.menu-scroll-area {
  flex: 1;
  height: 100%;
}

/* Navigation Menu */
.navigation-menu {
  padding: 0.5rem;
}

.submenu {
  padding-left: 1rem;
}

.menu-label {
  font-size: 0.9rem;
  font-weight: 500;
}

/* Sidebar Footer */
.sidebar-footer {
  margin-top: auto;
  background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.02) 100%);
}

.footer-divider {
  margin-bottom: 1rem;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: #1a1a1a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.8rem;
  color: #666;
  text-transform: capitalize;
}

/* Dark Mode */
:global(.dark) .sidebar {
  background: linear-gradient(180deg, #1e1e1e 0%, #121212 100%);
  border-left-color: #333;
}

:global(.dark) .sidebar-header {
  border-bottom-color: #333;
}

:global(.dark) .logo-text {
  background: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

:global(.dark) .action-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

:global(.dark) .role-label {
  color: #aaa;
}

:global(.dark) .user-name {
  color: #f5f5f5;
}

:global(.dark) .user-role {
  color: #aaa;
}

:global(.dark) .sidebar-footer {
  background: linear-gradient(180deg, transparent 0%, rgba(255, 255, 255, 0.02) 100%);
}

/* Animations */
@keyframes slideIn {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}

/* Responsive */
@media (max-width: 600px) {
  .sidebar {
    width: 260px;
  }

  .logo-text {
    font-size: 1.1rem;
  }
}


/* Search Section - Compact */
.search-section-compact {
  padding: 0.5rem 0.75rem;
  margin-bottom: 0.25rem;
}

.search-input-compact {
  width: 100%;
}


/* School Selector Button */
.school-selector-btn {
  max-width: 120px;
  font-size: 0.75rem;
  margin: 0 0.25rem;
}

.school-name-single {
  font-size: 0.8rem;
  font-weight: 600;
  color: #1976D2;
  margin: 0 0.5rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 150px;
}

/* Print */
@media print {
  .sidebar {
    display: none;
  }
}
</style>
