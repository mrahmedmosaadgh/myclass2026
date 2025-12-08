<template>
  <AppLayout title="Project Tracker">
    <div class="q-pa-md">
      <q-card flat bordered>
        <q-card-section>
          <div class="row q-col-gutter-md items-center  ">
            <q-input v-model="filters.search" label="Search" class="col" dense />
            <q-select v-model="filters.status" :options="statusOptions" label="Status" class="col" dense clearable />
            <q-select v-model="filters.type" :options="typeOptions" label="Type" class="col" dense clearable />
            <q-select v-model="filters.priority" :options="priorityOptions" label="Priority" class="col" dense clearable />
            <div class="p-0 flex scale-75    ">

                <q-btn color="primary" label="Add Task" @click="openForm()" class="q-ml-sm    " />
                <q-btn color="secondary" label="Export as JSON" @click="exportJson" class="q-ml-sm" />
            </div>
            <!-- <q-btn color="secondary" label="Import JSON" @click="showImportDialog = true" class="q-ml-sm" /> -->
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <div v-if="$q.screen.gt.sm">
            <q-table
              :rows="filteredTasks"
              :columns="columns"
              row-key="id"
              flat
              dense
              :loading="loading"
            >
              <template #body-cell-actions="props">
                <q-btn size="sm" icon="edit" @click="openForm(props.row)" flat />
                <q-btn size="sm" icon="delete" color="negative" @click="deleteTask(props.row.id)" flat />
                <q-btn
                  size="sm"
                  icon="check_circle"
                  color="positive"
                  v-if="props.row.status !== 'done'"
                  @click="markDone(props.row)"
                  flat
                />
              </template>
            </q-table>
          </div>
          <div v-else>
            <div class="row q-col-gutter-md">
              <div v-for="task in filteredTasks" :key="task.id" class="col-12">
                <q-card class="q-mb-md">
                  <q-card-section>
                    <div class="text-h6">{{ task.title }}</div>
                    <div class="text-caption text-grey">{{ task.type }} | {{ task.priority || 'no priority' }} | {{ task.status }}</div>
                    <div v-if="task.description" class="q-mt-sm">{{ task.description }}</div>
                    <div v-if="task.notes" class="q-mt-xs text-grey-7">{{ task.notes }}</div>
                    <div v-if="task.path" class="q-mt-xs text-blue-7 text-caption">{{ task.path }}</div>
                    <div class="q-mt-xs text-caption">Updated: {{ task.updated_at }}</div>
                  </q-card-section>
                  <q-card-actions align="right">
                    <q-btn size="sm" icon="edit" @click="openForm(task)" flat />
                    <q-btn size="sm" icon="delete" color="negative" @click="deleteTask(task.id)" flat />
                    <q-btn
                      size="sm"
                      icon="check_circle"
                      color="positive"
                      v-if="task.status !== 'done'"
                      @click="markDone(task)"
                      flat
                    />
                  </q-card-actions>
                </q-card>
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="showForm">
      <q-card style="min-width:350px">
        <q-card-section>
          <div class="text-h6">{{ form.id ? 'Edit Task' : 'Add Task' }}</div>
        </q-card-section>
        <q-card-section>
          <q-input v-model="form.title" label="Title" dense />
          <q-input v-model="form.path" label="Path" dense />
          <q-input v-model="form.description" label="Description" dense />
          <q-select v-model="form.priority" :options="priorityOptions" label="Priority" dense />
          <q-select v-model="form.type" :options="typeOptions" label="Type" dense />
          <q-select v-model="form.status" :options="statusOptions" label="Status" dense />
          <q-input v-model="form.notes" label="Notes" type="textarea" dense />
          <q-btn color="secondary" label="Import JSON to Form" @click="showFormImportDialog = true" class="q-mt-md" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn color="primary" label="Save" @click="saveTask" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="showFormImportDialog">
      <q-card style="min-width:350px">
        <q-card-section>
          <div class="text-h6">Import JSON to Form</div>
        </q-card-section>
        <q-card-section>
          <q-input v-model="formImportJsonText" type="textarea" label="Paste JSON here" autogrow />
          <div v-if="formImportError" class="text-negative q-mt-sm">{{ formImportError }}</div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup @click="resetFormImportDialog" />
          <q-btn color="primary" label="Import" @click="importJsonToForm" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="showImportDialog">
      <q-card style="min-width:400px">
        <q-card-section>
          <div class="text-h6">Import Tasks from JSON</div>
        </q-card-section>
        <q-card-section>
          <q-input v-model="importJsonText" type="textarea" label="Paste JSON here" autogrow />
          <div v-if="importError" class="text-negative q-mt-sm">{{ importError }}</div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup @click="resetImportDialog" />
          <q-btn color="primary" label="Import" @click="importJson" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useQuasar } from 'quasar';
const $q = useQuasar();

const tasks = ref([]);
const loading = ref(false);
const showForm = ref(false);
const form = ref({
  id: null,
  title: '',
  path: '',
  description: '',
  priority: '',
  type: '',
  status: '',
  notes: ''
});

const filters = ref({
  search: '',
  status: null,
  type: null,
  priority: null
});

const statusOptions = ['todo', 'in_progress', 'done'];
const typeOptions = ['UI', 'Backend', 'Feature'];
const priorityOptions = ['low', 'medium', 'high'];

const columns = [
  { name: 'title', label: 'Title', field: 'title', align: 'left' },
  { name: 'type', label: 'Type', field: 'type', align: 'left' },
  { name: 'priority', label: 'Priority', field: 'priority', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'left' },
  { name: 'updated_at', label: 'Updated', field: 'updated_at', align: 'left' },
  { name: 'actions', label: 'Actions', field: 'actions', align: 'right' }
];

const fetchTasks = async () => {
  loading.value = true;
  const { data } = await axios.get('/api/project-tasks');
  tasks.value = data;
  loading.value = false;
};

const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    const matchesSearch =
      !filters.value.search ||
      task.title.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      (task.description && task.description.toLowerCase().includes(filters.value.search.toLowerCase()));
    const matchesStatus = !filters.value.status || task.status === filters.value.status;
    const matchesType = !filters.value.type || task.type === filters.value.type;
    const matchesPriority = !filters.value.priority || task.priority === filters.value.priority;
    return matchesSearch && matchesStatus && matchesType && matchesPriority;
  });
});

function openForm(task = null) {
  if (task) {
    form.value = { ...task };
  } else {
    form.value = {
      id: null,
      title: '',
      path: '',
      description: '',
      priority: '',
      type: '',
      status: '',
      notes: ''
    };
  }
  showForm.value = true;
}

async function saveTask() {
  if (form.value.id) {
    await axios.put(`/api/project-tasks/${form.value.id}`, form.value);
  } else {
    await axios.post('/api/project-tasks', form.value);
  }
  showForm.value = false;
  fetchTasks();
}

async function deleteTask(id) {
  await axios.delete(`/api/project-tasks/${id}`);
  fetchTasks();
}

async function markDone(task) {
  await axios.put(`/api/project-tasks/${task.id}`, { ...task, status: 'done' });
  fetchTasks();
}

const showImportDialog = ref(false);
const importJsonText = ref('');
const importError = ref('');

const showFormImportDialog = ref(false);
const formImportJsonText = ref('');
const formImportError = ref('');

function exportJson() {
  const json = JSON.stringify(tasks.value, null, 2);
  if (navigator.clipboard) {
    navigator.clipboard.writeText(json).then(() => {
      $q.notify({ type: 'positive', message: 'Tasks copied as JSON to clipboard.' });
    }, () => {
      $q.notify({ type: 'negative', message: 'Clipboard write failed.' });
    });
  } else {
    // Fallback: show JSON in prompt
    window.prompt('Copy JSON:', json);
  }
}

function resetImportDialog() {
  importJsonText.value = '';
  importError.value = '';
}

function importJson() {
  importError.value = '';
  let parsed;
  try {
    parsed = JSON.parse(importJsonText.value);
  } catch (e) {
    importError.value = 'Invalid JSON.';
    return;
  }
  if (!Array.isArray(parsed)) {
    importError.value = 'JSON must be an array of tasks.';
    return;
  }
  // Validate each task
  for (const task of parsed) {
    if (!task.title || !task.type || !task.status) {
      importError.value = 'Each task must have title, type, and status.';
      return;
    }
  }
  tasks.value = parsed;
  showImportDialog.value = false;
  resetImportDialog();
  $q.notify({ type: 'positive', message: 'Tasks imported from JSON.' });
}

function resetFormImportDialog() {
  formImportJsonText.value = '';
  formImportError.value = '';
}

function importJsonToForm() {
  formImportError.value = '';
  let parsed;
  try {
    parsed = JSON.parse(formImportJsonText.value);
  } catch (e) {
    formImportError.value = 'Invalid JSON.';
    return;
  }
  if (typeof parsed !== 'object' || Array.isArray(parsed) || !parsed) {
    formImportError.value = 'JSON must be a single task object.';
    return;
  }
  // Only fill empty fields
  for (const key of Object.keys(form.value)) {
    if ((form.value[key] === '' || form.value[key] === null) && parsed[key]) {
      form.value[key] = parsed[key];
    }
  }
  showFormImportDialog.value = false;
  resetFormImportDialog();
  $q.notify({ type: 'positive', message: 'Form fields filled from JSON.' });
}

onMounted(fetchTasks);
</script>

<style scoped>
@media (max-width: 600px) {
  .q-table {
    display: none;
  }
}
</style>
