<template>
  <q-card style="min-width: 600px; max-width: 800px">
    <q-card-section class="row items-center q-pb-none">
      <div class="text-h6">{{ menu ? 'Edit Menu' : 'Create Menu' }}</div>
      <q-space />
      <q-btn icon="close" flat round dense @click="$emit('cancel')" />
    </q-card-section>

    <q-card-section>
      <q-form @submit="handleSubmit" class="q-gutter-md">
        <!-- Label -->
        <q-input
          v-model="form.label"
          label="Menu Label *"
          hint="Display name for the menu item"
          :rules="[val => !!val || 'Label is required']"
          outlined
        />

        <!-- Module -->
        <q-select
          v-model="form.module"
          :options="moduleOptions"
          label="Module *"
          hint="Feature grouping for this menu"
          :rules="[val => !!val || 'Module is required']"
          outlined
          emit-value
          map-options
        />

        <!-- Parent Menu -->
        <q-select
          v-model="form.parent_id"
          :options="parentOptions"
          label="Parent Menu"
          hint="Leave empty for root level menu"
          outlined
          clearable
          emit-value
          map-options
          :loading="loadingParents"
        />

        <!-- Route -->
        <q-select
          v-model="form.route"
          :options="routeOptions"
          label="Route"
          hint="Named Laravel route (optional for parent menus)"
          outlined
          clearable
          use-input
          input-debounce="300"
          @filter="filterRoutes"
          :loading="loadingRoutes"
        >
          <template v-slot:no-option>
            <q-item>
              <q-item-section class="text-grey">
                No routes found
              </q-item-section>
            </q-item>
          </template>
        </q-select>

        <!-- Permission -->
        <q-select
          v-model="form.permission"
          :options="permissionOptions"
          label="Permission"
          hint="Spatie permission required to see this menu"
          outlined
          clearable
          use-input
          input-debounce="300"
          @filter="filterPermissions"
          :loading="loadingPermissions"
        />

        <!-- Icon -->
        <q-input
          v-model="form.icon"
          label="Icon"
          hint="Icon name (e.g., 'home', 'settings')"
          outlined
        >
          <template v-slot:prepend>
            <q-icon :name="form.icon || 'help_outline'" />
          </template>
        </q-input>

        <!-- Order -->
        <q-input
          v-model.number="form.order"
          type="number"
          label="Order"
          hint="Display order (lower numbers appear first)"
          outlined
          :rules="[val => val >= 0 || 'Order must be 0 or greater']"
        />

        <!-- Active Status -->
        <q-toggle
          v-model="form.is_active"
          label="Active"
          color="positive"
        />

        <!-- Feature Flag -->
        <div class="q-gutter-sm">
          <q-toggle
            v-model="form.is_feature_flag"
            label="Feature Flag"
            color="purple"
          />
          
          <q-input
            v-if="form.is_feature_flag"
            v-model="form.feature_flag_key"
            label="Feature Flag Key *"
            hint="Configuration key for feature flag"
            :rules="[val => !form.is_feature_flag || !!val || 'Feature flag key is required']"
            outlined
          />
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

// Form data
const form = reactive({
  label: '',
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
