<script setup>
/**
 * StudentCard - Interactive student score tracking card
 */

import { ref, computed } from 'vue'
import AttendanceToggle from './AttendanceToggle.vue'
import { useAttendanceService } from '../composables/useAttendanceService'

/* -----------------------
 Props
----------------------- */

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

  disabled: {
    type: Boolean,
    default: false
  },

  editMode: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'update:scores',
  'update:attendance',
  'mark-absent'
])

const attendanceService = useAttendanceService()

/* -----------------------
 Local State
----------------------- */

const isAnimating = ref(false)
const showScoreInput = ref(null)

/* -----------------------
 Computed
----------------------- */

const isAbsent = computed(
  () => props.period.attendance_status === 'absent'
)

const isLocked = computed(
  () => isAbsent.value && props.period.locked
)

const currentTotal = computed(
  () => props.period.total_score ?? 0
)

const isDisabled = computed(
  () => props.disabled || props.readOnly
)

/* -----------------------
 Score Lookup Map
----------------------- */

const scoreMap = computed(() => {
  const map = {}
  for (const s of props.scores) {
    map[s.mapping_key] = s
  }
  return map
})

const getScore = (key) =>
  scoreMap.value[key]?.numeric_value ?? 0

const getMaxScore = (key) => scoreMap.value[key]?.max_value ?? 5

const getScoreOptions = (key) =>
  Array.from({ length: getMaxScore(key) + 1 }, (_, i) => i)

/* -----------------------
 Colors
----------------------- */

const scoreColors = {
  5: 'bg-green-500 hover:bg-green-600',
  4: 'bg-green-400 hover:bg-green-500',
  3: 'bg-yellow-500 hover:bg-yellow-600',
  2: 'bg-orange-500 hover:bg-orange-600',
  1: 'bg-orange-400 hover:bg-orange-500',
  0: 'bg-red-500 hover:bg-red-600'
}

/* -----------------------
 Score Emit Helper
----------------------- */

const emitScore = async (mappingId, value) => {
  if (!props.editMode || isDisabled.value) return
  
  const success = await attendanceService.updateScore(
    props.student.student_period_id,
    mappingId,
    value
  )
  
  if (success) {
    // Still emit for backward compatibility and dirty tracking
    emit('update:scores', {
      student_period_id: props.student.student_period_id,
      mapping_id: mappingId,
      numeric_value: value
    })
  }
}

/* -----------------------
 Cycle Score
----------------------- */

const cycleScore = async (categoryKey) => {
  if (!props.editMode || isDisabled.value || isLocked.value || isAbsent.value)
    return

  const scoreRecord = scoreMapFor(props.scores)[categoryKey]
  if (!scoreRecord) return

  const steps = scoreStepsFor(scoreRecord)
  const current = scoreRecord.numeric_value ?? 0
  const next = steps[(steps.indexOf(current) + 1) % steps.length]

  await emitScore(scoreRecord.mapping_id, next)

  isAnimating.value = true
  setTimeout(() => (isAnimating.value = false), 180)
}

/* -----------------------
 Manual Input
----------------------- */

const openScoreInput = (key) => {
  if (!props.editMode || isDisabled.value || isLocked.value || isAbsent.value)
    return
  showScoreInput.value = key
}

const updateScoreValue = async (key, value, commit = false) => {
  if (!props.editMode || isDisabled.value || isLocked.value || isAbsent.value)
    return

  const scoreRecord = scoreMap.value[key]
  if (!scoreRecord) return

  const num = Number(value) || 0
  const max = scoreRecord.max_value ?? 5

  const clamped = Math.max(0, Math.min(max, num))

  await emitScore(scoreRecord.mapping_id, clamped)

  if (commit) {
    showScoreInput.value = null
  }
}

/* -----------------------
 Attendance
----------------------- */

const toggleAttendance = async () => {
  if (!props.editMode || isDisabled.value) return

  const newStatus = isAbsent.value ? 'present' : 'absent'
  
  const success = await attendanceService.updateAttendance(
    props.student.student_period_id,
    newStatus
  )
  
  if (success) {
    // Still emit for backward compatibility and dirty tracking
    emit('update:attendance', {
      student_period_id: props.student.student_period_id,
      attendance_status: newStatus
    })
  }
}

const setAttendanceScore = async (value) => {
  if (!props.editMode || isDisabled.value || isAbsent.value) return

  const success = await attendanceService.updateAttendance(
    props.student.student_period_id,
    props.period.attendance_status,
    value
  )
  
  if (success) {
    // Still emit for backward compatibility and dirty tracking
    emit('update:attendance', {
      student_period_id: props.student.student_period_id,
      attendance_status: props.period.attendance_status,
      attendance_score: value
    })
  }
}

const markAbsent = () => {
  if (!props.editMode || isDisabled.value || isAbsent.value) return

  emit('mark-absent', {
    student_period_id: props.student.student_period_id
  })
}
</script>

<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-3 transition-all duration-200"
    :class="{
      'opacity-75': disabled,
      'border-2 border-red-400 dark:border-red-600': isLocked,
      'hover:shadow-lg': !disabled
    }"
  >
    <!-- Header -->

    <div class="flex items-center justify-between mb-2">
      <div class="flex items-center space-x-2">

        <!-- Avatar -->

        <div
          v-if="student.avatar"
          class="w-8 h-8 rounded-full overflow-hidden"
        >
          <img
            :src="student.avatar"
            :alt="student.name"
            class="w-8 h-8 object-cover"
          />
        </div>

        <div
          v-else
          class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-sm"
        >
          {{ student.name.charAt(0).toUpperCase() }}
        </div>

        <h4
          class="font-bold text-base text-gray-900 dark:text-white truncate max-w-[180px]"
          :title="student.name"
        >
          {{ student.name }}
        </h4>

      </div>

      <!-- Total -->

      <div
        class="px-2 py-1 rounded-full text-xs font-bold"
        :class="[
          currentTotal >= 15
            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
            : currentTotal >= 10
            ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
        ]"
      >
        {{ currentTotal }}
      </div>
    </div>

    <!-- Categories -->

    <div class="grid grid-cols-3 gap-2 mb-2">

      <button
        v-for="category in categories"
        :key="category.key"
        @click="cycleScore(category.key)"
        @dblclick="openScoreInput(category.key)"
        @keydown.enter="cycleScore(category.key)"
        tabindex="0"
        :disabled="!editMode || isDisabled || isLocked || isAbsent"
        class="flex flex-col items-center justify-center p-2 rounded-lg transition-all duration-200 transform"
        :class="[
          scoreColors[getScore(category.key)],
          isAnimating ? 'scale-95' : 'scale-100',
          isDisabled || isLocked
            ? 'cursor-not-allowed opacity-50'
            : 'cursor-pointer active:scale-95'
        ]"
      >

        <span class="text-xl mb-0.5">
          {{ category.icon || '📊' }}
        </span>

        <span class="text-[10px] font-medium text-white text-center leading-tight">
          {{ category.label }}
        </span>

        <span class="text-base font-bold text-white mt-0.5">

          <template
            v-if="editMode || showScoreInput === category.key"
          >
            <select
              :disabled="!editMode || isDisabled || isLocked || isAbsent"
              :value="getScore(category.key)"
              @change="updateScoreValue(category.key, $event.target.value, true)"
              class="w-12 text-center bg-white text-gray-900 font-bold rounded px-1 py-0.5"
            >
              <option
                v-for="opt in getScoreOptions(category.key)"
                :key="opt"
                :value="opt"
              >
                {{ opt }}
              </option>
            </select>
          </template>

          <template v-else>
            {{ getScore(category.key) }}
          </template>

        </span>

      </button>

    </div>

    <!-- Attendance -->

    <div class="border-t border-gray-200 dark:border-gray-700 pt-2">

      <div class="flex items-center justify-between mb-1.5">

        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
          Attendance
        </span>

        <button
          @click="toggleAttendance"
          :disabled="isDisabled"
          class="px-2 py-1 rounded-full text-xs font-medium transition-colors"
          :class="[
            isAbsent
              ? 'bg-red-500 text-white'
              : 'bg-green-500 text-white',
            isDisabled
              ? 'opacity-50 cursor-not-allowed'
              : 'hover:opacity-90'
          ]"
        >
          {{ isAbsent ? '❌ Absent' : '✅ Present' }}
        </button>

      </div>

      <!-- Attendance Score: only 3 or 5 -->

      <div
        v-if="!isAbsent"
        class="flex items-center gap-1.5 mb-1.5"
      >

        <button
          @click="setAttendanceScore(5)"
          :disabled="isDisabled"
          class="flex-1 py-1 rounded-lg text-xs font-bold transition-all border-2"
          :class="[
            period.attendance_score === 5
              ? 'bg-green-500 border-green-600 text-white scale-105'
              : 'bg-gray-100 dark:bg-gray-700 border-transparent text-gray-700 dark:text-gray-300',
            isDisabled ? 'opacity-50 cursor-not-allowed' : 'hover:border-green-400'
          ]"
          title="On time — full attendance score"
        >
          5 — On time
        </button>

        <button
          @click="setAttendanceScore(3)"
          :disabled="isDisabled"
          class="flex-1 py-1 rounded-lg text-xs font-bold transition-all border-2"
          :class="[
            period.attendance_score === 3
              ? 'bg-yellow-500 border-yellow-600 text-white scale-105'
              : 'bg-gray-100 dark:bg-gray-700 border-transparent text-gray-700 dark:text-gray-300',
            isDisabled ? 'opacity-50 cursor-not-allowed' : 'hover:border-yellow-400'
          ]"
          title="Late or left early — partial attendance score"
        >
          3 — Late
        </button>

      </div>

      <!-- Locked -->

      <div
        v-if="isLocked"
        class="mt-1.5 px-2 py-1 bg-red-100 dark:bg-red-900 border border-red-300 dark:border-red-700 rounded text-[10px] text-red-800 dark:text-red-200"
      >
        🔒 Locked (Absent) - Change attendance to modify
      </div>

    </div>

  </div>
</template>

<style scoped>

.transition-all {
  transition-property: all;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4,0,0.2,1);
}

</style>