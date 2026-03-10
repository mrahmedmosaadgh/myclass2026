<template>
  <div class="p-6 space-y-8 bg-gray-50 min-h-screen">
    <h1 class="text-3xl font-bold text-center text-gray-800">UnifiedStudentCard Examples</h1>
    
    <!-- Attendance Mode Example -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-blue-600">Attendance Mode</h2>
      <p class="text-gray-600 mb-6">Click cards to toggle attendance. Shows attendance indicator at bottom.</p>
      
      <div class="flex flex-wrap gap-6 justify-center">
        <UnifiedStudentCard
          v-for="student in sampleStudents"
          :key="`attendance-${student.id}`"
          :student="student"
          card-mode="attendance"
          :is-present="attendanceStatus[student.id]"
          :show-points-badge="true"
          :student-summary="getStudentSummary(student.id)"
          @toggle-attendance="handleToggleAttendance"
        />
      </div>
    </div>

    <!-- Selection Mode Example -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
      <h2 class="text-2xl font-bold mb-4 text-green-600">Selection Mode</h2>
      <p class="text-gray-600 mb-6">Click cards to select students for rewards. Absent students are disabled.</p>
      
      <div class="flex flex-wrap gap-6 justify-center">
        <UnifiedStudentCard
          v-for="student in sampleStudents"
          :key="`selection-${student.id}`"
          :student="student"
          card-mode="selection"
          :selected="selectedStudents.includes(student.id)"
          :disable-behavior="!attendanceStatus[student.id]"
          :show-points-badge="true"
          :student-summary="getStudentSummary(student.id)"
          @select="handleSelectStudent"
        />
      </div>
      
      <div class="mt-4 p-4 bg-blue-50 rounded-lg">
        <p class="font-semibold">Selected Students: {{ selectedStudents.length }}</p>
        <p class="text-sm text-gray-600">{{ selectedStudents.map(id => sampleStudents.find(s => s.id === id)?.firstName).join(', ') }}</p>
      </div>
    </div>

    <!-- Controls -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
      <h3 class="text-xl font-bold mb-4">Controls</h3>
      <div class="flex gap-4 flex-wrap">
        <q-btn color="positive" @click="markAllPresent">Mark All Present</q-btn>
        <q-btn color="warning" @click="markAllAbsent">Mark All Absent</q-btn>
        <q-btn color="primary" @click="selectAllPresent">Select All Present</q-btn>
        <q-btn color="negative" @click="clearSelection">Clear Selection</q-btn>
        <q-btn color="secondary" @click="randomizePoints">Randomize Points</q-btn>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import UnifiedStudentCard from './UnifiedStudentCard.vue'

// Sample data
const sampleStudents = ref([
  { id: 1, firstName: 'Alice', lastName: 'Johnson', avatar: null },
  { id: 2, firstName: 'Bob', lastName: 'Smith', avatar: null },
  { id: 3, firstName: 'Charlie', lastName: 'Brown', avatar: null },
  { id: 4, firstName: 'Diana', lastName: 'Wilson', avatar: null },
  { id: 5, firstName: 'Eve', lastName: 'Davis', avatar: null },
  { id: 6, firstName: 'Frank', lastName: 'Miller', avatar: null }
])

// Reactive state
const attendanceStatus = reactive({
  1: true,
  2: true,
  3: false,
  4: true,
  5: false,
  6: true
})

const selectedStudents = ref([])

const studentPoints = reactive({
  1: { positive: 15, negative: 2 },
  2: { positive: 8, negative: 0 },
  3: { positive: 5, negative: 3 },
  4: { positive: 12, negative: 1 },
  5: { positive: 3, negative: 5 },
  6: { positive: 20, negative: 0 }
})

// Methods
function getStudentSummary(studentId) {
  const points = studentPoints[studentId] || { positive: 0, negative: 0 }
  return {
    positive: points.positive,
    negative: points.negative,
    total: points.positive - points.negative
  }
}

function handleToggleAttendance(studentId, newStatus) {
  attendanceStatus[studentId] = newStatus
  console.log(`Student ${studentId} attendance: ${newStatus}`)
  
  // Remove from selection if marked absent
  if (!newStatus && selectedStudents.value.includes(studentId)) {
    selectedStudents.value = selectedStudents.value.filter(id => id !== studentId)
  }
}

function handleSelectStudent(studentId) {
  const index = selectedStudents.value.indexOf(studentId)
  if (index === -1) {
    selectedStudents.value.push(studentId)
  } else {
    selectedStudents.value.splice(index, 1)
  }
  console.log(`Selected students:`, selectedStudents.value)
}

function markAllPresent() {
  sampleStudents.value.forEach(student => {
    attendanceStatus[student.id] = true
  })
}

function markAllAbsent() {
  sampleStudents.value.forEach(student => {
    attendanceStatus[student.id] = false
  })
  selectedStudents.value = []
}

function selectAllPresent() {
  selectedStudents.value = sampleStudents.value
    .filter(student => attendanceStatus[student.id])
    .map(student => student.id)
}

function clearSelection() {
  selectedStudents.value = []
}

function randomizePoints() {
  sampleStudents.value.forEach(student => {
    studentPoints[student.id] = {
      positive: Math.floor(Math.random() * 25),
      negative: Math.floor(Math.random() * 8)
    }
  })
}
</script>

<style scoped>
.space-y-8 > * + * {
  margin-top: 2rem;
}
</style>