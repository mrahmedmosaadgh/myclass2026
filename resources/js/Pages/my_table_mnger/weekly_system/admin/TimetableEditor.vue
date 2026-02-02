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
      
      <!-- Live Mode Indicator -->
      <div class="col-auto row q-gutter-sm items-center">
         <q-chip 
            color="positive" 
            text-color="white" 
            dense 
            icon="cloud_done"
            class="q-mr-md"
          >
            Live Mode: Changes scale immediately
          </q-chip>
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
        
        <!-- Actions Menu -->
        <div class="col-12 col-sm-auto">
             <q-btn-dropdown
                color="primary"
                icon="settings"
                label="Actions"
                :disable="!selectedClassroomId"
             >
                <q-list style="min-width: 220px">
                    <!-- Actions Header -->
                    <q-item-label header class="text-weight-bold bg-grey-1">Tools</q-item-label>
                    
                    <q-item clickable v-close-popup @click="showAIImportDialog = true">
                        <q-item-section avatar>
                            <q-icon name="psychology" color="secondary" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>{{ t('common.aiGenerate') }}</q-item-label>
                            <q-item-label caption>Generate schedule with AI</q-item-label>
                        </q-item-section>
                    </q-item>

                    <q-item clickable v-close-popup @click="showRandomFillDialog = true">
                        <q-item-section avatar>
                            <q-icon name="shuffle" color="positive" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>Random Fill</q-item-label>
                            <q-item-label caption>Auto-fill empty slots</q-item-label>
                        </q-item-section>
                    </q-item>

                    <q-separator />

                    <!-- Links Header -->
                    <q-item-label header class="text-weight-bold bg-grey-1">Share Links</q-item-label>
                    
                    <q-item clickable v-close-popup @click="copyClassroomLink">
                        <q-item-section avatar>
                            <q-icon name="link" color="primary" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>Copy Student Link</q-item-label>
                             <q-item-label caption>Classroom view URL</q-item-label>
                        </q-item-section>
                    </q-item>

                    <q-item clickable v-close-popup @click="openClassroomPage">
                        <q-item-section avatar>
                            <q-icon name="open_in_new" color="secondary" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>Open Student Page</q-item-label>
                            <q-item-label caption>Open in new tab</q-item-label>
                        </q-item-section>
                    </q-item>

                    <q-item clickable v-close-popup @click="showTeacherLinkDialog = true">
                        <q-item-section avatar>
                            <q-icon name="person_search" color="accent" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label>Get Teacher Link...</q-item-label>
                            <q-item-label caption>Select teacher view</q-item-label>
                        </q-item-section>
                    </q-item>
                </q-list>
             </q-btn-dropdown>
        </div>

        <!-- Auto-Order Period Button -->
        <div class="col-12 col-sm-auto">
             <q-btn
                color="accent"
                icon="format_list_numbered"
                :label="t('common.autoOrder') || 'Auto-Order'"
                outline
                :disable="!selectedClassroomId"
                @click="handleAutoOrder"
                :loading="loadingAutoOrder"
             >
                <q-tooltip>Auto-fill period numbers (1, 2, 3...) for each subject</q-tooltip>
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
      </div>
      
      <q-separator class="q-my-md" />
      
      <!-- Advanced Filters -->
      <div class="row q-gutter-md items-center">
        <div class="col-12 col-md-auto">
            <q-select
                v-model="filterDays"
                :options="dayOptions"
                label="Days"
                outlined
                dense
                multiple
                emit-value
                map-options
                style="min-width: 150px"
            >
                <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                    <q-item v-bind="itemProps">
                    <q-item-section>
                        <q-item-label>{{ opt.label }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                        <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)" />
                    </q-item-section>
                    </q-item>
                </template>
                <template v-slot:selected>
                    <div v-if="filterDays.length === dayOptions.length">All Days</div>
                    <div v-else>{{ filterDays.length }} Days Selected</div>
                </template>
            </q-select>
        </div>
        
        <div class="col-12 col-md-auto">
             <q-select
                v-model="filterPeriods"
                :options="periodOptions"
                label="Periods"
                outlined
                dense
                multiple
                emit-value
                map-options
                style="min-width: 150px"
            >
             <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                    <q-item v-bind="itemProps">
                    <q-item-section>
                        <q-item-label>{{ opt.label }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                        <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)" />
                    </q-item-section>
                    </q-item>
                </template>
                <template v-slot:selected>
                    <div v-if="filterPeriods.length === periodOptions.length">All Periods</div>
                    <div v-else>{{ filterPeriods.length }} Periods</div>
                </template>
            </q-select>
        </div>

        <div class="col-12 col-md-3">
             <q-select
                v-model="filterSubjectIds"
                :options="subjects"
                option-value="id"
                option-label="name"
                label="Filter Subjects"
                outlined
                dense
                multiple
                emit-value
                map-options
                clearable
            >
                <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                    <q-item v-bind="itemProps">
                    <q-item-section>
                        <q-item-label>{{ opt.name }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                        <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)" />
                    </q-item-section>
                    </q-item>
                </template>
            </q-select>
        </div>

        <div class="col-12 col-md-3">
            <q-select
                v-model="filterTeacherIds"
                :options="teachers"
                option-value="id"
                option-label="name"
                label="Filter Teachers"
                outlined
                dense
                multiple
                emit-value
                map-options
                clearable
            >
               <template v-slot:option="{ itemProps, opt, selected, toggleOption }">
                    <q-item v-bind="itemProps">
                    <q-item-section>
                        <q-item-label>{{ opt.name }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                        <q-checkbox :model-value="selected" @update:model-value="toggleOption(opt)" />
                    </q-item-section>
                    </q-item>
                </template>
            </q-select>
        </div>
        
        <div class="col-auto">
             <q-btn flat round icon="restart_alt" color="grey" @click="resetFilters">
                <q-tooltip>Reset Filters</q-tooltip>
             </q-btn>
        </div>
        
        <!-- ... existing stats code ... -->

    <!-- Draft Management Panel -->
    <DraftManagementPanel
      v-model="showDraftManager"
      :schedule-data="schedules"
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
      :visible-days="filterDays"
      :visible-periods="filterPeriods"
      :filter-subject-ids="filterSubjectIds"
      :filter-teacher-ids="filterTeacherIds"
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
    />    <!-- Teacher Link Dialog -->
    <q-dialog v-model="showTeacherLinkDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Get Teacher Schedule Link</div>
        </q-card-section>

        <q-card-section>
          <q-select
            v-model="selectedTeacherForLink"
            :options="teachers"
            option-value="id"
            option-label="name"
            label="Select Teacher"
            outlined
            dense
            use-input
            hide-selected
            fill-input
            input-debounce="0"
            @filter="(val, update) => {
              update(() => {
                // Simple client-side filter if teachers list is loaded
                // For now assuming 'teachers' ref is available and populated.
                // If not, might need to rely on existing filter logic or backend search.
                // TimetableEditor has 'teachers' ref? Check script.
              })
            }"
            ref="teacherLinkSelect"
          >
             <template v-slot:no-option>
              <q-item>
                <q-item-section class="text-grey">
                  No results
                </q-item-section>
              </q-item>
            </template>
          </q-select>
          <div class="q-mt-sm text-caption text-grey">
            Select a teacher to generate a read-only schedule link.
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Open Page" color="secondary" @click="openSelectedTeacherPage" :disable="!selectedTeacherForLink" />
          <q-btn flat label="Copy Link" color="primary" @click="copySelectedTeacherLink" :disable="!selectedTeacherForLink" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'
import { route } from 'ziggy-js'  // Added import for route function
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

// Filters
const filterDays = ref([1, 2, 3, 4, 5]) // Default Sun-Thu
const filterPeriods = ref([1, 2, 3, 4, 5, 6, 7, 8])
const filterSubjectIds = ref([])
const filterTeacherIds = ref([])

const dayOptions = [
  { label: 'Sunday', value: 1 },
  { label: 'Monday', value: 2 },
  { label: 'Tuesday', value: 3 },
  { label: 'Wednesday', value: 4 },
  { label: 'Thursday', value: 5 },
  { label: 'Friday', value: 6 },
  { label: 'Saturday', value: 7 },
]

const periodOptions = Array.from({ length: 12 }, (_, i) => ({ label: `Period ${i+1}`, value: i+1 }))
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

const resetFilters = () => {
    filterDays.value = [1, 2, 3, 4, 5];
    filterPeriods.value = [1, 2, 3, 4, 5, 6, 7, 8];
    filterSubjectIds.value = [];
    filterTeacherIds.value = [];
}

// Methods

const fetchClassrooms = async () => {
  loadingClassrooms.value = true
  try {
    // Get school_id from URL query params if exists
    const urlParams = new URLSearchParams(window.location.search);
    const schoolId = urlParams.get('school');
    
    // Auto-select filter from URL
    const teacherIdParam = urlParams.get('teacher_id');
    if (teacherIdParam) {
        // Ensure it's an integer
        const tId = parseInt(teacherIdParam);
        if (!isNaN(tId)) {
            filterTeacherIds.value = [tId];
        }
    }
    
    const params = {};
    if (schoolId) {
        params.school_id = schoolId;
    }

    const response = await axios.get(`/api/classrooms`, { params })
    const result = response.data.data || response.data || []
    classrooms.value = Array.isArray(result) ? result : []
    
    // Check for classroom_id in URL
    const classroomIdParam = urlParams.get('classroom_id');
    let selectedFromUrl = false;
    
    if (classroomIdParam) {
        const cId = parseInt(classroomIdParam);
        const exists = classrooms.value.find(c => c.id === cId);
        if (exists) {
            selectedClassroomId.value = cId;
            selectedFromUrl = true;
        }
    }
    
    // Auto-select first classroom if not already set and not selected from URL
    if (classrooms.value.length && !selectedClassroomId.value && !selectedFromUrl) {
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
    const scheduleId = schedule.id
    if (!scheduleId || scheduleId.toString().startsWith('temp_')) {
        // If it's a temp slot or has no ID, just refresh/ignore
        await fetchSchedules()
        return
    }

    // Call update endpoint with null cst_id to clear/delete
    await axios.put(`/admin/schedules/${scheduleId}`, {
        cst_id: null,
        day: schedule.day_number || schedule.day, // Required validation fields even for delete? check controller
        period_number: schedule.period_number
    })
    
    $q.notify({ type: 'positive', message: 'Slot cleared successfully' })
    await fetchSchedules() // Refresh to sync state
  } catch (error) {
    console.error('Error clearing schedule:', error)
    $q.notify({ type: 'negative', message: 'Failed to clear slot' })
  }
}

const handleAssignSubmit = async (formData) => {
  saving.value = true
  try {
    const day = formData.day
    const period = formData.period 
    
    // Check if updating existing or creating new
    // We need to find if there is an existing schedule ID for this slot
    const existingSchedule = schedules.value.find(s => {
      const sDay = s.day_number || s.day
      return sDay == day && s.period_number == period
    })
    
    const payload = {
        cst_id: formData.cst_id,
        day: day,
        period_number: period,
        teacher_substitute_id: formData.teacher_substitute_id,
        co_teacher_id: formData.co_teacher_id,
        co_subject_id: formData.co_subject_id,
        notes: formData.notes,
        school_id: new URLSearchParams(window.location.search).get('school') // Ensure school context
    }

    if (existingSchedule && existingSchedule.id && !existingSchedule.id.toString().startsWith('temp_')) {
        // Update existing
        await axios.put(`/admin/schedules/${existingSchedule.id}`, payload)
    } else {
        // Create new
        await axios.post('/admin/schedules/store', payload)
    }
    
    $q.notify({ type: 'positive', message: 'Schedule updated successfully' })
    showAssignDialog.value = false
    await fetchSchedules() // Refresh state from backend
  } catch (error) {
    console.error('Error saving schedule:', error)
    const msg = error.response?.data?.message || 'Failed to update schedule'
    $q.notify({ type: 'negative', message: msg })
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


const loadingAutoOrder = ref(false)

const handleAutoOrder = async () => {
    if (!selectedClassroomId.value) return
    
    loadingAutoOrder.value = true
    try {
        await axios.post('/admin/schedules/auto-fill-orders', {
            classroom_id: selectedClassroomId.value
        })
        
        $q.notify({
            type: 'positive', 
            message: 'Period orders updated successfully',
            position: 'top'
        })
        
        // Refresh grid
        await fetchSchedules()
    } catch (error) {
        console.error('Error auto-filling orders:', error)
        $q.notify({
            type: 'negative',
            message: 'Failed to update Period orders'
        })
    } finally {
        loadingAutoOrder.value = false
    }
}

// Link Management
const showTeacherLinkDialog = ref(false)
const selectedTeacherForLink = ref(null)

const openClassroomPage = () => {
    if (!selectedClassroomId.value) return
    
    // Get classroom name and create URL-safe slug
    const classroom = classrooms.value.find(c => c.id === selectedClassroomId.value)
    const classroomName = classroom?.name || 'classroom'
    const classroomSlug = classroomName
      .toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with hyphens
      .replace(/[^\w\-]+/g, '')       // Remove non-word chars except hyphens
      .replace(/\-\-+/g, '-')         // Replace multiple hyphens with single hyphen
      .replace(/^-+/, '')             // Trim hyphens from start
      .replace(/-+$/, '');            // Trim hyphens from end
    
    // Generate URL
    const url = route('schedules.classroom.view', { 
        classroom_id: selectedClassroomId.value,
        classroom_name: classroomSlug
    })
    
    window.open(url, '_blank')
}

const openSelectedTeacherPage = () => {
    if (!selectedTeacherForLink.value) return
    
    const teacher = typeof selectedTeacherForLink.value === 'object' 
        ? selectedTeacherForLink.value 
        : teachers.value.find(t => t.id === selectedTeacherForLink.value)
        
    if (!teacher) return

    const teacherName = teacher.name || 'teacher'
    const teacherSlug = teacherName
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-')
      .replace(/^-+/, '')
      .replace(/-+$/, '')
    
    const url = route('schedules.teacher.view', { 
        teacher_id: teacher.id,
        teacher_name: teacherSlug
    })
    
    window.open(url, '_blank')
    
    showTeacherLinkDialog.value = false
    selectedTeacherForLink.value = null
}

const copySelectedTeacherLink = () => {
    if (!selectedTeacherForLink.value) return
    
    const teacher = typeof selectedTeacherForLink.value === 'object' 
        ? selectedTeacherForLink.value 
        : teachers.value.find(t => t.id === selectedTeacherForLink.value)
        
    if (!teacher) return

    const teacherName = teacher.name || 'teacher'
    const teacherSlug = teacherName
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^\w\-]+/g, '')
      .replace(/\-\-+/g, '-')
      .replace(/^-+/, '')
      .replace(/-+$/, '')
    
    const url = route('schedules.teacher.view', { 
        teacher_id: teacher.id,
        teacher_name: teacherSlug
    })
    
    navigator.clipboard.writeText(url).then(() => {
        $q.notify({
            type: 'positive',
            message: 'Teacher schedule link copied!',
            position: 'top',
            timeout: 2000
        })
        showTeacherLinkDialog.value = false
        selectedTeacherForLink.value = null
    })
}

const copyClassroomLink = () => {
    if (!selectedClassroomId.value) return
    
    // Get classroom name and create URL-safe slug
    const classroom = classrooms.value.find(c => c.id === selectedClassroomId.value)
    const classroomName = classroom?.name || 'classroom'
    const classroomSlug = classroomName
      .toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with hyphens
      .replace(/[^\w\-]+/g, '')       // Remove non-word chars except hyphens
      .replace(/\-\-+/g, '-')         // Replace multiple hyphens with single hyphen
      .replace(/^-+/, '')             // Trim hyphens from start
      .replace(/-+$/, '');            // Trim hyphens from end
    
    // Generate student schedule view URL with optional slug
    const url = route('schedules.classroom.view', { 
        classroom_id: selectedClassroomId.value,
        classroom_name: classroomSlug
    })
    
    navigator.clipboard.writeText(url).then(() => {
        $q.notify({
            type: 'positive',
            message: 'Student schedule link copied to clipboard!',
            position: 'top',
            timeout: 2000
        })
    })
}
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
