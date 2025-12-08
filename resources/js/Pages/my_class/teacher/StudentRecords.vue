<template>
  <div class="student-records-page q-pa-md">
    <div class="page-header q-mb-lg">
      <div class="row items-center justify-between q-mb-md">
        <div class="col-12 col-md-6">
          <h1 class="text-h4 q-my-none">Student Records</h1>
          <p class="text-subtitle1 q-mt-sm">{{ periodActivity?.classroom?.name || 'Loading...' }}</p>
        </div>
        
        <div class="col-12 col-md-6 row justify-end q-gutter-sm">
          <q-btn color="primary" icon="refresh" label="Refresh" @click="loadStudentRecords" :loading="loading" />
          <q-btn color="secondary" icon="save" label="Save All" @click="saveAllRecords" :loading="saving" />
        </div>
      </div>
      
      <q-separator />
    </div>
    
    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="3em" />
      <span class="q-ml-sm text-subtitle1">Loading student records...</span>
    </div>
    
    <div v-else-if="!studentRecords.length" class="text-center q-pa-xl">
      <q-icon name="people" size="4em" color="grey-7" />
      <p class="text-h6 q-mt-md">No student records found</p>
      <q-btn color="primary" label="Load Records" @click="loadStudentRecords" />
    </div>
    
    <student-records-grid
      v-else
      :students="studentRecords"
      :loading="loading"
      @update:student="updateStudentRecord"
      @save-student="saveStudentRecord"
    />
    
    <!-- Success/Error notifications -->
    <q-dialog v-model="successDialog">
      <q-card>
        <q-card-section class="row items-center bg-positive text-white">
          <q-avatar icon="check_circle" text-color="white" />
          <span class="q-ml-sm text-h6">Success</span>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        
        <q-card-section>
          All student records have been saved successfully.
        </q-card-section>
        
        <q-card-actions align="right">
          <q-btn flat label="OK" color="positive" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';
import StudentRecordsGrid from './components/StudentRecordsGrid.vue';

const props = defineProps({
  periodActivityId: {
    type: [Number, String],
    required: true
  }
});

const $q = useQuasar();

// State
const loading = ref(false);
const saving = ref(false);
const successDialog = ref(false);
const periodActivity = ref(null);
const studentRecords = ref([]);
const updatedRecords = ref(new Set());

// Load data on mount
onMounted(() => {
  loadPeriodActivity();
  loadStudentRecords();
});

// Methods
async function loadPeriodActivity() {
  try {
    const response = await axios.get(`/api/period-activities/${props.periodActivityId}`);
    periodActivity.value = response.data;
  } catch (error) {
    console.error('Error loading period activity:', error);
    $q.notify({
      color: 'negative',
      message: `Failed to load period activity: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
  }
}

async function loadStudentRecords() {
  loading.value = true;
  
  try {
    const response = await axios.get(`/api/period-activities/${props.periodActivityId}/student-records`);
    
    if (response.data && Array.isArray(response.data)) {
      studentRecords.value = response.data;
      updatedRecords.value = new Set(); // Reset updated records
    } else {
      studentRecords.value = [];
      $q.notify({
        color: 'warning',
        message: 'No student records found',
        icon: 'warning'
      });
    }
  } catch (error) {
    console.error('Error loading student records:', error);
    $q.notify({
      color: 'negative',
      message: `Failed to load student records: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
  } finally {
    loading.value = false;
  }
}

function updateStudentRecord(student) {
  const index = studentRecords.value.findIndex(s => s.id === student.id);
  if (index !== -1) {
    studentRecords.value[index] = {...student};
    updatedRecords.value.add(student.id);
  }
}

async function saveStudentRecord(student) {
  try {
    // Use the new API endpoint
    await axios.put(`/api/period-activities/${props.periodActivityId}/student-records/${student.id}`, student);
    updatedRecords.value.delete(student.id);
    
    $q.notify({
      color: 'positive',
      message: `Record for ${student.name} saved successfully`,
      icon: 'check'
    });
  } catch (error) {
    console.error('Error saving student record:', error);
    $q.notify({
      color: 'negative',
      message: `Failed to save record: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
  }
}

async function saveAllRecords() {
  if (studentRecords.value.length === 0) {
    $q.notify({
      color: 'warning',
      message: 'No student records to save',
      icon: 'warning'
    });
    return;
  }
  
  saving.value = true;
  
  try {
    // Get only the records that have been updated
    const recordsToSave = studentRecords.value.filter(record => 
      updatedRecords.value.has(record.id)
    );
    
    if (recordsToSave.length === 0) {
      $q.notify({
        color: 'info',
        message: 'No changes to save',
        icon: 'info'
      });
      saving.value = false;
      return;
    }
    
    // Save all updated records in a batch
    await axios.put(`/api/period-activities/${props.periodActivityId}/student-records/batch`, {
      records: recordsToSave
    });
    
    // Clear the updated records set
    updatedRecords.value = new Set();
    
    // Show success dialog
    successDialog.value = true;
  } catch (error) {
    console.error('Error saving student records:', error);
    $q.notify({
      color: 'negative',
      message: `Failed to save records: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
  } finally {
    saving.value = false;
  }
}
</script>

<style>
.student-records-page .page-header {
  background-color: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-radius: 8px;
  padding: 16px;
}

.student-records-page .page-header h1 {
  font-weight: 500;
}

.student-records-page .q-card {
  transition: all 0.3s ease;
}

.student-records-page .q-card:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.body--dark .student-records-page .page-header {
  background-color: rgba(30, 30, 30, 0.8);
}
</style>




