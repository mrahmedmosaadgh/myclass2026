<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useAppStore } from '@/Stores/AppStore';
import { useStringHelpers } from '@/composables/useStringHelpers';
import InertiaLinkWrapper from '@/Components/InertiaLinkWrapper.vue';
import { useMenuStore } from '@/Stores/useMenuStore';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import ToolsSwitcherPanel from '@/Components/ToolsSwitcherPanel.vue';
import { useI18n } from 'vue-i18n';

const Ap = useAppStore();
const menuStore = useMenuStore();
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
  let foundId = null;

  if (School_id) {
    foundId = parseInt(School_id);
  } else {
    // Try to find default from user roles
    const teacherSchoolId = usePage()?.props?.auth?.user?.teacher?.School_id;
    const studentSchoolId = usePage()?.props?.auth?.user?.student?.School_id;
    
    if (teacherSchoolId) foundId = teacherSchoolId;
    else if (studentSchoolId) foundId = studentSchoolId;
  }

  if (foundId) {
    selectedSchool_id.value = foundId;
    localStorage.setItem('school_id', foundId);
    
    // Find and set the school data object
    const userSchools = usePage()?.props?.auth?.user?.schools || [];
    const school = userSchools.find(s => s.id === foundId);
    if (school) {
        selectedSchool_data.value = school;
        Ap.selectedSchool_data = school;
    }
  }
};

const user = computed(() => usePage().props.auth.user);
const userRoles = computed(() => user.value?.roles || []);

// Available roles for switching
const availableRoles = ref(userRoles.value);

// Initialize selected role from localStorage or default
const selectedRole = ref(
  localStorage.getItem('selectedRole') ||
  (userRoles.value.length > 0 ? userRoles.value[0] : 'guest')
);

const originalRole = ref(null);
const isPreviewMode = ref(false);

onMounted(() => {
    loadSchool();
    // Initial fetch
    fetchMenusForRole(selectedRole.value);
    
    // Listen for admin preview changes
    window.addEventListener('admin:preview-role-changed', handlePreviewRoleChange);
});

onUnmounted(() => {
    window.removeEventListener('admin:preview-role-changed', handlePreviewRoleChange);
});

const handlePreviewRoleChange = (event) => {
    const previewRole = event.detail.role;
    
    if (previewRole) {
        // Entering preview mode
        if (!originalRole.value) {
            originalRole.value = selectedRole.value;
        }
        isPreviewMode.value = true;
        selectedRole.value = previewRole; // This triggers the watcher to fetch menus
        
        $q.notify({
            type: 'info',
            message: `Previewing Sidebar as: ${capitalizeFirst(previewRole)}`,
            timeout: 2000,
            position: 'top-right'
        });
    } else {
        // Exiting preview mode (revert to admin/original)
        if (originalRole.value) {
            selectedRole.value = originalRole.value;
            originalRole.value = null;
        }
        isPreviewMode.value = false;
    }
};

// Save selected role to localStorage and dispatch event
watch(selectedRole, (newRole) => {
  if (!isPreviewMode.value) {
      localStorage.setItem('selectedRole', newRole);
  }
  fetchMenusForRole(newRole);
  document.dispatchEvent(new CustomEvent('role-changed', {
    detail: { role: newRole }
  }));
});

async function fetchMenusForRole(role, force = false) {
    expandedMenus.value.clear();
    await menuStore.fetchMenus(role, force);
}

// Check if user can switch roles
const canSwitchRoles = computed(() => {
  return userRoles.value.some(role => ['admin', 'superadmin'].includes(role));
});

// Check if user can manage menus
const canManageMenus = computed(() => {
  const roles = userRoles.value || [];
  const permissions = user.value?.permissions || [];
  return roles.includes('admin') || roles.includes('superadmin') || permissions.includes('manage-menus');
});

// Menu items derived from store
const menuItems = computed(() => {
    return menuStore.getMenusForRole(selectedRole.value);
});
const expandedMenus = ref(new Set());

// --- Search & Localization Logic ---
const { locale } = useI18n();
const searchTerm = ref('');

const getLocalizedTitle = (item) => {
    if (!item) return '';
    if (locale.value === 'ar' && item.label_ar) {
        return item.label_ar;
    }
    return item.title;
};

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
// -----------------------------------

const logout = () => {
  if (hasRoute('logout')) {
    router.post(window.route('logout'));
  } else {
    console.warn('Logout route not found');
  }
};

// Computed params for routes
const currentRouteParams = computed(() => {
    const params = {};
    // Extract school slug if available
    if (selectedSchool_data.value?.slug) {
        params.school_slug = selectedSchool_data.value.slug;
        params.school = selectedSchool_data.value.slug;
    } else if (selectedSchool_data.value?.id) {
        // Fallback to ID if slug missing (though route might demand slug)
        params.school_slug = selectedSchool_data.value.id;
        params.school = selectedSchool_data.value.id;
    }
    return params;
});

// Helper functions for routes
const hasRoute = (name) => {
  try {
    if (typeof window === 'undefined' || typeof window.route === 'undefined') return false;
    return window.route().has(name);
  } catch (error) {
    return false;
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

const safeRoute = (path, params = {}) => {
  try {
    // Handle null, undefined, or empty paths
    if (!path) return '#';

    // Handle absolute paths and URLs
    if (typeof path === 'string' && (path.startsWith('/') || path.startsWith('http'))) {
      return path;
    }

    // Check if route exists in Ziggy
    if (!hasRoute(path)) {
      if (!['admin.dashboard', 'teacher.home', 'student.home'].includes(path)) {
        console.warn(`Route not found: ${path}`);
      }
      return typeof path === 'string' && path.startsWith('/') ? path : '#';
    }

    // Attempt to merge fallback params
    const mergedParams = { ...currentRouteParams.value, ...params };
    
    // Check if we have all required parameters for this route to avoid Ziggy crashing
    // This is a safety check because Ziggy throws hard errors for missing params
    try {
        // We do a try-catch here specifically for route generation
        return window.route(path, mergedParams);
    } catch (routeError) {
        // Filter out "parameter is required" errors to keep console clean
        const isMissingParam = routeError.message && routeError.message.includes('parameter is required');
        
        if (!isMissingParam) {
            console.warn(`Error generating route ${path}:`, routeError);
        } else {
             // Optional: Log missing params for debugging if needed, but keep it quiet for users
             // console.debug(`Skipping route ${path} due to missing params.`);
        }
        
        return '#';
    }

  } catch (error) {
    console.warn(`General error in safeRoute for ${path}:`, error);
    return '#';
  }
};

// Helper to check if a menu item is active
const isItemActive = (item) => {
  if (!item || !item.to) return false;

  // 1. If it's a named route
  if (hasRoute(item.to)) {
    try {
      // Check exact match or if it's a parent resource (e.g., 'teachers.index' matches 'teachers.*')
      // simple check: route().current(item.to)
      return route().current(item.to);
    } catch (e) {
      return false;
    }
  }
  
  // 2. Fallback: Path comparison (if item.to is a URL path like '/dashboard')
  if (typeof item.to === 'string' && item.to.startsWith('/')) {
    return usePage().url === item.to || usePage().url.startsWith(item.to + '/');
  }

  return false;
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
  
      round
      :icon="sidebarOpen ? 'close' : 'menu'"
      @click="toggleSidebar"
      class="q-ml-sm fixed top-0 right-0 z-50 scale-150 m-8 bg-red-400"
    />
 
<!-- userRoles:{{ userRoles }} -->

    <!-- User Menu -->
    <q-btn-dropdown flat class="q-ml-md my-8 mx-12" :label="user.name">
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
        :class="[$q.dark.isActive ? 'bg-grey-9' : 'bg-grey-3', isPreviewMode ? 'bg-purple-1' : '']"
      >
    <div class="p-0  relative    ">
            <!-- Offline Indicator -->
            <q-banner v-if="menuStore.offline" dense class="bg-warning text-white q-mb-xs">
                <template v-slot:avatar>
                    <q-icon name="wifi_off" size="sm" color="white" />
                </template>
                <div class="text-caption">Offline Mode (Cached)</div>
            </q-banner>

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
                <!-- Edit Menu Button -->
                <q-btn
                  v-if="canManageMenus"
                  round
                  flat
                  icon="edit_note"
                  color="grey-7"
                  size="sm"
                  class="q-mr-sm"
                  @click="router.visit(route('admin.menus.index'))"
                >
                  <q-tooltip>Edit Menus</q-tooltip>
                </q-btn>

                <q-btn
                  v-if="canManageMenus"
                  round
                  flat
                  icon="refresh"
                  :loading="menuStore.loading"
                  color="grey-7"
                  size="sm"
                  class="q-mr-sm"
                  @click="fetchMenusForRole(selectedRole, true)"
                >
                  <q-tooltip>Refresh Menus</q-tooltip>
                </q-btn>

                <!--    toolsSwitcher -->
                <ToolsSwitcherPanel class="q-mr-sm" />

<!-- ========================================================= -->

        </div> 
        </q-banner>

        <!-- Search Input -->
        <div class="q-px-md q-pb-sm">
          <q-input
            v-model="searchTerm"
            dense
            outlined
            placeholder="Search..."
            clearable
            class="full-width"
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>

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
            <template v-for="(item, index) in filteredMenuItems" :key="index">
              <!-- Menu item with children (submenu) -->
              <template v-if="item.children && item.children.length">
                <q-expansion-item
                  :icon="getDefaultIcon(item.icon)"
                  :label="getLocalizedTitle(item)"
                  :caption="item.inactive ? 'Inactive' : ''"
                  :disable="item.inactive"
                  :default-opened="expandedMenus.has(index) || !!searchTerm || item.children.some(child => isItemActive(child))"
                  @update:model-value="(val) => val ? expandedMenus.add(index) : expandedMenus.delete(index)"
                  expand-separator
                  :header-class="{ 'text-primary bg-blue-50': item.children.some(child => isItemActive(child)) }"
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
                          :active="isItemActive(childItem)"
                          active-class="text-primary bg-blue-100"
                        >
                          <q-item-section avatar>
                            <q-icon :name="getDefaultIcon(childItem.icon)" />
                          </q-item-section>
                          <q-item-section>
                            <q-item-label>{{ getLocalizedTitle(childItem) }}</q-item-label>
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
                          <q-item-label>{{ getLocalizedTitle(disabledItem) }}</q-item-label>
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
                  :active="isItemActive(item)"
                  active-class="text-primary bg-blue-100"
                >
                  <q-item-section avatar>
                    <q-icon :name="getDefaultIcon(item.icon)" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ getLocalizedTitle(item) }}</q-item-label>
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
                  <q-item-label>{{ getLocalizedTitle(item) }}</q-item-label>
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

