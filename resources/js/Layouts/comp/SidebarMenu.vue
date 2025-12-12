<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useAppStore } from '@/Stores/AppStore';
import { useStringHelpers } from '@/composables/useStringHelpers';
import InertiaLinkWrapper from '@/Components/InertiaLinkWrapper.vue';
import menuConfig from './MenuConfig/index.js';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import ToolsSwitcherPanel from '@/Components/ToolsSwitcherPanel.vue';
const Ap = useAppStore();
const { capitalizeFirst } = useStringHelpers();

// Sidebar state
const sidebarOpen = ref(false);
const selectedSchool_data = ref({});
const selectedSchool_id = ref(null);

// Reference to the sidebar element
const sidebarRef = ref(null);

// Function to toggle the sidebar
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

// Function to close the sidebar
const closeSidebar = () => {
  sidebarOpen.value = false;
};

const onSchoolSelected = (value1) => {
  selectedSchool_data.value = value1;
  Ap.selectedSchool_data = selectedSchool_data.value;
  localStorage.setItem('school_id', value1.id);
};

const loadSchool = () => {
  const School_id = localStorage.getItem('school_id');
  if (School_id) {
    selectedSchool_id.value = parseInt(School_id);
  } else {
    const School_id2 = usePage()?.props?.auth?.user?.teacher?.School_id;
    if (School_id2) {
      selectedSchool_id.value = School_id2;
      localStorage.setItem('school_id', selectedSchool_id.value);
      return;
    }

    const School_id3 = usePage()?.props?.auth?.user?.student?.School_id;
    if (School_id3) {
      selectedSchool_id.value = School_id3;
      localStorage.setItem('school_id', selectedSchool_id.value);
      return;
    }
  }
};

onMounted(loadSchool);

// No need to update localStorage for sidebar state

const user = computed(() => usePage().props.auth.user);
const userRoles = computed(() => user.value?.roles || []);

// Available roles for switching
const availableRoles = ref(userRoles.value);

// Initialize selected role from localStorage or default
const selectedRole = ref(
  localStorage.getItem('selectedRole') ||
  (userRoles.value.length > 0 ? userRoles.value[0] : 'guest')
);

// Save selected role to localStorage and dispatch event
watch(selectedRole, (newRole) => {
  localStorage.setItem('selectedRole', newRole);
  document.dispatchEvent(new CustomEvent('role-changed', {
    detail: { role: newRole }
  }));
});

// Check if user can switch roles
const canSwitchRoles = computed(() => {
  return userRoles.value.some(role => ['admin', 'superadmin'].includes(role));
});

// Cache for menu items
const menuCache = ref({});
const menuItems = ref([]);
const expandedMenus = ref(new Set());

// Initialize menu items
onMounted(() => {
  menuItems.value = getMenuForRole(selectedRole.value);
});

// Update menu items when role changes
watch(selectedRole, (newRole) => {
  menuItems.value = getMenuForRole(newRole);
  expandedMenus.value.clear();
});

// Helper function to get menu items for a specific role with caching
function getMenuForRole(role) {
  const normalizedRole = role ? role.toLowerCase() : 'guest';
  if (menuCache.value[normalizedRole]) {
    return menuCache.value[normalizedRole];
  }
  let items = menuConfig[normalizedRole] || menuConfig.guest;
  menuCache.value[normalizedRole] = items;
  return items;
}

const logout = () => {
  if (hasRoute('logout')) {
    router.post(window.route('logout'));
  } else {
    console.warn('Logout route not found');
  }
};

// Helper functions for routes
const hasRoute = (name) => {
  try {
    if (typeof window === 'undefined' || typeof window.route === 'undefined') return false;
    return window.route().has(name);
  } catch (error) {
    return false;
  }
};

const safeRoute = (path, params = {}) => {
  try {
    // Handle null, undefined, or empty paths
    if (!path) return '#';

    // Handle absolute paths and URLs
    if (typeof path === 'string' && (path.startsWith('/') || path.startsWith('http'))) {
      return path;
    }

    // Check if route exists
    if (!hasRoute(path)) {
      // Don't log warnings for common routes
      if (!['admin.dashboard', 'teacher.home', 'student.home'].includes(path)) {
        console.warn(`Route not found: ${path}`);
      }
      return typeof path === 'string' && path.startsWith('/') ? path : '/route-not-found';
    }

    // Return the route
    return window.route(path, params);
  } catch (error) {
    console.warn(`Route error with ${path}:`, error);
    return typeof path === 'string' && path.startsWith('/') ? path : '/route-not-found';
  }
};

const isRouteMissing = (path) => {
  // Handle null, undefined, or empty paths
  if (!path) return true;

  // Handle special paths
  if (path === '#' || path === '/route-not-found') return true;

  // Handle absolute paths without extensions (likely valid)
  if (typeof path === 'string' && path.startsWith('/') && !path.includes('.')) {
    return false;
  }

  // Check if route exists
  return !hasRoute(path);
};

// Helper function to get a default icon if none is provided
const getDefaultIcon = (icon) => {
  if (!icon || typeof icon !== 'string') {
    return 'help_outline'; // Default fallback icon
  }
  return icon;
};
</script>

<template>
  <div class="flex items-center">
    <!-- Menu Button -->
    <q-btn
      flat
      round
      :icon="sidebarOpen ? 'close' : 'menu'"
      @click="toggleSidebar"
      class="q-ml-sm fixed top-0 right-0"
    />
 
<!-- userRoles:{{ userRoles }} -->

    <!-- User Menu -->
    <q-btn-dropdown flat class="q-ml-md" :label="user.name">
      <q-list>
        <InertiaLinkWrapper :href="safeRoute('profile.show')">
          <q-item clickable v-close-popup tag="div">
            <q-item-section>
              <q-item-label>Profile</q-item-label>
            </q-item-section>
          </q-item>
        </InertiaLinkWrapper>
        <q-item clickable v-close-popup @click="logout">
          <q-item-section>
            <q-item-label>Logout</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-btn-dropdown>

    <!-- Sidebar Menu Dialog -->
    <q-dialog
      ref="sidebarRef"
      v-model="sidebarOpen"
      position="right"
      full-height
      seamless
      :maximized="false"
      transition-show="slide-left"
      transition-hide="slide-right"
      @click-outside="closeSidebar"
    >

      <q-card
        class="column no-wrap sidebar-card relative "
        style="width: 250px"
        :class="$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-3'"
      >
    <div class="p-0  relative    ">
           <!-- Role indicator in sidebar -->
      <q-banner v-if="canSwitchRoles" class="  q-mb-sm mt-2">
          <template v-slot:avatar>
              <q-icon name="visibility" class="scale-50" color="primary" />
            </template>
            <div class="text-caption text-primary">VIEWING AS:</div> 
            <div class="text-subtitle1 text-weight-bold text-primary">
            <!-- {{ capitalizeFirst(selectedRole) }} -->
            





  
    <!-- Role Switcher (Only visible for admin/superadmin) -->
    <div class="p-0 z-50   "> 
      <q-btn-dropdown
        v-if="canSwitchRoles"
        flat
        :label="capitalizeFirst(selectedRole)"
        color="primary"
        class="q-px-sm"
      >
        <q-list>
          <q-item
            v-for="role in availableRoles"
            :key="role"
            clickable
            v-close-popup
            @click="selectedRole = role"
            :active="selectedRole === role"
            active-class="text-primary"
          >
            <q-item-section>
              <q-item-label>{{ capitalizeFirst(role) }}</q-item-label>
            </q-item-section>
          </q-item>

          <q-separator v-if="$page?.props?.auth?.user?.schools" />

          <q-item
            v-for="school in $page?.props?.auth?.user?.schools"
            :key="school.id"
            clickable
            v-close-popup
            @click="onSchoolSelected(school)"
            :active="selectedSchool_id === school.id"
            active-class="text-primary"
          >
            <q-item-section>
              <q-item-label>{{ school?.name }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>
    </div>





<!-- ========================================================= -->

                <!-- Language Switcher -->
                <LanguageSwitcher class="q-mr-sm" />
                <!--  School Switcher -->
                <!--    toolsSwitcher -->
                <ToolsSwitcherPanel class="q-mr-sm" />

<!-- ========================================================= -->

        </div> 
        </q-banner>

<div class="p-0 absolute -top-2">

    <q-btn
    flat
    round
    :icon="sidebarOpen ? 'close' : 'menu'"
    @click="toggleSidebar"
    class="q-ml-sm "
    />
</div>
        </div>
        <q-scroll-area class="col">
          <!-- Render menu items based on selected role -->
          <q-list padding>
            <template v-for="(item, index) in menuItems" :key="index">
              <!-- Menu item with children (submenu) -->
              <template v-if="item.children && item.children.length">
                <q-expansion-item
                  :icon="getDefaultIcon(item.icon)"
                  :label="item.title"
                  :caption="item.inactive ? 'Inactive' : ''"
                  :disable="item.inactive"
                  :default-opened="expandedMenus.has(index)"
                  @update:model-value="(val) => val ? expandedMenus.add(index) : expandedMenus.delete(index)"
                  expand-separator
                >
                  <q-list class="q-pl-md">
                    <!-- Active child items -->
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
                        >
                          <q-item-section avatar>
                            <q-icon :name="getDefaultIcon(childItem.icon)" />
                          </q-item-section>
                          <q-item-section>
                            <q-item-label>{{ childItem.title }}</q-item-label>
                          </q-item-section>
                          <q-tooltip v-if="childItem.tooltip">{{ childItem.tooltip }}</q-tooltip>
                        </q-item>
                      </InertiaLinkWrapper>
                    </template>

                    <!-- Disabled child items -->
                    <template v-for="(disabledItem, disabledIndex) in item.children" :key="`disabled-${disabledIndex}`">
                      <q-item
                        v-if="disabledItem && (disabledItem.disabled || disabledItem.inactive || isRouteMissing(disabledItem?.to))"
                        disable
                        clickable
                        v-ripple
                      >
                        <q-item-section avatar>
                          <q-icon :name="getDefaultIcon(disabledItem.icon)" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>{{ disabledItem.title }}</q-item-label>
                          <q-item-label caption v-if="disabledItem.inactive">Inactive</q-item-label>
                          <q-item-label caption v-else-if="isRouteMissing(disabledItem?.to)">Coming Soon</q-item-label>
                        </q-item-section>
                        <q-tooltip v-if="disabledItem.tooltip">{{ disabledItem.tooltip }}</q-tooltip>
                      </q-item>
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
                >
                  <q-item-section avatar>
                    <q-icon :name="getDefaultIcon(item.icon)" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ item.title }}</q-item-label>
                  </q-item-section>
                  <q-tooltip v-if="item.tooltip">{{ item.tooltip }}</q-tooltip>
                </q-item>
              </InertiaLinkWrapper>

              <!-- Disabled regular menu item -->
              <q-item
                v-else-if="item && !item.children"
                disable
                clickable
                v-ripple
              >
                <q-item-section avatar>
                  <q-icon :name="getDefaultIcon(item.icon)" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ item.title }}</q-item-label>
                  <q-item-label caption v-if="item.inactive">Inactive</q-item-label>
                  <q-item-label caption v-else-if="isRouteMissing(item.to)">Coming Soon</q-item-label>
                </q-item-section>
                <q-tooltip v-if="item.tooltip">{{ item.tooltip }}</q-tooltip>
              </q-item>
            </template>
          </q-list>
        </q-scroll-area>
      </q-card>
    </q-dialog>
  </div>
</template>

<style scoped>
.inactive {
  opacity: 0.6;
}

/* Sidebar card styling */
.sidebar-card {
  border-radius: 0;
  height: 100vh;
  max-height: 100vh;
}

/* Fix for Inertia links inside Quasar components */
:deep(a) {
  text-decoration: none;
  color: inherit;
  display: block;
}

:deep(.q-item) {
  width: 100%;
}

/* Ensure proper styling for active items */
:deep(.q-item.q-router-link--active),
:deep(.q-item--active) {
  color: var(--q-primary);
  background-color: rgba(25, 118, 210, 0.1);
}

/* Dark mode support */
:global(.dark) :deep(.q-item.q-router-link--active),
:global(.dark) :deep(.q-item--active) {
  background-color: rgba(64, 158, 255, 0.2);
}

/* Custom styling for the dialog to make it look like a drawer */
:deep(.q-dialog__inner) {
  max-width: 250px !important;
}

:deep(.q-dialog__inner--minimized > div) {
  max-width: 250px !important;
  width: 250px !important;
}
</style>

