<script setup>
/**
 * StudentCard - Interactive student score tracking card
 * 
 * Features:
 * - 4 tap targets for categories (Book, Homework, Behavior, Attendance)
 * - Tap cycle: 5 → 3 → 0 → 5 (or custom values)
 * - Visual feedback for each score level
 * - Absent lock enforcement
 * - Total score display
 */

import { ref, computed } from 'vue';

const props = defineProps({
  student: {
    type: Object,
    required: true,
    default: () => ({
      id: null,
      name: '',
      avatar: null,
    }),
  },
  period: {
    type: Object,
    required: true,
    default: () => ({
      attendance_status: 'present',
      attendance_score: 5,
      total_score: 0,
      locked: false,
    }),
  },
  scores: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [
      { key: 'book_participation', label: 'Book', icon: '📚' },
      { key: 'homework', label: 'Homework', icon: '📝' },
      { key: 'behavior', label: 'Behavior', icon: '⭐' },
    ],
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  editMode: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:scores', 'update:attendance', 'mark-absent']);

// Local state for visual feedback
const isAnimating = ref(false);
const lastTapTime = ref(0);
const showScoreInput = ref(null); // Track which category is being edited

// Computed properties
const isAbsent = computed(() => props.period.attendance_status === 'absent');
const isLocked = computed(() => props.period.locked && isAbsent.value);
const currentTotal = computed(() => props.period.total_score || 0);
const isDisabled = computed(() => props.disabled || props.readOnly);

// Score color mapping
const scoreColors = {
  5: 'bg-green-500 hover:bg-green-600',
  4: 'bg-green-400 hover:bg-green-500',
  3: 'bg-yellow-500 hover:bg-yellow-600',
  2: 'bg-orange-500 hover:bg-orange-600',
  1: 'bg-red-400 hover:bg-red-500',
  0: 'bg-red-500 hover:bg-red-600',
};

const scoreTextColors = {
  5: 'text-green-700 dark:text-green-300',
  3: 'text-yellow-700 dark:text-yellow-300',
  0: 'text-red-700 dark:text-red-300',
};

// Get score for a category
const getScore = (categoryKey) => {
  const score = props.scores.find(s => s.mapping_key === categoryKey);
  return score ? score.numeric_value : 0; // Default to 0
};

// Cycle through scores: 5 → 3 → 0 → 5 (double-click)
const cycleScore = async (categoryKey) => {
  if (isDisabled.value || isLocked.value) return;
  
  // Debounce rapid taps
  const now = Date.now();
  if (now - lastTapTime.value < 200) return;
  lastTapTime.value = now;
  
  isAnimating.value = true;
  
  try {
    const scoreRecord = props.scores.find(s => s.mapping_key === categoryKey);
    if (!scoreRecord?.mapping_id) {
      return;
    }

    const currentScore = scoreRecord.numeric_value ?? 0;
    const maxScore = scoreRecord.max_value ?? 5;
    const midScore = Math.max(1, Math.round(maxScore * 0.6));
    let newScore;
    
    // Cycle: 5 → 3 → 0 → 5
    if (currentScore === maxScore) {
      newScore = midScore;
    } else if (currentScore === midScore) {
      newScore = 0;
    } else {
      newScore = maxScore;
    }
    
    emit('update:scores', {
      student_period_id: props.student.student_period_id,
      mapping_id: scoreRecord.mapping_id,
      numeric_value: newScore,
    });
    
    // Reset animation flag
    setTimeout(() => {
      isAnimating.value = false;
    }, 300);
    
  } catch (error) {
    console.error('Error cycling score:', error);
    isAnimating.value = false;
  }
};

// Open score input for manual entry
const openScoreInput = (categoryKey) => {
  if (isDisabled.value || isLocked.value) return;
  showScoreInput.value = categoryKey;
};

const updateScoreValue = (categoryKey, value, commit = false) => {
  if (isDisabled.value || isLocked.value) return;

  const scoreRecord = props.scores.find(s => s.mapping_key === categoryKey);
  if (!scoreRecord?.mapping_id) return;

  const numValue = Number.isFinite(Number(value)) ? Number(value) : 0;
  const maxValue = scoreRecord.max_value ?? 5;
  const clampedValue = Math.max(0, Math.min(maxValue, numValue));

  emit('update:scores', {
    student_period_id: props.student.student_period_id,
    mapping_id: scoreRecord.mapping_id,
    numeric_value: clampedValue,
  });

  if (commit && !props.editMode) {
    showScoreInput.value = null;
  }
};

// Set attendance score manually
const setAttendanceScore = (value) => {
  if (isDisabled.value) return;
  
  emit('update:attendance', {
    student_period_id: props.student.student_period_id,
    attendance_status: props.period.attendance_status,
    attendance_score: value,
  });
};

// Toggle attendance status
const toggleAttendance = async () => {
  if (isDisabled.value) return;
  
  const newStatus = isAbsent.value ? 'present' : 'absent';
  
  emit('update:attendance', {
    student_period_id: props.student.student_period_id,
    attendance_status: newStatus,
  });
};

// Mark as absent (quick action)
const markAbsent = async () => {
  if (isDisabled.value) return;
  
  emit('mark-absent', {
    student_period_id: props.student.student_period_id,
  });
};
</script>

<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 transition-all duration-200"
    :class="{
      'opacity-75': disabled,
      'border-2 border-red-400 dark:border-red-600': isLocked,
      'hover:shadow-lg': !disabled,
    }"
  >
    <!-- Header: Student Info -->
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center space-x-3">
        <!-- Avatar -->
        <div
          v-if="student.avatar"
          class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center"
        >
          <img :src="student.avatar" :alt="student.name" class="w-10 h-10 rounded-full" />
        </div>
        <div v-else class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
          {{ student.name.charAt(0).toUpperCase() }}
        </div>
        
        <!-- Name -->
        <h4 class="font-semibold text-gray-900 dark:text-white truncate max-w-[150px]">
          {{ student.name }}
        </h4>
      </div>
      
      <!-- Total Score Badge -->
      <div
        class="px-3 py-1 rounded-full text-sm font-bold"
        :class="[
          currentTotal >= 15 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
          currentTotal >= 10 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
          'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        ]"
      >
        {{ currentTotal }}/20
      </div>
    </div>

    <!-- Category Tap Targets -->
    <div class="grid grid-cols-3 gap-2 mb-3">
      <!-- Book & Participation -->
      <button
        @click="openScoreInput('book_participation')"
        :disabled="isDisabled || isLocked"
        class="flex flex-col items-center justify-center p-3 rounded-lg transition-all duration-200 transform"
        :class="[
          scoreColors[getScore('book_participation')],
          isAnimating ? 'scale-95' : 'scale-100',
          isDisabled || isLocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer active:scale-95',
        ]"
      >
        <span class="text-2xl mb-1">📚</span>
        <span class="text-xs font-medium text-white">Book</span>
        <span class="text-lg font-bold text-white mt-1">
          <span v-if="props.editMode || showScoreInput === 'book_participation'">
            <input
              type="number"
              min="0"
              max="5"
              :value="getScore('book_participation')"
              @input="updateScoreValue('book_participation', $event.target.value, false)"
              @blur="updateScoreValue('book_participation', $event.target.value, true)"
              class="w-8 text-center bg-white text-gray-900 font-bold rounded px-1"
              :autofocus="!props.editMode && showScoreInput === 'book_participation'"
            />
          </span>
          <span v-else>{{ getScore('book_participation') }}</span>
        </span>
      </button>

      <!-- Homework -->
      <button
        @click="openScoreInput('homework')"
        :disabled="isDisabled || isLocked"
        class="flex flex-col items-center justify-center p-3 rounded-lg transition-all duration-200 transform"
        :class="[
          scoreColors[getScore('homework')],
          isAnimating ? 'scale-95' : 'scale-100',
          isDisabled || isLocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer active:scale-95',
        ]"
      >
        <span class="text-2xl mb-1">📝</span>
        <span class="text-xs font-medium text-white">HW</span>
        <span class="text-lg font-bold text-white mt-1">
          <span v-if="props.editMode || showScoreInput === 'homework'">
            <input
              type="number"
              min="0"
              max="5"
              :value="getScore('homework')"
              @input="updateScoreValue('homework', $event.target.value, false)"
              @blur="updateScoreValue('homework', $event.target.value, true)"
              class="w-8 text-center bg-white text-gray-900 font-bold rounded px-1"
              :autofocus="!props.editMode && showScoreInput === 'homework'"
            />
          </span>
          <span v-else>{{ getScore('homework') }}</span>
        </span>
      </button>

      <!-- Behavior -->
      <button
        @click="openScoreInput('behavior')"
        :disabled="isDisabled || isLocked"
        class="flex flex-col items-center justify-center p-3 rounded-lg transition-all duration-200 transform"
        :class="[
          scoreColors[getScore('behavior')],
          isAnimating ? 'scale-95' : 'scale-100',
          isDisabled || isLocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer active:scale-95',
        ]"
      >
        <span class="text-2xl mb-1">⭐</span>
        <span class="text-xs font-medium text-white">Behave</span>
        <span class="text-lg font-bold text-white mt-1">
          <span v-if="props.editMode || showScoreInput === 'behavior'">
            <input
              type="number"
              min="0"
              max="5"
              :value="getScore('behavior')"
              @input="updateScoreValue('behavior', $event.target.value, false)"
              @blur="updateScoreValue('behavior', $event.target.value, true)"
              class="w-8 text-center bg-white text-gray-900 font-bold rounded px-1"
              :autofocus="!props.editMode && showScoreInput === 'behavior'"
            />
          </span>
          <span v-else>{{ getScore('behavior') }}</span>
        </span>
      </button>
    </div>

    <!-- Attendance Status -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
          Attendance
        </span>
        
        <button
          @click="toggleAttendance"
          :disabled="isDisabled"
          class="px-3 py-1 rounded-full text-sm font-medium transition-colors"
          :class="[
            isAbsent
              ? 'bg-red-500 text-white hover:bg-red-600'
              : 'bg-green-500 text-white hover:bg-green-600',
            isDisabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
          ]"
        >
          {{ isAbsent ? '❌ Absent' : '✅ Present' }}
        </button>
      </div>
      
      <!-- Attendance Score Buttons (only when present) -->
      <div v-if="!isAbsent" class="flex items-center space-x-1 mb-2">
        <span class="text-xs text-gray-600 dark:text-gray-400">Score:</span>
        <button
          v-for="score in [0, 1, 2, 3, 4, 5]"
          :key="score"
          @click="setAttendanceScore(score)"
          :class="[
            'w-8 h-8 rounded-full text-sm font-bold transition-all',
            props.period.attendance_score === score
              ? 'bg-indigo-600 text-white scale-110'
              : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600',
            isDisabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
          ]"
        >
          {{ score }}
        </button>
      </div>
      
      <!-- Locked Warning -->
      <div
        v-if="isLocked"
        class="mt-2 px-2 py-1 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 rounded text-xs text-red-800 dark:text-red-200"
      >
        🔒 Locked (Absent) - Change attendance to modify
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Smooth animations */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>
