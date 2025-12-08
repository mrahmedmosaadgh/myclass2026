<template>
  <div>
    <!-- School Selector -->
    <q-select
      v-model="selectedSchool"
      :options="schoolOptions"
      label="Select School"
      option-value="id"
      option-label="name"
      emit-value
      map-options
      class="q-mb-md"
      @update:model-value="saveSchoolToLocalStorage"
    />

    <q-card class="my-card">
      <q-card-section>
        <div class="text-h6">Import Classroom-Subject-Teacher</div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <input
          type="file"
          ref="fileInput"
          class="hidden"
          accept=".xlsx,.xls"
          @change="handleFileSelect"
          :disabled="!selectedSchool"
        />

        <q-btn
          @click="$refs.fileInput.click()"
          :loading="isUploading"
          label="Import Classroom Data"
          icon="upload"
          color="primary"
          class="q-mb-md"
        />

        <q-btn
          flat
          color="primary"
          label="Download Template"
          @click="downloadTemplate"
          class="q-ml-sm"
        />

        <q-tabs v-model="tab" class="text-teal q-mt-md" v-if="previewData.length">
          <q-tab name="preview" icon="visibility" label="Data Preview" />
          <q-tab name="raw" icon="code" label="Raw Data" />
        </q-tabs>

        <q-tab-panels v-model="tab" animated>
          <q-tab-panel name="preview">
            <q-table
              flat
              bordered
              :rows="previewData"
              :columns="tableColumns"
              row-key="id"
              :pagination="{ rowsPerPage: 10 }"
              :loading="loading"
            >
              <template v-slot:header-cell-status="props">
                <q-th auto-width class="text-center">Status</q-th>
              </template>

              <template v-slot:body-cell-status="props">
                <q-td auto-width class="text-center">
                  <q-icon
                    v-if="!Object.keys(props.row.errors || {}).length"
                    name="check_circle"
                    color="positive"
                    size="sm"
                  />
                  <q-icon
                    v-else
                    name="error"
                    color="negative"
                    size="sm"
                  />
                </q-td>
              </template>

              <template v-slot:body-cell="props">
                <q-td :props="props" :class="{ 'text-negative': props.row.errors?.[props.col.name] }">
                  {{ props.row.data[props.col.name] }}
                  <q-tooltip v-if="props.row.errors?.[props.col.name]" anchor="top middle" self="bottom middle">
                    {{ props.row.errors[props.col.name] }}
                  </q-tooltip>
                </q-td>
              </template>

              <template v-slot:bottom-row>
                <q-tr v-if="hasErrors">
                  <q-td colspan="100%" class="bg-red-1 text-negative text-center">
                    <q-icon name="warning" class="q-mr-xs" />
                    {{ errorSummary }}
                  </q-td>
                </q-tr>
              </template>
            </q-table>
          </q-tab-panel>

          <q-tab-panel name="raw">
            <q-editor
              v-model="rawData"
              readonly
              min-height="300px"
            />
          </q-tab-panel>
        </q-tab-panels>
      </q-card-section>

      <q-card-actions align="right" v-if="previewData.length">
        <q-btn flat label="Cancel" color="negative" @click="resetImport" />
        <q-btn
          label="Validate Data"
          color="warning"
          @click="validateData"
          :disable="!selectedSchool"
          class="q-mr-sm"
        />
        <q-btn
          label="Confirm Import"
          color="positive"
          @click="submitImport"
          :disable="hasErrors || !selectedSchool || !isValidated"
        />
      </q-card-actions>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { importFromExcel, downloadTemplate as downloadExcelTemplate } from '@/Utils/exportHelper';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

const $page = usePage();

const $q = useQuasar();
const fileInput = ref(null);
const tab = ref('preview');
const rawData = ref('');
const previewData = ref([]);
const isUploading = ref(false);
const loading = ref(false);
const selectedSchool = ref(null);
const schoolOptions = ref([]);
const isValidated = ref(false);

// Load school options and selected school from localStorage
onMounted(() => {
  // Load school options (replace with your actual data source)
  schoolOptions.value = $page.props.auth.user.school || [];

  // Load selected school from localStorage
  const savedSchool = localStorage.getItem('selectedSchoolId');
  if (savedSchool) {
    selectedSchool.value = parseInt(savedSchool);
  }
});

// Save selected school to localStorage
const saveSchoolToLocalStorage = (schoolId) => {
  localStorage.setItem('selectedSchoolId', schoolId);
};

const columns = [
  { key: 'status', label: 'status', required: false },
  { key: 'classroom', label: 'Classroom', required: true },
  { key: 'subject', label: 'Subject', required: true },
  { key: 'teacher', label: 'Teacher', required: true },
  { key: 'classes_per_week', label: 'Classes/Week', required: true, type: 'number' }
];

const tableColumns = [
//   {
//     name: 'status',
//     label: 'Status',
//     align: 'center',
//     sortable: true
//   },
  ...columns.map(col => ({
    name: col.key,
    label: col.label,
    field: row => row.data[col.key],
    align: 'left',
    sortable: true
  }))
];

const hasErrors = computed(() => {
  return previewData.value.some(row => Object.keys(row.errors || {}).length > 0);
});

const errorSummary = computed(() => {
  const errorCount = previewData.value.filter(
    row => Object.keys(row.errors || {}).length
  ).length;
  return `${errorCount} of ${previewData.value.length} rows contain errors`;
});

const validateRow = async (row) => {
  const errors = {};

  // Basic validation
  columns.forEach(col => {
    if (col.required && !row[col.key]) {
      errors[col.key] = `${col.label} is required`;
    }
    if (col.type === 'number' && isNaN(row[col.key])) {
      errors[col.key] = `Must be a valid number`;
    }
  });

  // Skip database checks if basic validation failed
  if (Object.keys(errors).length) {
    return { data: row, errors };
  }

  // Check database existence
  try {
    const { data: validation } = await axios.post(route('classroom-subject-teacher.validate'), {
      classroom: String(row.classroom), // Convert to string
      subject: row.subject,
      teacher: row.teacher
    });

    if (!validation.classroom_exists) {
      errors.classroom = 'Classroom not found';
    }
    if (!validation.subject_exists) {
      errors.subject = 'Subject not found';
    }
    if (!validation.teacher_exists) {
      errors.teacher = 'Teacher not found';
    }
  } catch (error) {
    console.error('Validation error:', error);
    errors._system = 'Validation service unavailable';
  }

  return { data: row, errors };
};

const handleFileSelect = async (event) => {
  try {
    isUploading.value = true;
    const file = event.target.files[0];
    const result = await importFromExcel(file);

    // Ensure we're working with an array of data
    const data = Array.isArray(result) ? result :
               (result.data ? result.data :
               Object.values(result));

    // Just store raw data without validation
    previewData.value = data.map(row => ({
      data: row,
      errors: {}
    }));

    rawData.value = JSON.stringify(data, null, 2);
    $q.notify({
      message: `${data.length} records loaded`,
      color: 'positive',
      icon: 'check_circle'
    });
  } catch (error) {
    $q.notify({
      message: 'Error importing file',
      color: 'negative',
      icon: 'error'
    });
    console.error('Import error:', error);
  } finally {
    isUploading.value = false;
    event.target.value = '';
  }
};

const downloadTemplate = () => {
  downloadExcelTemplate(columns, 'classroom-subject-teacher-template');
  $q.notify({
    message: 'Template downloaded',
    color: 'positive'
  });
};

const resetImport = () => {
  previewData.value = [];
  rawData.value = '';
};

const validateData = async () => {
  loading.value = true;

  try {
    // Always use the original import data for validation
    const validationData = previewData.value.map(item => ({
      classroom: item.data.classroom || item.original_data?.classroom,
      subject: item.data.subject || item.original_data?.subject,
      teacher: item.data.teacher || item.original_data?.teacher,
      classes_per_week: item.data.classes_per_week
    }));

    const response = await axios.post('/admin/classroom-subject-teachers/validate', {
      school_id: selectedSchool.value,
      data: validationData
    });

    if (response.data.success) {
      // Merge validation results while preserving original data
      previewData.value = response.data.validatedData.map((validatedItem, index) => ({
        ...previewData.value[index],
        ...validatedItem.data,
        errors: validatedItem.errors
      }));

      isValidated.value = true;
      $q.notify({
        type: response.data.hasErrors ? 'warning' : 'positive',
        message: response.data.message || 'Validation complete'
      });
    }
  } catch (error) {
    console.error('Validation error:', error);
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Validation failed!'
    });
  } finally {
    loading.value = false;
  }
};

const submitImport = () => {
  if (!isValidated.value) {
    $q.notify({
      type: 'warning',
      message: 'Please validate data first'
    });
    return;
  }

  loading.value = true;
  axios.post('/admin/classroom-subject-teachers/import', {
    data: previewData.value.filter(item => !Object.keys(item.errors || {}).length).map(item => item.data),
    school_id: selectedSchool.value
  })
  .then(response => {
    $q.notify({
      type: 'positive',
      message: response.data.message || 'Import successful!'
    });
    // router.visit('/admin/classroom-subject-teachers');
  })
  .catch(error => {
    console.error('Import error:', error);
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Import failed!'
    });
  })
  .finally(() => {
    loading.value = false;
  });
};
</script>
