<script setup>
import { computed } from 'vue'

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

const emit = defineEmits(['change', 'focus'])

// Computed property to get current attendance state
const currentState = computed(() => {
  const status = props.student.period?.attendance_status
  const score = props.student.period?.attendance_score ?? 5
  
  if (status === 'absent') return 'absent'
  if (score === 3) return 'late'
  return 'present'
})

// Get display properties for current state
const stateConfig = computed(() => {
  const configs = {
    present: {
      label: 'On Time',
      icon: '✓',
      points: 5,
      bgColor: 'bg-gradient-to-r from-green-500 to-green-600',
      borderColor: 'border-green-600',
      textColor: 'text-white',
      hoverBg: 'hover:from-green-600 hover:to-green-700'
    },
    late: {
      label: 'Late',
      icon: '⚠',
      points: 3,
      bgColor: 'bg-gradient-to-r from-yellow-500 to-yellow-600',
      borderColor: 'border-yellow-600',
      textColor: 'text-white',
      hoverBg: 'hover:from-yellow-600 hover:to-yellow-700'
    },
    absent: {
      label: 'Absent',
      icon: '✕',
      points: 0,
      bgColor: 'bg-gradient-to-r from-red-500 to-red-600',
      borderColor: 'border-red-600',
      textColor: 'text-white',
      hoverBg: 'hover:from-red-600 hover:to-red-700'
    }
  }
  
  return configs[currentState.value]
})

// Toggle to next state in cycle
const toggle = () => {
  if (props.disabled) return
  
  const nextState = {
    'present': 'late',
    'late': 'absent', 
    'absent': 'present'
  }[currentState.value]
  
  emit('change', {
    student_period_id: props.student.student_period_id,
    state: nextState
  })
}

// Handle keyboard events
const handleKeydown = (event) => {
  if (props.disabled) return
  
  if (event.key === ' ' || event.key === 'Enter') {
    event.preventDefault()
    toggle()
  }
}

// Handle focus
const handleFocus = (event) => {
  emit('focus', props.student.student_period_id)
}
</script>

<template>
  <button
    @click="toggle"
    @keydown="handleKeydown"
    @focus="handleFocus"
    :disabled="disabled"
    class="attendance-toggle relative w-full h-12 px-3 py-2 rounded-lg font-semibold text-sm transition-all duration-200 transform border-2 focus:outline-none focus:ring-2 focus:ring-offset-1"
    :class="[
      stateConfig.bgColor,
      stateConfig.borderColor,
      stateConfig.textColor,
      stateConfig.hoverBg,
      disabled ? 'cursor-not-allowed opacity-50 scale-95' : 'cursor-pointer hover:scale-105 active:scale-95 shadow-md hover:shadow-lg',
      'focus:ring-blue-500 focus:ring-offset-white dark:focus:ring-offset-gray-800'
    ]"
    :title="`${stateConfig.label} - ${stateConfig.points} points`"
  >
    <!-- Icon and Label -->
    <div class="flex items-center justify-center gap-2">
      <span class="text-lg leading-none">{{ stateConfig.icon }}</span>
      <span class="font-medium">{{ stateConfig.label }}</span>
      <span class="text-xs opacity-75">({{ stateConfig.points }})</span>
    </div>
    
    <!-- Subtle pulse animation on state change -->
    <div 
      v-if="!disabled"
      class="absolute inset-0 rounded-lg opacity-0 animate-ping"
      :class="stateConfig.bgColor"
    ></div>
  </button>
</template>

<style scoped>
/* Custom animations */
.attendance-toggle {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.attendance-toggle:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

/* Smooth transitions for state changes */
.attendance-toggle:hover:not(:disabled) {
  transform: translateY(-1px);
}

.attendance-toggle:active:not(:disabled) {
  transform: translateY(0) scale(0.95);
}

/* Pulse animation for state changes */
@keyframes ping {
  75%, 100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 0.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
