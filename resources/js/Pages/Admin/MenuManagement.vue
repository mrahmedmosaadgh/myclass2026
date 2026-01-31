<template>
  <AdminLayout>
    <div class="q-pa-md">
      <!-- Header Section -->
      <div class="row q-col-gutter-sm q-mb-lg items-center justify-between">
        <div class="col-12 col-sm">
          <div class="row items-center q-gutter-x-sm text-grey-8 q-mb-xs">
            <q-icon name="settings" size="sm" />
            <div class="text-h5 text-weight-bold">Menu Management</div>
          </div>
          <div class="text-subtitle2 text-grey-7">Configure and organize application navigation menus</div>
        </div>
        <div class="col-12 col-sm-auto row q-gutter-sm justify-end">
           <q-btn
            color="secondary"
            icon="upload"
            label="Import"
            outline
            class="col-grow sm:col-auto"
            @click="showBulkImportDialog = true"
          />
          <q-btn
            color="primary"
            icon="add"
            label="Create Menu"
            unelevated
            class="col-grow sm:col-auto"
            @click="showCreateDialog = true"
          />
        </div>
      </div>

      <q-card flat bordered class="bg-white rounded-borders">
          <!-- Module Dropdown (split button) -->
          <div class="row items-center q-gutter-md q-pa-md bg-grey-1">
            <q-btn-dropdown
              split
              color="orange"
              push
              glossy
              no-caps
              :label="activeModule ? formatModuleName(activeModule) : 'All Modules'"
              icon="folder"
              @click="activeModule = null"
            >
              <q-list style="min-width: 200px">
                <q-item
                  clickable
                  v-close-popup
                  @click="activeModule = null"
                  :active="activeModule === null"
                  active-class="bg-primary text-white"
                >
                    <q-item-section avatar>
                        <q-avatar icon="apps" color="grey-7" text-color="white" />
                    </q-item-section>
                    <q-item-section>
                        <q-item-label>All Modules</q-item-label>
                        <q-item-label caption>Show everything</q-item-label>
                    </q-item-section>
                </q-item>
                <q-separator />
                <q-item
                  v-for="module in modules"
                  :key="module"
                  clickable
                  v-close-popup
                  @click="activeModule = module"
                  :active="activeModule === module"
                  active-class="bg-primary text-white"
                >
                  <q-item-section avatar>
                    <q-avatar icon="widgets" color="primary" text-color="white" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ formatModuleName(module) }}</q-item-label>
                    <q-item-label caption>{{ module }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>

            <!-- Role Dropdown -->
            <q-btn-dropdown
              split
              color="teal"
              push
              glossy
              no-caps
              :label="selectedRoleLabel"
              icon="badge"
              @click="updateSelectedRole(null)"
            >
              <q-list style="min-width: 220px">
                <q-item clickable v-close-popup @click="updateSelectedRole(null)">
                  <q-item-section avatar>
                    <q-avatar icon="public" color="grey-7" text-color="white" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label><strong>All Roles</strong></q-item-label>
                    <q-item-label caption>Show menus for all roles</q-item-label>
                  </q-item-section>
                </q-item>
                <q-separator />
                <q-item
                  v-for="role in props.roles"
                  :key="role.id"
                  clickable
                  v-close-popup
                  @click="updateSelectedRole(role)"
                >
                  <q-item-section avatar>
                    <q-avatar icon="person" color="primary" text-color="white" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ role.name }}</q-item-label>
                    <q-item-label caption>{{ role.permissions?.length || 0 }} permissions</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-btn-dropdown>

            <!-- Preview badge & loading state -->
            <div class="row items-center q-ml-sm">
              <div v-if="previewLoading" class="row items-center q-gutter-sm">
                <q-spinner-dots size="18px" color="primary" />
                <div class="text-caption text-grey-6">Loading preview...</div>
              </div>
              <div v-else-if="selectedRole" class="text-caption text-primary row items-center">
                <div class="text-caption">VIEWING AS:</div>
                <strong class="q-ml-xs">{{ selectedRole.name }} (Preview)</strong>
                <q-icon name="info" size="xs" class="q-ml-xs cursor-pointer text-primary">
                  <q-tooltip anchor="top middle" self="bottom middle">This shows the menus a user with the selected role would see (server-side preview). It does not reflect your personal permissions.</q-tooltip>
                </q-icon>
              </div>
            </div>

            <!-- Preview Toggle -->
            <q-toggle
              v-model="previewMode"
              label="Preview Mode"
              left-label
              dense
              @update:model-value="updatePreviewMode"
            />

            <q-space />
          </div>

          <q-separator />

      <div class="q-pa-none">
          <!-- We render a single list if "All Modules" is selected, or just the active one -->
          <!-- Using a computed property to flatten or filter -->
          <MenuList
            :menus="filteredMenus"
            :module="activeModule || 'all'"
            :preview="previewMode"
            @edit="handleEdit"
            @delete="handleDelete"
            @reorder="handleReorder"
            @toggle="handleToggle"
          />
      </div>
      </q-card>

      <!-- Create/Edit Dialog -->
      <q-dialog v-model="showFormDialog" persistent>
        <MenuForm
          :menu="selectedMenu"
          :modules="modules"
          @save="handleSave"
          @cancel="closeFormDialog"
        />
      </q-dialog>

      <!-- Delete Confirmation Dialog -->
      <MenuDeleteDialog
        v-model="showDeleteDialog"
        :menu="menuToDelete"
        @confirm="confirmDelete"
        @cancel="showDeleteDialog = false"
      />

      <!-- Bulk Import Dialog -->
      <BulkImportDialog
        v-model="showBulkImportDialog"
        @imported="handleBulkImported"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MenuList from './MenuManagement/components/MenuList.vue';
import MenuForm from './MenuManagement/components/MenuForm.vue';
import MenuDeleteDialog from './MenuManagement/components/MenuDeleteDialog.vue';
import BulkImportDialog from './MenuManagement/components/BulkImportDialog.vue';
import { useMenuStore } from '@/Stores/useMenuStore';
import axios from 'axios';

const $q = useQuasar();
const page = usePage();
const menuStore = useMenuStore();

// Props from Inertia
const props = defineProps({
  menus: Array,
  modules: Array,
  roles: {
    type: Array,
    default: () => []
  }
});

// State
const activeModule = ref('academics'); // Start with default or first one
const showFormDialog = ref(false);
const showCreateDialog = ref(false);
const showDeleteDialog = ref(false);
const showBulkImportDialog = ref(false);
const selectedMenu = ref(null);
const menuToDelete = ref(null);
const previewMode = ref(Boolean(new URLSearchParams(window.location.search).get('preview')));

// Smart Preset Logic
const selectedPreset = ref(null);
const presetOptions = ref([]);

const filterPresets = (val, update, abort) => {
  if (val.length < 2) {
    abort();
    return;
  }

  update(() => {
    axios.get('/api/admin/menus/helpers/presets', { params: { q: val } })
        .then(response => {
            presetOptions.value = response.data;
        })
        .catch(error => {
            console.error('Failed to fetch presets', error);
            presetOptions.value = [];
        });
  });
};

const applyPreset = (preset) => {
    if (!preset) return;
    
    // Auto-fill form fields
    form.value.label = preset.label;
    form.value.label_ar = preset.label_ar || '';
    form.value.route = preset.route;
    form.value.icon = preset.icon;
    form.value.module = preset.module; // Make sure module case matches
    form.value.role_specific = preset.role_specific;
    
    // Notify user
    $q.notify({
        type: 'positive',
        message: 'Form auto-filled from preset!',
        position: 'top',
        timeout: 1000
    });
    
    // Clear selection so they can search again if needed, 
    // but keep options for visual feedback
    // selectedPreset.value = null; 
};

const selectedRoleId = ref(new URLSearchParams(window.location.search).get('role'));
const selectedRole = ref(
  selectedRoleId.value ? props.roles.find(r => String(r.id) === String(selectedRoleId.value)) : null
);

// Preview-specific menus fetched from the server when admin selects a role
const previewMenus = ref(null);
const previewLoading = ref(false);

// Watch for role changes to trigger sidebar preview sync
watch(selectedRole, (newRole) => {
    // Dispatch event for SidebarMenu to pick up
    // We send null if no role is selected (to revert to admin view)
    const roleName = newRole ? newRole.name : null;
    
    // Use a custom event that SidebarMenu listens to
    window.dispatchEvent(new CustomEvent('admin:preview-role-changed', { 
        detail: { role: roleName } 
    }));
});

const filteredMenus = computed(() => {
    // 1. First, select the source (Preview menus OR All props menus)
    let source = previewMenus.value ? previewMenus.value : props.menus;
    
    // 2. Filter by Module (if one is selected)
    if (activeModule.value) {
        source = source.filter(menu => menu.module === activeModule.value);
    }
    
    // 3. Filter by Role Permission (Client-side simulation)
    // Only apply this logic if we are NOT in server-side preview mode (previewMenus is null)
    // AND a role is selected.
    if (!previewMenus.value && selectedRole.value) {
        const roleName = selectedRole.value.name;
        const rolePerms = (selectedRole.value.permissions || []).map(p => p.name);
        
        const filterRecursive = (menu) => {
            if (menu.role_specific && menu.role_specific !== roleName) return null;
            if (menu.permission && !rolePerms.includes(menu.permission)) return null;

            let children = menu.children || [];
            if (children.length > 0) {
                children = children.map(child => filterRecursive(child)).filter(c => c !== null);
            }
            return { ...menu, children: children };
        };

        source = source.map(m => filterRecursive(m)).filter(m => m !== null);
    }

    return source;
});
function updatePreviewMode(val) {
  const params = new URLSearchParams(window.location.search);
  if (val) params.set('preview', '1');
  else params.delete('preview');
  const query = params.toString();
  const newUrl = window.location.pathname + (query ? '?' + query : '');
  // Use history.replaceState to update the URL without triggering an Inertia visit
  window.history.replaceState(null, '', newUrl);
}

async function updateSelectedRole(role) {

  // Keep local refs in sync immediately so UI updates before navigation
  selectedRoleId.value = role && role.id ? String(role.id) : null;
  selectedRole.value = role || null;

  // Build a query object and update the URL using history.replaceState (no Inertia visit)
  const params = new URLSearchParams(window.location.search);
  if (role && role.id) params.set('role', String(role.id));
  else params.delete('role');

  const qObj = {};
  for (const [k, v] of params.entries()) qObj[k] = v;

  const newUrl = window.location.pathname + (Object.keys(qObj).length ? ('?' + new URLSearchParams(qObj).toString()) : '');
  window.history.replaceState(null, '', newUrl);

  // If a role is selected, fetch server-side preview menus (requires manage-menus permission)
  if (role && role.name) {
    previewLoading.value = true;
    try {
      const data = await menuStore.fetchMenus(role.name, true, { preview: true });
      previewMenus.value = data;
    } catch (err) {
      previewMenus.value = null;
      if (err?.response?.status === 403) {
        $q.notify({ type: 'negative', message: 'You are not authorized to preview role menus.' });
      } else {
        $q.notify({ type: 'negative', message: 'Failed to load preview menus' });
      }
    } finally {
      previewLoading.value = false;
    }
  } else {
    previewMenus.value = null;
  }
}

function handleRoleClick(role) {
  updateSelectedRole(role);
}

// Keep selectedRole in sync if props.roles or the URL change (e.g., after an Inertia reload)
watch([
  () => props.roles,
  () => selectedRoleId.value,
  () => window.location.search
], () => {
  const id = selectedRoleId.value || new URLSearchParams(window.location.search).get('role');
  selectedRole.value = id ? props.roles.find(r => String(r.id) === String(id)) : null;
}); 

// Computed
const selectedRoleLabel = computed(() => selectedRole.value ? selectedRole.value.name : 'All Roles');

const getMenusByModule = (module) => {
  // If we have server-side preview menus available, use those directly
  if (previewMenus.value) {
    return previewMenus.value.filter(menu => menu.module === module);
  }

  let menus = props.menus.filter(menu => menu.module === module);

  // If a role is selected, filter menus to those that are either public (no permission)
  // or have a permission that belongs to the selected role.
  if (selectedRole.value) {
    const roleName = selectedRole.value.name; // e.g., 'teacher'
    const rolePerms = (selectedRole.value.permissions || []).map(p => p.name);
    
    // Recursive filter function
    const filterRecursive = (menu) => {
        // 1. Role Specific Check: Only show if NULL or matches current role
        if (menu.role_specific && menu.role_specific !== roleName) {
            return null;
        }

        // 2. Permission Check: Only show if NULL or user has permission
        if (menu.permission && !rolePerms.includes(menu.permission)) {
            return null;
        }

        // 3. Process Children Recursively
        let children = menu.children || [];
        if (children.length > 0) {
            children = children.map(child => filterRecursive(child)).filter(c => c !== null);
        }

        // Return a copy of the menu with filtered children
        return {
            ...menu,
            children: children
        };
    };

    menus = menus
        .map(m => filterRecursive(m))
        .filter(m => m !== null);
  }

  return menus;
};

const formatModuleName = (module) => {
  return module
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

// Watchers
watch(showCreateDialog, (val) => {
  if (val) {
    selectedMenu.value = null;
    showFormDialog.value = true;
    showCreateDialog.value = false;
  }
});

// On mount, if URL contains a role param, fetch preview menus for it automatically
onMounted(() => {
  if (selectedRole.value && selectedRole.value.name) {
    updateSelectedRole(selectedRole.value);
  }
});

// Handlers
const handleEdit = (menu) => {
  selectedMenu.value = menu;
  showFormDialog.value = true;
};

const handleDelete = (menu) => {
  menuToDelete.value = menu;
  showDeleteDialog.value = true;
};

const handleSave = async (menuData) => {
  try {
    if (selectedMenu.value) {
      // Update existing menu
      await axios.put(`/api/admin/menus/${selectedMenu.value.id}`, menuData);
      $q.notify({
        type: 'positive',
        message: 'Menu updated successfully',
      });
    } else {
      // Create new menu
      await axios.post('/api/admin/menus', menuData);
      $q.notify({
        type: 'positive',
        message: 'Menu created successfully',
      });
    }
    
    closeFormDialog();
    router.reload({ only: ['menus'] });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to save menu',
    });
  }
};

const confirmDelete = async () => {
  try {
    await axios.delete(`/api/admin/menus/${menuToDelete.value.id}`);
    $q.notify({
      type: 'positive',
      message: 'Menu deleted successfully',
    });
    showDeleteDialog.value = false;
    router.reload({ only: ['menus'] });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to delete menu',
    });
  }
};

const handleReorder = async (items) => {
  try {
    await axios.post('/api/admin/menus/reorder', { items });
    $q.notify({
      type: 'positive',
      message: 'Menu order updated successfully',
    });
    router.reload({ only: ['menus'] });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to reorder menus',
    });
  }
};

const getPayload = (menu) => {
    return {
        label: menu.label,
        label_ar: menu.label_ar,
        route: menu.route,
        permission: menu.permission,
        module: menu.module,
        parent_id: menu.parent_id,
        order: menu.order,
        icon: menu.icon,
        is_active: menu.is_active,
        is_feature_flag: menu.is_feature_flag,
        feature_flag_key: menu.feature_flag_key,
        meta: menu.meta,
        v2_component: menu.v2_component,
        requires_context: menu.requires_context,
        role_specific: menu.role_specific,
        v2_enabled: menu.v2_enabled,
    };
};

const handleToggle = async (menu) => {
    if (!menu || !menu.id) return;
    
    try {
        const payload = getPayload(menu);
        payload.is_active = !menu.is_active; // Toggle the value

        await axios.put(`/api/admin/menus/${menu.id}`, payload);
        
         $q.notify({
            type: 'positive',
            message: `Menu ${payload.is_active ? 'Activated' : 'Deactivated'}`,
            timeout: 1000
        });
        
        // Optimistic update
        // We need to update the local 'menus' prop copy or reload
        // Since props are read-only, we rely on router.reload, but we can also manually update the store if used
        router.reload({ only: ['menus'] });
    } catch (error) {
        console.error('Toggle error:', error);
        $q.notify({
            type: 'negative',
            message: error.response?.data?.message || 'Failed to toggle status'
        });
    }
};

const handleBulkImported = async () => {
  // Invalidate menu store cache
  menuStore.menus.clear();
  router.reload({ only: ['menus'] });
};

const closeFormDialog = () => {
  showFormDialog.value = false;
  selectedMenu.value = null;
};
</script>
