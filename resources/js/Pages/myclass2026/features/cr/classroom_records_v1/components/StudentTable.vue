<script setup>
import { computed, ref } from 'vue'
import { useQuasar } from 'quasar'
import AttendanceToggle from './AttendanceToggle.vue'
import DropdownConfirm from './DropdownConfirm.vue'
import { useAttendanceService } from '../composables/useAttendanceService'

const $q = useQuasar()
const attendanceService = useAttendanceService()

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

// Track focused student for keyboard navigation
const focusedStudentId = ref(null)

// Attendance edit mode state
const attendanceEditMode = ref(false)
const attendanceEditTimer = ref(null)

// Confirmation state for dropdown
const pendingConfirmation = ref(null)
const bulkConfirmation = ref(null)

// Selection mode state
const selectionMode = ref(false)
const selectedStudents = ref(new Set())
const selectedColumn = ref(null)
const bulkActionValue = ref(null)

// Toggle attendance edit mode
const toggleAttendanceEditMode = () => {
  attendanceEditMode.value = !attendanceEditMode.value
  
  if (attendanceEditMode.value) {
    // Start 2-minute timer
    startAttendanceEditTimer()
  } else {
    // Clear timer
    clearAttendanceEditTimer()
  }
}

// Start the auto-disable timer
const startAttendanceEditTimer = () => {
  clearAttendanceEditTimer()
  attendanceEditTimer.value = setTimeout(() => {
    attendanceEditMode.value = false
  }, 2 * 60 * 1000) // 2 minutes
}

// Clear the timer
const clearAttendanceEditTimer = () => {
  if (attendanceEditTimer.value) {
    clearTimeout(attendanceEditTimer.value)
    attendanceEditTimer.value = null
  }
}

// Reset timer on user interaction
const resetAttendanceEditTimer = () => {
  if (attendanceEditMode.value) {
    startAttendanceEditTimer()
  }
}

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

  handleScoreUpdate(student.student_period_id, scoreRecord.mapping_id, value)
}

const setAttendanceStatus = async (student, status) => {
  if (!props.editMode || isDisabled.value) return
  
  // Use centralized service for attendance update
  const success = await attendanceService.updateAttendance(student.student_period_id, status)
  
  if (success) {
    // Still emit for backward compatibility and dirty tracking
    emit('update:attendance', {
      student_period_id: student.student_period_id,
      attendance_status: status
    })
  }
}

const setAttendanceScore = async (student, value) => {
  if (!props.editMode || isDisabled.value || (student.period?.attendance_status === 'absent')) return
  
  // Use centralized service for attendance update
  const success = await attendanceService.updateAttendance(
    student.student_period_id, 
    student.period?.attendance_status, 
    Number(value) || 0
  )
  
  if (success) {
    // Still emit for backward compatibility and dirty tracking
    emit('update:attendance', {
      student_period_id: student.student_period_id,
      attendance_status: student.period?.attendance_status,
      attendance_score: Number(value) || 0
    })
  }
}

// Combined function to handle both status and score in one action
const setCombinedAttendance = async (student, value) => {
  if (!props.editMode || isDisabled.value) return
  
  if (value === 'absent') {
    // Use centralized service for attendance update
    const success = await attendanceService.updateAttendance(student.student_period_id, 'absent', 0)
    
    if (success) {
      // Still emit for backward compatibility and dirty tracking
      emit('update:attendance', {
        student_period_id: student.student_period_id,
        attendance_status: 'absent',
        attendance_score: 0
      })
    }
  } else {
    // Use centralized service for attendance update
    const success = await attendanceService.updateAttendance(student.student_period_id, 'present', Number(value) || 0)
    
    if (success) {
      // Still emit for backward compatibility and dirty tracking
      emit('update:attendance', {
        student_period_id: student.student_period_id,
        attendance_status: 'present',
        attendance_score: Number(value) || 0
      })
    }
  }
}

// Handle attendance toggle changes from AttendanceToggle component
const handleAttendanceToggle = async (data) => {
  if (!attendanceEditMode.value || isDisabled.value) return
  
  // Reset timer on user interaction
  resetAttendanceEditTimer()
  
  const { student_period_id, state } = data
  
  // Use centralized service for attendance update
  const success = await attendanceService.updateAttendance(student_period_id, state)
  
  if (success) {
    // Still emit for backward compatibility and dirty tracking
    const attendanceData = {
      student_period_id,
      attendance_status: state,
      attendance_score: state === 'absent' ? 0 : (state === 'late' ? 3 : 5)
    }
    emit('update:attendance', attendanceData)
  }
}

// Handle confirmation requests from AttendanceToggle
const handleShowConfirmation = (data) => {
  pendingConfirmation.value = data
}

// Confirm attendance change
const confirmAttendanceChange = () => {
  if (!pendingConfirmation.value) return
  
  const { student_period_id, state } = pendingConfirmation.value
  
  const attendanceData = {
    student_period_id,
    attendance_status: state,
    attendance_score: state === 'absent' ? 0 : (state === 'late' ? 3 : 5)
  }
  
  emit('update:attendance', attendanceData)
  pendingConfirmation.value = null
}

// Cancel attendance change
const cancelAttendanceChange = () => {
  pendingConfirmation.value = null
}

// Confirm bulk action
const confirmBulkAction = () => {
  if (!bulkConfirmation.value) return
  
  if (bulkConfirmation.value.title.includes('Mark All Absent')) {
    executeMarkAllAbsent()
  } else {
    executeBulkAction()
  }
  
  bulkConfirmation.value = null
}

// Cancel bulk action
const cancelBulkAction = () => {
  bulkConfirmation.value = null
}

// Selection mode functions
const toggleSelectionMode = () => {
  selectionMode.value = !selectionMode.value
  if (!selectionMode.value) {
    // Clear selections when exiting selection mode
    selectedStudents.value.clear()
    selectedColumn.value = null
    bulkActionValue.value = null
  }
}

const toggleStudentSelection = (studentId) => {
  if (selectedStudents.value.has(studentId)) {
    selectedStudents.value.delete(studentId)
  } else {
    selectedStudents.value.add(studentId)
  }
}

const selectAllStudents = () => {
  selectedStudents.value.clear()
  props.students.forEach(student => {
    selectedStudents.value.add(student.student_period_id)
  })
}

const clearSelection = () => {
  selectedStudents.value.clear()
}

const selectColumn = (columnKey) => {
  selectedColumn.value = columnKey
  bulkActionValue.value = null
}

const applyBulkAction = () => {
  if (!selectedColumn.value || selectedStudents.value.size === 0) return
  
  const actionData = {
    students: Array.from(selectedStudents.value),
    column: selectedColumn.value,
    value: bulkActionValue.value
  }
  
  // Set up confirmation
  bulkConfirmation.value = {
    title: `Apply ${selectedColumn.value} to ${selectedStudents.value.size} students?`,
    message: `Set ${selectedColumn.value} to ${bulkActionValue.value} for ${selectedStudents.value.size} selected students.<br>Continue?`,
    confirmLabel: '✅ Apply',
    cancelLabel: '❌ Cancel',
    confirmColor: 'primary',
    cancelColor: 'grey-7'
  }
}

const executeBulkAction = async () => {
  if (!selectedColumn.value || selectedStudents.value.size === 0) return
  
  const studentIds = Array.from(selectedStudents.value)
  let success = false
  
  if (selectedColumn.value === 'attendance') {
    // Use centralized service for bulk attendance update
    success = await attendanceService.bulkUpdateAttendance(studentIds, bulkActionValue.value)
  } else {
    // Use centralized service for bulk score update
    const sampleStudent = props.students.find(s => s.scores?.find(score => score.category_key === selectedColumn.value))
    if (sampleStudent) {
      const scoreRecord = scoreMapFor(sampleStudent.scores)[selectedColumn.value]
      if (scoreRecord) {
        success = await attendanceService.bulkUpdateScores(studentIds, scoreRecord.mapping_id, Number(bulkActionValue.value) || 0)
      }
    }
  }
  
  if (success) {
    // Still emit for backward compatibility and dirty tracking
    selectedStudents.value.forEach(studentId => {
      if (selectedColumn.value === 'attendance') {
        emit('update:attendance', {
          student_period_id: studentId,
          attendance_status: bulkActionValue.value,
          attendance_score: bulkActionValue.value === 'absent' ? 0 : (bulkActionValue.value === 'late' ? 3 : 5)
        })
      } else {
        // Handle score columns
        const student = props.students.find(s => s.student_period_id === studentId)
        const scoreRecord = scoreMapFor(student.scores)[selectedColumn.value]
        if (scoreRecord) {
          emit('update:scores', {
            student_period_id: studentId,
            mapping_id: scoreRecord.mapping_id,
            numeric_value: Number(bulkActionValue.value) || 0
          })
        }
      }
    })
    
    // Clear selections after applying
    selectedStudents.value.clear()
    selectedColumn.value = null
    bulkActionValue.value = null
  }
}

// Handle focus events from AttendanceToggle
const handleAttendanceFocus = (studentPeriodId) => {
  focusedStudentId.value = studentPeriodId
}

// Bulk action functions
const markAllPresent = () => {
  if (!attendanceEditMode.value || isDisabled.value) return
  
  // Reset timer on user interaction
  resetAttendanceEditTimer()
  
  props.students.forEach(student => {
    emit('update:attendance', {
      student_period_id: student.student_period_id,
      attendance_status: 'present',
      attendance_score: 5
    })
  })
}

const markAllLate = () => {
  if (!attendanceEditMode.value || isDisabled.value) return
  
  // Reset timer on user interaction
  resetAttendanceEditTimer()
  
  props.students.forEach(student => {
    emit('update:attendance', {
      student_period_id: student.student_period_id,
      attendance_status: 'present',
      attendance_score: 3
    })
  })
}

const markAllAbsent = () => {
  if (!attendanceEditMode.value || isDisabled.value) return
  
  // Count students with points
  const studentsWithPoints = props.students.filter(student => 
    student.period?.total_score > 0
  )
  
  // If no students have points, proceed directly
  if (studentsWithPoints.length === 0) {
    executeMarkAllAbsent()
    return
  }
  
  // Set up bulk confirmation with detailed breakdown
  let message = `${studentsWithPoints.length} students have points that will be affected:\n\n`
  
  // Add breakdown for each student with points
  studentsWithPoints.forEach(student => {
    const attendanceScore = student.period?.attendance_score || 0
    const totalScore = student.period?.total_score || 0
    const otherPoints = totalScore - attendanceScore
    
    message += `👤 ${student.name}:\n`
    message += `  • Current: ${totalScore} points (Attendance: ${attendanceScore}, Other: ${otherPoints})\n`
    message += `  • After absent: ${otherPoints} points\n\n`
  })
  
  message += `⚠️ Total points lost: ${studentsWithPoints.reduce((sum, student) => sum + (student.period?.attendance_score || 0), 0)} attendance points`
  
  bulkConfirmation.value = {
    title: '🗑️ Mark All Absent?',
    message: message,
    confirmLabel: '🗑️ Mark All Absent',
    cancelLabel: '❌ Cancel',
    confirmColor: 'negative',
    cancelColor: 'grey-7'
  }
}

const executeMarkAllAbsent = () => {
  // Reset timer on user interaction
  resetAttendanceEditTimer()
  
  props.students.forEach(student => {
    emit('update:attendance', {
      student_period_id: student.student_period_id,
      attendance_status: 'absent',
      attendance_score: 0
    })
  })
}

// Keyboard navigation for attendance column
const handleTableKeydown = (event) => {
  if (!props.editMode || isDisabled.value) return
  
  const currentIndex = props.students.findIndex(s => s.student_period_id === focusedStudentId.value)
  
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      if (currentIndex < props.students.length - 1) {
        focusedStudentId.value = props.students[currentIndex + 1].student_period_id
        // Focus the next attendance toggle
        setTimeout(() => {
          const nextToggle = document.querySelector(`[data-attendance-id="${focusedStudentId.value}"]`)
          nextToggle?.focus()
        }, 0)
      }
      break
      
    case 'ArrowUp':
      event.preventDefault()
      if (currentIndex > 0) {
        focusedStudentId.value = props.students[currentIndex - 1].student_period_id
        // Focus the previous attendance toggle
        setTimeout(() => {
          const prevToggle = document.querySelector(`[data-attendance-id="${focusedStudentId.value}"]`)
          prevToggle?.focus()
        }, 0)
      }
      break
      
    case 'a':
    case 'A':
      if (event.ctrlKey || event.metaKey) {
        event.preventDefault()
        markAllAbsent()
      }
      break
      
    case 'p':
    case 'P':
      if (event.ctrlKey || event.metaKey) {
        event.preventDefault()
        markAllPresent()
      }
      break
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
    <div class="overflow-x-auto" @keydown="handleTableKeydown">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900/40">
          <tr>
            <!-- Selection Mode Checkbox Column -->
            <th v-if="selectionMode" class="px-4 py-3 text-center">
              <input
                type="checkbox"
                :checked="selectedStudents.size === props.students.length"
                :indeterminate="selectedStudents.size > 0 && selectedStudents.size < props.students.length"
                @change="selectedStudents.size === props.students.length ? clearSelection() : selectAllStudents()"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
            </th>
            
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
              @click="selectionMode && selectColumn(category.key)"
              :class="{
                'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800': selectionMode,
                'bg-blue-50 dark:bg-blue-900/30': selectionMode && selectedColumn === category.key
              }"
            >
              {{ category.label }}
              <span v-if="selectionMode && selectedColumn === category.key" class="ml-1 text-blue-600">✓</span>
            </th>
            <th 
              class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
              @click="selectionMode && selectColumn('attendance')"
              :class="{
                'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800': selectionMode,
                'bg-blue-50 dark:bg-blue-900/30': selectionMode && selectedColumn === 'attendance'
              }"
            >
              <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                  <span>Attendance</span>
                  <span v-if="selectionMode && selectedColumn === 'attendance'" class="ml-1 text-blue-600">✓</span>
                  <!-- Selection Mode Toggle -->
                  <button
                    @click="toggleSelectionMode"
                    class="px-2 py-1 text-xs font-medium rounded transition-colors"
                    :class="selectionMode ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'"
                  >
                    {{ selectionMode ? 'Exit Selection' : 'Select Mode' }}
                  </button>
                </div>
                
                <!-- Selection Mode Controls -->
                <div v-if="selectionMode" class="flex flex-col gap-2">
                  <!-- Bulk Action Controls -->
                  <div v-if="selectedColumn && selectedStudents.size > 0" class="flex flex-col gap-1">
                    <!-- Attendance Options -->
                    <div v-if="selectedColumn === 'attendance'" class="flex gap-1">
                      <select
                        v-model="bulkActionValue"
                        class="px-2 py-1 text-xs border rounded"
                      >
                        <option value="">Select...</option>
                        <option value="present">Present (5)</option>
                        <option value="late">Late (3)</option>
                        <option value="absent">Absent (0)</option>
                      </select>
                      <button
                        @click="applyBulkAction"
                        :disabled="!bulkActionValue"
                        class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded hover:bg-blue-200 disabled:opacity-50"
                      >
                        Apply ({{ selectedStudents.size }})
                      </button>
                    </div>
                    
                    <!-- Score Options -->
                    <div v-else class="flex gap-1">
                      <select
                        v-model="bulkActionValue"
                        class="px-2 py-1 text-xs border rounded"
                      >
                        <option value="">Select...</option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                      </select>
                      <button
                        @click="applyBulkAction"
                        :disabled="!bulkActionValue"
                        class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded hover:bg-blue-200 disabled:opacity-50"
                      >
                        Apply ({{ selectedStudents.size }})
                      </button>
                    </div>
                  </div>
                  
                  <!-- Selection Info -->
                  <div v-else class="text-xs text-gray-500">
                    {{ selectedColumn ? `Select students for ${selectedColumn}` : 'Click a column to select' }}
                  </div>
                </div>
                
                <!-- Original Attendance Edit Mode (when not in selection mode) -->
                <div v-if="!selectionMode" class="flex gap-1">
                  <!-- Attendance Edit Mode Toggle -->
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input
                      type="checkbox"
                      :checked="attendanceEditMode"
                      @change="toggleAttendanceEditMode"
                      class="sr-only peer"
                    />
                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">Edit</span>
                  </label>
                  
                  <div v-if="attendanceEditMode && !isDisabled" class="flex gap-1">
                    <button
                      @click="markAllPresent"
                      class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors"
                      title="Mark all students present (Ctrl+P)"
                    >
                      All P
                    </button>
                    
                    <button
                      @click="markAllLate"
                      class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-700 rounded hover:bg-orange-200 transition-colors"
                      title="Mark all students late (Ctrl+L)"
                    >
                      All L
                    </button>
                    
                    <!-- Dropdown confirmation for bulk actions -->
                    <DropdownConfirm
                      v-if="bulkConfirmation"
                      :title="bulkConfirmation.title"
                      :message="bulkConfirmation.message"
                      :confirm-label="bulkConfirmation.confirmLabel"
                      :cancel-label="bulkConfirmation.cancelLabel"
                      :confirm-color="bulkConfirmation.confirmColor"
                      :cancel-color="bulkConfirmation.cancelColor"
                      :label="bulkConfirmation.title.includes('Apply') ? 'Apply' : 'All A'"
                      :color="bulkConfirmation.title.includes('Apply') ? 'blue' : 'red-7'"
                      @confirm="confirmBulkAction"
                      @cancel="cancelBulkAction"
                    />
                    
                    <button
                      v-else
                      @click="markAllAbsent"
                      class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors"
                      title="Mark all students absent (Ctrl+A)"
                    >
                      All A
                    </button>
                    
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                      <svg class="w-3 h-3 mr-1 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                      </svg>
                      2:00
                    </div>
                  </div>
                </div>
              </div>
            </th>
          </tr>
        </thead>

        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="student in students"
            :key="student.student_period_id"
            class="hover:bg-gray-50 dark:hover:bg-gray-900/30"
            :class="{
              'bg-blue-50 dark:bg-blue-900/20': selectionMode && selectedStudents.has(student.student_period_id)
            }"
          >
            <!-- Selection Mode Checkbox -->
            <td v-if="selectionMode" class="px-4 py-3 text-center">
              <input
                type="checkbox"
                :checked="selectedStudents.has(student.student_period_id)"
                @change="toggleStudentSelection(student.student_period_id)"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
            </td>
            
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
              <template v-if="attendanceEditMode">
                <div class="flex items-center gap-2">
                  <AttendanceToggle
                    :student="student"
                    :disabled="isDisabled"
                    @change="handleAttendanceToggle"
                    @show-confirmation="handleShowConfirmation"
                    @focus="handleAttendanceFocus"
                    :data-attendance-id="student.student_period_id"
                  />
                  
                  <!-- Show dropdown confirmation when needed -->
                  <DropdownConfirm
                    v-if="pendingConfirmation && pendingConfirmation.student_period_id === student.student_period_id"
                    :title="pendingConfirmation.config.title"
                    :message="pendingConfirmation.config.message"
                    :confirm-label="pendingConfirmation.config.confirmLabel"
                    :cancel-label="pendingConfirmation.config.cancelLabel"
                    :confirm-color="pendingConfirmation.config.confirmColor"
                    :cancel-color="pendingConfirmation.config.cancelColor"
                    label="⚠️"
                    color="warning"
                    @confirm="confirmAttendanceChange"
                    @cancel="cancelAttendanceChange"
                  />
                </div>
              </template>
              <template v-else>
                <div class="flex items-center justify-center">
                  <span 
                    class="px-3 py-2 rounded-lg text-sm font-semibold"
                    :class="{
                      'bg-green-100 text-green-700': student.period?.attendance_status === 'present',
                      'bg-yellow-100 text-yellow-700': student.period?.attendance_status === 'present' && student.period?.attendance_score === 3,
                      'bg-red-100 text-red-700': student.period?.attendance_status === 'absent'
                    }"
                  >
                    {{ student.period?.attendance_status === 'absent' ? 'Absent (0)' : 
                       student.period?.attendance_score === 3 ? 'Late (3)' : 'Present (5)' }}
                  </span>
                </div>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

