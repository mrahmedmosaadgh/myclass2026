<template>
  <Head :title="t('weeklySystem.timetableEditor.title')" />
  <div class="q-pa-md">
    <WeeklyPlanMenu />
    <!-- Page Header -->
    <div class="row items-center q-mb-lg justify-between">
      <div class="col-auto">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="grid_view" class="q-mr-sm" color="primary" />
          {{ t('weeklySystem.timetableEditor.title') }}
        </h4>
        <p class="text-grey-7 q-mb-none">
          {{ t('weeklySystem.timetableEditor.subtitle') }}
        </p>
      </div>
      
      <!-- Sandbox Toolbar -->
      <div class="col-auto row q-gutter-sm">
         <q-chip 
            v-if="hasUnsavedChanges" 
            color="warning" 
            text-color="dark" 
            dense 
            icon="edit"
            class="q-mr-md"
          >
            Unscheduled Draft Changes
          </q-chip>
          
          <q-btn
            unelevated
            color="white"
            text-color="primary"
            icon="save"
            label="Save as Draft"
            :disable="loadingSchedules"
            @click="showSaveDraftDialog = true"
          >
            <q-tooltip>Save current changes to a named draft</q-tooltip>
          </q-btn>

          <q-btn
            unelevated
            color="secondary"
            text-color="white"
            icon="publish"
            label="Publish to Live"
            :disable="loadingSchedules"
            @click="publishSandboxState"
          >
            <q-tooltip>Apply current changes to live schedule</q-tooltip>
          </q-btn>
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
          <q-btn
            color="secondary"
            icon="save"
            label="Manage Drafts"
            outline
            @click="showDraftManager = true"
          />
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

    <!-- Draft Management Panel -->
    <DraftManagementPanel
      v-model="showDraftManager"
      @compare="openComparison"
      @load="handleDraftLoad"
    />

    <!-- Draft Comparison Dialog -->
    <DraftComparisonDialog
      v-model="showComparisonDialog"
      :draft-name="comparingDraftName"
      :comparison="comparisonData"
      :loading="comparing"
      :publishing="loadingDraft"
      @publish="publishDraft"
    />

    <!-- Save Draft Dialog (Enhanced) -->
    <q-dialog v-model="showSaveDraftDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Save Schedule Draft</div>
        </q-card-section>

        <q-card-section class="q-pt-none q-gutter-md">
          <q-input 
            filled 
            v-model="draftName" 
            autofocus 
            label="Draft Name" 
            hint="Unique name for this version"
            :rules="[val => !!val || 'Name is required']"
          />
          <q-input
            filled
            v-model="draftDescription"
            type="textarea"
            label="Description (Optional)"
            rows="3"
            hint="What changed in this version?"
          />
          <q-checkbox v-model="overwriteDraft" label="Overwrite if exists" />
        </q-card-section>

        <q-card-actions align="right" class="text-primary">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Save Draft" @click="saveDraft" color="primary" />
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
            
            <!-- Collapsible Subject Breakdown -->
            <q-expansion-item
              v-if="classroomStats.subject_breakdown && classroomStats.subject_breakdown.length > 0"
              v-model="isBreakdownExpanded"
              icon="analytics"
              label="Subject Breakdown"
              caption="Expected vs Actual"
              class="q-mt-md bg-grey-1 rounded-borders overflow-hidden"
              header-class="text-primary"
            >
              <q-markup-table dense flat square class="subject-breakdown-table">
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
            </q-expansion-item>
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
import DraftManagementPanel from '../components/timetable/DraftManagementPanel.vue'
import DraftComparisonDialog from '../components/timetable/DraftComparisonDialog.vue'

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

// Draft Management State
const showDraftManager = ref(false)
const showSaveDraftDialog = ref(false)
const showComparisonDialog = ref(false)
const draftName = ref('')
const draftDescription = ref('')
const overwriteDraft = ref(false)
const comparisonData = ref(null)
const comparingDraftName = ref('')
const comparing = ref(false)
const loadingDraft = ref(false)

// UI State
const isBreakdownExpanded = ref(false)
const hasUnsavedChanges = ref(false)

const publishSandboxState = async () => {
    // Save current changes as a temporary draft then publish it?
    // Or send sandbox payload to a new endpoint?
    // User requested "save in draft then btn to publish".
    // So if hasUnsavedChanges, warn user to Save Draft first?
    // Or just "Quick Publish" (Auto-save draft -> Publish)
    
    if (hasUnsavedChanges.value) {
        $q.dialog({
            title: 'Unsaved Changes',
            message: 'You have unsaved changes in the sandbox. You should save changes to a Draft before publishing, or we can auto-save a backup draft and publish now.',
            ok: { label: 'Save & Publish', color: 'primary' },
            cancel: true
        }).onOk(() => {
            // Auto Save Draft 'Auto_Publish_Timestamp'
            autoSaveAndPublish()
        })
        return
    }
    
    // Protocol: To publish, we essentially overwrite live with current view
    // We can use the 'schedules' payload in a direct 'publish' call if we add one, 
    // OR we forcing saving a draft first.
    // Let's implement an 'autoSaveAndPublish' flow.
}

const autoSaveAndPublish = async () => {
  loadingSchedules.value = true
  try {
     const tempDraftName = `PUBLISH_${date.formatDate(Date.now(), 'YYYY-MM-DD_HH-mm-ss')}`
     
     // 1. Save Draft
     await axios.post('/weekly-system/api/drafts/save', {
        name: tempDraftName,
        description: 'Auto-saved before publish from Sandbox',
        overwrite: true,
        schedules: schedules.value
     })
     
     // 2. Publish (Load) Draft
     await axios.post('/weekly-system/api/drafts/load', {
        name: tempDraftName,
        create_backup: true
     })
      
     hasUnsavedChanges.value = false
     $q.notify({ type: 'positive', message: 'Published to Live successfully!' })
     await fetchSchedules() // Refresh from live to confirm
     
  } catch (error) {
      console.error(error)
      $q.notify({ type: 'negative', message: 'Publish failed' })
  } finally {
      loadingSchedules.value = false
  }
}

const saveDraft = async () => {
  if (!draftName.value) return
  
  try {
    await axios.post('/weekly-system/api/drafts/save', {
      name: draftName.value,
      description: draftDescription.value,
      overwrite: overwriteDraft.value,
      schedules: schedules.value // Send local sandbox state
    })
    
    // Reset modified flag after successful save (optional, depending on UX. Usually saving 'as draft' means we are still working)
    // hasUnsavedChanges.value = false 
    
    $q.notify({ type: 'positive', message: 'Draft saved successfully' })
    showSaveDraftDialog.value = false
    draftName.value = ''
    draftDescription.value = ''
    overwriteDraft.value = false
  } catch (error) {
    if (error.response?.status === 409) {
      $q.notify({ 
        type: 'warning', 
        message: 'Draft name exists. Check "Overwrite" to replace it.',
        timeout: 5000
      })
    } else {
      console.error('Error saving draft:', error)
      $q.notify({ type: 'negative', message: 'Failed to save draft' })
    }
  }
}

const openComparison = async (draft) => {
  showComparisonDialog.value = true
  comparingDraftName.value = draft.name
  comparing.value = true
  comparisonData.value = null
  
  try {
    const response = await axios.post('/weekly-system/api/drafts/compare', {
      name: draft.name
    })
    comparisonData.value = response.data.comparison
  } catch (error) {
    console.error('Error comparing drafts:', error)
    $q.notify({ type: 'negative', message: 'Failed to compare schedules' })
    showComparisonDialog.value = false
  } finally {
    comparing.value = false
  }
}

const publishDraft = async () => {
  loadingDraft.value = true
  try {
    await axios.post('/weekly-system/api/drafts/load', {
      name: comparingDraftName.value,
      create_backup: true
    })
    $q.notify({ type: 'positive', message: 'Draft published successfully! Auto-backup created.' })
    showComparisonDialog.value = false
    showDraftManager.value = false
    await fetchSchedules() // Refresh live grid
  } catch (error) {
    console.error('Error publishing draft:', error)
    $q.notify({ type: 'negative', message: 'Failed to publish draft' })
  } finally {
    loadingDraft.value = false
  }
}

const handleDraftLoad = async (draft) => {
  // This is a direct load without comparison (via Manager)
  loadingDraft.value = true
  try {
    await axios.post('/weekly-system/api/drafts/load', {
      name: draft.name,
      create_backup: true
    })
    $q.notify({ type: 'positive', message: 'Draft loaded successfully! Auto-backup created.' })
    showDraftManager.value = false
    await fetchSchedules()
  } catch (error) {
    console.error('Error loading draft:', error)
    $q.notify({ type: 'negative', message: 'Failed to load draft' })
  } finally {
    loadingDraft.value = false
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
    // Get school_id from URL query params if exists
    const urlParams = new URLSearchParams(window.location.search);
    const schoolId = urlParams.get('school');
    
    const params = {};
    if (schoolId) {
        params.school_id = schoolId;
    }

    const response = await axios.get(`/api/classrooms`, { params })
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
  // Local update only (Sandbox Mode)
  try {
    const idx = schedules.value.findIndex(s => 
      s.day_number == schedule.day_number && 
      s.period_number == schedule.period_number
    )
    
    if (idx !== -1) {
       // Clear assignment properties but keep slot
       schedules.value[idx] = {
         ...schedules.value[idx],
         cst_id: null,
         teacher_substitute_id: null,
         co_teacher_id: null,
         co_subject_id: null,
         // Keeping display properties null for grid
         subject_name: null,
         teacher_name: null,
         color: null
       }
       hasUnsavedChanges.value = true
       calculateStats() // Recalc local stats
       $q.notify({ type: 'info', message: 'Slot cleared (Sandbox)' })
    }
  } catch (error) {
    console.error('Error clearing schedule:', error)
  }
}

const handleAssignSubmit = async (formData) => {
  // Local update only (Sandbox Mode)
  saving.value = true
  try {
    const day = formData.day
    const period = formData.period // This comes from CSTAssignDialog which emits 'period' not 'period_number'
    
    const idx = schedules.value.findIndex(s => 
      s.day_number == day && 
      s.period_number == period
    )
    
    // Find selected CST details to populate grid immediately
    const cst = cstOptions.value.find(c => c.id === formData.cst_id)
    
    if (idx !== -1) {
       // Update existing slot
       schedules.value[idx] = {
         ...schedules.value[idx],
         cst_id: formData.cst_id,
         teacher_substitute_id: formData.teacher_substitute_id,
         co_teacher_id: formData.co_teacher_id,
         co_subject_id: formData.co_subject_id,
         // Add display props for Grid
         subject_name: cst ? cst.subject_name : 'Unknown',
         teacher_name: cst ? cst.teacher_name : 'Unknown',
         color: cst ? cst.color : '#e0e0e0'
       }
    } else {
       // Create new slot locally
       schedules.value.push({
         id: 'temp_' + Date.now(),
         day_number: day,
         period_number: period,
         cst_id: formData.cst_id,
         // ... other fields
         subject_name: cst ? cst.subject_name : 'Unknown',
         teacher_name: cst ? cst.teacher_name : 'Unknown'
       })
    }
    
    hasUnsavedChanges.value = true
    calculateStats() // Recalc local stats
    $q.notify({ type: 'positive', message: 'Schedule updated (Sandbox)' })
    showAssignDialog.value = false
    // await fetchSchedules() // DO NOT FETCH LIVE
  } catch (error) {
    console.error('Error saving schedule:', error)
    $q.notify({ type: 'negative', message: 'Failed to update sandbox' })
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
