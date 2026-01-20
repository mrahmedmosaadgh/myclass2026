<template>
  <Head :title="t('weeklySystem.timetableEditor.title')" />
  <div class="q-pa-md">
    <WeeklyPlanMenu />
    <!-- Page Header -->
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="grid_view" class="q-mr-sm" color="primary" />
          {{ t('weeklySystem.timetableEditor.title') }}
        </h4>
        <p class="text-grey-7 q-mb-none">
          {{ t('weeklySystem.timetableEditor.subtitle') }}
        </p>
      </div>
    </div>

    <!-- Filters Row -->
    <q-card flat bordered class="q-pa-md q-mb-lg">
      <div class="row q-gutter-md items-end">
        <!-- Classroom Selector -->
        <div class="col-12 col-sm-6 col-md-4">
          <q-select
            v-model="selectedClassroomId"
            :options="classrooms"
            option-value="id"
            option-label="name"
            :label="t('weeklyPlans.classroom')"
            outlined
            dense
            emit-value
            map-options
            :loading="loadingClassrooms"
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
            :label="t('common.aiGenerate')"
            outline
            :disable="!selectedClassroomId"
            @click="showAIImportDialog = true"
          >
            <q-tooltip v-if="!selectedClassroomId">
              {{ t('common.selectClassroomFirst') }}
            </q-tooltip>
          </q-btn>
        </div>

        <!-- Random Fill Button -->
        <div class="col-12 col-sm-auto">
          <q-btn
            color="positive"
            icon="shuffle"
            label="Random Fill"
            outline
            :disable="!selectedClassroomId"
            @click="showRandomFillDialog = true"
          >
            <q-tooltip v-if="!selectedClassroomId">
              {{ t('common.selectClassroomFirst') }}
            </q-tooltip>
          </q-btn>
        </div>

        <!-- Draft Management -->
        <div class="col-12 col-sm-auto">
          <q-btn-dropdown
            color="secondary"
            icon="save"
            label="Drafts"
            outline
          >
            <q-list>
              <q-item clickable v-close-popup @click="showSaveDraftDialog = true">
                <q-item-section avatar>
                  <q-icon name="save" color="primary" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Save Current as Draft</q-item-label>
                </q-item-section>
              </q-item>
              
              <q-item clickable v-close-popup @click="openLoadDraftDialog">
                <q-item-section avatar>
                  <q-icon name="restore" color="secondary" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>Load Draft</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>

        <!-- Assignments Overview Button -->
        <div class="col-12 col-sm-auto">
          <q-btn
            color="info"
            icon="assignment_ind"
            label="Assignments Overview"
            outline
            @click="showOverviewDialog = true"
          />
        </div>
        
        <!-- ... existing stats code ... -->

    <!-- Save Draft Dialog -->
    <q-dialog v-model="showSaveDraftDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Save Schedule Draft</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-input dense v-model="draftName" autofocus label="Draft Name" @keyup.enter="saveDraft" />
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save" @click="saveDraft" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Load Draft Dialog -->
    <q-dialog v-model="showLoadDraftDialog">
      <q-card style="min-width: 350px">
        <q-card-section>
          <div class="text-h6">Load Schedule Draft</div>
          <p class="text-caption text-negative">Warning: This will replace the current live schedule.</p>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-select
            v-model="selectedDraft"
            :options="availableDrafts"
            label="Select Draft"
            outlined
            dense
          />
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Load" color="negative" @click="loadDraft" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- ... existing dialogs ... -->



        <!-- Statistics -->
        <div class="col-12">
          <!-- Overall Statistics -->
          <div v-if="overallStats" class="q-mb-sm">
            <div class="text-caption text-grey-7 q-mb-xs">{{ t('weeklyPlans.totalSlots') }} ({{ t('weeklyPlans.allClassrooms') }})</div>
            <div class="row q-gutter-sm">
              <q-chip dense icon="apps" color="blue-2" text-color="blue-9" size="sm">
                {{ overallStats.total_slots }} {{ t('common.total') }}
              </q-chip>
              <q-chip dense icon="check_circle" color="green-2" text-color="green-9" size="sm">
                {{ overallStats.assigned_slots }} {{ t('common.assigned') }}
              </q-chip>
              <q-chip dense icon="radio_button_unchecked" color="grey-3" text-color="grey-8" size="sm">
                {{ overallStats.unassigned_slots }} {{ t('common.empty') }}
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
                {{ overallStats.conflict_count }} {{ t('common.conflicts') }}
              </q-chip>
            </div>
          </div>
          
          <!-- Current Classroom Statistics -->
          <div v-if="classroomStats">
            <div class="text-caption text-grey-7 q-mb-xs">{{ t('weeklyPlans.totalSlots') }} ({{ t('weeklyPlans.currentClassroom') }})</div>
            <div class="row q-gutter-sm">
              <q-chip dense icon="apps" color="blue-2" text-color="blue-9" size="sm">
                {{ classroomStats.total_slots }} {{ t('common.total') }}
              </q-chip>
              <q-chip dense icon="check_circle" color="green-2" text-color="green-9" size="sm">
                {{ classroomStats.assigned_slots }} {{ t('common.assigned') }}
              </q-chip>
              <q-chip dense icon="radio_button_unchecked" color="grey-3" text-color="grey-8" size="sm">
                {{ classroomStats.unassigned_slots }} {{ t('common.empty') }}
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
                {{ classroomStats.conflict_count }} {{ t('common.conflicts') }}
              </q-chip>
            </div>
            
            <!-- Subject Breakdown Table -->
            <div v-if="classroomStats.subject_breakdown && classroomStats.subject_breakdown.length > 0" class="q-mt-md">
              <div class="text-caption text-grey-7 q-mb-xs">Subject Breakdown</div>
              <q-markup-table dense flat bordered class="subject-breakdown-table">
                <thead>
                  <tr>
                    <th class="text-left">Subject</th>
                    <th class="text-left">Teacher</th>
                    <th class="text-center">Expected</th>
                    <th class="text-center">Actual</th>
                    <th class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in classroomStats.subject_breakdown" :key="index">
                    <td class="text-left">
                      <q-badge color="blue-2" text-color="blue-9">{{ item.subject_name }}</q-badge>
                    </td>
                    <td class="text-left">{{ item.teacher_name }}</td>
                    <td class="text-center">
                      <q-chip dense size="sm" color="grey-3" text-color="grey-9">{{ item.expected }}</q-chip>
                    </td>
                    <td class="text-center">
                      <q-chip dense size="sm" :color="item.actual === item.expected ? 'green-2' : 'orange-2'" :text-color="item.actual === item.expected ? 'green-9' : 'orange-9'">
                        {{ item.actual }}
                      </q-chip>
                    </td>
                    <td class="text-center">
                      <q-icon v-if="item.actual === item.expected" name="check_circle" color="positive" size="sm">
                        <q-tooltip>Correct</q-tooltip>
                      </q-icon>
                      <q-icon v-else-if="item.actual < item.expected" name="arrow_downward" color="warning" size="sm">
                        <q-tooltip>Missing {{ item.expected - item.actual }} period(s)</q-tooltip>
                      </q-icon>
                      <q-icon v-else name="arrow_upward" color="warning" size="sm">
                        <q-tooltip>{{ item.actual - item.expected }} extra period(s)</q-tooltip>
                      </q-icon>
                    </td>
                  </tr>
                </tbody>
              </q-markup-table>
            </div>
          </div>
        </div>
      </div>
    </q-card>

    <!-- Loading State -->
    <div v-if="loadingSchedules" class="row justify-center q-pa-xl">
      <q-spinner-dots size="50px" color="primary" />
    </div>

    <q-card v-else-if="!selectedClassroomId" flat bordered class="text-center q-pa-xl">
      <q-icon name="touch_app" size="64px" color="grey-5" />
      <p class="text-h6 text-grey-7 q-mt-md">{{ t('weeklySystem.timetableEditor.selectClassroom') }}</p>
      <p class="text-grey-6">{{ t('weeklySystem.timetableEditor.chooseClassroom') }}</p>
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
      @close="showAssignDialog = false"
    />

    <!-- Conflict Details Dialog -->
    <q-dialog v-model="showConflictDialog" position="right">
      <q-card style="width: 500px; max-width: 80vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ t('weeklySystem.timetableEditor.conflictDetails') }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <div v-if="loadingConflictDetails" class="text-center q-pa-md">
            <q-spinner color="primary" size="3em" />
          </div>
          <div v-else-if="conflictDetails.length === 0" class="text-center text-grey-7 q-pa-md">
            {{ t('common.noConflicts') }}
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
                  {{ t('weeklySystem.timetableEditor.assignedTo') }}: {{ conflict.classrooms.join(', ') }}
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
      @applied="handleAIImportApplied"
    />

    <!-- Random Fill Dialog -->
    <RandomFillDialog
      v-model="showRandomFillDialog"
      :classroom-id="selectedClassroomId"
      :classroom-name="selectedClassroomName"
      @applied="handleRandomFillApplied"
    />

    <!-- CST Overview Dialog -->
    <CSTOverviewDialog
      v-model="showOverviewDialog"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import TimetableGrid from '../components/timetable/TimetableGrid.vue'
import CSTAssignDialog from '../components/timetable/CSTAssignDialog.vue'
import StatusBadge from '../components/shared/StatusBadge.vue'
import WeeklyPlanMenu from '../WeeklyPlanMenu.vue'
import AIImportDialog from '../components/timetable/AIImportDialog.vue'
import RandomFillDialog from '../components/timetable/RandomFillDialog.vue'
import CSTOverviewDialog from '../components/timetable/CSTOverviewDialog.vue'

const { t } = useI18n()
const $q = useQuasar()

// Data
const classrooms = ref([])
const schedules = ref([])
const cstOptions = ref([])
const teachers = ref([])
const subjects = ref([])
const teacherConflicts = ref({})

// Selected values
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
const slotAvailability = ref(null)
const loadingAvailability = ref(false)
const showOverviewDialog = ref(false)

// Loading states
const loadingClassrooms = ref(false)
const loadingSchedules = ref(false)
const loadingCST = ref(false)
const loadingTeachers = ref(false)
const loadingSubjects = ref(false)
const saving = ref(false)

const showSaveDraftDialog = ref(false)
const showLoadDraftDialog = ref(false)
const draftName = ref('')
const selectedDraft = ref(null)
const availableDrafts = ref([])

const saveDraft = async () => {
  if (!draftName.value) return
  
  try {
    await axios.post('/weekly-system/api/drafts/save', {
      name: draftName.value
    })
    $q.notify({ type: 'positive', message: 'Draft saved successfully' })
    showSaveDraftDialog.value = false
    draftName.value = ''
  } catch (error) {
    console.error('Error saving draft:', error)
    $q.notify({ type: 'negative', message: 'Failed to save draft' })
  }
}

const openLoadDraftDialog = async () => {
  try {
    const response = await axios.get('/weekly-system/api/drafts')
    availableDrafts.value = response.data.drafts || []
    showLoadDraftDialog.value = true
  } catch (error) {
     console.error('Error fetching drafts:', error)
    $q.notify({ type: 'negative', message: 'Failed to fetch drafts' })
  }
}

const loadDraft = async () => {
  if (!selectedDraft.value) return
  
  try {
    await axios.post('/weekly-system/api/drafts/load', {
      name: selectedDraft.value
    })
    $q.notify({ type: 'positive', message: 'Draft loaded successfully' })
    showLoadDraftDialog.value = false
    await fetchSchedules() // Refresh grid
  } catch (error) {
    console.error('Error loading draft:', error)
    $q.notify({ type: 'negative', message: 'Failed to load draft' })
  }
}

// Statistics
const stats = ref(null) 
const classroomStats = ref(null)
const overallStats = ref(null)

// Conflict Details Dialog
const showConflictDialog = ref(false)
const loadingConflictDetails = ref(false)
const conflictDetails = ref([])
const conflictType = ref('overall') 
const showAIImportDialog = ref(false)
const showRandomFillDialog = ref(false)

// Methods

const fetchClassrooms = async () => {
  loadingClassrooms.value = true
  try {
    const response = await axios.get(`/api/classrooms`)
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
  if (!selectedClassroomId.value) return

  loadingSchedules.value = true
  try {
    const response = await axios.get('/admin/schedules', {
      params: {
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
    if (response.data.stats) {
      stats.value = response.data.stats
    }
    
    // Fallback calculation
    if (!classroomStats.value && !overallStats.value) {
      calculateStats()
    }
    
    await fetchTeacherConflicts()
  } catch (error) {
    console.error('Error fetching schedules:', error)
    $q.notify({ type: 'negative', message: 'Failed to load schedules' })
  } finally {
    loadingSchedules.value = false
  }
}

const fetchTeacherConflicts = async () => {
  try {
    const response = await axios.get('/weekly-system/api/teacher-conflicts')
    teacherConflicts.value = response.data.data?.conflicts || {}
  } catch (error) {
    console.error('Error fetching teacher conflicts:', error)
    teacherConflicts.value = {}
  }
}

const fetchCSTOptions = async () => {
  if (!selectedClassroomId.value) return

  loadingCST.value = true
  try {
    const response = await axios.get('/api/classroom-subject-teachers', {
      params: {
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
  loadingTeachers.value = true
  try {
    const response = await axios.get('/api/teachers')
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
  if (!Array.isArray(schedules.value) || schedules.value.length === 0) {
    stats.value = {
      total_slots: 0,
      assigned_slots: 0,
      unassigned_slots: 0,
      conflict_count: 0
    }
    return
  }
  
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
  
  if (selectedClassroomId.value) {
    loadingAvailability.value = true
    slotAvailability.value = null
    try {
      const response = await axios.get('/weekly-system/api/slot-availability', {
        params: {
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
      period_number: formData.period 
    }

    if (formData.schedule_id) {
      await axios.put(`/admin/schedules/${formData.schedule_id}`, payload)
    } else {
      await axios.post('/admin/schedules', {
        ...payload,
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
  // Client-side can be enough
}

const showConflictDetails = async (type) => {
  conflictType.value = type
  showConflictDialog.value = true
  loadingConflictDetails.value = true
  conflictDetails.value = []

  try {
    const params = {}
    
    if (type === 'classroom' && selectedClassroomId.value) {
      params.classroom_id = selectedClassroomId.value
    }

    const response = await axios.get('/weekly-system/api/teacher-conflicts', { params })
    
    if (response.data.success && response.data.data) {
      const conflicts = response.data.data.conflicts || []
      const conflictsList = Array.isArray(conflicts) ? conflicts : Object.values(conflicts)
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

const getDayName = (dayNumber) => {
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  return days[dayNumber - 1] || `Day ${dayNumber}`
}

const handleAIImportApplied = async () => {
  schedules.value = [] 
  await fetchSchedules()
  $q.notify({ 
    type: 'positive', 
    message: 'AI import completed successfully! Timetable refreshed.',
    position: 'top'
  })
}

const handleRandomFillApplied = async () => {
  schedules.value = [] 
  await fetchSchedules()
  $q.notify({ 
    type: 'positive', 
    message: 'Random fill completed successfully! Timetable refreshed.',
    position: 'top'
  })
}

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
  await Promise.all([
    fetchClassrooms(),
    fetchTeachers(),
    fetchSubjects()
  ])
})
</script>

<style scoped>
h4 {
  font-size: 1.5rem;
}

.subject-breakdown-table {
  font-size: 0.875rem;
  max-width: 600px;
}

.subject-breakdown-table th {
  background-color: #f5f5f5;
  font-weight: 600;
  padding: 8px;
}

.subject-breakdown-table td {
  padding: 6px 8px;
}
</style>
