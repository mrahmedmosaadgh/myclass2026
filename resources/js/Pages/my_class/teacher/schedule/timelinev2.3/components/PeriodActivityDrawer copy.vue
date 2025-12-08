<template>
  <q-dialog
    v-model="isOpen"
    persistent
    maximized
    transition-show="slide-up"
    transition-hide="slide-down"
    class="period-activity-dialog"
  >
    <q-card class="full-height">
      <q-card-section class="bg-primary text-white">
        <div class="row items-center">
          <div class="col">
            <div class="text-h6">{{ periodData?.subject }} - {{ periodData?.classroom }}</div>
            <div class="text-subtitle2">{{ calendarDate }} | {{ periodData?.from }} - {{ periodData?.to }}</div>
          </div>
          <div class="col-auto">
            <q-btn flat round dense icon="close" @click="isOpen = false" />
          </div>
        </div>
      </q-card-section>

      <q-card-section class="q-pa-none">
        <q-scroll-area style="height: calc(100vh - 70px);">
          <!-- Loading Spinner -->
          <div v-if="loading" class="q-pa-lg flex flex-center">
            <q-spinner color="primary" size="3em" />
          </div>

          <!-- Content when loaded -->
          <div v-else>
            <!-- Create Period Activity Button (if not exists) -->
            <div v-if="!periodActivity" class="q-pa-md">
              <q-card flat bordered>
                <q-card-section class="text-center">
                  <p class="text-h6">No period activity record exists for this period</p>
                  <q-btn
                    color="primary"
                    label="Create Period Activity"
                    @click="createPeriodActivity"
                    :loading="saving"
                  />
                </q-card-section>
              </q-card>
            </div>

            <!-- Period Activity Form (if exists) -->
            <div v-else>
              <!-- Teacher Section -->
              <q-card flat bordered class="q-ma-md">
                <q-card-section>
                  <div class="text-h6">Teacher Information</div>
                  <q-checkbox v-model="periodActivity.teacher_present" label="Teacher Present" />

                  <q-select
                    v-if="!periodActivity.teacher_present"
                    v-model="periodActivity.teacher_substitute_id"
                    :options="teacherOptions"
                    label="Substitute Teacher"
                    outlined
                    class="q-mt-sm"
                  />
                </q-card-section>
              </q-card>

              <!-- Period Status -->
              <q-card flat bordered class="q-ma-md">
                <q-card-section>
                  <div class="text-h6">Period Status</div>
                  <q-option-group
                    v-model="periodActivity.period_status"
                    :options="statusOptions"
                    color="primary"
                  />
                </q-card-section>
              </q-card>

              <!-- Notes -->
              <q-card flat bordered class="q-ma-md">
                <q-card-section>
                  <div class="text-h6">Notes</div>
                  <q-input
                    v-model="periodActivity.lesson_notes"
                    type="textarea"
                    label="Lesson Notes"
                    outlined
                    class="q-mb-md"
                  />
                  <q-input
                    v-model="periodActivity.improvement_notes"
                    type="textarea"
                    label="Improvement Notes"
                    outlined
                  />
                </q-card-section>
              </q-card>

              <!-- Student Records -->
              <q-card flat bordered class="q-ma-md">
                <q-card-section>
                  <div class="text-h6">Student Records</div>

                  <div v-if="loadingStudents" class="q-pa-md flex flex-center">
                    <q-spinner color="primary" size="2em" />
                  </div>

                  <div v-else-if="!studentRecords.length" class="text-center q-pa-md">
                    <p>No student records found for this class</p>
                    <q-btn color="primary" label="Load Student Records" @click="loadStudentRecords" />
                  </div>

                  <div v-else>
                    <q-tabs
                      v-model="studentTab"
                      dense
                      class="text-grey"
                      active-color="primary"
                      indicator-color="primary"
                      align="justify"
                      narrow-indicator
                    >
                      <q-tab name="attendance" label="Attendance" />
                      <q-tab name="homework" label="Homework" />
                      <q-tab name="behavior" label="Behavior" />
                      <q-tab name="participation" label="Participation" />
                    </q-tabs>

                    <q-separator />

                    <q-tab-panels v-model="studentTab" animated>
                      <q-tab-panel name="attendance">
                        <div v-for="student in studentRecords" :key="student.id" class="q-mb-md">
                          <div class="row items-center q-gutter-md">
                            <div class="col-3">{{ student.name }}</div>
                            <div class="col">
                              <q-option-group
                                v-model="student.attendance_status"
                                :options="attendanceOptions"
                                color="primary"
                                inline
                              />
                            </div>
                            <div class="col-2" v-if="student.attendance_status === 'late'">
                              <q-input
                                v-model.number="student.late_minutes"
                                type="number"
                                label="Minutes"
                                dense
                                outlined
                              />
                            </div>
                          </div>
                        </div>
                      </q-tab-panel>

                      <q-tab-panel name="homework">
                        <div v-for="student in studentRecords" :key="student.id" class="q-mb-md">
                          <div class="row items-center q-gutter-md">
                            <div class="col-3">{{ student.name }}</div>
                            <div class="col-3">
                              <q-checkbox v-model="student.homework_completed" label="Completed" />
                            </div>
                            <div class="col-3">
                              <q-input
                                v-model.number="student.homework_score"
                                type="number"
                                label="Score"
                                dense
                                outlined
                              />
                            </div>
                          </div>
                        </div>
                      </q-tab-panel>

                      <q-tab-panel name="behavior">
                        <div v-for="student in studentRecords" :key="student.id" class="q-mb-md">
                          <div class="row items-center q-gutter-md">
                            <div class="col-3">{{ student.name }}</div>
                            <div class="col-2">
                              <q-input
                                v-model.number="student.behavior_plus_marks"
                                type="number"
                                label="Plus"
                                dense
                                outlined
                              />
                            </div>
                            <div class="col-2">
                              <q-input
                                v-model.number="student.behavior_minus_marks"
                                type="number"
                                label="Minus"
                                dense
                                outlined
                              />
                            </div>
                            <div class="col-5">
                              <q-input
                                v-model="student.behavior_notes"
                                type="textarea"
                                label="Notes"
                                dense
                                outlined
                              />
                            </div>
                          </div>
                        </div>
                      </q-tab-panel>

                      <q-tab-panel name="participation">
                        <div v-for="student in studentRecords" :key="student.id" class="q-mb-md">
                          <div class="row items-center q-gutter-md">
                            <div class="col-3">{{ student.name }}</div>
                            <div class="col-2">
                              <q-input
                                v-model.number="student.participation_score"
                                type="number"
                                label="Score"
                                dense
                                outlined
                              />
                            </div>
                            <div class="col-7">
                              <q-input
                                v-model="student.participation_notes"
                                type="textarea"
                                label="Notes"
                                dense
                                outlined
                              />
                            </div>
                          </div>
                        </div>
                      </q-tab-panel>
                    </q-tab-panels>
                  </div>
                </q-card-section>
              </q-card>

              <!-- Save Button -->
              <div class="q-pa-md">
                <q-btn
                  color="primary"
                  label="Save"
                  @click="savePeriodActivity"
                  class="full-width"
                  :loading="saving"
                />
              </div>
            </div>
          </div>
        </q-scroll-area>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Configure axios to include CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
axios.defaults.withCredentials = true;

const $q = useQuasar();
const props = defineProps({
  modelValue: Boolean,
  periodData: Object,
  calendarDate: String
});

const emit = defineEmits(['update:modelValue']);

// Drawer state
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => {
    emit('update:modelValue', value);
  }
});

// Data
const loading = ref(false);
const saving = ref(false);
const loadingStudents = ref(false);
const periodActivity = ref(null);
const students = ref([]);
const studentRecords = ref([]);
const studentTab = ref('attendance');

// Options
const statusOptions = [
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' },
  { label: 'Modified', value: 'modified' },
  { label: 'Event Affected', value: 'event_affected' }
];

const attendanceOptions = [
  { label: 'Present', value: 'present' },
  { label: 'Absent', value: 'absent' },
  { label: 'Late', value: 'late' },
  { label: 'Excused', value: 'excused' }
];

const teacherOptions = ref([]);

// Load period activity data - simplified to use the updated controller endpoint
const loadPeriodActivity = async () => {
  if (!props.periodData || !props.calendarDate) return;

  loading.value = true;

  try {
    console.log('Loading period activity for:', {
      schedule_id: props.periodData.schedule_id,
      calendar_date: props.calendarDate,
      classroom_id: props.periodData.classroom_id
    });

    // Use the updated controller endpoint that handles getOrCreatePeriodActivity
    const response = await axios.get('/api/period-activities', {
      params: {
        schedule_id: props.periodData.schedule_id,
        calendar_date: props.calendarDate,
        classroom_id: props.periodData.classroom_id,
        teacher_id: usePage().props.auth.user?.teacher?.id
      }
    });

    console.log('Period activity response:', response.data);

    // The controller now returns the period activity and student records together
    if (response.data && response.data.records) {
      // Check if records is an array with data property or just an array
      if (Array.isArray(response.data.records.data) && response.data.records.data.length > 0) {
        periodActivity.value = response.data.records.data[0];
      } else if (Array.isArray(response.data.records) && response.data.records.length > 0) {
        periodActivity.value = response.data.records[0];
      } else {
        periodActivity.value = null;
      }
      
      // If student records are included in the response
      if (response.data.student_records) {
        studentRecords.value = response.data.student_records;
      } else {
        // If not, we'll need to load them separately
        await loadStudentRecords();
      }
    } else if (response.data && response.data.period_activity) {
      // Handle the case where period_activity is directly in the response
      periodActivity.value = response.data.period_activity;
      
      if (response.data.student_records) {
        studentRecords.value = response.data.student_records;
      } else {
        await loadStudentRecords();
      }
    } else {
      periodActivity.value = null;
      studentRecords.value = [];
    }
  } catch (error) {
    console.error('Error loading period activity:', error);
    console.error('Error details:', error.response?.data || error.message);

    $q.notify({
      color: 'negative',
      message: `Failed to load period activity data: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
    periodActivity.value = null;
  } finally {
    loading.value = false;
  }
};

// Load student records for a period activity
const loadStudentRecords = async () => {
  if (!periodActivity.value?.id) {
    console.log('No period activity ID available, cannot load student records');
    return;
  }

  loadingStudents.value = true;

  try {
    console.log('Loading student records for period activity:', periodActivity.value.id);

    const response = await axios.get(`/api/period-activities/${periodActivity.value.id}/student-records`);

    console.log('Student records response:', response.data);

    if (response.data && Array.isArray(response.data) && response.data.length > 0) {
      studentRecords.value = response.data;
      students.value = response.data.map(record => ({
        id: record.student_id,
        name: record.name
      }));
    } else {
      console.log('No student records found');
      studentRecords.value = [];
      // Don't clear students.value here, as we might have loaded them separately
    }
  } catch (error) {
    console.error('Error loading student records:', error);
    console.error('Error details:', error.response?.data || error.message);

    $q.notify({
      color: 'negative',
      message: `Failed to load student records: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
  } finally {
    loadingStudents.value = false;
  }
};

// Save period activity and student records
const savePeriodActivity = async () => {
  if (!periodActivity.value?.id) {
    console.error('No period activity ID available');
    return;
  }

  saving.value = true;
  
  try {
    console.log('Saving period activity:', periodActivity.value);

    // Save period activity
    const activityResponse = await axios.put(`/api/period-activities/${periodActivity.value.id}`, {
      ...periodActivity.value,
      // Don't send updated_by, let the server handle it
      updated_by: undefined
    });

    console.log('Period activity saved:', activityResponse.data);
    periodActivity.value = activityResponse.data;

    // Save student records
    console.log('Saving student records:', studentRecords.value);

    const recordsResponse = await axios.put(`/api/period-activities/${periodActivity.value.id}/student-records/batch`, {
      records: studentRecords.value
    });

    console.log('Student records saved:', recordsResponse.data);

    $q.notify({
      color: 'positive',
      message: 'Period activity saved successfully',
      icon: 'check'
    });
  } catch (error) {
    console.error('Error saving period activity:', error);
    console.error('Error details:', error.response?.data || error.message);

    $q.notify({
      color: 'negative',
      message: `Failed to save period activity: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
  } finally {
    saving.value = false;
  }
};

// Load students for the classroom
const loadStudents = async () => {
  if (!props.periodData?.classroom_id) {
    console.error('No classroom ID available');
    return;
  }

  loadingStudents.value = true;

  try {
    console.log('Loading students for classroom:', props.periodData.classroom_id);

    const response = await axios.get(`/api/classrooms/${props.periodData.classroom_id}/students`);

    console.log('Students response:', response.data);

    if (response.data && Array.isArray(response.data)) {
      students.value = response.data;
      
      // If we have a period activity, initialize student records
      if (periodActivity.value?.id) {
        // Check if we already have student records
        if (studentRecords.value.length === 0) {
          // Create empty student records for each student
          studentRecords.value = students.value.map(student => ({
            id: null,
            period_activity_id: periodActivity.value.id,
            student_id: student.id,
            name: student.name,
            attendance_status: 'present',
            late_minutes: 0,
            homework_completed: false,
            homework_score: null,
            behavior_plus_marks: 0,
            behavior_minus_marks: 0,
            behavior_notes: '',
            participation_score: null,
            participation_notes: ''
          }));
        }
      }
    } else {
      console.log('No students found or invalid response format');
      students.value = [];
    }
  } catch (error) {
    console.error('Error loading students:', error);
    console.error('Error details:', error.response?.data || error.message);

    $q.notify({
      color: 'negative',
      message: `Failed to load students: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
    students.value = [];
  } finally {
    loadingStudents.value = false;
  }
};

// Create a new period activity
const createPeriodActivity = async () => {
  if (!props.periodData) {
    console.error('No period data available');
    return;
  }

  saving.value = true;

  try {
    console.log('Creating period activity for:', props.periodData);

    const response = await axios.post('/api/period-activities', {
      schedule_id: props.periodData.schedule_id,
      calendar_date: props.calendarDate,
      teacher_id: usePage().props.auth.user?.teacher?.id,
      classroom_id: props.periodData.classroom_id
    });

    console.log('Period activity created:', response.data);

    if (response.data && response.data.period_activity) {
      periodActivity.value = response.data.period_activity;
      
      if (response.data.student_records) {
        studentRecords.value = response.data.student_records;
      } else {
        // If student records weren't created automatically, load students
        await loadStudents();
      }

      $q.notify({
        color: 'positive',
        message: 'Period activity created successfully',
        icon: 'check'
      });
    }
  } catch (error) {
    console.error('Error creating period activity:', error);
    console.error('Error details:', error.response?.data || error.message);

    $q.notify({
      color: 'negative',
      message: `Failed to create period activity: ${error.response?.data?.message || error.message}`,
      icon: 'error'
    });
  } finally {
    saving.value = false;
  }
};

// Watch for changes in period data to load activity
watch(() => [props.periodData, props.calendarDate, isOpen.value], ([newPeriodData, newDate, newIsOpen]) => {
  if (newPeriodData && newDate && newIsOpen) {
    loadPeriodActivity();
  }
}, { immediate: true });

// Watch for changes to periodActivity
watch(periodActivity, (newValue) => {
  if (newValue?.id) {
    // If we have a period activity but no student records, load them
    if (studentRecords.value.length === 0) {
      loadStudentRecords();
    }
  }
});

// Watch for dialog open/close
watch(isOpen, (newValue) => {
  if (newValue) {
    // Reset data when dialog opens
    error.value = null;
    periodActivity.value = null;
    studentRecords.value = [];
    students.value = [];
    
    // Load period activity data
    loadPeriodActivity();
  }
});
</script>

<style scoped>
.period-activity-dialog {
  z-index: 3000;
}

.q-dialog__inner > div {
  max-width: 100%;
}
</style>








