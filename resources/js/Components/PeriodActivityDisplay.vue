<template>
  <div class="period-activity-container q-pa-md">
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="rocket-loader">
        <div class="rocket">🚀</div>
        <div class="stars">
          <div class="star" v-for="n in 10" :key="n">✨</div>
        </div>
      </div>
      <p class="loading-text">Loading your classroom adventure...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">😕</div>
      <h3 class="error-title">Oops! Something went wrong</h3>
      <p class="error-message">{{ error }}</p>
      <q-btn color="primary" label="Try Again" @click="fetchPeriodActivity" :loading="loading" />
    </div>

    <!-- No Period Activity State -->
    <div v-else-if="!periodActivity" class="no-data-container">
      <div class="no-data-icon">📚</div>
      <h3 class="no-data-title">No Class Data Yet</h3>
      <p class="no-data-message">Would you like to create this class period?</p>
      <q-btn 
        color="primary" 
        label="Create Class Period" 
        @click="createPeriodActivity" 
        :loading="saving"
        class="q-mt-md"
      />
    </div>

    <!-- Success State -->
    <div v-else class="success-container">
      <!-- Period Activity Info Card -->
      <q-card class="activity-card q-mb-md">
        <q-card-section :class="['activity-header', periodActivity.period_status]">
          <div class="row items-center">
            <div class="col">
              <div class="activity-title">
                <span class="emoji">{{ statusEmoji }}</span>
                {{ formatDate(periodActivity.calendar?.date) }} Class
              </div>
            </div>
            <div class="col-auto">
              <q-badge :color="statusColor" class="activity-badge">
                {{ periodActivity.period_status }}
              </q-badge>
            </div>
          </div>
        </q-card-section>
        
        <q-card-section>
          <div class="row q-mb-sm">
            <div class="col-4 info-label">Teacher:</div>
            <div class="col info-value">{{ periodActivity.teacher?.name || 'Unknown' }}</div>
          </div>
          
          <div class="row q-mb-sm">
            <div class="col-4 info-label">Time:</div>
            <div class="col info-value">{{ formatTime(periodActivity.schedule?.start_time) }} - {{ formatTime(periodActivity.schedule?.end_time) }}</div>
          </div>
          
          <div v-if="periodActivity.lesson_notes" class="row q-mb-sm">
            <div class="col-4 info-label">Today's Lesson:</div>
            <div class="col">
              <q-card flat bordered class="notes-box">
                <q-card-section>
                  {{ periodActivity.lesson_notes }}
                </q-card-section>
              </q-card>
            </div>
          </div>
          
          <!-- Edit button for teachers -->
          <div v-if="isTeacher" class="row q-mt-md">
            <div class="col-12 text-right">
              <q-btn 
                color="primary" 
                :icon="editMode ? 'visibility' : 'edit'" 
                :label="editMode ? 'View Mode' : 'Edit Mode'" 
                @click="editMode = !editMode" 
                flat
              />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Edit Mode for Teachers -->
      <div v-if="isTeacher && editMode">
        <q-card class="q-mb-md">
          <q-card-section>
            <div class="text-h6">Class Details</div>
          </q-card-section>
          
          <q-card-section>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-select
                  v-model="periodActivity.period_status"
                  :options="statusOptions"
                  label="Period Status"
                  outlined
                  emit-value
                  map-options
                />
              </div>
              
              <div class="col-12 col-md-6">
                <q-checkbox v-model="periodActivity.teacher_present" label="Teacher Present" />
              </div>
              
              <div class="col-12">
                <q-input
                  v-model="periodActivity.lesson_notes"
                  type="textarea"
                  label="Lesson Notes"
                  outlined
                  autogrow
                />
              </div>
              
              <div class="col-12">
                <q-input
                  v-model="periodActivity.improvement_notes"
                  type="textarea"
                  label="Improvement Notes"
                  outlined
                  autogrow
                />
              </div>
            </div>
          </q-card-section>
        </q-card>
        
        <!-- Student Records Editor -->
        <q-card class="q-mb-md">
          <q-card-section>
            <div class="text-h6">Student Records</div>
          </q-card-section>
          
          <q-tabs
            v-model="activeTab"
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

          <q-tab-panels v-model="activeTab" animated>
            <q-tab-panel name="attendance">
              <div v-for="(record, index) in studentRecords" :key="record.id || index" class="q-mb-sm">
                <div class="row items-center q-col-gutter-sm">
                  <div class="col-12 col-sm-4">
                    <strong>{{ record.student?.name }}</strong>
                  </div>
                  <div class="col-12 col-sm-4">
                    <q-select
                      v-model="record.attendance_status"
                      :options="attendanceOptions"
                      dense
                      outlined
                      emit-value
                      map-options
                    />
                  </div>
                  <div class="col-12 col-sm-4" v-if="record.attendance_status === 'late'">
                    <q-input
                      v-model.number="record.late_minutes"
                      type="number"
                      label="Minutes Late"
                      dense
                      outlined
                    />
                  </div>
                </div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="homework">
              <div v-for="(record, index) in studentRecords" :key="record.id || index" class="q-mb-sm">
                <div class="row items-center q-col-gutter-sm">
                  <div class="col-12 col-sm-4">
                    <strong>{{ record.student?.name }}</strong>
                  </div>
                  <div class="col-12 col-sm-4">
                    <q-checkbox v-model="record.homework_completed" label="Completed" />
                  </div>
                  <div class="col-12 col-sm-4">
                    <q-input
                      v-model.number="record.homework_score"
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
              <div v-for="(record, index) in studentRecords" :key="record.id || index" class="q-mb-sm">
                <div class="row items-center q-col-gutter-sm">
                  <div class="col-12 col-sm-3">
                    <strong>{{ record.student?.name }}</strong>
                  </div>
                  <div class="col-12 col-sm-2">
                    <q-input
                      v-model.number="record.behavior_plus_marks"
                      type="number"
                      label="Plus"
                      dense
                      outlined
                    />
                  </div>
                  <div class="col-12 col-sm-2">
                    <q-input
                      v-model.number="record.behavior_minus_marks"
                      type="number"
                      label="Minus"
                      dense
                      outlined
                    />
                  </div>
                  <div class="col-12 col-sm-5">
                    <q-input
                      v-model="record.behavior_notes"
                      label="Notes"
                      dense
                      outlined
                    />
                  </div>
                </div>
              </div>
            </q-tab-panel>

            <q-tab-panel name="participation">
              <div v-for="(record, index) in studentRecords" :key="record.id || index" class="q-mb-sm">
                <div class="row items-center q-col-gutter-sm">
                  <div class="col-12 col-sm-3">
                    <strong>{{ record.student?.name }}</strong>
                  </div>
                  <div class="col-12 col-sm-3">
                    <q-input
                      v-model.number="record.participation_score"
                      type="number"
                      label="Score"
                      dense
                      outlined
                    />
                  </div>
                  <div class="col-12 col-sm-6">
                    <q-input
                      v-model="record.participation_notes"
                      label="Notes"
                      dense
                      outlined
                    />
                  </div>
                </div>
              </div>
            </q-tab-panel>
          </q-tab-panels>
          
          <q-card-actions align="right">
            <q-btn 
              color="primary" 
              label="Save Changes" 
              @click="savePeriodActivity" 
              :loading="saving"
            />
          </q-card-actions>
        </q-card>
      </div>

      <!-- Student Records Display (View Mode) -->
      <div v-else-if="studentRecords.length" class="students-section">
        <div class="section-title q-mb-md">
          <span class="emoji">👨‍👩‍👧‍👦</span> 
          <span class="q-ml-sm">Classmates</span>
        </div>
        
        <div class="row q-col-gutter-md">
          <div 
            v-for="record in studentRecords" 
            :key="record.id" 
            class="col-12 col-sm-6 col-md-4 col-lg-3"
          >
            <q-card class="student-card" :class="record.attendance_status">
              <q-card-section class="q-pb-none">
                <div class="row items-center">
                  <div class="col-auto">
                    <q-avatar :color="getAvatarColor(record.attendance_status)" text-color="white" class="student-avatar">
                      {{ getInitials(record.student?.name) }}
                    </q-avatar>
                  </div>
                  <div class="col q-ml-sm">
                    <div class="student-name">{{ record.student?.name }}</div>
                    <div class="student-status">
                      {{ getStatusEmoji(record.attendance_status) }}
                      {{ record.attendance_status }}
                      <span v-if="record.attendance_status === 'late'">
                        ({{ record.late_minutes }} min)
                      </span>
                    </div>
                  </div>
                </div>
              </q-card-section>
              
              <q-card-section v-if="record.homework_completed || record.behavior_plus_marks > 0">
                <div class="row q-gutter-xs">
                  <q-badge v-if="record.homework_completed" color="positive" label="Homework Done" />
                  <q-badge v-if="record.behavior_plus_marks > 0" color="positive">
                    +{{ record.behavior_plus_marks }} Stars
                  </q-badge>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>

      <!-- No Students Message -->
      <div v-else class="no-students">
        <p>No students found for this class period.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();

const props = defineProps({
  scheduleId: {
    type: [Number, String],
    required: true
  },
  calendarId: {
    type: [Number, String],
    required: true
  },
  teacherId: {
    type: [Number, String],
    required: true
  },
  classroomId: {
    type: [Number, String],
    required: true
  },
  isTeacher: {
    type: Boolean,
    default: false
  }
});

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const periodActivity = ref(null);
const studentRecords = ref([]);
const editMode = ref(false);
const activeTab = ref('attendance');

// Options for dropdowns
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

// Computed property for status emoji
const statusEmoji = computed(() => {
  if (!periodActivity.value) return '📚';
  
  switch (periodActivity.value.period_status) {
    case 'completed': return '✅';
    case 'cancelled': return '❌';
    case 'modified': return '🔄';
    case 'event_affected': return '🎭';
    default: return '📚';
  }
});

// Computed property for status color
const statusColor = computed(() => {
  if (!periodActivity.value) return 'grey';
  
  switch (periodActivity.value.period_status) {
    case 'completed': return 'positive';
    case 'cancelled': return 'negative';
    case 'modified': return 'warning';
    case 'event_affected': return 'info';
    default: return 'grey';
  }
});

// Format date to friendly string
const formatDate = (dateString) => {
  if (!dateString) return 'Unknown Date';
  
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    weekday: 'long', 
    month: 'short', 
    day: 'numeric' 
  });
};

// Format time to friendly string
const formatTime = (timeString) => {
  if (!timeString) return '';
  
  // Convert 24-hour format to 12-hour format
  const [hours, minutes] = timeString.split(':');
  const hour = parseInt(hours);
  const ampm = hour >= 12 ? 'PM' : 'AM';
  const hour12 = hour % 12 || 12;
  
  return `${hour12}:${minutes} ${ampm}`;
};

// Get initials from name
const getInitials = (name) => {
  if (!name) return '?';
  
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

// Get emoji for attendance status
const getStatusEmoji = (status) => {
  switch (status) {
    case 'present': return '😊';
    case 'absent': return '😴';
    case 'late': return '⏰';
    case 'excused': return '🤒';
    default: return '❓';
  }
};

// Get avatar color for attendance status
const getAvatarColor = (status) => {
  switch (status) {
    case 'present': return 'positive';
    case 'absent': return 'negative';
    case 'late': return 'warning';
    case 'excused': return 'info';
    default: return 'grey';
  }
};

// Fetch period activity data
const fetchPeriodActivity = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    // First check if period activity exists
    const response = await axios.post('/api/period-activities', {
      schedule_id: props.scheduleId,
      calendar_id: props.calendarId,
      teacher_id: props.teacherId,
      classroom_id: props.classroomId,
      teacher_present: true,
      period_status: 'completed'
    });
    
    periodActivity.value = response.data.period_activity;
    studentRecords.value = response.data.student_records;
    
  } catch (err) {
    console.error('Failed to fetch period activity:', err);
    error.value = 'We couldn\'t load your classroom data. Please try again!';
  } finally {
    loading.value = false;
  }
};

// Create a new period activity
const createPeriodActivity = async () => {
  saving.value = true;
  error.value = null;
  
  try {
    const periodData = {
      schedule_id: props.scheduleId,
      calendar_id: props.calendarId,
      teacher_id: props.teacherId,
      teacher_present: true,
      period_status: 'completed',
      lesson_notes: '',
      improvement_notes: '',
      was_duty_period: false,
      duty_notes: '',
      classroom_id: props.classroomId
    };
    
    const response = await axios.post('/api/period-activities', periodData);
    
    periodActivity.value = response.data.period_activity;
    studentRecords.value = response.data.student_records;
    
    $q.notify({
      color: 'positive',
      message: 'Class period created successfully!',
      icon: 'check_circle'
    });
    
  } catch (err) {
    console.error('Failed to create period activity:', err);
    error.value = 'We couldn\'t create the class period. Please try again!';
    
    $q.notify({
      color: 'negative',
      message: 'Failed to create class period',
      icon: 'error'
    });
  } finally {
    saving.value = false;
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
    // Save period activity
    const response = await axios.put(`/api/period-activities/${periodActivity.value.id}`, periodActivity.value);
    periodActivity.value = response.data.period_activity;

    // Save student records
    await axios.post(`/api/period-activities/${periodActivity.value.id}/student-records`, studentRecords.value);

    $q.notify({
      color: 'positive',
      message: 'Period activity and student records saved successfully!',
      icon: 'check_circle'
    });

    // Exit edit mode
    editMode.value = false;

  } catch (err) {
    console.error('Failed to save period activity or student records:', err);
    $q.notify({
      color: 'negative',
      message: 'Failed to save changes. Please try again!',
      icon: 'error'
    });
  } finally {
    saving.value = false;
  }
};

// Fetch data on component mount
onMounted(fetchPeriodActivity);
</script>

<style scoped>
.period-activity-container {
  font-family: 'Comic Sans MS', cursive, sans-serif;
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f8f9fa;
  border-radius: 15px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Loading Animation */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}

.rocket-loader {
  position: relative;
  height: 100px;
  width: 100px;
}

.rocket {
  font-size: 40px;
  position: absolute;
  animation: fly 2s infinite;
}

.stars {
  position: absolute;
  width: 100%;
  height: 100%;
}

.star {
  position: absolute;
  font-size: 15px;
  opacity: 0;
  animation: twinkle 3s infinite;
}

@keyframes fly {
  0% { transform: translateY(20px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
  100% { transform: translateY(20px) rotate(0deg); }
}

@keyframes twinkle {
  0% { opacity: 0; }
  50% { opacity: 1; }
  100% { opacity: 0; }
}

.loading-text {
  margin-top: 20px;
  font-size: 18px;
  color: #6c757d;
}

/* Error State */
.error-container {
  text-align: center;
  padding: 30px;
}

.error-icon {
  font-size: 50px;
  margin-bottom: 20px;
}

.error-title {
  color: #dc3545;
  margin-bottom: 10px;
}

.retry-button {
  margin-top: 20px;
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-size: 16px;
  transition: background-color 0.3s;
}

.retry-button:hover {
  background-color: #0056b3;
}

/* Activity Card */
.activity-card {
  background-color: white;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 20px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.activity-header {
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.activity-header.completed {
  background-color: #28a745;
}

.activity-header.cancelled {
  background-color: #dc3545;
}

.activity-header.modified {
  background-color: #ffc107;
  color: #343a40;
}

.activity-header.event_affected {
  background-color: #17a2b8;
}

.activity-title {
  margin: 0;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
}

.emoji {
  margin-right: 10px;
  font-size: 1.5em;
}

.activity-badge {
  padding: 5px 10px;
  border-radius: 20px;
  background-color: rgba(255, 255, 255, 0.3);
  font-size: 0.9rem;
  text-transform: capitalize;
}

.activity-body {
  padding: 15px;
}

.info-row {
  display: flex;
  margin-bottom: 10px;
  align-items: flex-start;
}

.info-label {
  font-weight: bold;
  width: 120px;
  color: #6c757d;
}

.info-value {
  flex: 1;
}

.notes-box {
  background-color: #fff9c4;
  padding: 10px;
  border-radius: 8px;
  border-left: 4px solid #ffc107;
}

/* Students Section */
.students-section {
  margin-top: 30px;
}

.section-title {
  display: flex;
  align-items: center;
  font-size: 1.5rem;
  margin-bottom: 15px;
  color: #343a40;
}

.students-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
}

.student-card {
  background-color: white;
  border-radius: 10px;
  padding: 15px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s;
}

.student-card:hover {
  transform: translateY(-5px);
}

.student-card.present {
  border-top: 4px solid #28a745;
}

.student-card.absent {
  border-top: 4px solid #dc3545;
}

.student-card.late {
  border-top: 4px solid #ffc107;
}

.student-card.excused {
  border-top: 4px solid #17a2b8;
}

.student-avatar {
  width: 50px;
  height: 50px;
  background-color: #6c757d;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 10px;
  font-size: 18px;
  font-weight: bold;
}

.student-name {
  font-weight: bold;
  margin-bottom: 5px;
  font-size: 14px;
}

.student-status {
  font-size: 12px;
  color: #6c757d;
  text-transform: capitalize;
}

/* No Data State */
.no-data-container {
  text-align: center;
  padding: 40px 20px;
}

.no-data-icon {
  font-size: 50px;
  margin-bottom: 20px;
}

.no-data-title {
  color: #6c757d;
  margin-bottom: 10px;
}

.no-data-message {
  color: #adb5bd;
}

/* No Students Message */
.no-students {
  background-color: #f8d7da;
  color: #721c24;
  padding: 15px;
  border-radius: 8px;
  text-align: center;
  margin-top: 20px;
}
</style>

