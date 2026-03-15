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

import { ref, reactive, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
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

// State
const loading = ref(false);
const error = ref(null);
const sessionData = ref(null);
const contextReady = ref(false);

// DEBUG: Log props on mount
onMounted(() => {
  console.log('🔍 CR Page Props:', {
    classrooms: props.classrooms,
    subjects: props.subjects,
    isAdmin: props.isAdmin,
    teacherId: props.teacherId,
    initialContext: props.initialContext,
  });
});

// Context form
const contextForm = reactive({
  classroom_id: props.initialContext?.classroom_id || null,
  subject_id: props.initialContext?.subject_id || null,
  teacher_id: props.teacherId || props.initialContext?.teacher_id || null, // Use resolved teacher_id
  date: props.initialContext?.date || new Date().toISOString().split('T')[0],
  day_number: props.initialContext?.day_number || 1,
  period_number: props.initialContext?.period_number || 1,
  period_code: props.initialContext?.period_code || '',
});

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
  console.log('🔍 initSession called:', {
    classroom_id: contextForm.classroom_id,
    subject_id: contextForm.subject_id,
    teacher_id: contextForm.teacher_id,
    date: contextForm.date,
    period_code: contextForm.period_code,
    day_number: contextForm.day_number,
    period_number: contextForm.period_number,
  });
  
  if (!contextForm.classroom_id || !contextForm.subject_id) {
    console.log('⚠️ initSession aborted: missing classroom or subject');
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    console.log('📡 Calling /api/cr/init-session...');
    const response = await axios.post('/api/cr/init-session', {
      classroom_id: contextForm.classroom_id,
      subject_id: contextForm.subject_id,
      teacher_id: contextForm.teacher_id,
      date: contextForm.date,
      period_code: contextForm.period_code,
      day_number: contextForm.day_number,
      period_number: contextForm.period_number,
    });

    console.log('✅ Session loaded:', response.data);
    sessionData.value = response.data;
    contextReady.value = true;
    loading.value = false;
  } catch (err) {
    console.error('❌ initSession failed:', err);
    error.value = err.response?.data?.error || err.message || 'Failed to load session';
    loading.value = false;
    contextReady.value = false;
  }
};

/**
 * Handle context ready (all fields filled)
 */
const handleContextReady = () => {
  if (contextReady.value && hasUnsavedChanges.value) {
    // If changing context with unsaved changes, force save first
    forceSave().then(() => {
      initSession();
    });
  } else {
    initSession();
  }
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
        v-model="contextForm"
        :mode="isStandalone ? 'interactive' : 'readonly'"
        :source="isStandalone ? 'standalone' : 'teacher_schedule'"
        :read-only="isReadonly"
        :options="{
          classrooms: classrooms,
          subjects: subjects,
        }"
        :academic-context="academicContext"
        @context-ready="handleContextReady"
      />

      <!-- Save Status Indicator -->
      <div v-if="contextReady && !isReadonly" class="mb-4 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <!-- Saving Indicator -->
          <span v-if="isSaving" class="flex items-center text-sm text-yellow-600 dark:text-yellow-400">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Saving...
          </span>

          <!-- Saved Successfully -->
          <span v-else-if="lastSavedAt" class="text-sm text-green-600 dark:text-green-400">
            ✓ Saved {{ new Date(lastSavedAt).toLocaleTimeString() }}
          </span>

          <!-- Has Unsaved Changes -->
          <span v-else-if="hasUnsavedChanges" class="text-sm text-orange-600 dark:text-orange-400">
            ⚠ Unsaved changes
          </span>
        </div>

        <!-- Manual Save Button -->
        <button
          v-if="hasUnsavedChanges"
          @click="forceSave"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors"
        >
          Save Now
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div v-for="n in 8" :key="n" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 animate-pulse">
          <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-32"></div>
          </div>
          <div class="grid grid-cols-3 gap-2 mb-4">
            <div class="h-16 bg-gray-300 dark:bg-gray-600 rounded"></div>
            <div class="h-16 bg-gray-300 dark:bg-gray-600 rounded"></div>
            <div class="h-16 bg-gray-300 dark:bg-gray-600 rounded"></div>
          </div>
          <div class="h-8 bg-gray-300 dark:bg-gray-600 rounded"></div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-lg p-6 text-center">
        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">
          Failed to Load Session
        </h3>
        <p class="text-red-600 dark:text-red-300 mb-4">{{ error }}</p>
        <button
          @click="retryLoad"
          class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition-colors"
        >
          Retry
        </button>
      </div>

      <!-- Student Grid -->
      <div v-else-if="contextReady && sessionData" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <StudentCard
          v-for="student in sessionData.students"
          :key="student.id"
          :student="student"
          :period="student.period"
          :scores="student.scores"
          :disabled="isReadonly"
          @update:scores="handleScoreUpdate"
          @update:attendance="handleAttendanceUpdate"
          @mark-absent="handleMarkAbsent"
        />
      </div>

      <!-- Empty State -->
      <div v-else-if="!isStandalone" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No students found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          This session doesn't have any students enrolled.
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
