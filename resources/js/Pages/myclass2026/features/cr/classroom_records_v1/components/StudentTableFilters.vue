<script setup>
import { computed } from 'vue'

const props = defineProps({
  students: {
    type: Array,
    required: true,
    default: () => []
  },
  modelValue: {
    type: String,
    default: 'all'
  }
})

const emit = defineEmits(['update:modelValue'])

// Get unique first letters from ALL students (always show all available letters)
const firstLetters = computed(() => {
  const letters = new Set()
  props.students.forEach(student => {
    const firstLetter = student.name?.charAt(0)?.toUpperCase() || '#'
    if (firstLetter.match(/[A-Z]/)) {
      letters.add(firstLetter)
    } else {
      letters.add('#') // For non-alphabetic characters
    }
  })
  return Array.from(letters).sort()
})

// Get filtered students based on current filter value
const getFilteredStudents = () => {
  if (props.modelValue === 'all') {
    return props.students
  }
  
  if (['present', 'absent'].includes(props.modelValue)) {
    return props.students.filter(s => s.period?.attendance_status === props.modelValue)
  }
  
  // Filter by letter
  return props.students.filter(student => {
    const firstLetter = student.name?.charAt(0)?.toUpperCase() || '#'
    if (props.modelValue === '#') {
      return !firstLetter.match(/[A-Z]/)
    }
    return firstLetter === props.modelValue
  })
}

// Attendance counts based on ALL students (not filtered)
const totalCount = computed(() => props.students.length)
const presentCount = computed(() => props.students.filter(s => s.period?.attendance_status !== 'absent').length)
const absentCount = computed(() => props.students.filter(s => s.period?.attendance_status === 'absent').length)

// Current filtered count
const filteredCount = computed(() => getFilteredStudents().length)

// Attendance filter options
const attendanceFilters = computed(() => [
  { value: 'all', label: `All (${totalCount.value})`, color: 'bg-gray-500 hover:bg-gray-600' },
  { value: 'present', label: `Present (${presentCount.value})`, color: 'bg-green-500 hover:bg-green-600' },
  { value: 'absent', label: `Absent (${absentCount.value})`, color: 'bg-red-500 hover:bg-red-600' }
])

// Get count label for a letter filter
const getLetterCount = (letter) => {
  if (letter === '#') {
    return props.students.filter(s => {
      const firstLetter = s.name?.charAt(0)?.toUpperCase() || '#'
      return !firstLetter.match(/[A-Z]/)
    }).length
  }
  return props.students.filter(s => s.name?.charAt(0)?.toUpperCase() === letter).length
}

// Handle filter selection
const selectFilter = (value) => {
  emit('update:modelValue', value)
}

// Check if filter is active
const isActive = (value) => {
  return props.modelValue === value
}
</script>

<template>
  <div class="mb-4 space-y-3">
    <!-- First Letter Filters -->
    <div class="flex flex-wrap items-center gap-2">
      <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Filter by name:</span>
      <button
        v-for="letter in firstLetters"
        :key="letter"
        @click="selectFilter(letter)"
        :class="[
          'px-3 py-1 rounded-full text-sm font-semibold transition-all duration-200 transform hover:scale-105',
          isActive(letter)
            ? 'bg-indigo-600 text-white shadow-lg ring-2 ring-indigo-300 dark:ring-indigo-700'
            : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/30'
        ]"
      >
        {{ letter }} ({{ getLetterCount(letter) }})
      </button>
    </div>

    <!-- Attendance Status Filters -->
    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
      <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Attendance:</span>
      <button
        v-for="filter in attendanceFilters"
        :key="filter.value"
        @click="selectFilter(filter.value)"
        :class="[
          'px-4 py-1.5 rounded-full text-sm font-semibold transition-all duration-200 transform hover:scale-105',
          isActive(filter.value)
            ? filter.color + ' text-white shadow-lg ring-2 ring-opacity-50'
            : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
        ]"
      >
        {{ filter.label }}
      </button>
    </div>
  </div>
</template>
