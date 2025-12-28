<template>
  <div class="q-pa-md">
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
        <!-- Schedule Copy Selector -->
        <div class="col-12 col-sm-6 col-md-4">
          <q-select
            v-model="selectedCopyId"
            :options="scheduleCopies"
            option-value="id"
            option-label="name"
            label="Schedule Copy"
            outlined
            dense
            emit-value
            map-options
            :loading="loadingCopies"
            @update:model-value="handleCopyChange"
          >
            <template v-slot:prepend>
              <q-icon name="content_copy" color="primary" />
            </template>
            <template v-slot:option="{ opt, itemProps }">
              <q-item v-bind="itemProps">
                <q-item-section>
                  <q-item-label>{{ opt.name }}</q-item-label>
                  <q-item-label caption>
                    {{ opt.academic_year?.name }} - {{ opt.semester?.name }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <StatusBadge :status="opt.status" />
                </q-item-section>
              </q-item>
            </template>
          </q-select>
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
            :disable="!selectedCopyId"
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

        <!-- Statistics -->
        <div class="col-auto">
          <div v-if="stats" class="row q-gutter-sm">
            <q-chip dense icon="check_circle" color="green-2" text-color="green-9">
              {{ stats.assigned_slots }} assigned
            </q-chip>
            <q-chip dense icon="radio_button_unchecked" color="grey-3" text-color="grey-8">
              {{ stats.empty_slots }} empty
            </q-chip>
          </div>
        </div>
      </div>
    </q-card>

    <!-- Loading State -->
    <div v-if="loadingSchedules" class="row justify-center q-pa-xl">
      <q-spinner-dots size="50px" color="primary" />
    </div>

    <!-- Empty State -->
    <q-card v-else-if="!selectedCopyId || !selectedClassroomId" flat bordered class="text-center q-pa-xl">
      <q-icon name="touch_app" size="64px" color="grey-5" />
      <p class="text-h6 text-grey-7 q-mt-md">Select a schedule copy and classroom</p>
      <p class="text-grey-6">Choose from the dropdowns above to view and edit the timetable</p>
    </q-card>

    <!-- Timetable Grid -->
    <TimetableGrid
      v-else
      :schedules="schedules"
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
      @submit="handleAssignSubmit"
      @filter-cst="handleFilterCST"
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

const $q = useQuasar()

// Data
const scheduleCopies = ref([])
const classrooms = ref([])
const schedules = ref([])
const cstOptions = ref([])
const teachers = ref([])
const subjects = ref([])

// Selected values
const selectedCopyId = ref(null)
const selectedClassroomId = ref(null)
const selectedClassroomName = computed(() => {
  const classroom = classrooms.value.find(c => c.id === selectedClassroomId.value)
  return classroom?.name || ''
})

// Dialog state
const showAssignDialog = ref(false)
const selectedSchedule = ref(null)
const selectedDay = ref(null)
const selectedPeriod = ref(null)

// Loading states
const loadingCopies = ref(false)
const loadingClassrooms = ref(false)
const loadingSchedules = ref(false)
const loadingCST = ref(false)
const loadingTeachers = ref(false)
const loadingSubjects = ref(false)
const saving = ref(false)

// Statistics
const stats = ref(null)

// Methods
const fetchScheduleCopies = async () => {
  loadingCopies.value = true
  try {
    const response = await axios.get('/hr/schedule-copies')
    scheduleCopies.value = response.data.data || response.data || []
    // Auto-select active copy if exists
    const activeCopy = scheduleCopies.value.find(c => c.status === 'active')
    if (activeCopy) {
      selectedCopyId.value = activeCopy.id
    }
  } catch (error) {
    console.error('Error fetching schedule copies:', error)
    $q.notify({ type: 'negative', message: 'Failed to load schedule copies' })
  } finally {
    loadingCopies.value = false
  }
}

const fetchClassrooms = async () => {
  if (!selectedCopyId.value) return
  
  const copy = scheduleCopies.value.find(c => c.id === selectedCopyId.value)
  if (!copy) return

  loadingClassrooms.value = true
  try {
    const response = await axios.get(`/api/classrooms?school_id=${copy.school_id}`)
    classrooms.value = response.data.data || response.data || []
    // Auto-select first classroom
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
  if (!selectedCopyId.value || !selectedClassroomId.value) return

  loadingSchedules.value = true
  try {
    const response = await axios.get('/hr/schedules', {
      params: {
        copy_id: selectedCopyId.value,
        classroom_id: selectedClassroomId.value
      }
    })
    schedules.value = response.data.data || response.data || []
    calculateStats()
  } catch (error) {
    console.error('Error fetching schedules:', error)
    $q.notify({ type: 'negative', message: 'Failed to load schedules' })
  } finally {
    loadingSchedules.value = false
  }
}

const fetchCSTOptions = async () => {
  if (!selectedCopyId.value) return
  
  const copy = scheduleCopies.value.find(c => c.id === selectedCopyId.value)
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
  if (!selectedCopyId.value) return
  
  const copy = scheduleCopies.value.find(c => c.id === selectedCopyId.value)
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
  const total = schedules.value.length
  const assigned = schedules.value.filter(s => s.cst_id).length
  stats.value = {
    total_slots: total,
    assigned_slots: assigned,
    empty_slots: total - assigned
  }
}

const handleCopyChange = async () => {
  selectedClassroomId.value = null
  schedules.value = []
  await fetchClassrooms()
}

const handleClassroomChange = async () => {
  await Promise.all([
    fetchSchedules(),
    fetchCSTOptions()
  ])
}

const handleCellClick = ({ day, period, schedule }) => {
  selectedDay.value = day
  selectedPeriod.value = period
  selectedSchedule.value = schedule
  showAssignDialog.value = true
}

const handleEdit = (schedule) => {
  selectedSchedule.value = schedule
  selectedDay.value = schedule.day_number
  selectedPeriod.value = schedule.period_number
  showAssignDialog.value = true
}

const handleClear = async (schedule) => {
  try {
    await axios.put(`/hr/schedules/${schedule.id}`, {
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
    if (formData.schedule_id) {
      // Update existing schedule
      await axios.put(`/hr/schedules/${formData.schedule_id}`, formData)
    } else {
      // Create new schedule
      await axios.post('/hr/schedules', {
        ...formData,
        copy_id: selectedCopyId.value,
        school_id: scheduleCopies.value.find(c => c.id === selectedCopyId.value)?.school_id,
        day_number: formData.day,
        period_number: formData.period
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

// Watchers
watch(selectedCopyId, async (newVal) => {
  if (newVal) {
    await Promise.all([
      fetchClassrooms(),
      fetchTeachers(),
      fetchSubjects()
    ])
  }
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
