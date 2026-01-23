<template>
  <AdminLayout>
    <div class="q-pa-md">
      <!-- Header Section -->
      <div class="row q-mb-lg items-center justify-between">
        <div class="col">
          <div class="row items-center q-gutter-x-sm text-grey-8 q-mb-xs">
            <q-icon name="settings" size="sm" />
            <div class="text-h5 text-weight-bold">Menu Management</div>
          </div>
          <div class="text-subtitle2 text-grey-7">Configure and organize application navigation menus</div>
        </div>
        <div class="col-auto row q-gutter-sm">
           <q-btn
            color="secondary"
            icon="upload"
            label="Import"
            outline
            @click="showBulkImportDialog = true"
          />
          <q-btn
            color="primary"
            icon="add"
            label="Create Menu"
            unelevated
            @click="showCreateDialog = true"
          />
        </div>
      </div>

      <q-card flat bordered class="bg-white rounded-borders">
          <!-- Module Tabs -->
          <q-tabs
            v-model="activeModule"
            dense
            class="text-grey-7 bg-grey-1"
            active-color="primary"
            indicator-color="primary"
            align="left"
            narrow-indicator
          >
            <q-tab
              v-for="module in modules"
              :key="module"
              :name="module"
              class="q-px-lg q-py-md"
            >
                <div class="row items-center no-wrap">
                    <q-icon name="widgets" class="q-mr-sm" size="xs" />
                    {{ formatModuleName(module) }}
                </div>
            </q-tab>
          </q-tabs>

          <q-separator />

      <q-tab-panels v-model="activeModule" animated class="bg-transparent">
        <q-tab-panel v-for="module in modules" :key="module" :name="module" class="q-pa-none">
          <MenuList
            :menus="getMenusByModule(module)"
            :module="module"
            @edit="handleEdit"
            @delete="handleDelete"
            @reorder="handleReorder"
          />
        </q-tab-panel>
      </q-tab-panels>
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
import { ref, computed, watch } from 'vue';
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
});

// State
const activeModule = ref(props.modules[0] || 'academics');
const showFormDialog = ref(false);
const showCreateDialog = ref(false);
const showDeleteDialog = ref(false);
const showBulkImportDialog = ref(false);
const selectedMenu = ref(null);
const menuToDelete = ref(null);

// Computed
const getMenusByModule = (module) => {
  return props.menus.filter(menu => menu.module === module);
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
