<script setup>
/**
 * StudentCardV2 - Minimal card with badge that opens detailed dialog on click
 */

import { ref, computed } from 'vue'

const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  period: {
    type: Object,
    required: true
  },
  scores: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  badgeNumber: {
    type: [Number, String],
    default: null
  },
  showBadge: {
    type: Boolean,
    default: true
  },
  readOnly: Boolean,
  editMode: Boolean,
  disabled: Boolean,
  // Style preferences
  useRandomAvatar: {
    type: Boolean,
    default: true
  },
  showBadgeOnCard: {
    type: Boolean,
    default: true
  },
  scoreLabelFormat: {
    type: String,
    default: 'with-label',
    validator: (value) => ['with-label', 'number-only'].includes(value)
  },
  cardSize: {
    type: String,
    default: 'standard',
    validator: (value) => ['compact', 'standard', 'large'].includes(value)
  },
  nameFormat: {
    type: String,
    default: 'first',
    validator: (value) => ['first', 'firstSecond', 'firstLast', 'full'].includes(value)
  }
})

const emit = defineEmits([
  'update:scores',
  'update:attendance',
  'mark-absent'
])

const showDialog = ref(false)

const isAbsent = computed(() => props.period.attendance_status === 'absent')
const isLocked = computed(() => isAbsent.value && props.period.locked)
const currentTotal = computed(() => props.period.total_score ?? 0)
const isDisabled = computed(() => props.disabled || props.readOnly)
const isNotSet = computed(() => !props.period.attendance_status || props.period.attendance_status === '')

// Get badge color based on score and attendance
const badgeColorClass = computed(() => {
  if (isAbsent.value) {
    return 'bg-gradient-to-r from-red-600 to-red-700'
  }
  if (isNotSet.value || currentTotal.value === 0) {
    return 'bg-gradient-to-r from-gray-600 to-gray-700'
  }
  return 'bg-gradient-to-r from-green-600 to-green-700'
})

// Generate display name based on format
const displayName = computed(() => {
  const name = props.student.name || ''
  const parts = name.split(' ').filter(Boolean)
  
  switch (props.nameFormat) {
    case 'firstSecond':
      return parts.length >= 2 ? `${parts[0]} ${parts[1]}` : name
    case 'firstLast':
      return parts.length >= 2 ? `${parts[0]} ${parts[parts.length - 1]}` : name
    case 'full':
      return name
    case 'first':
    default:
      return parts[0] || name
  }
})

// Generate initials for avatar
const initials = computed(() => {
  const name = props.student.name || ''
  const parts = name.split(' ').filter(Boolean)
  
  if (parts.length === 0) return '?'
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
  if (parts.length === 2) {
    return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase()
  }
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
})

// Avatar color based on student ID or name
const avatarColor = computed(() => {
  const colors = [
    '#6366f1', // Indigo
    '#8b5cf6', // Violet
    '#ec4899', // Pink
    '#f59e0b', // Amber
    '#10b981', // Emerald
    '#3b82f6', // Blue
    '#ef4444', // Red
    '#14b8a6', // Teal
  ]
  
  const id = props.student.student_period_id || props.student.name || 0
  const hash = typeof id === 'string' 
    ? id.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0)
    : id
  
  return colors[hash % colors.length]
})

// Get random avatar for student
const getStudentAvatar = () => {
  const avatarFiles = [
    'cute_v5_1.png',
    'cute_v5_2.png',
    'cute_v5_3.png',
    'cute_v5_7.png',
    'cute_v5_8.png',
    'cute_v5_9.png',
    'cute_v5_10.png',
    'cute_v5_13.png',
    'cute_v5_15.png',
    'cute_v5_18.png',
    'cute_v5_19.png'
  ]
  
  const id = props.student.student_period_id || props.student.name || 0
  const hash = typeof id === 'string' 
    ? id.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0)
    : id
  
  const avatarIndex = hash % avatarFiles.length
  return `/images/avatars/avatar/${avatarFiles[avatarIndex]}`
}

// Score map for quick lookup
const scoreMap = computed(() => {
  const map = {}
  for (const s of props.scores) {
    map[s.mapping_key] = s
  }
  return map
})

const getScore = (key) => {
  const scoreRecord = scoreMap.value[key]
  return scoreRecord?.numeric_value ?? null // Return null if not set
}

const getMaxScore = (key) => scoreMap.value[key]?.max_value ?? 5

const getScoreOptions = (key) => {
  const max = getMaxScore(key)
  return Array.from({ length: max + 1 }, (_, i) => i)
}

// Emit score update
const emitScore = (mappingId, value) => {
  emit('update:scores', {
    student_period_id: props.student.student_period_id,
    mapping_id: mappingId,
    numeric_value: value
  })
}

// Cycle through scores on click
const cycleScore = async (categoryKey) => {
  if (!props.editMode || isDisabled.value || isLocked.value || isAbsent.value) return

  const scoreRecord = scoreMap.value[categoryKey]
  if (!scoreRecord) return

  const max = scoreRecord.max_value ?? 5
  const mid = Math.round(max * 0.6)
  const steps = [max, mid, 0]
  
  const current = scoreRecord.numeric_value ?? 0
  const next = steps[(steps.indexOf(current) + 1) % steps.length]
  
  emitScore(scoreRecord.mapping_id, next)
}

// Toggle attendance
const toggleAttendance = () => {
  if (!props.editMode || isDisabled.value) return

  const newStatus = isAbsent.value ? 'present' : 'absent'
  
  emit('update:attendance', {
    student_period_id: props.student.student_period_id,
    attendance_status: newStatus
  })
}

// Set attendance score
const setAttendanceScore = (value) => {
  if (!props.editMode || isDisabled.value || isAbsent.value) return

  emit('update:attendance', {
    student_period_id: props.student.student_period_id,
    attendance_status: props.period.attendance_status,
    attendance_score: value
  })
}

// Score colors for dialog buttons
const scoreColors = {
  5: 'bg-green-500 hover:bg-green-600',
  4: 'bg-green-400 hover:bg-green-500',
  3: 'bg-yellow-500 hover:bg-yellow-600',
  2: 'bg-orange-500 hover:bg-orange-600',
  1: 'bg-orange-400 hover:bg-orange-500',
  0: 'bg-gray-400 hover:bg-gray-500',
  null: 'bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500' // Unselected
}
</script>

<template>
  <div>
    <!-- Minimal Card - Simple and Clean -->
    <div
      @click="showDialog = true"
      class="bg-white dark:bg-gray-800 rounded-2xl shadow-md transition-all duration-200 hover:shadow-lg hover:scale-105 cursor-pointer"
      :class="[
        disabled ? 'opacity-75' : '',
        isLocked ? 'border-2 border-red-400 dark:border-red-600' : '',
        cardSize === 'compact' ? 'p-2' : cardSize === 'standard' ? 'p-4' : 'p-6'
      ]"
    >
      <div class="flex flex-col items-center">
        <!-- Avatar with Badge -->
        <div class="relative inline-block" :class="cardSize === 'compact' ? 'mb-2' : cardSize === 'standard' ? 'mb-3' : 'mb-4'">
          <div
            v-if="(useRandomAvatar && getStudentAvatar()) || student.avatar"
            class="rounded-full overflow-hidden border-3 border-white shadow-lg"
            :class="cardSize === 'compact' ? 'w-10 h-10' : cardSize === 'standard' ? 'w-16 h-16' : 'w-24 h-24'"
          >
            <img
              :src="student.avatar || (useRandomAvatar ? getStudentAvatar() : undefined)"
              :alt="student.name"
              class="w-full h-full object-cover"
              @error="(e) => e.target.style.display = 'none'"
            />
          </div>
          <div
            v-else
            class="rounded-full flex items-center justify-center text-white font-bold border-3 border-white shadow-lg"
            :class="cardSize === 'compact' ? 'w-14 h-14 text-lg' : cardSize === 'standard' ? 'w-20 h-20 text-2xl' : 'w-24 h-24 text-3xl'"
            :style="{ backgroundColor: avatarColor }"
          >
            {{ initials }}
          </div>
          
          <!-- Badge with Total Score - Positioned at top-right -->
          <div
            v-if="showBadge && showBadgeOnCard"
            class="absolute -top-2 -right-8 text-white rounded-full flex items-center justify-center font-bold border-2 border-white shadow-lg z-10"
            :class="[
              badgeColorClass,
              cardSize === 'compact' ? 'w-8 h-8 text-sm' : cardSize === 'standard' ? 'w-10 h-10 text-base' : 'w-12 h-12 text-lg'
            ]"
          >
            {{ currentTotal }}
          </div>
        </div>
        
        <!-- Name -->
        <h4 class="font-bold text-gray-900 dark:text-white truncate text-center max-w-full mt-2" :class="cardSize === 'compact' ? 'text-xs' : cardSize === 'standard' ? 'text-sm' : 'text-base'" :title="student.name">
          {{ displayName }}
        </h4> 
      </div>
    </div>

    <!-- Dialog with Full Details -->
    <q-dialog v-model="showDialog" maximized>
      <q-card class="flex flex-col">
        <!-- Dialog Header -->
        <q-card-section class="bg-primary text-white text-center py-6">
          <div class="flex items-center justify-between mb-4">
            <q-btn flat round dense icon="close" color="white" @click="showDialog = false" />
            <div class="flex-1 text-center">
              <h3 class="text-2xl font-bold">{{ student.name }}</h3>
            </div>
            <div class="w-10"></div>
          </div>
          
          <!-- Large Total Score Display -->
          <div
            class="inline-block px-8 py-4 rounded-full text-3xl font-bold mt-2"
            :class="[
              currentTotal >= 15
                ? 'bg-white text-green-600'
                : currentTotal >= 10
                ? 'bg-white text-yellow-600'
                : 'bg-white text-red-600'
            ]"
          >
            {{ currentTotal }}
          </div>
        </q-card-section>

        <!-- Dialog Content -->
        <q-card-section class="flex-1 overflow-auto p-6">
          <div class="max-w-3xl mx-auto space-y-8">
            
            <!-- Categories Section -->
            <div>
              <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Category Scores</h4>
              
              <!-- Message when attendance not set -->
              <div v-if="!props.period.attendance_status || props.period.attendance_status === ''" class="mb-6 p-6 bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-300 dark:border-yellow-700 rounded-2xl text-center">
                <div class="text-yellow-800 dark:text-yellow-300 text-lg font-bold mb-2">
                  ⚠️ Set Attendance First
                </div>
                <div class="text-yellow-700 dark:text-yellow-400 text-sm">
                  Please select attendance status (Present/Absent) before entering scores
                </div>
              </div>
              
              <!-- Message when absent -->
              <div v-else-if="isAbsent" class="mb-6 p-6 bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-2xl text-center">
                <div class="text-red-800 dark:text-red-300 text-lg font-bold mb-2">
                  🔒 Student is Absent
                </div>
                <div class="text-red-700 dark:text-red-400 text-sm">
                  Category scores are locked for absent students
                </div>
              </div>
              
              <!-- Category Score Buttons (only when present) -->
              <div v-else class="grid grid-cols-3 gap-4">
                <button
                  v-for="category in categories"
                  :key="category.key"
                  @click="cycleScore(category.key)"
                  :disabled="!editMode || isDisabled || isLocked"
                  class="flex flex-col items-center justify-center p-6 rounded-2xl transition-all duration-200 transform hover:scale-105 active:scale-95"
                  :class="[
                    scoreColors[getScore(category.key)] || 'bg-gray-300',
                    isDisabled || isLocked ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                  ]"
                >
                  <span class="text-4xl mb-2">{{ category.icon || '📊' }}</span>
                  <span class="text-base font-medium text-white text-center mb-2">
                    {{ category.label }}
                  </span>
                  <span class="text-3xl font-bold text-white">
                    <template v-if="getScore(category.key) !== null">
                      {{ getScore(category.key) }}
                      <span class="text-lg opacity-80">/ {{ getMaxScore(category.key) }}</span>
                    </template>
                    <template v-else>
                      <span class="text-2xl opacity-60">-</span>
                    </template>
                  </span>
                </button>
              </div>
            </div>

            <!-- Attendance Section -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
              <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Attendance</h4>
              
              <!-- Attendance Toggle -->
              <div class="flex items-center justify-between mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-xl">
                <span class="text-lg font-medium text-gray-700 dark:text-gray-300">
                  Status
                </span>
                <button
                  @click="toggleAttendance"
                  :disabled="isDisabled"
                  class="px-6 py-3 rounded-full text-base font-bold transition-all"
                  :class="[
                    isAbsent
                      ? 'bg-red-500 text-white hover:bg-red-600'
                      : props.period.attendance_status === 'present'
                      ? 'bg-green-500 text-white hover:bg-green-600'
                      : 'bg-gray-400 text-white hover:bg-gray-500',
                    isDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                  ]"
                >
                  {{ isAbsent ? '❌ Absent' : props.period.attendance_status === 'present' ? '✅ Present' : '⚪ Not Set' }}
                </button>
              </div>

              <!-- Attendance Score Buttons -->
              <div v-if="!isAbsent" class="grid grid-cols-2 gap-4">
                <button
                  @click="setAttendanceScore(5)"
                  :disabled="isDisabled"
                  class="py-6 rounded-xl text-lg font-bold transition-all border-2"
                  :class="[
                    period.attendance_score === 5
                      ? 'bg-green-500 border-green-600 text-white scale-105 shadow-lg'
                      : 'bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-green-400',
                    isDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                  ]"
                >
                  ✅ On time (5)
                </button>
                <button
                  @click="setAttendanceScore(3)"
                  :disabled="isDisabled"
                  class="py-6 rounded-xl text-lg font-bold transition-all border-2"
                  :class="[
                    period.attendance_score === 3
                      ? 'bg-yellow-500 border-yellow-600 text-white scale-105 shadow-lg'
                      : 'bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-yellow-400',
                    isDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                  ]"
                >
                  ⚠️ Late / Left early (3)
                </button>
              </div>

              <!-- Locked Message -->
              <div
                v-if="isLocked"
                class="mt-6 px-6 py-4 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-xl text-base text-red-800 dark:text-red-200 flex items-center gap-4"
              >
                <span class="text-3xl">🔒</span>
                <div>
                  <div class="font-bold">Locked (Absent)</div>
                  <div class="text-sm">Change attendance status to modify scores</div>
                </div>
              </div>
            </div>
          </div>
        </q-card-section>

        <!-- Dialog Footer -->
        <q-card-actions align="center" class="p-6 border-t border-gray-200 dark:border-gray-700">
          <q-btn
            label="Close"
            color="primary"
            unelevated
            size="xl"
            class="px-12 rounded-xl"
            @click="showDialog = false"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style scoped>
.transition-all {
  transition-property: all;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
