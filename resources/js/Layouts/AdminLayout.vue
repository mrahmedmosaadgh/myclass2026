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

    // If Ziggy route metadata is available, check for required parameters
    // and avoid calling window.route if required params are missing.
    try {
      const ziggy = window?.Ziggy;
      const routeMeta = ziggy?.routes?.[path];
      if (routeMeta && routeMeta.uri) {
        const requiredParams = [];
        const re = /{([^}]+)}/g;
        let m;
        while ((m = re.exec(routeMeta.uri)) !== null) {
          // m[1] may include trailing ? for optional params
          const raw = m[1];
          const isOptional = raw.endsWith('?');
          const name = isOptional ? raw.slice(0, -1) : raw;
          if (!isOptional) requiredParams.push(name);
        }

        // If any required param is missing from mergedParams, skip route
        const missing = requiredParams.filter(p => mergedParams[p] === undefined || mergedParams[p] === null);
        if (missing.length > 0) {
          // Silently return '#' to avoid Ziggy throwing about missing params
          return '#';
        }
      }
    } catch (err) {
      // Ignore and fall back to attempting to generate route
    }

    return window.route(path, mergedParams);
  } catch (error) {
    // Suppress Ziggy missing-parameter errors to avoid console noise for expected cases
    const msg = String(error?.message || error);
    if (msg.includes('Ziggy error') && msg.includes('parameter')) {
      return '#';
    }

    // Don't spam console in production; keep helpful warnings during development
    if (process.env.NODE_ENV !== 'production') {
      console.warn(`Route error with ${path}:`, error);
    }
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

</script>


<template>
  <BaseLayout>
    <!-- Top Navigation -->
    <template #navigation>
      <div class="flex items-center space-x-2">
        <Link v-for="action in quickActions"
              :key="action.name"
              :href="action.href"
              class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors">
          <span class="mr-1">{{ action.icon }}</span>
          <span class="hidden md:inline">{{ action.name }}</span>
        </Link>
      </div>
    </template>

    <!-- Sidebar Content -->
    <template #sidebar>
      <div class="q-py-md">
        <div v-if="menuStore.loading" class="q-pa-md flex flex-center">
          <q-spinner-dots color="primary" size="40px" />
          <div class="text-grey-6 q-mt-sm">Loading navigation...</div>
        </div>
        
        <q-list v-else padding class="menu-list">
          <template v-for="item in navigation" :key="item.id || item.title">
            <!-- Items with children -->
            <q-expansion-item
              v-if="item.children && item.children.length > 0"
              :icon="item.icon || 'folder'"
              :label="item.title"
              header-class="text-grey-8 text-weight-medium"
              expand-separator
            >
              <q-list padding class="q-pl-md">
                <Link v-for="child in item.children"
                      :key="child.id || child.title"
                      :href="safeRoute(child.to)"
                      class="no-decoration">
                  <q-item clickable 
                          v-ripple
                          :active="safeRoute(child.to) && $page.url.startsWith(safeRoute(child.to))"
                          active-class="bg-indigo-50 text-indigo-700"
                          class="rounded-borders q-mx-sm">
                    <q-item-section avatar v-if="child.icon" class="min-w-0 q-pr-sm">
                      <q-icon :name="child.icon" size="xs" />
                    </q-item-section>
                    <q-item-section class="text-caption">{{ child.title }}</q-item-section>
                  </q-item>
                </Link>
              </q-list>
            </q-expansion-item>

            <!-- Regular items -->
            <Link v-else :href="safeRoute(item.to)" class="no-decoration">
              <q-item clickable 
                      v-ripple
                      :active="safeRoute(item.to) && $page.url === safeRoute(item.to)"
                      active-class="bg-indigo-50 text-indigo-700 font-bold"
                      class="rounded-borders q-mx-sm">
                <q-item-section avatar class="min-w-0 q-pr-sm">
                  <q-icon :name="item.icon || 'link'" />
                </q-item-section>
                <q-item-section>{{ item.title }}</q-item-section>
              </q-item>
            </Link>
          </template>

          <q-separator q-my-md />

          <!-- System Overview section in sidebar -->
          <q-item-label header class="text-uppercase text-weight-bolder text-grey-6" style="font-size: 11px; letter-spacing: 1px;">
            System Overview
          </q-item-label>
          
          <div class="q-px-md q-gutter-y-sm q-pb-lg">
            <q-card flat bordered class="bg-grey-1">
              <q-card-section class="q-pa-sm">
                <div class="text-caption text-grey-7">Active Users</div>
                <div class="text-h6 text-weight-bold">1,234</div>
              </q-card-section>
            </q-card>
            
            <q-card flat bordered class="bg-grey-1">
              <q-card-section class="q-pa-sm">
                <div class="text-caption text-grey-7">Classes Today</div>
                <div class="text-h6 text-weight-bold">42</div>
              </q-card-section>
            </q-card>
          </div>
        </q-list>
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
