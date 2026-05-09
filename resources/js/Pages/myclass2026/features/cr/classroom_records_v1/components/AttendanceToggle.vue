<script setup>
import { computed, ref } from 'vue'
import DropdownConfirm from './DropdownConfirm.vue'

const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['change', 'focus', 'show-confirmation'])

// Computed property to get current attendance state
const currentState = computed(() => {
  const status = props.student.period?.attendance_status
  const score = props.student.period?.attendance_score ?? 5
  
  if (status === 'absent') return 'absent'
  if (score === 3) return 'late'
  return 'present'
})

// Model for q-btn-toggle
const model = computed({
  get: () => currentState.value,
  set: (value) => {
    if (props.disabled) return
    
    // Check if trying to mark absent and student has points
    if (value === 'absent' && props.student.period?.total_score > 0) {
      // Emit event to parent to show dropdown confirmation
      emit('show-confirmation', {
        student_period_id: props.student.student_period_id,
        state: 'absent',
        config: showAbsenceConfirmation()
      })
    } else {
      emit('change', {
        student_period_id: props.student.student_period_id,
        state: value
      })
    }
  }
})

// Show confirmation for absence
const showAbsenceConfirmation = () => {
  const points = props.student.period?.total_score || 0
  
  // Get detailed breakdown of points
  const attendanceScore = props.student.period?.attendance_score || 0
  const totalScore = props.student.period?.total_score || 0
  const otherPoints = totalScore - attendanceScore
  
  // Build detailed message
  let message = `Student currently has ${totalScore} points`
  
  if (totalScore > 0) {
    message += `\n\n📊 Current Points:`
    message += `\n• Attendance: ${attendanceScore} points`
    if (otherPoints > 0) {
      message += `\n• Other categories: ${otherPoints} points`
    }
    message += `\n\n⚠️ Marking absent will:`
    message += `\n• Reset attendance to 0 points`
    message += `\n• Keep other category points`
    message += `\n• Total will become: ${otherPoints} points`
  }
  
  // Return confirmation config for parent to handle
  return {
    title: '⚠️ Mark Student Absent?',
    message: message,
    confirmLabel: '🗑️ Mark Absent',
    cancelLabel: '❌ Cancel',
    confirmColor: 'negative',
    cancelColor: 'grey-7'
  }
}

// Get display properties for each state
const stateConfigs = computed(() => ({
  present: {
    label: 'On Time',
    letter: 'P',
    points: 5,
    bgColor: 'bg-green-500',
    textColor: 'text-white',
    circleColor: 'bg-green-500'
  },
  late: {
    label: 'Late',
    letter: 'L',
    points: 3,
    bgColor: 'bg-orange-500',
    textColor: 'text-white',
    circleColor: 'bg-orange-500'
  },
  absent: {
    label: 'Absent',
    letter: 'A',
    points: 0,
    bgColor: 'bg-red-500',
    textColor: 'text-white',
    circleColor: 'bg-red-500'
  }
}))

// Options for q-btn-toggle
const toggleOptions = computed(() => [
  { 
    value: 'present', 
    slot: 'present'
  },
  { 
    value: 'late', 
    slot: 'late'
  },
  { 
    value: 'absent', 
    slot: 'absent'
  }
])

// Handle focus
const handleFocus = (event) => {
  emit('focus', props.student.student_period_id)
}

// Mark student as not absent (present)
const markNotAbsent = () => {
  if (props.disabled) return
  
  emit('change', {
    student_period_id: props.student.student_period_id,
    state: 'present'
  })
}
</script>

<template>
  <div class="attendance-toggle-container" @focus="handleFocus">
    <!-- Not Absent Button - Shows when student is absent -->
    <div v-if="currentState === 'absent' && !disabled" class="mb-2">
      <button
        @click="markNotAbsent"
        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 flex items-center justify-center gap-2"
        title="Mark student as Present (can be changed to Late if needed)"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Not Absent - Mark Present
      </button>
    </div>

    <q-btn-toggle
      v-model="model"
      :disable="disabled"
      push
      glossy
      toggle-color="primary"
      :options="toggleOptions"
      class="w-full"
      @update:model-value="handleFocus"
    >
      <!-- Present Slot - Only Letter P -->
      <template v-slot:present>
        <div 
          class="flex items-center justify-center px-4 py-3"
          :class="[
            stateConfigs.present.bgColor,
            stateConfigs.present.textColor,
            'font-bold text-lg rounded-lg transition-all duration-200'
          ]"
          :title="`${stateConfigs.present.label} (${stateConfigs.present.points} points)`"
        >
          {{ stateConfigs.present.letter }}
        </div>
      </template>

      <!-- Late Slot - Only Letter L -->
      <template v-slot:late>
        <div 
          class="flex items-center justify-center px-4 py-3"
          :class="[
            stateConfigs.late.bgColor,
            stateConfigs.late.textColor,
            'font-bold text-lg rounded-lg transition-all duration-200'
          ]"
          :title="`${stateConfigs.late.label} (${stateConfigs.late.points} points)`"
        >
          {{ stateConfigs.late.letter }}
        </div>
      </template>

      <!-- Absent Slot - Only Letter A -->
      <template v-slot:absent>
        <div 
          class="flex items-center justify-center px-4 py-3"
          :class="[
            stateConfigs.absent.bgColor,
            stateConfigs.absent.textColor,
            'font-bold text-lg rounded-lg transition-all duration-200'
          ]"
          :title="`${stateConfigs.absent.label} (${stateConfigs.absent.points} points)`"
        >
          {{ stateConfigs.absent.letter }}
        </div>
      </template>
    </q-btn-toggle>
  </div>
</template>

<style scoped>
/* Custom animations for q-btn-toggle */
.attendance-toggle-container :deep(.q-btn-toggle) {
  border-radius: 0.5rem;
  overflow: hidden;
}

.attendance-toggle-container :deep(.q-btn) {
  min-height: 48px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 0;
}

.attendance-toggle-container :deep(.q-btn--active) {
  transform: scale(1.02);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.attendance-toggle-container :deep(.q-btn:hover:not(.q-btn--active)) {
  transform: translateY(-1px);
}

.attendance-toggle-container :deep(.q-btn:active) {
  transform: translateY(0) scale(0.98);
}

/* Focus styles */
.attendance-toggle-container :deep(.q-btn:focus) {
  outline: 2px solid rgba(59, 130, 246, 0.5);
  outline-offset: 2px;
}
</style>
