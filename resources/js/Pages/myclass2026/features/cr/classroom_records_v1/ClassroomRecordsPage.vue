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
 * Handle score updates from StudentCard
 */
const handleScoreUpdate = (updateData) => {
  if (isReadonly.value) return;

  // 1. Optimistic update: Update local state immediately
  const student = sessionData.value?.students.find(
    s => s.student_period_id === updateData.student_period_id
  );
  
  if (student) {
    const score = student.scores.find(
      s => s.mapping_id === updateData.mapping_id
    );
    
    if (score) {
      // Update score immediately
      score.numeric_value = updateData.numeric_value;
      
      // Recalculate total score
      recalculateTotal(student);
    }
  }

  // 2. Mark dirty for background save
  markDirty(updateData.student_period_id, {
    student_period_id: updateData.student_period_id,
    scores: [{
      mapping_id: updateData.mapping_id,
      numeric_value: updateData.numeric_value,
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
    student.period.attendance_status = updateData.attendance_status;
    
    // If absent, zero out all scores and lock
    if (updateData.attendance_status === 'absent') {
      student.period.attendance_score = 0;
      student.period.locked = true;
      student.scores.forEach(score => score.numeric_value = 0);
      student.period.total_score = 0;
    } else {
      // If changing away from absent, unlock and reset to defaults
      if (student.period.locked) {
        student.period.locked = false;
        student.period.attendance_score = 5;
        student.scores.forEach(score => score.numeric_value = 5);
        recalculateTotal(student);
      }
    }
  }

  // 2. Mark dirty for background save
  markDirty(updateData.student_period_id, {
    student_period_id: updateData.student_period_id,
    attendance_status: updateData.attendance_status,
  });
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
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Classroom Records
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Track student performance and attendance in real-time
        </p>
      </div>

      <!-- Session Context Bar -->
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

      <!-- Load Session Button -->
      <div v-if="!contextReady && !loading" class="mb-4 flex justify-end">
        <button
          @click="loadSession"
          :disabled="!store.sessionContext.classroom_id || !store.sessionContext.subject_id"
          class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-md transition-colors shadow-sm flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>Load Session</span>
        </button>
      </div>

      <!-- Save Status Indicator -->
      <div v-if="contextReady && !isReadonly" class="mb-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <span class="text-sm text-gray-600 dark:text-gray-400">
            {{ saveStatus }}
          </span>
          <span
            v-if="isSaving"
            class="inline-block w-4 h-4 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"
          ></span>
        </div>
        <div v-if="lastSavedAt" class="text-sm text-gray-500 dark:text-gray-400">
          Last saved: {{ lastSavedAt }}
        </div>
      </div>

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

      <!-- Student Cards Grid -->
      <div v-else-if="contextReady && sessionData?.students" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <StudentCard
          v-for="student in sessionData.students"
          :key="student.student_period_id"
          :student="student"
          :session="sessionData.session"
          :read-only="isReadonly"
          @update="markDirty"
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
