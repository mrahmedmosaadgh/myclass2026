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

import { ref, computed, watch } from 'vue';

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
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:scores', 'update:attendance', 'mark-absent']);

// Local state for visual feedback
const isAnimating = ref(false);
const lastTapTime = ref(0);

// Computed properties
const isAbsent = computed(() => props.period.attendance_status === 'absent');
const isLocked = computed(() => props.period.locked && isAbsent.value);
const currentTotal = computed(() => props.period.total_score || 0);

// Score color mapping
const scoreColors = {
  5: 'bg-green-500 hover:bg-green-600',
  3: 'bg-yellow-500 hover:bg-yellow-600',
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
  return score ? score.numeric_value : 5; // Default to 5
};

// Cycle through scores: 5 → 3 → 0 → 5
const cycleScore = async (categoryKey) => {
  if (props.disabled || isLocked.value) return;
  
  // Debounce rapid taps
  const now = Date.now();
  if (now - lastTapTime.value < 200) return;
  lastTapTime.value = now;
  
  isAnimating.value = true;
  
  try {
    const currentScore = getScore(categoryKey);
    let newScore;
    
    // Cycle: 5 → 3 → 0 → 5
    if (currentScore === 5) {
      newScore = 3;
    } else if (currentScore === 3) {
      newScore = 0;
    } else {
      newScore = 5;
    }
    
    // Find mapping_id for this category
    const mapping = props.categories.find(c => c.key === categoryKey);
    const scoreRecord = props.scores.find(s => s.mapping_key === categoryKey);
    
    if (mapping && scoreRecord) {
      emit('update:scores', {
        student_period_id: props.student.student_period_id,
        mapping_id: mapping.id,
        numeric_value: newScore,
      });
    }
    
    // Reset animation flag
    setTimeout(() => {
      isAnimating.value = false;
    }, 300);
    
  } catch (error) {
    console.error('Error cycling score:', error);
    isAnimating.value = false;
  }
};

// Toggle attendance status
const toggleAttendance = async () => {
  if (props.disabled) return;
  
  const newStatus = isAbsent.value ? 'present' : 'absent';
  
  emit('update:attendance', {
    student_period_id: props.student.student_period_id,
    attendance_status: newStatus,
  });
};

// Mark as absent (quick action)
const markAbsent = async () => {
  if (props.disabled) return;
  
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
        @click="cycleScore('book_participation')"
        :disabled="disabled || isLocked"
        class="flex flex-col items-center justify-center p-3 rounded-lg transition-all duration-200 transform"
        :class="[
          scoreColors[getScore('book_participation')],
          isAnimating ? 'scale-95' : 'scale-100',
          disabled || isLocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer active:scale-95',
        ]"
      >
        <span class="text-2xl mb-1">📚</span>
        <span class="text-xs font-medium text-white">Book</span>
        <span class="text-lg font-bold text-white mt-1">{{ getScore('book_participation') }}</span>
      </button>

      <!-- Homework -->
      <button
        @click="cycleScore('homework')"
        :disabled="disabled || isLocked"
        class="flex flex-col items-center justify-center p-3 rounded-lg transition-all duration-200 transform"
        :class="[
          scoreColors[getScore('homework')],
          isAnimating ? 'scale-95' : 'scale-100',
          disabled || isLocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer active:scale-95',
        ]"
      >
        <span class="text-2xl mb-1">📝</span>
        <span class="text-xs font-medium text-white">HW</span>
        <span class="text-lg font-bold text-white mt-1">{{ getScore('homework') }}</span>
      </button>

      <!-- Behavior -->
      <button
        @click="cycleScore('behavior')"
        :disabled="disabled || isLocked"
        class="flex flex-col items-center justify-center p-3 rounded-lg transition-all duration-200 transform"
        :class="[
          scoreColors[getScore('behavior')],
          isAnimating ? 'scale-95' : 'scale-100',
          disabled || isLocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer active:scale-95',
        ]"
      >
        <span class="text-2xl mb-1">⭐</span>
        <span class="text-xs font-medium text-white">Behave</span>
        <span class="text-lg font-bold text-white mt-1">{{ getScore('behavior') }}</span>
      </button>
    </div>

    <!-- Attendance Status -->
    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
          Attendance
        </span>
        
        <button
          @click="toggleAttendance"
          :disabled="disabled"
          class="px-3 py-1 rounded-full text-sm font-medium transition-colors"
          :class="[
            isAbsent
              ? 'bg-red-500 text-white hover:bg-red-600'
              : 'bg-green-500 text-white hover:bg-green-600',
            disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
          ]"
        >
          {{ isAbsent ? '❌ Absent' : '✅ Present' }}
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
