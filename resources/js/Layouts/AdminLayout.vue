<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import { useMenuStore } from '@/Stores/useMenuStore';
import { useAppStore } from '@/Stores/AppStore';

const menuStore = useMenuStore();
const Ap = useAppStore();

const user = computed(() => usePage().props.auth.user);
const userRoles = computed(() => user.value?.roles || []);
const selectedRole = ref(userRoles.value[0] || 'guest');
const selectedSchool_data = ref({});
const selectedSchool_id = ref(null);

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

onMounted(async () => {
  loadSchool();
  await menuStore.fetchMenus(selectedRole.value);
});

// Get navigation from store
const navigation = computed(() => menuStore.getMenusForRole(selectedRole.value));

// Computed params for routes
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

const safeRoute = (path, params = {}) => {
  try {
    if (!path) return '#';
    if (typeof path === 'string' && (path.startsWith('/') || path.startsWith('http'))) return path;
    if (!hasRoute(path)) return '#';

    const mergedParams = { ...currentRouteParams.value, ...params };
    return window.route(path, mergedParams);
  } catch (error) {
    console.warn(`Route error with ${path}:`, error);
    return '#';
  }
};

// Quick actions - updated to use safeRoute if needed, or keeping hardcoded for now
const quickActions = computed(() => {
  return [
    { name: 'Add User', href: safeRoute('dashboard'), icon: '➕' }, // Assuming dashboard exists
    { name: 'Create Class', href: safeRoute('dashboard'), icon: '📚' },
    { name: 'View Reports', href: safeRoute('dashboard'), icon: '📊' },
  ];
});

const expandedItems = ref(new Set());

const toggleExpand = (itemName) => {
  if (expandedItems.value.has(itemName)) {
    expandedItems.value.delete(itemName);
  } else {
    expandedItems.value.add(itemName);
  }
};
</script>

<template>
  <BaseLayout>
    <!-- Top Navigation -->
    <template #navigation>
      <div class="flex space-x-4">
        <Link v-for="action in quickActions"
              :key="action.name"
              :href="action.href"
              class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md text-indigo-600 bg-indigo-50 hover:bg-indigo-100">
          <span class="mr-1">{{ action.icon }}</span>
          {{ action.name }}
        </Link>
      </div>
    </template>

    <!-- Responsive Navigation -->
    <template #responsive-navigation>
      <div class="pt-2 pb-3 space-y-1">
        <!-- Loading state -->
        <div v-if="menuStore.loading" class="pl-3 pr-4 py-2 text-gray-500">
          Loading navigation...
        </div>
        
        <template v-else v-for="item in navigation" :key="item.id || item.title">
          <button v-if="item.children && item.children.length > 0"
                  @click="toggleExpand(item.title)"
                  class="w-full text-left block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300">
            {{ item.icon }} {{ item.title }}
          </button>
          <div v-if="item.children && item.children.length > 0 && expandedItems.has(item.title)" class="ml-4">
            <Link v-for="child in item.children"
                  :key="child.id || child.title"
                  :href="safeRoute(child.to)"
                  class="block pl-3 pr-4 py-2 border-l-4 text-sm font-medium hover:bg-gray-50 hover:border-gray-300">
              {{ child.icon }} {{ child.title }}
            </Link>
          </div>
          <Link v-else-if="!item.children || item.children.length === 0"
                :href="safeRoute(item.to)"
                class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:bg-gray-50 hover:border-gray-300">
            {{ item.icon }} {{ item.title }}
          </Link>
        </template>
      </div>
    </template>

    <!-- Sidebar -->
    <template #sidebar>
      <nav class="mt-5 px-2">
        <div v-if="menuStore.loading" class="text-gray-500 px-2 py-2">
          Loading navigation...
        </div>
        
        <div v-else class="space-y-1">
          <template v-for="item in navigation" :key="item.id || item.title">
            <!-- Items with children -->
            <div v-if="item.children && item.children.length > 0" class="space-y-1">
              <button @click="toggleExpand(item.title)"
                      class="w-full group flex items-center px-2 py-2 text-base font-medium rounded-md text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
                {{ item.title }}
                <svg :class="[expandedItems.has(item.title) ? 'transform rotate-90' : '', 'ml-auto h-5 w-5']"
                     xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20"
                     fill="currentColor">
                  <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
              </button>

              <div v-show="expandedItems.has(item.title)" class="space-y-1">
                <Link v-for="child in item.children"
                      :key="child.id || child.title"
                      :href="safeRoute(child.to)"
                      :class="[safeRoute(child.to) && $page.url.startsWith(safeRoute(child.to))
                        ? 'bg-indigo-50 text-indigo-600'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                        'group flex items-center pl-10 pr-2 py-2 text-sm font-medium rounded-md']">
                  <span class="mr-3 flex-shrink-0">{{ child.icon }}</span>
                  {{ child.title }}
                </Link>
              </div>
            </div>

            <!-- Regular items -->
            <Link v-else
                  :href="safeRoute(item.to)"
                  :class="[safeRoute(item.to) && $page.url.startsWith(safeRoute(item.to))
                    ? 'bg-indigo-50 text-indigo-600'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                    'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
              <span class="mr-3 flex-shrink-0">{{ item.icon }}</span>
              {{ item.title }}
            </Link>
          </template>
        </div>
      </nav>

      <!-- System Overview -->
      <div class="mt-8 px-4">
        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
          System Overview
        </h3>
        <div class="mt-2 space-y-2">
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Active Users</p>
            <p class="text-2xl font-semibold text-gray-900">1,234</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">Classes Today</p>
            <p class="text-2xl font-semibold text-gray-900">42</p>
          </div>
          <div class="bg-white p-4 rounded-lg shadow-sm">
            <p class="text-sm font-medium text-gray-600">System Status</p>
            <p class="text-sm font-medium text-green-600">All Systems Operational</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Main Content -->
    <slot></slot>
  </BaseLayout>
</template>

<style scoped>
.transform {
  transition: transform 0.15s ease-in-out;
}
</style>
