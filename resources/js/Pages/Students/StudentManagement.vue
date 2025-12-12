<template>
  <div class="p-6">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-semibold text-gray-900">Students</h1>
      <ViewToggle v-model:view="currentView" />
    </div>

    <!-- Cards View -->
    <div v-if="currentView === 'card'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <StudentCard
        v-for="student in students"
        :key="student.id"
        :student="student"
      >
        <template #actions>
          <button
            class="text-indigo-600 hover:text-indigo-900"
            @click="editStudent(student)"
          >
            Edit
          </button>
        </template>
      </StudentCard>
    </div>

    <!-- Table View -->
    <StudentTable
      v-else
      :students="students"
    >
      <template #actions="{ student }">
        <button
          class="text-indigo-600 hover:text-indigo-900"
          @click="editStudent(student)"
        >
          Edit
        </button>
      </template>
    </StudentTable>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import StudentCard from '@/Components/Students/StudentCard.vue'
import StudentTable from '@/Components/Students/StudentTable.vue'
import ViewToggle from '@/Components/Students/ViewToggle.vue'

const currentView = ref(localStorage.getItem('studentViewPreference') || 'card')
const students = ref([
  {
    id: 1,
    name: 'John Doe',
    class: '10A',
    grade: 'A',
    status: 'active',
    attend: 1,
    email: 'john@example.com',
    phone: '+1234567890',
    address: '123 School St'
  },
  {
    id: 2,
    name: 'Jane Smith',
    class: '10A',
    grade: 'B',
    status: 'active',
    attend: 0,
    email: 'jane@example.com',
    phone: '+1234567891',
    address: '124 School St'
  }
])

// Save view preference to localStorage
watch(currentView, (newView) => {
  try { localStorage.setItem('studentViewPreference', newView) } catch (e) { /* ignore */ }
})

const editStudent = (student) => {
  console.log('Edit student:', student)
}
</script>
