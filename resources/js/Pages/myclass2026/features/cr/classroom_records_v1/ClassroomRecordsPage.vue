<script setup>
/**
 * ClassroomRecordsPage - Main page for tracking student classroom records
 * 
 * Features:
 * - Session context selection (or readonly display)
 * - Student card grid with interactive scoring
 * - Auto-save with debouncing
 * - Loading and error states
 * - Admin read-only mode support
 */

import { ref, computed, onMounted, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { useClassroomRecordsStore } from '@/stores/classroomRecords';
import SessionContextBar from './components/SessionContextBar.vue';
import StudentCard from './components/StudentCard.vue';
import StudentCardV2 from './components/StudentCardV2.vue';
import StudentTable from './components/StudentTable.vue';
import StudentTableFilters from './components/StudentTableFilters.vue';
import CrCategoryMappingsManager from './components/CrCategoryMappingsManager.vue';
import { useDirtyBatch } from './composables/useDirtyBatch';

// Props from Inertia
const props = defineProps({
  // Passed when opened from teacher schedule (deep link)
  initialContext: {
    type: Object,
    default: null,
  },
  // Whether user is admin (read-only mode)
  isAdmin: {
    type: Boolean,
    default: false,
  },
  // Available options for standalone mode
  classrooms: {
    type: Array,
    default: () => [],
  },
  subjects: {
    type: Array,
    default: () => [],
  },
  grades: {
    type: Array,
    default: () => [],
  },
  // Resolved teacher_id from backend (security fix)
  teacherId: {
    type: Number,
    default: null,
  },
  // Academic context for period code generation
  academicContext: {
    type: Object,
    default: () => ({
      year_id: 1,
      semester: 1,
    }),
  },
});

// Use Pinia store
const store = useClassroomRecordsStore();

// Dialog/tab state
const actionsOpen = ref(false);
const dialogOpen = ref(false);
const maximized = ref(true);
const activeTab = ref('session');

// Card version preference (saved in localStorage)
const cardVersion = ref('v2');

// Card V2 style preferences (saved in localStorage)
const useRandomAvatar = ref(true);
const showBadgeOnCard = ref(true);
const scoreLabelFormat = ref('with-label');
const cardSize = ref('standard');
const nameFormat = ref('first');

// Load card version preference on mount
onMounted(() => {
  // Load card version
  const savedVersion = localStorage.getItem('classroomRecordsCardVersion');
  if (savedVersion && ['v1', 'v2'].includes(savedVersion)) {
    cardVersion.value = savedVersion;
  }
  
  // Load V2 card style preferences
  const savedAvatar = localStorage.getItem('cr_useRandomAvatar');
  if (savedAvatar !== null) {
    useRandomAvatar.value = savedAvatar === 'true';
  }
  
  const savedBadge = localStorage.getItem('cr_showBadgeOnCard');
  if (savedBadge !== null) {
    showBadgeOnCard.value = savedBadge === 'true';
  }
  
  const savedScoreFormat = localStorage.getItem('cr_scoreLabelFormat');
  if (savedScoreFormat && ['with-label', 'number-only'].includes(savedScoreFormat)) {
    scoreLabelFormat.value = savedScoreFormat;
  }
  
  const savedCardSize = localStorage.getItem('cr_cardSize');
  if (savedCardSize && ['compact', 'standard', 'large'].includes(savedCardSize)) {
    cardSize.value = savedCardSize;
  }
  
  const savedNameFormat = localStorage.getItem('cr_nameFormat');
  if (savedNameFormat && ['first', 'firstSecond', 'firstLast', 'full'].includes(savedNameFormat)) {
    nameFormat.value = savedNameFormat;
  }
});

// Save card version preference when changed
const setCardVersion = (version) => {
  cardVersion.value = version;
  localStorage.setItem('classroomRecordsCardVersion', version);
};

// Save V2 card style preferences
const saveCardStylePreferences = () => {
  localStorage.setItem('cr_useRandomAvatar', useRandomAvatar.value);
  localStorage.setItem('cr_showBadgeOnCard', showBadgeOnCard.value);
  localStorage.setItem('cr_scoreLabelFormat', scoreLabelFormat.value);
  localStorage.setItem('cr_cardSize', cardSize.value);
  localStorage.setItem('cr_nameFormat', nameFormat.value);
};

// Get session label helper function
const getSessionLabel = (type) => {
  const options = store.options;
  const context = store.sessionContext;
  
  if (type === 'classroom') {
    const classroom = options.classrooms?.find(c => c.value === context.classroom_id);
    return classroom?.label || `Classroom #${context.classroom_id}`;
  }
  
  if (type === 'subject') {
    const subject = options.subjects?.find(s => s.value === context.subject_id);
    return subject?.label || `Subject #${context.subject_id}`;
  }
  
  if (type === 'teacher') {
    const teacher = options.teachers?.find(t => t.value === context.teacher_id);
    return teacher?.label || `Teacher #${context.teacher_id}`;
  }
  
  return 'N/A';
};

const openDialog = (tab) => {
  actionsOpen.value = false;
  activeTab.value = tab;
  dialogOpen.value = true;
};

const closeDialog = () => {
  activeTab.value = 'session';
  dialogOpen.value = false;
};

// Initialize store from props on mount
onMounted(() => {
  console.log('🔍 CR Page Props:', {
    classrooms: props.classrooms,
    subjects: props.subjects,
    isAdmin: props.isAdmin,
    teacherId: props.teacherId,
    initialContext: props.initialContext,
  });
  
  // Set available options in store
  store.setOptions(props.classrooms, props.subjects);
  
  // Set initial teacher_id if available
  if (props.teacherId) {
    store.updateContextField('teacher_id', props.teacherId);
  }
});

// Watch for context changes and trigger auto-save
watch(() => store.sessionContext, () => {
  if (contextReady.value && hasUnsavedChanges.value) {
    forceSave();
  }
}, { deep: true });

// Watch for card style preference changes and auto-save
watch([useRandomAvatar, showBadgeOnCard, scoreLabelFormat, cardSize, nameFormat], () => {
  saveCardStylePreferences();
}, { deep: true });

/**
 * Manually load session (button click)
 */
const loadSession = () => {
  console.log(' Load Session button clicked');
  if (!store.sessionContext.classroom_id || !store.sessionContext.subject_id) {
    store.setError('Please select a classroom and subject first');
    return;
  }
  
  // Clear any previous session
  store.clearSession();
  
  // Trigger context-ready manually
  handleContextReady();
};

// Computed properties from store
const loading = computed(() => store.loading);
const error = computed(() => store.error);
const sessionData = computed(() => store.sessionData);
const contextReady = computed(() => store.contextReady);

// Edit mode for enabling/disabling editing
const editMode = ref(false);

// View mode: cards or table
const viewMode = ref('cards');

// Filter for student table
const studentFilter = ref('all');

// Determine mode
const isStandalone = computed(() => !props.initialContext);
// Admin is readonly ONLY when viewing from schedule (not in standalone mode)
const isReadonly = computed(() => props.isAdmin && !isStandalone.value);

// Use dirty batch composable
const {
  markDirty,
  hasUnsavedChanges,
  saveStatus,
  isSaving,
  lastSavedAt,
  forceSave,
} = useDirtyBatch({
  debounceDelay: 1500,
  autoSave: true,
  enableUnloadProtection: true,
});

/**
 * Initialize session from API
 */
const initSession = async () => {
  console.log('🔍 initSession called with store context:', {
    classroom_id: store.sessionContext.classroom_id,
    subject_id: store.sessionContext.subject_id,
    teacher_id: store.sessionContext.teacher_id,
    date: store.sessionContext.date,
    period_code: store.sessionContext.period_code,
    day_number: store.sessionContext.day_number,
    period_number: store.sessionContext.period_number,
  });
  
  if (!store.sessionContext.classroom_id || !store.sessionContext.subject_id) {
    console.log('⚠️ initSession aborted: missing classroom or subject');
    return;
  }

  store.setLoading(true);
  store.setError(null);

  try {
    console.log('📡 Calling /api/cr/init-session...');
    const response = await axios.post('/api/cr/init-session', {
      classroom_id: store.sessionContext.classroom_id,
      subject_id: store.sessionContext.subject_id,
      teacher_id: store.sessionContext.teacher_id,
      date: store.sessionContext.date,
      period_code: store.sessionContext.period_code,
      day_number: store.sessionContext.day_number,
      period_number: store.sessionContext.period_number,
    }, {
      timeout: 10000, // 10 second timeout
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    });

    console.log('✅ Session loaded:', response.data);
    store.setSessionData(response.data);
    store.setLoading(false);
  } catch (err) {
    console.error('❌ initSession failed:', err);
    console.error('Error details:', {
      message: err.message,
      response: err.response?.data,
      status: err.response?.status,
    });
    store.setError(err.response?.data?.error || err.message || 'Failed to load session');
    store.setLoading(false);
  }
};

/**
 * Handle context ready (all fields filled)
 */
const handleContextReady = () => {
  console.log('🚀 handleContextReady triggered');
  initSession();
};

/**
 * Handle score updates from StudentCard / StudentTable
 */
const handleScoreUpdate = (updateData) => {
  if (isReadonly.value) return;

  const { student_period_id, mapping_id, numeric_value } = updateData;
  if (!student_period_id || typeof numeric_value === 'undefined') return;

  // 1. Optimistic update: Update local state immediately
  const student = sessionData.value?.students.find(
    s => s.student_period_id === student_period_id
  );
  
  if (student) {
    const score = student.scores.find(
      s => s.mapping_id === mapping_id
    );
    
    if (score) {
      score.numeric_value = numeric_value;
      recalculateTotal(student);
    }
  }

  // 2. Mark dirty for background save
  markDirty(student_period_id, {
    student_period_id,
    scores: [{
      mapping_id,
      numeric_value,
    }],
  });
};

/**
 * Handle attendance updates
 */
const handleAttendanceUpdate = (updateData) => {
  if (isReadonly.value) return;

  // 1. Optimistic update
  const student = sessionData.value?.students.find(
    s => s.student_period_id === updateData.student_period_id
  );
  
  if (student) {
    const wasAbsent = student.period.attendance_status === 'absent';
    
    if (typeof updateData.attendance_status !== 'undefined') {
      student.period.attendance_status = updateData.attendance_status;
    }
    if (typeof updateData.attendance_score !== 'undefined' && student.period.attendance_status !== 'absent') {
      student.period.attendance_score = updateData.attendance_score;
    }
    
    // If absent, zero out all scores and lock
    if (updateData.attendance_status === 'absent') {
      student.period.attendance_score = 0;
      student.period.locked = true;
      student.scores.forEach(score => score.numeric_value = 0);
      student.period.total_score = 0;
    } else {
      // If changing away from absent, unlock
      if (wasAbsent && student.period.locked) {
        student.period.locked = false;
      }
      // Always recalculate total when not absent
      recalculateTotal(student);
    }
  }

  // 2. Mark dirty for background save
  const dirtyPayload = {
    student_period_id: updateData.student_period_id,
  };

  if (typeof updateData.attendance_status !== 'undefined') {
    dirtyPayload.attendance_status = updateData.attendance_status;
  }
  if (typeof updateData.attendance_score !== 'undefined') {
    dirtyPayload.attendance_score = updateData.attendance_score;
  }

  markDirty(updateData.student_period_id, dirtyPayload);
};

/**
 * Quick mark as absent
 */
const handleMarkAbsent = (updateData) => {
  if (isReadonly.value) return;

  // Optimistic update handled by handleAttendanceUpdate
  handleAttendanceUpdate({
    student_period_id: updateData.student_period_id,
    attendance_status: 'absent',
  });
};

/**
 * Recalculate total score for a student
 */
const recalculateTotal = (student) => {
  const attendanceScore = student.period.attendance_score || 0;
  const categoryScoresSum = student.scores.reduce(
    (sum, score) => sum + (score.numeric_value || 0),
    0
  );
  student.period.total_score = attendanceScore + categoryScoresSum;
};

/**
 * Filter students based on selected filter
 */
const filteredStudents = computed(() => {
  if (!sessionData.value?.students) return [];
  
  const students = sessionData.value.students;
  
  // If 'all' or no filter, return all students
  if (studentFilter.value === 'all') {
    return students;
  }
  
  // Check if filter is an attendance status
  if (['present', 'absent'].includes(studentFilter.value)) {
    return students.filter(student => 
      student.period?.attendance_status === studentFilter.value
    );
  }
  
  // Otherwise, filter by first letter
  return students.filter(student => {
    const firstLetter = student.name?.charAt(0)?.toUpperCase() || '#';
    if (studentFilter.value === '#') {
      return !firstLetter.match(/[A-Z]/);
    }
    return firstLetter === studentFilter.value;
  });
});

/**
 * Retry loading after error
 */
const retryLoad = () => {
  error.value = null;
  initSession();
};
</script>

<template>
  <Head title="Classroom Records" />

  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Classroom Records
          </h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Track student performance and attendance in real-time
          </p>
          
          <!-- Session Info -->
          <div v-if="store.sessionContext.classroom_id && store.sessionContext.subject_id" class="mt-3 flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <q-icon name="mdi-school" size="sm" class="text-primary" />
              <div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Classroom</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ getSessionLabel('classroom') }}
                </div>
              </div>
            </div>
            
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <q-icon name="mdi-book-open-page-variant" size="sm" class="text-primary" />
              <div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Subject</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ getSessionLabel('subject') }}
                </div>
              </div>
            </div>
            
            <div v-if="store.sessionContext.teacher_id" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
              <q-icon name="mdi-account" size="sm" class="text-primary" />
              <div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Teacher</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                  {{ getSessionLabel('teacher') }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <q-btn-dropdown
            v-model="actionsOpen"
            unelevated
            color="primary"
            icon="mdi-dots-vertical"
            label="Actions"
            auto-close
            dense
            no-caps
            flat
          >
            <q-list class="min-w-[240px]">
              <!-- View Mode Section -->
              <q-item-label header class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                View Mode
              </q-item-label>
              <q-item clickable @click="viewMode = 'cards'">
                <q-item-section avatar>
                  <q-icon name="mdi-view-grid" :color="viewMode === 'cards' ? 'primary' : ''" />
                </q-item-section>
                <q-item-section>
                  <q-item-label :class="{ 'text-primary font-bold': viewMode === 'cards' }">Cards View</q-item-label>
                </q-item-section>
                <q-item-section side v-if="viewMode === 'cards'">
                  <q-icon name="check" color="primary" />
                </q-item-section>
              </q-item>
              <q-item clickable @click="viewMode = 'table'">
                <q-item-section avatar>
                  <q-icon name="mdi-view-list" :color="viewMode === 'table' ? 'primary' : ''" />
                </q-item-section>
                <q-item-section>
                  <q-item-label :class="{ 'text-primary font-bold': viewMode === 'table' }">Table View</q-item-label>
                </q-item-section>
                <q-item-section side v-if="viewMode === 'table'">
                  <q-icon name="check" color="primary" />
                </q-item-section>
              </q-item>
              
              <q-separator class="my-2" />
              
              <!-- Edit Mode Section -->
              <q-item-label header class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                Edit Mode
              </q-item-label>
              <q-item clickable @click="editMode = !editMode" :disable="isReadonly">
                <q-item-section avatar>
                  <q-icon :name="editMode ? 'mdi-pencil' : 'mdi-pencil-off'" :color="editMode ? 'primary' : ''" />
                </q-item-section>
                <q-item-section>
                  <q-item-label :class="{ 'text-primary font-bold': editMode }">
                    {{ editMode ? 'Editing Enabled' : 'Editing Disabled' }}
                  </q-item-label>
                  <q-item-label caption>
                    {{ isReadonly ? 'Read-only mode' : editMode ? 'Click to disable editing' : 'Click to enable editing' }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side v-if="editMode">
                  <q-icon name="check" color="primary" />
                </q-item-section>
              </q-item>
              
              <q-separator class="my-2" />
              
              <!-- Session Actions Section -->
              <q-item-label header class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                Session
              </q-item-label>
              <q-item clickable @click="openDialog('session')">
                <q-item-section>
                  <div class="text-sm font-semibold">Change session</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Select classroom / subject</div>
                </q-item-section>
              </q-item>
              <q-item clickable @click="openDialog('categories')">
                <q-item-section>
                  <div class="text-sm font-semibold">Manage categories</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">Configure scoring categories</div>
                </q-item-section>
              </q-item>
              
              <q-separator class="my-2" />
              
              <!-- Card Version Section -->
              <q-item-label header class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">
                Card Style
              </q-item-label>
              <q-item clickable @click="setCardVersion('v1')">
                <q-item-section avatar>
                  <q-icon name="mdi-card-outline" :color="cardVersion === 'v1' ? 'primary' : ''" />
                </q-item-section>
                <q-item-section>
                  <q-item-label :class="{ 'text-primary font-bold': cardVersion === 'v1' }">Classic Card (V1)</q-item-label>
                  <q-item-label caption>Traditional layout with all details visible</q-item-label>
                </q-item-section>
                <q-item-section side v-if="cardVersion === 'v1'">
                  <q-icon name="check" color="primary" />
                </q-item-section>
              </q-item>
              <q-item clickable @click="setCardVersion('v2')">
                <q-item-section avatar>
                  <q-icon name="mdi-card-account-details-outline" :color="cardVersion === 'v2' ? 'primary' : ''" />
                </q-item-section>
                <q-item-section>
                  <q-item-label :class="{ 'text-primary font-bold': cardVersion === 'v2' }">Minimal Card (V2)</q-item-label>
                  <q-item-label caption>Clean design with avatar and badge</q-item-label>
                </q-item-section>
                <q-item-section side v-if="cardVersion === 'v2'">
                  <q-icon name="check" color="primary" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>
      </div>

      <!-- Configuration dialog -->
      <q-dialog v-model="dialogOpen" persistent :maximized="maximized">
        <q-card :class="maximized ? 'h-full flex flex-col' : 'w-[min(1000px,95vw)] max-h-[90vh] flex flex-col'">
          <!-- Header -->
          <q-card-section class="shrink-0 pb-2 flex items-start justify-between bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
            <div>
              <div class="text-xl font-bold text-gray-900 dark:text-white">Classroom Records</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">
                Manage session settings and scoring configuration.
              </div>
            </div>
            <div class="flex items-center gap-1">
              <q-btn
                flat
                round
                dense
                :icon="maximized ? 'fullscreen_exit' : 'fullscreen'"
                @click="maximized = !maximized"
                class="text-gray-400 hover:text-primary transition-colors"
                :title="maximized ? 'Restore' : 'Maximize'"
              />
              <q-btn
                flat
                round
                dense
                icon="close"
                v-close-popup
                class="text-gray-400 hover:text-red-500 transition-colors"
                @click="dialogOpen = false"
              />
            </div>
          </q-card-section>

          <!-- Tabs (Non-scrollable) -->
          <q-card-section class="shrink-0 py-0 px-4 bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800">
            <q-tabs v-model="activeTab" class="text-primary" align="left" dense narrow-indicator>
              <q-tab name="session" label="Session" />
              <q-tab name="options" label="Options" />
              <q-tab name="categories" label="Categories" />
            </q-tabs>
          </q-card-section>

          <!-- Content (Scrollable) -->
          <q-card-section class="flex-1 overflow-auto p-0">
            <q-tab-panels v-model="activeTab" animated class="bg-transparent h-full">
              <q-tab-panel name="session" class="p-4 sm:p-6 pb-20">
                <div class="max-w-4xl mx-auto space-y-6">
                  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                    <SessionContextBar
                      v-model="store.sessionContext"
                      :mode="isStandalone ? 'interactive' : 'readonly'"
                      :source="isStandalone ? 'standalone' : 'teacher_schedule'"
                      :read-only="isReadonly"
                      :options="{
                        classrooms: store.options.classrooms,
                        subjects: store.options.subjects,
                      }"
                      :academic-context="academicContext"
                      @context-ready="handleContextReady"
                    />

                    <div class="mt-8 flex justify-end">
                      <q-btn
                        label="Load Session"
                        color="primary"
                        unelevated
                        size="lg"
                        class="px-8 rounded-lg font-bold"
                        @click="loadSession"
                        :disable="!store.sessionContext.classroom_id || !store.sessionContext.subject_id || loading"
                      />
                    </div>
                  </div>
                </div>
              </q-tab-panel>

              <q-tab-panel name="options" class="p-4 sm:p-6">
                <div class="max-w-2xl mx-auto space-y-8">
                  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                      <div class="text-sm">
                        <div class="font-bold text-gray-900 dark:text-gray-100">Save status</div>
                        <div class="text-gray-500 dark:text-gray-400">{{ saveStatus }}</div>
                      </div>
                      <div class="flex items-center gap-2">
                        <span v-if="isSaving" class="inline-block w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                      </div>
                    </div>

                    <div>
                      <div class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3">View mode</div>
                      <q-option-group
                        v-model="viewMode"
                        :options="[
                          { label: 'Cards View', value: 'cards' },
                          { label: 'Table View', value: 'table' },
                        ]"
                        type="radio"
                        inline
                        class="text-gray-700 dark:text-gray-300"
                      />
                    </div>

                    <!-- Card Style Options -->
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-800">
                      <div class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Card Style (V2)
                      </div>
                      
                      <div class="space-y-4">
                        <!-- Avatar Style -->
                        <div>
                          <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Avatar Display
                          </div>
                          <q-toggle
                            v-model="useRandomAvatar"
                            label="Use random cute avatars"
                            hint="When enabled, each student gets a random cute avatar. When disabled, shows colored initials."
                          />
                        </div>

                        <!-- Badge Display -->
                        <div>
                          <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Badge Display
                          </div>
                          <q-toggle
                            v-model="showBadgeOnCard"
                            label="Show student number badge"
                            hint="Display student number as a badge on the avatar"
                          />
                        </div>

                        <!-- Score Label Format -->
                        <div>
                          <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Score Label Format
                          </div>
                          <q-option-group
                            v-model="scoreLabelFormat"
                            :options="[
                              { label: 'Score: 15', value: 'with-label' },
                              { label: '15 (just number)', value: 'number-only' }
                            ]"
                            type="radio"
                            inline
                          />
                        </div>

                        <!-- Card Size -->
                        <div>
                          <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Card Size
                          </div>
                          <q-option-group
                            v-model="cardSize"
                            :options="[
                              { label: 'Compact', value: 'compact' },
                              { label: 'Standard', value: 'standard' },
                              { label: 'Large', value: 'large' }
                            ]"
                            type="radio"
                            inline
                          />
                        </div>

                        <!-- Name Format -->
                        <div>
                          <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Name Display Format
                          </div>
                          <q-option-group
                            v-model="nameFormat"
                            :options="[
                              { label: 'First name only', value: 'first' },
                              { label: 'First + Second name', value: 'firstSecond' },
                              { label: 'First + Last name', value: 'firstLast' },
                              { label: 'Full name', value: 'full' }
                            ]"
                            type="radio"
                            inline
                          />
                        </div>
                      </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                      <div class="text-xs text-gray-500 flex items-center gap-2">
                        <q-icon name="info" size="xs" />
                        Edit mode is now available from the header (pencil icon) for quicker access.
                      </div>
                      <div v-if="isReadonly" class="text-xs text-orange-500 mt-2 flex items-center gap-2 font-medium">
                        <q-icon name="warning" size="xs" />
                        Read-only mode is enabled (viewing from schedule).
                      </div>
                    </div>
                  </div>
                </div>
              </q-tab-panel>

              <q-tab-panel name="categories" class="p-4 sm:p-6">
                <div class="max-w-6xl mx-auto">
                  <CrCategoryMappingsManager
                    :read-only="isReadonly"
                    :options="{
                      grades: props.grades,
                      subjects: store.options.subjects,
                    }"
                    @updated="loadSession"
                  />
                </div>
              </q-tab-panel>
            </q-tab-panels>
          </q-card-section>

          <!-- Footer Actions (Maximized footer) -->
          <q-card-actions v-if="!maximized" align="right" class="shrink-0 p-4 border-t border-gray-100 dark:border-gray-800">
            <q-btn flat label="Close" color="primary" v-close-popup @click="dialogOpen = false" class="px-6" />
          </q-card-actions>
          <div v-else class="shrink-0 p-4 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 flex justify-end">
             <q-btn unelevated label="Finish Configuration" color="primary" v-close-popup @click="dialogOpen = false" class="px-8 font-bold rounded-lg" />
          </div>
        </q-card>
      </q-dialog>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="text-center">
          <div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
          <p class="mt-4 text-gray-600 dark:text-gray-400">Loading session data...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
              {{ error }}
            </h3>
          </div>
        </div>
      </div>

      <!-- Student View -->
      <div v-else-if="contextReady && sessionData?.students">
        <!-- Student Table Filters - Always Visible -->
        <StudentTableFilters
          v-model="studentFilter"
          :students="sessionData.students"
        />
        
        <div
          v-if="viewMode === 'cards'"
          class="flex flex-wrap items-center gap-2"
        >
          <!-- Render V1 or V2 based on user preference -->
          <template v-if="cardVersion === 'v1'">
            <StudentCard 
              v-for="student in filteredStudents"
              :key="student.student_period_id + '-v1'"
              :student="student"
              :period="student.period"
              :scores="student.scores"
              :categories="sessionData.mappings"
              :read-only="isReadonly"
              :edit-mode="editMode"
              @update:scores="handleScoreUpdate"
              @update:attendance="handleAttendanceUpdate"
              @mark-absent="handleMarkAbsent"
            />
          </template>
          
          <template v-else>
            <StudentCardV2 class="min-w-[12rem] max-w-[12rem]"
              v-for="student in filteredStudents"
              :key="student.student_period_id + '-v2'"
              :student="student"
              :period="student.period"
              :scores="student.scores"
              :categories="sessionData.mappings"
              :badge-number="student.student_id || student.student_period_id"
              :show-badge="showBadgeOnCard"
              :use-random-avatar="useRandomAvatar"
              :show-badge-on-card="showBadgeOnCard"
              :score-label-format="scoreLabelFormat"
              :card-size="cardSize"
              :name-format="nameFormat"
              :read-only="isReadonly"
              :edit-mode="editMode"
              @update:scores="handleScoreUpdate"
              @update:attendance="handleAttendanceUpdate"
              @mark-absent="handleMarkAbsent"
            />
          </template>
        </div>

        <StudentTable
          v-else
          :students="filteredStudents"
          :categories="sessionData.mappings"
          :read-only="isReadonly"
          :edit-mode="editMode"
          @update:scores="handleScoreUpdate"
          @update:attendance="handleAttendanceUpdate"
          @mark-absent="handleMarkAbsent"
        />
      </div>

      <!-- No Students Message -->
      <div v-else-if="contextReady" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
          No students found for this session.
        </p>
      </div>
      
      <!-- No Filter Results Message -->
      <div v-else-if="filteredStudents.length === 0 && studentFilter !== 'all'" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
          No students match the current filter: "{{ studentFilter }}"
        </p>
        <button
          @click="studentFilter = 'all'"
          class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
        >
          Clear Filter
        </button>
      </div>

      <!-- Welcome Message -->
      <div v-else class="text-center py-12">
        <p class="text-lg text-gray-600 dark:text-gray-400">
          Select a classroom and subject to view student records.
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom animations */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
