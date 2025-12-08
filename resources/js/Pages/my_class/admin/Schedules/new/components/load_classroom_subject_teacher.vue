<template>
  <div>
    <q-table
      :rows="classroomData"
      :columns="columns"
      :loading="loading"
      row-key="id"
      flat
      bordered
    >
      <template v-slot:top>
        <div class="text-h6">Classroom Subjects</div>
      </template>
    </q-table>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  schoolId: {
    type: Number,
    required: true
  }
})

const classroomData = ref([])
const loading = ref(false)

const columns = [
  { name: 'classroom', label: 'Classroom', field: 'classroom_name', align: 'left' },
  { name: 'subject', label: 'Subject', field: 'subject_name', align: 'left' },
  { name: 'teacher', label: 'Teacher', field: 'teacher_name', align: 'left' }
]

const fetchData = () => {
  if (!props.schoolId) return

  loading.value = true
  axios.get(`/admin/school/${props.schoolId}/classroom-subject-teachers`)
    .then(response => {
      classroomData.value = response.data
    })
    .catch(error => {
      console.error('Error fetching data:', error)
    })
    .finally(() => {
      loading.value = false
    })
}

watch(() => props.schoolId, fetchData, { immediate: true })
</script>
