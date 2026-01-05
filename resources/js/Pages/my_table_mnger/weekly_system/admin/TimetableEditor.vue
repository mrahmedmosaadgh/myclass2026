<template>
  <Head title="Timetable Editor" />
  <div class="q-pa-md">
    <WeeklyPlanMenu />
    <!-- Page Header -->
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="grid_view" class="q-mr-sm" color="primary" />
          Timetable Editor
        </h4>
        <p class="text-grey-7 q-mb-none">
          Assign subjects and teachers to the schedule grid
        </p>
      </div>
    </div>

    <!-- Filters Row -->
    <q-card flat bordered class="q-pa-md q-mb-lg">
      <div class="row q-gutter-md items-end">
        <!-- Active Schedule Copy -->
        <div class="col-12 col-sm-6 col-md-4">
          <q-skeleton v-if="loadingCopies" type="rect" height="40px" />
          <q-banner v-else-if="activeCopy" dense class="bg-grey-1" rounded>
            <div class="row items-center no-wrap">
              <q-icon name="content_copy" color="primary" class="q-mr-sm" />
              <div class="col">
                <div class="text-weight-medium">{{ activeCopy.name }}</div>
                <div class="text-caption text-grey-7">
                  {{ activeCopy.academic_year?.name }} - {{ activeCopy.semester?.name }}
                </div>
              </div>
              <div class="col-auto">
                <StatusBadge :status="activeCopy.status" />
              </div>
            </div>
          </q-banner>
          <q-banner v-else dense class="bg-red-1 text-red-9" rounded>
            <div class="row items-center no-wrap">
              <q-icon name="error" class="q-mr-sm" />
              <div>No active schedule copy found</div>
            </div>
          </q-banner>
        </div>

        <!-- Classroom Selector -->
        <div class="col-12 col-sm-6 col-md-4">
          <q-select
            v-model="selectedClassroomId"
            :options="classrooms"
            option-value="id"
            option-label="name"
            label="Classroom"
            outlined
            dense
            emit-value
            map-options
            :loading="loadingClassrooms"
            :disable="!activeCopy"
            @update:model-value="handleClassroomChange"
          >
            <template v-slot:prepend>
              <q-icon name="meeting_room" color="secondary" />
            </template>
            <template v-slot:option="{ opt, itemProps }">
              <q-item v-bind="itemProps">
                <q-item-section>
                  <q-item-label>{{ opt.name }}</q-item-label>
                  <q-item-label caption>{{ opt.grade?.name || opt.grade_name }}</q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>

        <!-- AI Import Button -->
        <div class="col-12 col-sm-auto">
          <q-btn
            color="secondary"
            icon="psychology"
            label="Generate via AI"
            outline
            :disable="!activeCopy || !selectedClassroomId"
            @click="showAIImportDialog = true"
          >
            <q-tooltip v-if="!activeCopy || !selectedClassroomId">
              Select a classroom first
            </q-tooltip>
          </q-btn>
        </div>

        <!-- Statistics -->
        <div class="col-12">
          <!-- Overall Statistics -->
          <div v-if="overallStats" class="q-mb-sm">
            <div class="text-caption text-grey-7 q-mb-xs">Overall (All Classrooms)</div>
            <div class="row q-gutter-sm">
              <q-chip dense icon="apps" color="blue-2" text-color="blue-9" size="sm">
                {{ overallStats.total_slots }} total
              </q-chip>
              <q-chip dense icon="check_circle" color="green-2" text-color="green-9" size="sm">
                {{ overallStats.assigned_slots }} assigned
              </q-chip>
              <q-chip dense icon="radio_button_unchecked" color="grey-3" text-color="grey-8" size="sm">
                {{ overallStats.unassigned_slots }} empty
              </q-chip>
              <q-chip 
                v-if="overallStats.conflict_count > 0" 
                dense 
                icon="warning" 
                color="orange-2" 
                text-color="orange-9"
                size="sm"
                clickable
                @click="showConflictDetails('overall')"
              >
                {{ overallStats.conflict_count }} conflicts
              </q-chip>
            </div>
          </div>
          
          <!-- Current Classroom Statistics -->
          <div v-if="classroomStats">
            <div class="text-caption text-grey-7 q-mb-xs">Current Classroom</div>
            <div class="row q-gutter-sm">
              <q-chip dense icon="apps" color="blue-2" text-color="blue-9" size="sm">
                {{ classroomStats.total_slots }} total
              </q-chip>
              <q-chip dense icon="check_circle" color="green-2" text-color="green-9" size="sm">
                {{ classroomStats.assigned_slots }} assigned
              </q-chip>
              <q-chip dense icon="radio_button_unchecked" color="grey-3" text-color="grey-8" size="sm">
                {{ classroomStats.unassigned_slots }} empty
              </q-chip>
              <q-chip 
                v-if="classroomStats.conflict_count > 0" 
                dense 
                icon="warning" 
                color="orange-2" 
                text-color="orange-9"
                size="sm"
                clickable
                @click="showConflictDetails('classroom')"
              >
                {{ classroomStats.conflict_count }} conflicts
              </q-chip>
            </div>
          </div>
        </div>
      </div>
    </q-card>

    <!-- Loading State -->
    <div v-if="loadingSchedules" class="row justify-center q-pa-xl">
      <q-spinner-dots size="50px" color="primary" />
    </div>

    <!-- Empty State -->
    <q-card v-else-if="!activeCopy" flat bordered class="text-center q-pa-xl">
      <q-icon name="error" size="64px" color="red-5" />
      <p class="text-h6 text-grey-7 q-mt-md">No active schedule copy</p>
      <p class="text-grey-6">Activate a schedule copy to view and edit the timetable</p>
    </q-card>

    <q-card v-else-if="!selectedClassroomId" flat bordered class="text-center q-pa-xl">
      <q-icon name="touch_app" size="64px" color="grey-5" />
      <p class="text-h6 text-grey-7 q-mt-md">Select a classroom</p>
      <p class="text-grey-6">Choose a classroom to view and edit the timetable</p>
    </q-card>

    <!-- Timetable Grid -->
    <TimetableGrid
      v-else
      :schedules="schedules"
      :teacher-conflicts="teacherConflicts"
      @cell-click="handleCellClick"
      @edit="handleEdit"
      @clear="handleClear"
    />

    <!-- CST Assignment Dialog -->
    <CSTAssignDialog
      v-model="showAssignDialog"
      :schedule="selectedSchedule"
      :day="selectedDay"
      :period="selectedPeriod"
      :classroom-name="selectedClassroomName"
      :cst-options="cstOptions"
      :teachers="teachers"
      :subjects="subjects"
      :loading-c-s-t="loadingCST"
      :loading-teachers="loadingTeachers"
      :loading-subjects="loadingSubjects"
      :saving="saving"
      :slot-availability="slotAvailability"
      :loading-availability="loadingAvailability"
      @submit="handleAssignSubmit"
      @filter-cst="handleFilterCST"
    />

    <!-- Conflict Details Dialog -->
    <q-dialog v-model="showConflictDialog" position="right">
      <q-card style="width: 500px; max-width: 80vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Conflict Details</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div v-if="loadingConflictDetails" class="text-center q-pa-md">
            <q-spinner color="primary" size="3em" />
          </div>
          <div v-else-if="conflictDetails.length === 0" class="text-center text-grey-7 q-pa-md">
            No conflicts found
          </div>
          <q-list v-else separator>
            <q-item v-for="(conflict, index) in conflictDetails" :key="index">
              <q-item-section>
                <q-item-label class="text-weight-medium">
                  {{ conflict.teacher_name }}
                </q-item-label>
                <q-item-label caption>
                  {{ conflict.day_name }} - Period {{ conflict.period_number }}
                </q-item-label>
                <q-item-label caption class="text-orange-9">
                  Assigned to: {{ conflict.classrooms.join(', ') }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-badge color="orange" :label="conflict.classrooms.length + ' classes'" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- AI Import Dialog -->
    <AIImportDialog
      v-model="showAIImportDialog"
      :classroom-id="selectedClassroomId"
      :classroom-name="selectedClassroomName"
      :subjects="subjects"
      :copy-id="activeCopy?.id"
      @applied="handleAIImportApplied"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import TimetableGrid from '../components/timetable/TimetableGrid.vue'
import CSTAssignDialog from '../components/timetable/CSTAssignDialog.vue'
import StatusBadge from '../components/shared/StatusBadge.vue'
import WeeklyPlanMenu from '../WeeklyPlanMenu.vue'
import AIImportDialog from '../components/timetable/AIImportDialog.vue'

const $q = useQuasar()

// Data
const scheduleCopies = ref([])
const classrooms = ref([])
const schedules = ref([])
const cstOptions = ref([])
const teachers = ref([])
const subjects = ref([])
const teacherConflicts = ref({})

// Selected values
const selectedClassroomId = ref(null)
const activeCopy = computed(() => {
  return scheduleCopies.value.find(c => c?.status === 'active' || c?.active === true) || null
})
const selectedClassroomName = computed(() => {
  const classroom = classrooms.value.find(c => c.id === selectedClassroomId.value)
  return classroom?.name || ''
})

// Dialog state
const showAssignDialog = ref(false)
const selectedSchedule = ref(null)
const selectedDay = ref(null)
const selectedPeriod = ref(null)
const slotAvailability = ref(null)
const loadingAvailability = ref(false)

// Loading states
const loadingCopies = ref(false)
const loadingClassrooms = ref(false)
const loadingSchedules = ref(false)
const loadingCST = ref(false)
const loadingTeachers = ref(false)
const loadingSubjects = ref(false)
const saving = ref(false)

// Statistics
const stats = ref(null) // Keep for backward compatibility
const classroomStats = ref(null)
const overallStats = ref(null)

// Conflict Details Dialog
const showConflictDialog = ref(false)
const loadingConflictDetails = ref(false)
const conflictDetails = ref([])
const conflictType = ref('overall') // 'overall' or 'classroom'
const showAIImportDialog = ref(false)

// Methods
const fetchScheduleCopies = async () => {
  loadingCopies.value = true
  try {
    const response = await axios.get('/admin/schedule-copies')
    const result = response.data.data || response.data || []
    scheduleCopies.value = Array.isArray(result) ? result : (result.data || [])
  } catch (error) {
    console.error('Error fetching schedule copies:', error)
    $q.notify({ type: 'negative', message: 'Failed to load schedule copies' })
  } finally {
    loadingCopies.value = false
  }
}

const fetchClassrooms = async () => {
  if (!activeCopy.value) return
  const copy = activeCopy.value
  if (!copy) return

  loadingClassrooms.value = true
  try {
    const response = await axios.get(`/api/classrooms?school_id=${copy.school_id}`)
    const result = response.data.data || response.data || []
    classrooms.value = Array.isArray(result) ? result : []
    // Auto-select first classroom if not already set
    if (classrooms.value.length && !selectedClassroomId.value) {
      selectedClassroomId.value = classrooms.value[0].id
    }
  } catch (error) {
    console.error('Error fetching classrooms:', error)
    $q.notify({ type: 'negative', message: 'Failed to load classrooms' })
  } finally {
    loadingClassrooms.value = false
  }
}

const fetchSchedules = async () => {
  if (!activeCopy.value || !selectedClassroomId.value) return

  loadingSchedules.value = true
  try {
    const response = await axios.get('/admin/schedules', {
      params: {
        copy_id: activeCopy.value.id,
        classroom_id: selectedClassroomId.value
      }
    })
    const result = response.data.data || response.data || []
    schedules.value = Array.isArray(result) ? result : []
    
    // Use backend-provided statistics
    if (response.data.classroom_stats) {
      classroomStats.value = response.data.classroom_stats
    }
    if (response.data.overall_stats) {
      overallStats.value = response.data.overall_stats
    }
    // Backward compatibility
    if (response.data.stats) {
      stats.value = response.data.stats
    }
    
    // Fallback to frontend calculation if no backend stats
    if (!classroomStats.value && !overallStats.value) {
      calculateStats()
    }
    
    // Fetch conflicts after loading schedules
    await fetchTeacherConflicts()
  } catch (error) {
    console.error('Error fetching schedules:', error)
    $q.notify({ type: 'negative', message: 'Failed to load schedules' })
  } finally {
    loadingSchedules.value = false
  }
}

const fetchTeacherConflicts = async () => {
  if (!activeCopy.value) return
  
  try {
    const response = await axios.get('/weekly-system/api/teacher-conflicts', {
      params: {
        copy_id: activeCopy.value.id
      }
    })
    teacherConflicts.value = response.data.data?.conflicts || {}
  } catch (error) {
    console.error('Error fetching teacher conflicts:', error)
    teacherConflicts.value = {}
  }
}

const fetchCSTOptions = async () => {
  if (!activeCopy.value) return
  const copy = activeCopy.value
  if (!copy) return

  loadingCST.value = true
  try {
    const response = await axios.get('/api/classroom-subject-teachers', {
      params: {
        school_id: copy.school_id,
        classroom_id: selectedClassroomId.value
      }
    })
    cstOptions.value = (response.data.data || response.data || []).map(cst => ({
      ...cst,
      display_label: `${cst.subject_name} - ${cst.teacher_name}`
    }))
  } catch (error) {
    console.error('Error fetching CST options:', error)
  } finally {
    loadingCST.value = false
  }
}

const fetchTeachers = async () => {
  if (!activeCopy.value) return
  const copy = activeCopy.value
  if (!copy) return

  loadingTeachers.value = true
  try {
    const response = await axios.get('/api/teachers', {
      params: { school_id: copy.school_id }
    })
    teachers.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching teachers:', error)
  } finally {
    loadingTeachers.value = false
  }
}

const fetchSubjects = async () => {
  loadingSubjects.value = true
  try {
    const response = await axios.get('/api/subjects')
    subjects.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching subjects:', error)
  } finally {
    loadingSubjects.value = false
  }
}

const calculateStats = () => {
  // Fallback calculation only if backend doesn't provide stats
  if (!Array.isArray(schedules.value) || schedules.value.length === 0) {
    stats.value = {
      total_slots: 0,
      assigned_slots: 0,
      unassigned_slots: 0,
      conflict_count: 0
    }
    return
  }
  
  // Count assigned: must have cst_id AND day_number AND period_number
  const assigned = schedules.value.filter(s => 
    s && s.cst_id && s.day_number != null && s.period_number != null
  ).length
  
  const total = schedules.value.length
  const conflicts = Object.keys(teacherConflicts.value || {}).length
  
  stats.value = {
    total_slots: total,
    assigned_slots: assigned,
    unassigned_slots: total - assigned,
    conflict_count: conflicts
  }
}

const handleClassroomChange = async () => {
  // Watcher will handle data fetching
}

const handleCellClick = async ({ day, period, schedule }) => {
  selectedDay.value = day
  selectedPeriod.value = period
  selectedSchedule.value = schedule
  
  // Fetch availability information before opening dialog
  if (activeCopy.value && selectedClassroomId.value) {
    loadingAvailability.value = true
    slotAvailability.value = null
    try {
      const response = await axios.get('/weekly-system/api/slot-availability', {
        params: {
          copy_id: activeCopy.value.id,
          classroom_id: selectedClassroomId.value,
          day: day,
          period: period
        }
      })
      slotAvailability.value = response.data.data
    } catch (error) {
      console.error('Error fetching slot availability:', error)
      slotAvailability.value = null
    } finally {
      loadingAvailability.value = false
    }
  }
  
  showAssignDialog.value = true
}

const handleEdit = (schedule) => {
  selectedSchedule.value = schedule
  selectedDay.value = schedule.day
  selectedPeriod.value = schedule.period_number
  showAssignDialog.value = true
}

const handleClear = async (schedule) => {
  try {
    await axios.put(`/admin/schedules/${schedule.id}`, {
      cst_id: null,
      teacher_substitute_id: null,
      co_teacher_id: null,
      co_subject_id: null
    })
    $q.notify({ type: 'info', message: 'Schedule slot cleared' })
    await fetchSchedules()
  } catch (error) {
    console.error('Error clearing schedule:', error)
    $q.notify({ type: 'negative', message: 'Failed to clear schedule' })
  }
}

const handleAssignSubmit = async (formData) => {
  saving.value = true
  try {
    const payload = {
      ...formData,
      day: formData.day,
      period_number: formData.period // Map period to period_number matching backend expectation
    }

    if (formData.schedule_id) {
      // Update existing schedule
      await axios.put(`/admin/schedules/${formData.schedule_id}`, payload)
    } else {
      if (!activeCopy.value) {
        $q.notify({ type: 'negative', message: 'No active schedule copy found' })
        return
      }
      // Create new schedule
      await axios.post('/admin/schedules', {
        ...payload,
        copy_id: activeCopy.value.id,
        school_id: activeCopy.value.school_id,
        day_number: formData.day
      })
    }
    
    $q.notify({ type: 'positive', message: 'Schedule updated successfully' })
    showAssignDialog.value = false
    await fetchSchedules()
  } catch (error) {
    console.error('Error saving schedule:', error)
    $q.notify({ type: 'negative', message: error.response?.data?.message || 'Failed to save schedule' })
  } finally {
    saving.value = false
  }
}

const handleFilterCST = (searchTerm) => {
  // Could implement server-side filtering here if needed
}

// Show conflict details dialog (Added via update)
const showConflictDetails = async (type) => {
  conflictType.value = type
  showConflictDialog.value = true
  loadingConflictDetails.value = true
  conflictDetails.value = []

  try {
    const params = {
      copy_id: activeCopy.value?.id
    }
    
    if (type === 'classroom' && selectedClassroomId.value) {
      params.classroom_id = selectedClassroomId.value
    }

    const response = await axios.get('/weekly-system/api/teacher-conflicts', { params })
    
    if (response.data.success && response.data.data) {
      const conflicts = response.data.data.conflicts || []
      
      // Transform conflicts into display format
      // The API returns an object where keys are either unique conflict keys (teacher-day-period)
      // or schedule IDs. We only want the unique ones (without schedule_id property)
      const conflictsList = Array.isArray(conflicts) ? conflicts : Object.values(conflicts)
      
      // Filter out duplicates: only show unique conflicts that don't have schedule_id
      // (schedule_id keyed entries are for cell display, not for the dialog)
      const uniqueConflicts = conflictsList.filter(c => !c.schedule_id)
      
      conflictDetails.value = uniqueConflicts.map(conflict => ({
        teacher_name: conflict.teacher_name,
        day_name: getDayName(conflict.day),
        period_number: conflict.period,
        classrooms: conflict.classrooms.map(c => c.classroom_name)
      }))
    }
  } catch (error) {
    console.error('Error fetching conflict details:', error)
    $q.notify({ type: 'negative', message: 'Failed to load conflict details' })
  } finally {
    loadingConflictDetails.value = false
  }
}

// Helper to get day name
const getDayName = (dayNumber) => {
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  return days[dayNumber - 1] || `Day ${dayNumber}`
}

// Handle AI Import completion
const handleAIImportApplied = async () => {
  // Do not close dialog automatically
  schedules.value = [] // clear to force reactivity
  await fetchSchedules()
  $q.notify({ 
    type: 'positive', 
    message: 'AI import completed successfully! Timetable refreshed.',
    position: 'top'
  })
}

watch(activeCopy, async (newVal) => {
  if (!newVal) return
  selectedClassroomId.value = null
  classrooms.value = []
  schedules.value = []
  stats.value = null
  await Promise.all([
    fetchClassrooms(),
    fetchTeachers(),
    fetchSubjects()
  ])
})

watch(selectedClassroomId, async (newVal) => {
  if (newVal) {
    await Promise.all([
      fetchSchedules(),
      fetchCSTOptions()
    ])
  }
})

// Lifecycle
onMounted(async () => {
  await fetchScheduleCopies()
})
</script>

<style scoped>
h4 {
  font-size: 1.5rem;
}
</style>
