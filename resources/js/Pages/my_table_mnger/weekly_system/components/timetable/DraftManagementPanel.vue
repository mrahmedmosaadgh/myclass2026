<template>
  <q-dialog v-model="internalModel" full-width>
    <q-card class="column" style="min-height: 500px; max-height: 80vh">
      <q-card-section class="bg-primary text-white row items-center">
        <div class="text-h6">Draft Management</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pa-md col scroll">
        <!-- Search & Filter -->
        <div class="row q-gutter-md q-mb-md">
          <div class="col-12 col-md-4">
            <q-input dense outlined v-model="search" label="Search drafts" class="bg-white">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
          <q-space />
          <div class="col-auto">
            <q-btn color="primary" label="Refresh List" icon="refresh" flat @click="loadDrafts" :loading="loading" />
          </div>
        </div>

        <!-- Drafts Table -->
        <q-table
          :rows="filteredDrafts"
          :columns="columns"
          row-key="name"
          :loading="loading"
          flat
          bordered
          :pagination="{ rowsPerPage: 0 }"
        >
          <template v-slot:body-cell-name="props">
            <q-td :props="props">
              <div class="text-weight-bold">{{ props.value }}</div>
              <div class="text-caption text-grey">{{ props.row.description }}</div>
              <q-chip v-if="props.row.is_backup" size="xs" color="grey-3" text-color="grey-8" icon="restore">Auto-Backup</q-chip>
            </q-td>
          </template>

          <template v-slot:body-cell-created="props">
            <q-td :props="props">
              <div>{{ formatDate(props.row.timestamp) }}</div>
              <div class="text-caption text-grey">by {{ props.row.created_by_name || 'System' }}</div>
            </q-td>
          </template>

          <template v-slot:body-cell-actions="props">
            <q-td :props="props" class="text-right">
              <q-btn-group flat>
                <q-btn 
                  flat dense color="primary" icon="compare_arrows" 
                  @click="$emit('compare', props.row)"
                >
                  <q-tooltip>Compare with Live</q-tooltip>
                </q-btn>
                <q-btn 
                  flat dense color="secondary" icon="restore" 
                  @click="confirmLoad(props.row)"
                >
                  <q-tooltip>Load/Restore</q-tooltip>
                </q-btn>
                <q-btn 
                  flat dense color="negative" icon="delete" 
                  @click="confirmDelete(props.row)"
                >
                  <q-tooltip>Delete</q-tooltip>
                </q-btn>
              </q-btn-group>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-dialog>

  <!-- Confirm Load Dialog -->
  <q-dialog v-model="showLoadConfirm">
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="warning" color="warning" text-color="white" />
        <span class="q-ml-sm text-h6">Load Draft: {{ selectedDraft?.name }}</span>
      </q-card-section>

      <q-card-section>
        <p>This action will replace the current live schedule with this draft.</p>
        <p class="text-grey-7">Current schedule will be automatically backed up as <strong>AUTO_BACKUP_{{ new Date().toISOString().slice(0,10) }}...</strong></p>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="primary" v-close-popup />
        <q-btn flat label="Proceed & Load" color="negative" @click="executeLoad" />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <!-- Confirm Delete Dialog -->
  <q-dialog v-model="showDeleteConfirm">
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="delete" color="negative" text-color="white" />
        <span class="q-ml-sm text-h6">Delete Draft: {{ selectedDraft?.name }}</span>
      </q-card-section>

      <q-card-section>
        Are you sure you want to delete this draft? This action cannot be undone.
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="primary" v-close-popup />
        <q-btn flat label="Delete" color="negative" @click="executeDelete" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { date, useQuasar } from 'quasar';
import axios from 'axios';

const props = defineProps({
  modelValue: Boolean
});

const emit = defineEmits(['update:modelValue', 'compare', 'load']);
const $q = useQuasar();

const internalModel = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const drafts = ref([]);
const loading = ref(false);
const search = ref('');
const showLoadConfirm = ref(false);
const showDeleteConfirm = ref(false);
const selectedDraft = ref(null);

const columns = [
  { name: 'name', label: 'Draft Name', align: 'left', field: 'name', sortable: true },
  { name: 'created', label: 'Created', align: 'left', field: 'timestamp', sortable: true },
  { name: 'stats', label: 'Assignments', align: 'center', field: row => row.total_assignments, sortable: true },
  { name: 'actions', label: 'Actions', align: 'right' }
];

const filteredDrafts = computed(() => {
  if (!search.value) return drafts.value;
  const term = search.value.toLowerCase();
  return drafts.value.filter(d => 
    d.name.toLowerCase().includes(term) || 
    (d.description && d.description.toLowerCase().includes(term)) ||
    (d.created_by_name && d.created_by_name.toLowerCase().includes(term))
  );
});

async function loadDrafts() {
  loading.value = true;
  try {
    const response = await axios.get(route('weekly-system.api.drafts.index'));
    if (response.data.success) {
      // Sort by date desc by default
      drafts.value = response.data.drafts.sort((a, b) => 
        new Date(b.timestamp) - new Date(a.timestamp)
      );
    }
  } catch (error) {
    console.error('Failed to load drafts', error);
    $q.notify({ type: 'negative', message: 'Failed to load drafts list' });
  } finally {
    loading.value = false;
  }
}

function formatDate(timestamp) {
  if (!timestamp) return '-';
  return date.formatDate(timestamp, 'MMM D, YYYY HH:mm');
}

function confirmLoad(draft) {
  selectedDraft.value = draft;
  showLoadConfirm.value = true;
}

function executeLoad() {
  if (!selectedDraft.value) return;
  emit('load', selectedDraft.value);
  showLoadConfirm.value = false;
}

function confirmDelete(draft) {
  selectedDraft.value = draft;
  showDeleteConfirm.value = true;
}

async function executeDelete() {
  if (!selectedDraft.value) return;
  
  try {
    const response = await axios.post(route('weekly-system.api.drafts.delete'), {
      name: selectedDraft.value.name
    });
    
    if (response.data.success) {
      $q.notify({ type: 'positive', message: 'Draft deleted' });
      loadDrafts(); // Refresh list
    }
  } catch (error) {
    console.error('Failed to delete draft', error);
    $q.notify({ type: 'negative', message: 'Failed to delete draft' });
  } finally {
    showDeleteConfirm.value = false;
    selectedDraft.value = null;
  }
}

// Initial load when component is mounted
onMounted(() => {
  loadDrafts();
});

// Reload when dialog opens
</script>
