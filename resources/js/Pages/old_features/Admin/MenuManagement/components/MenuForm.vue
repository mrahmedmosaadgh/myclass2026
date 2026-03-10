<template>
  <q-card style="min-width: 600px; max-width: 800px">
    <q-card-section class="row items-center q-pb-none">
      <div class="text-h6">{{ menu ? 'Edit Menu' : 'Create Menu' }}</div>
      <q-space />
      <q-btn icon="close" flat round dense @click="$emit('cancel')" />
    </q-card-section>

    <q-card-section>
      <q-form @submit="handleSubmit" class="q-gutter-md">
        
        <!-- Smart Preset Search (New) -->
        <div v-if="!menu" class="bg-blue-1 q-pa-sm rounded-borders q-mb-md">
            <div class="text-subtitle2 text-primary q-mb-xs flex items-center">
                <q-icon name="auto_awesome" class="q-mr-xs" />
                Smart Mode (Optional)
            </div>
            <q-select
                v-model="selectedPreset"
                :options="presetOptions"
                label="Search for a feature (e.g. 'Teacher Schedule')"
                outlined
                dense
                use-input
                hide-selected
                fill-input
                input-debounce="300"
                @filter="filterPresets"
                @update:model-value="applyPreset"
                placeholder="Type to search..."
            >
                <template v-slot:no-option>
                <q-item>
                    <q-item-section class="text-grey">
                    No presets found
                    </q-item-section>
                </q-item>
                </template>
                <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                        <q-item-section avatar>
                            <q-icon :name="scope.opt.icon" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>{{ scope.opt.label }}</q-item-label>
                            <q-item-label caption>{{ scope.opt.module }} • {{ scope.opt.role_specific || 'General' }}</q-item-label>
                        </q-item-section>
                    </q-item>
                </template>
            </q-select>
        </div>

        <!-- General Info Section -->
        <div class="text-subtitle2 text-primary q-mb-sm">General Information</div>
        <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
                <q-input
                v-model="form.label"
                label="Menu Label *"
                hint="Display name"
                :rules="[val => !!val || 'Label is required']"
                outlined
                dense
                />
            </div>
            <div class="col-12 col-md-6">
                <q-input
                v-model="form.label_ar"
                label="Label (Arabic)"
                hint="Arabic display name"
                outlined
                dir="rtl"
                dense
                />
            </div>
        </div>

        <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
                 <q-select
                v-model="form.module"
                :options="moduleOptions"
                label="Module *"
                hint="Feature grouping"
                :rules="[val => !!val || 'Module is required']"
                outlined
                dense
                emit-value
                map-options
                />
            </div>
            <div class="col-12 col-md-6">
               <MenuIconPicker 
                v-model="form.icon"
                @update:model-value="form.icon = $event"
               />
            </div>
        </div>

        <q-separator class="q-my-sm" />

        <!-- Routing & Hierarchy -->
        <div class="text-subtitle2 text-primary q-mb-sm">Hierarchy & Routing</div>
        
        <q-select
          v-model="form.parent_id"
          :options="parentOptions"
          label="Parent Menu"
          hint="Leave empty for root level menu"
          outlined
          dense
          clearable
          emit-value
          map-options
          :loading="loadingParents"
        />

        <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
                <q-select
                v-model="form.route"
                :options="routeOptions"
                label="Route"
                hint="Laravel named route"
                outlined
                dense
                clearable
                use-input
                input-debounce="300"
                @filter="filterRoutes"
                :loading="loadingRoutes"
                >
                <template v-slot:no-option>
                    <q-item>
                    <q-item-section class="text-grey">No routes found</q-item-section>
                    </q-item>
                </template>
                </q-select>
            </div>
            <div class="col-12 col-md-6">
                <q-select
                v-model="form.permission"
                :options="permissionOptions"
                label="Permission"
                hint="Required permission"
                outlined
                dense
                clearable
                use-input
                input-debounce="300"
                @filter="filterPermissions"
                :loading="loadingPermissions"
                />
            </div>
        </div>

        <q-separator class="q-my-sm" />

        <!-- Settings -->
        <div class="text-subtitle2 text-primary q-mb-sm">Settings</div>
        
        <div class="row items-center q-gutter-md">
            <div class="col-auto">
                 <q-input
                v-model.number="form.order"
                type="number"
                label="Sort Order"
                outlined
                dense
                style="width: 120px"
                :rules="[val => val >= 0 || 'Order >= 0']"
                />
            </div>
            <div class="col">
                <q-toggle
                v-model="form.is_active"
                label="Active Status"
                color="positive"
                />
            </div>
        </div>
        
        <!-- Advanced Feature Flag -->
        <div class="bg-grey-1 q-pa-sm rounded-borders">
            <div class="row items-center">
                 <q-toggle
                    v-model="form.is_feature_flag"
                    label="Enable Functionality Flag"
                    color="purple"
                    size="sm"
                />
            </div>
             <q-slide-transition>
                <div v-if="form.is_feature_flag" class="q-mt-sm">
                    <q-input
                        v-model="form.feature_flag_key"
                        label="Feature Flag Key *"
                        dense
                        outlined
                        :rules="[val => !form.is_feature_flag || !!val || 'Key required']"
                    />
                </div>
            </q-slide-transition>
        </div>
      </q-form>
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Cancel" color="grey-7" @click="$emit('cancel')" />
      <q-btn
        unelevated
        label="Save"
        color="primary"
        @click="handleSubmit"
        :loading="saving"
      />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import MenuIconPicker from '@/Components/MenuIconPicker.vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps({
  menu: {
    type: Object,
    default: null,
  },
  modules: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['save', 'cancel']);

// State
const saving = ref(false);
const loadingRoutes = ref(false);
const loadingPermissions = ref(false);
const loadingParents = ref(false);

const allRoutes = ref([]);
const allPermissions = ref([]);
const allParents = ref([]);

const routeOptions = ref([]);
const permissionOptions = ref([]);

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
    form.label = preset.label;
    form.label_ar = preset.label_ar || '';
    form.route = preset.route;
    form.icon = preset.icon;
    form.module = preset.module; 
    form.role_specific = preset.role_specific;
    
    // Notify user
    $q.notify({
        type: 'positive',
        message: 'Form auto-filled from preset!',
        position: 'top',
        timeout: 1000
    });
    
    // Clear selection for UX
    selectedPreset.value = null; 
};

// Form data
const form = reactive({
  label: '',
  label_ar: '',
  module: '',
  parent_id: null,
  route: null,
  permission: null,
  icon: '',
  order: 0,
  is_active: true,
  is_feature_flag: false,
  feature_flag_key: null,
});

// Computed
const moduleOptions = computed(() => {
  return props.modules.map(module => ({
    label: module.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
    value: module,
  }));
});

const parentOptions = computed(() => {
  return allParents.value.map(parent => ({
    label: `${parent.label} (${parent.module})`,
    value: parent.id,
  }));
});

// Load data on mount
onMounted(async () => {
  await Promise.all([
    loadRoutes(),
    loadPermissions(),
    loadParents(),
  ]);

  // Populate form if editing
  if (props.menu) {
    Object.assign(form, {
      label: props.menu.label,
      label_ar: props.menu.label_ar,
      module: props.menu.module,
      parent_id: props.menu.parent_id,
      route: props.menu.route,
      permission: props.menu.permission,
      icon: props.menu.icon,
      order: props.menu.order,
      is_active: props.menu.is_active,
      is_feature_flag: props.menu.is_feature_flag || false,
      feature_flag_key: props.menu.feature_flag_key,
    });
  }
});

// Methods
const loadRoutes = async () => {
  try {
    loadingRoutes.value = true;
    const response = await axios.get('/api/admin/menus/helpers/routes');
    allRoutes.value = response.data.map(route => route.name);
    routeOptions.value = allRoutes.value;
  } catch (error) {
    console.error('Failed to load routes:', error);
  } finally {
    loadingRoutes.value = false;
  }
};

const loadPermissions = async () => {
  try {
    loadingPermissions.value = true;
    const response = await axios.get('/api/admin/menus/helpers/permissions');
    allPermissions.value = response.data.map(perm => perm.name);
    permissionOptions.value = allPermissions.value;
  } catch (error) {
    console.error('Failed to load permissions:', error);
  } finally {
    loadingPermissions.value = false;
  }
};

const loadParents = async () => {
  try {
    loadingParents.value = true;
    const params = props.menu ? { exclude_id: props.menu.id } : {};
    const response = await axios.get('/api/admin/menus/helpers/parents', { params });
    allParents.value = response.data;
  } catch (error) {
    console.error('Failed to load parents:', error);
  } finally {
    loadingParents.value = false;
  }
};

const filterRoutes = (val, update) => {
  update(() => {
    const needle = val.toLowerCase();
    routeOptions.value = allRoutes.value.filter(
      route => route.toLowerCase().includes(needle)
    );
  });
};

const filterPermissions = (val, update) => {
  update(() => {
    const needle = val.toLowerCase();
    permissionOptions.value = allPermissions.value.filter(
      perm => perm.toLowerCase().includes(needle)
    );
  });
};

const handleSubmit = () => {
  saving.value = true;
  emit('save', { ...form });
  saving.value = false;
};
</script>
