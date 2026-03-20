<script setup>
import { computed } from 'vue'

const props = defineProps({
  students: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  },
  readOnly: Boolean,
  editMode: Boolean,
  disabled: Boolean
})

const emit = defineEmits([
  'update:scores',
  'update:attendance',
  'mark-absent'
])

const isDisabled = computed(() => props.disabled || props.readOnly)

const scoreMapFor = (scores = []) => {
  const map = {}
  for (const s of scores) map[s.mapping_key] = s
  return map
}

const maxFor = (scoreRecord) => scoreRecord?.max_value ?? 5

const optionsUpTo = (max) =>
  Array.from({ length: (Number(max) || 0) + 1 }, (_, i) => i)

const options0To5 = optionsUpTo(5)

const emitScore = (student, scoreRecord, value) => {
  if (!props.editMode || isDisabled.value) return

  if (!student?.student_period_id) {
    console.warn('[StudentTable] emitScore skipped: missing student_period_id', {
      student,
      scoreRecord,
      value,
    })
    return
  }

  if (!scoreRecord || typeof scoreRecord.mapping_id === 'undefined') {
    console.warn(
      '[StudentTable] Cannot save Book/Homework/Behavior because this student has no score rows yet. Run init-session again (or run `php artisan cr:backfill-scores`) to create missing scores.',
      {
        student_period_id: student.student_period_id,
        scoresLength: Array.isArray(student.scores) ? student.scores.length : null,
        scoreRecord,
        value,
      }
    )
    return
  }

  const num = Number(value) || 0
  const max = maxFor(scoreRecord)
  const clamped = Math.max(0, Math.min(max, num))

  emit('update:scores', {
    student_period_id: student.student_period_id,
    mapping_id: scoreRecord.mapping_id,
    numeric_value: clamped
  })
}

const setAttendanceStatus = (student, status) => {
  if (!props.editMode || isDisabled.value) return
  emit('update:attendance', {
    student_period_id: student.student_period_id,
    attendance_status: status
  })
}

const setAttendanceScore = (student, value) => {
  if (!props.editMode || isDisabled.value || (student.period?.attendance_status === 'absent')) return
  emit('update:attendance', {
    student_period_id: student.student_period_id,
    attendance_status: student.period?.attendance_status,
    attendance_score: Number(value) || 0
  })
}

// Combined function to handle both status and score in one action
const setCombinedAttendance = (student, value) => {
  if (!props.editMode || isDisabled.value) return
  
  if (value === 'absent') {
    // Mark as absent with 0 score
    emit('update:attendance', {
      student_period_id: student.student_period_id,
      attendance_status: 'absent',
      attendance_score: 0
    })
  } else {
    // Mark as present with specific score (5 or 3)
    emit('update:attendance', {
      student_period_id: student.student_period_id,
      attendance_status: 'present',
      attendance_score: Number(value) || 0
    })
  }
}

// Get current attendance value for the dropdown
const getAttendanceValue = (student) => {
  const status = student.period?.attendance_status
  const score = student.period?.attendance_score ?? 5
  
  if (status === 'absent') {
    return 'absent'
  }
  
  // Return the score (5 or 3)
  return score.toString()
}

const quickAbsent = (student) => {
  if (!props.editMode || isDisabled.value || (student.period?.attendance_status === 'absent')) return
  emit('mark-absent', { student_period_id: student.student_period_id })
}
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900/40">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
              Student
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
              Total
            </th>
            <th
              v-for="category in categories"
              :key="category.key"
              class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
            >
              {{ category.label }}
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
              Attendance
            </th>
          </tr>
        </thead>

        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="student in students"
            :key="student.student_period_id"
            class="hover:bg-gray-50 dark:hover:bg-gray-900/30"
          >
            <td class="px-4 py-3">
              <div class="flex items-center gap-3">
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
                  class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold"
                >
                  {{ student.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <div class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-[220px]">
                    {{ student.name }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    ID: {{ student.student_period_id }}
                  </div>
                </div>
              </div>
            </td>

            <td class="px-4 py-3">
              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold"
                :class="[
                  (student.period?.total_score ?? 0) >= 15
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                    : (student.period?.total_score ?? 0) >= 10
                    ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                ]"
              >
                {{ student.period?.total_score ?? 0 }}
              </span>
            </td>

            <td
              v-for="category in categories"
              :key="category.key"
              class="px-4 py-3"
            >
              <template v-if="editMode">
                <select
                  :disabled="!editMode || isDisabled || (student.period?.attendance_status === 'absent')"
                  :value="scoreMapFor(student.scores)[category.key]?.numeric_value ?? 0"
                  @change="emitScore(student, scoreMapFor(student.scores)[category.key], $event.target.value)"
                  class="h-9 w-16 rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm"
                >
                  <option
                    v-for="opt in optionsUpTo(maxFor(scoreMapFor(student.scores)[category.key]))"
                    :key="opt"
                    :value="opt"
                  >
                    {{ opt }}
                  </option>
                </select>
              </template>
              <template v-else>
                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                  {{ scoreMapFor(student.scores)[category.key]?.numeric_value ?? 0 }}
                </span>
              </template>
            </td>

            <td class="px-4 py-3">
              <select
                :disabled="!editMode || isDisabled"
                :value="getAttendanceValue(student)"
                @change="setCombinedAttendance(student, $event.target.value)"
                class="h-9 w-44 rounded border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 text-sm font-semibold"
              >
                <option value="5">✅ On time (5)</option>
                <option value="3">⚠️ Late / Left early (3)</option>
                <option value="absent">❌ Absent (0)</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

