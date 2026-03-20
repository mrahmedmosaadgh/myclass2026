<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  readOnly: {
    type: Boolean,
    default: false,
  },
  options: {
    type: Object,
    default: () => ({
      grades: [],
      subjects: [],
    }),
  },
});

const emit = defineEmits(['updated']);

const mappings = ref([]);
const loading = ref(false);
const saving = ref(false);
const error = ref(null);
const subTab = ref('basic'); // 'basic' or 'scoring'

const newMapping = ref({
  label: '',
  icon: '📊',
  grade_id: null,
  subject_id: null,
  type: 'numeric',
  max_value: 5,
  passing_value: 3,
  default_value: 5,
  sort_order: 0,
  active: true,
});

const resetNew = () => {
  newMapping.value = {
    label: '',
    icon: '📊',
    grade_id: null,
    subject_id: null,
    type: 'numeric',
    max_value: 5,
    passing_value: 3,
    default_value: 5,
    sort_order: 0,
    active: true,
  };
};

const loadMappings = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/api/cr/category-mappings');
    mappings.value = response.data.mappings ?? response.data;
  } catch (err) {
    error.value = err.response?.data?.error || err.message || 'Failed to load categories';
  } finally {
    loading.value = false;
  }
};

const saveMapping = async (mapping) => {
  if (props.readOnly) return;

  saving.value = true;
  error.value = null;

  const payload = {
    label: mapping.label,
    icon: mapping.icon || '📊',
    grade_id: mapping.grade_id,
    subject_id: mapping.subject_id,
    type: mapping.type,
    max_value: Number(mapping.max_value) || 0,
    passing_value: mapping.passing_value === '' ? null : Number(mapping.passing_value),
    default_value: Number(mapping.default_value) || 0,
    sort_order: Number(mapping.sort_order) || 0,
    active: Boolean(mapping.active),
  };

  try {
    let response;

    if (mapping.id) {
      response = await axios.patch(`/api/cr/category-mappings/${mapping.id}`, payload);
    } else {
      response = await axios.post('/api/cr/category-mappings', payload);
    }

    const updated = response.data;

    // Replace or add
    const idx = mappings.value.findIndex((m) => m.id === updated.id);
    if (idx >= 0) {
      mappings.value.splice(idx, 1, updated);
    } else {
      mappings.value.unshift(updated);
    }

    emit('updated');
    resetNew();
  } catch (err) {
    error.value = err.response?.data?.error || err.message || 'Failed to save category';
  } finally {
    saving.value = false;
  }
};

const deleteMapping = async (mapping) => {
  if (props.readOnly || !mapping.id) return;

  if (!confirm('Delete this category? This cannot be undone.')) return;

  saving.value = true;
  error.value = null;

  try {
    await axios.delete(`/api/cr/category-mappings/${mapping.id}`);
    mappings.value = mappings.value.filter((m) => m.id !== mapping.id);
    emit('updated');
  } catch (err) {
    error.value = err.response?.data?.error || err.message || 'Failed to delete category';
  } finally {
    saving.value = false;
  }
};

onMounted(loadMappings);
</script>

<template>
  <div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <div class="text-xl font-bold text-gray-900 dark:text-white">Scoring Categories</div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          Define how performance is tracked and scored.
        </div>
      </div>
      
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-1 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
          <button
            @click="subTab = 'basic'"
            :class="subTab === 'basic' ? 'bg-white dark:bg-gray-700 shadow-sm text-primary' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
            class="px-4 py-2 text-xs font-bold rounded-lg transition-all"
          >
            Basic Setup
          </button>
          <button
            @click="subTab = 'scoring'"
            :class="subTab === 'scoring' ? 'bg-white dark:bg-gray-700 shadow-sm text-primary' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
            class="px-4 py-2 text-xs font-bold rounded-lg transition-all"
          >
            Scoring Rules
          </button>
        </div>

        <button
          type="button"
          class="flex items-center justify-center w-10 h-10 text-gray-500 bg-white border border-gray-200 rounded-xl shadow-sm hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-gray-700 transition-colors"
          @click="loadMappings"
          :disabled="loading"
          title="Refresh mappings"
        >
          <span class="material-icons text-lg" :class="{ 'animate-spin': loading }">refresh</span>
        </button>
      </div>
    </div>

    <div v-if="error" class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-200">
      {{ error }}
    </div>

    <div class="overflow-hidden border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm bg-white dark:bg-gray-800">
      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm border-collapse">
          <thead class="bg-gray-50/80 dark:bg-gray-900/50 backdrop-blur-sm">
            <tr class="divide-x divide-gray-100 dark:divide-gray-800">
              <th class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 min-w-[200px]">Label</th>
              <th v-if="subTab === 'basic'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 w-24 text-center">Icon</th>
              <th v-if="subTab === 'basic'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 min-w-[150px]">Grade</th>
              <th v-if="subTab === 'basic'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 min-w-[150px]">Subject</th>
              <th v-if="subTab === 'basic'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 w-32">Type</th>
              <th v-if="subTab === 'scoring'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 w-24 text-center">Max</th>
              <th v-if="subTab === 'scoring'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 w-24 text-center">Default</th>
              <th v-if="subTab === 'scoring'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 w-24 text-center">Pass</th>
              <th v-if="subTab === 'basic'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 w-24 text-center">Order</th>
              <th v-if="subTab === 'basic'" class="px-4 py-4 font-bold text-gray-900 dark:text-gray-100 w-24 text-center">Active</th>
              <th class="px-4 py-4 w-40"></th>
            </tr>
          </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="loading">
            <td class="px-4 py-8 text-sm text-gray-500 dark:text-gray-400 text-center" colspan="8">
              <div class="flex items-center justify-center gap-2">
                <span class="inline-block w-4 h-4 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                Loading categories...
              </div>
            </td>
          </tr>

          <tr v-for="mapping in mappings" :key="mapping.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-900/40 transition-colors divide-x divide-gray-100 dark:divide-gray-800">
            <td class="px-4 py-3">
              <input
                class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-medium"
                v-model="mapping.label"
                :disabled="props.readOnly"
                placeholder="Display Name"
              />
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
               <input
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                v-model="mapping.icon"
                :disabled="props.readOnly"
                placeholder="📊"
              />
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
              <select
                class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-medium"
                v-model="mapping.grade_id"
                :disabled="props.readOnly"
              >
                <option :value="null">Global</option>
                <option v-for="g in options.grades" :key="g.id" :value="g.id">{{ g.name }}</option>
              </select>
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
              <select
                class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-medium"
                v-model="mapping.subject_id"
                :disabled="props.readOnly"
              >
                <option :value="null">Global</option>
                <option v-for="s in options.subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
              <select
                class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all font-mono text-[11px]"
                v-model="mapping.type"
                :disabled="props.readOnly"
              >
                <option value="numeric">numeric</option>
                <option value="text">text</option>
                <option value="json">json</option>
              </select>
            </td>
            <td v-if="subTab === 'scoring'" class="px-4 py-3">
              <input
                type="number"
                min="0"
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                v-model.number="mapping.max_value"
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'scoring'" class="px-4 py-3">
              <input
                type="number"
                min="0"
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                v-model.number="mapping.default_value"
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'scoring'" class="px-4 py-3">
              <input
                type="number"
                min="0"
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                v-model.number="mapping.passing_value"
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
              <input
                type="number"
                min="0"
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"
                v-model.number="mapping.sort_order"
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3 text-center">
              <q-checkbox
                v-model="mapping.active"
                :disabled="props.readOnly"
                dense
                color="primary"
              />
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-right">
              <div class="flex items-center justify-end gap-2">
                <q-btn
                  flat
                  round
                  dense
                  icon="save"
                  color="primary"
                  @click="saveMapping(mapping)"
                  :disabled="props.readOnly || saving"
                  title="Save changes"
                />
                <q-btn
                  flat
                  round
                  dense
                  icon="delete"
                  color="red"
                  @click="deleteMapping(mapping)"
                  :disabled="props.readOnly || saving"
                  title="Delete category"
                />
              </div>
            </td>
          </tr>

          <tr class="bg-gray-50/50 dark:bg-gray-900/30 divide-x divide-gray-100 dark:divide-gray-800">
            <td class="px-4 py-3">
              <input
                class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all font-bold"
                v-model="newMapping.label"
                placeholder="New Category Label..."
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
               <input
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                v-model="newMapping.icon"
                :disabled="props.readOnly"
                placeholder="Icon"
              />
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
              <select
                class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all font-bold"
                v-model="newMapping.grade_id"
                :disabled="props.readOnly"
              >
                <option :value="null">Global</option>
                <option v-for="g in options.grades" :key="g.id" :value="g.id">{{ g.name }}</option>
              </select>
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
              <select
                class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all font-bold"
                v-model="newMapping.subject_id"
                :disabled="props.readOnly"
              >
                <option :value="null">Global</option>
                <option v-for="s in options.subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
              <select
                class="w-full px-3 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all font-mono text-[11px]"
                v-model="newMapping.type"
                :disabled="props.readOnly"
              >
                <option value="numeric">numeric</option>
                <option value="text">text</option>
                <option value="json">json</option>
              </select>
            </td>
            <td v-if="subTab === 'scoring'" class="px-4 py-3">
              <input
                type="number"
                min="0"
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                v-model.number="newMapping.max_value"
                placeholder="Max"
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'scoring'" class="px-4 py-3">
              <input
                type="number"
                min="0"
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                v-model.number="newMapping.default_value"
                placeholder="Def"
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'scoring'" class="px-4 py-3">
              <input
                type="number"
                min="0"
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                v-model.number="newMapping.passing_value"
                placeholder="Pass"
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3">
              <input
                type="number"
                min="0"
                class="w-full px-2 py-2 text-sm border rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-center focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                v-model.number="newMapping.sort_order"
                placeholder="0"
                :disabled="props.readOnly"
              />
            </td>
            <td v-if="subTab === 'basic'" class="px-4 py-3 text-center">
              <q-checkbox
                v-model="newMapping.active"
                :disabled="props.readOnly"
                dense
                color="green"
              />
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-right">
              <q-btn
                unelevated
                label="Add Category"
                color="green"
                size="sm"
                class="font-bold rounded-lg px-4"
                @click="saveMapping(newMapping)"
                :disabled="props.readOnly || saving"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>
