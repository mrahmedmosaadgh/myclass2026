<template>
  <div class="student-records-grid">
    <!-- Header with search and quick actions -->
    <div class="grid-header q-mb-md">
      <div class="row items-center justify-between q-mb-sm">
        <div class="col-12 col-md-4">
          <q-input
            v-model="searchQuery"
            dense
            outlined
            placeholder="Search students..."
            class="search-input"
          >
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
        
        <div class="col-12 col-md-8 row justify-end q-gutter-sm">
          <q-btn-group outline>
            <q-btn color="positive" icon="check_circle" label="All Present" @click="markAllAs('present')" />
            <q-btn color="warning" icon="schedule" label="All Late" @click="markAllAs('late')" />
            <q-btn color="negative" icon="cancel" label="All Absent" @click="markAllAs('absent')" />
          </q-btn-group>
          
          <q-btn color="primary" icon="filter_list">
            Filter
            <q-menu>
              <q-list style="min-width: 150px">
                <q-item clickable v-close-popup @click="filterBy('all')">
                  <q-item-section>All Students</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="filterBy('present')">
                  <q-item-section>Present Only</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="filterBy('absent')">
                  <q-item-section>Absent Only</q-item-section>
                </q-item>
                <q-item clickable v-close-popup @click="filterBy('late')">
                  <q-item-section>Late Only</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
          
          <q-btn color="secondary" icon="dark_mode" @click="toggleDarkMode" />
        </div>
      </div>
      
      <!-- Tab navigation -->
      <q-tabs
        v-model="activeTab"
        class="text-primary"
        active-color="primary"
        indicator-color="primary"
        align="justify"
        narrow-indicator
      >
        <q-tab name="attendance" icon="how_to_reg" label="Attendance" />
        <q-tab name="homework" icon="assignment" label="Homework" />
        <q-tab name="behavior" icon="psychology" label="Behavior" />
        <q-tab name="participation" icon="record_voice_over" label="Participation" />
      </q-tabs>
    </div>
    
    <!-- Student cards grid -->
    <q-tab-panels v-model="activeTab" animated class="transparent">
      <q-tab-panel name="attendance">
        <div class="row q-col-gutter-md">
          <div 
            v-for="student in filteredStudents" 
            :key="student.id"
            class="col-12 col-sm-6 col-md-4 col-lg-3"
          >
            <q-card 
              :class="['student-card', `status-${student.attendance_status}`]"
              :dark="isDarkMode"
            >
              <q-card-section class="student-header">
                <div class="row items-center no-wrap">
                  <q-avatar size="60px" class="q-mr-sm">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(student.name)}&background=random&color=fff`">
                  </q-avatar>
                  
                  <div class="student-info">
                    <div class="text-h6 ellipsis-2-lines">{{ student.name }}</div>
                    <q-badge :color="getStatusColor(student.attendance_status)">
                      {{ capitalizeFirst(student.attendance_status) }}
                    </q-badge>
                  </div>
                </div>
              </q-card-section>
              
              <q-separator />
              
              <q-card-section>
                <div class="row q-col-gutter-sm">
                  <div class="col-12">
                    <q-select
                      v-model="student.attendance_status"
                      :options="attendanceOptions"
                      outlined
                      dense
                      emit-value
                      map-options
                      @update:model-value="updateStudentRecord(student)"
                    />
                  </div>
                  
                  <div class="col-12" v-if="student.attendance_status === 'late'">
                    <q-input
                      v-model.number="student.late_minutes"
                      type="number"
                      label="Minutes Late"
                      outlined
                      dense
                      @update:model-value="updateStudentRecord(student)"
                    />
                  </div>
                </div>
              </q-card-section>
              
              <q-card-actions align="right">
                <q-btn flat color="grey" icon="edit" @click="openNotes(student)">
                  Notes
                </q-btn>
                <q-btn flat :color="getStatusColor(student.attendance_status)" icon="save" @click="saveStudentRecord(student)">
                  Save
                </q-btn>
              </q-card-actions>
            </q-card>
          </div>
        </div>
      </q-tab-panel>
      
      <q-tab-panel name="homework">
        <div class="row q-col-gutter-md">
          <div 
            v-for="student in filteredStudents" 
            :key="student.id"
            class="col-12 col-sm-6 col-md-4 col-lg-3"
          >
            <q-card :dark="isDarkMode" class="student-card">
              <q-card-section class="student-header">
                <div class="row items-center no-wrap">
                  <q-avatar size="60px" class="q-mr-sm">
                    <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(student.name)}&background=random&color=fff`">
                  </q-avatar>
                  
                  <div class="student-info">
                    <div class="text-h6 ellipsis-2-lines">{{ student.name }}</div>
                    <q-badge :color="student.homework_completed ? 'positive' : 'grey'">
                      {{ student.homework_completed ? 'Completed' : 'Not Completed' }}
                    </q-badge>
                  </div>
                </div>
              </q-card-section>
              
              <q-separator />
              
              <q-card-section>
                <div class="row q-col-gutter-sm">
                  <div class="col-12">
                    <q-toggle
                      v-model="student.homework_completed"
                      label="Homework Completed"
                      color="positive"
                      @update:model-value="updateStudentRecord(student)"
                    />
                  </div>
                  
                  <div class="col-12" v-if="student.homework_completed">
                    <q-input
                      v-model.number="student.homework_score"
                      type="number"
                      label="Score"
                      outlined
                      dense
                      @update:model-value="updateStudentRecord(student)"
                    />
                  </div>
                </div>
              </q-card-section>
              
              <q-card-actions align="right">
                <q-btn flat color="grey" icon="edit" @click="openNotes(student)">
                  Notes
                </q-btn>
                <q-btn flat color="primary" icon="save" @click="saveStudentRecord(student)">
                  Save
                </q-btn>
              </q-card-actions>
            </q-card>
          </div>
        </div>
      </q-tab-panel>
      
      <!-- Similar panels for behavior and participation tabs -->
      <!-- I'll add these in the next iteration if you like this approach -->
    </q-tab-panels>
    
    <!-- Notes dialog -->
    <q-dialog v-model="notesDialog" persistent>
      <q-card style="min-width: 350px" :dark="isDarkMode">
        <q-card-section>
          <div class="text-h6">Notes for {{ selectedStudent?.name }}</div>
        </q-card-section>
        
        <q-card-section>
          <q-input
            v-model="selectedStudent.notes"
            type="textarea"
            autofocus
            outlined
          />
        </q-card-section>
        
        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" v-close-popup />
          <q-btn flat label="Save Notes" color="positive" @click="saveNotes" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';

const props = defineProps({
  students: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:student', 'save-student']);

const $q = useQuasar();

// State
const searchQuery = ref('');
const activeTab = ref('attendance');
const currentFilter = ref('all');
const isDarkMode = ref(false);
const notesDialog = ref(false);
const selectedStudent = ref(null);

// Options for dropdowns
const attendanceOptions = [
  { label: 'Present', value: 'present' },
  { label: 'Absent', value: 'absent' },
  { label: 'Late', value: 'late' },
  { label: 'Excused', value: 'excused' }
];

// Computed properties
const filteredStudents = computed(() => {
  let result = [...props.students];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(student => 
      student.name.toLowerCase().includes(query)
    );
  }
  
  // Apply status filter
  if (currentFilter.value !== 'all') {
    result = result.filter(student => 
      student.attendance_status === currentFilter.value
    );
  }
  
  return result;
});

// Methods
function getStatusColor(status) {
  const colors = {
    present: 'positive',
    absent: 'negative',
    late: 'warning',
    excused: 'info'
  };
  return colors[status] || 'grey';
}

function capitalizeFirst(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function markAllAs(status) {
  $q.dialog({
    title: 'Confirm',
    message: `Are you sure you want to mark all students as ${status}?`,
    cancel: true,
    persistent: true
  }).onOk(() => {
    filteredStudents.value.forEach(student => {
      student.attendance_status = status;
      updateStudentRecord(student);
    });
    
    $q.notify({
      color: 'positive',
      message: `All students marked as ${status}`,
      icon: 'check'
    });
  });
}

function filterBy(filter) {
  currentFilter.value = filter;
}

function toggleDarkMode() {
  isDarkMode.value = !isDarkMode.value;
  $q.dark.set(isDarkMode.value);
}

function updateStudentRecord(student) {
  emit('update:student', student);
}

function saveStudentRecord(student) {
  emit('save-student', student);
  
  $q.notify({
    color: 'positive',
    message: `Record updated for ${student.name}`,
    icon: 'check'
  });
}

function openNotes(student) {
  selectedStudent.value = {...student};
  notesDialog.value = true;
}

function saveNotes() {
  const index = props.students.findIndex(s => s.id === selectedStudent.value.id);
  if (index !== -1) {
    const updatedStudent = {...props.students[index], notes: selectedStudent.value.notes};
    emit('update:student', updatedStudent);
    emit('save-student', updatedStudent);
  }
}
</script>

<style>
.student-records-grid .grid-header {
  position: sticky;
  top: 0;
  z-index: 2;
  background: inherit;
}

.student-records-grid .student-card {
  transition: all 0.3s ease;
  height: 100%;
}

.student-records-grid .student-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.student-records-grid .student-card.status-present {
  border-left: 4px solid var(--q-positive);
}

.student-records-grid .student-card.status-absent {
  border-left: 4px solid var(--q-negative);
}

.student-records-grid .student-card.status-late {
  border-left: 4px solid var(--q-warning);
}

.student-records-grid .student-card.status-excused {
  border-left: 4px solid var(--q-info);
}

.student-records-grid .student-header {
  padding-bottom: 8px;
}

.student-records-grid .student-info {
  overflow: hidden;
  flex: 1;
}

.student-records-grid .ellipsis-2-lines {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.student-records-grid .search-input {
  width: 100%;
}
</style>

