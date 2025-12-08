<template>
    {{ selectedSchool }}
    <br>
    {{ schoolOptions }}
  <q-select
    v-model="selectedSchool"
    :options="schoolOptions"
    label="Select School"
    outlined
    emit-value
    map-options
    @update:model-value="onSchoolSelected"
    :loading="loading"
  />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  role: {
    type: String,
    required: false
  },
  teacherId: {
    type: Number,
    default: null
  }
})
const emit = defineEmits( ['selected_school','schools'])

const selectedSchool = ref(null)
const schoolOptions = ref([])
const loading = ref(false)

const loadSchools = () => {
  loading.value = true

  const endpoint = props.role === 'admin'
    ? '/admin/schools'
    : `/teacher/${props.teacherId}/schools`

  axios.get(endpoint)
    .then(response => {
      schoolOptions.value = response.data.map(school => ({
        label: school.name,
        value: school.id
      }))

      // Load saved school if exists
      const savedSchool = localStorage.getItem('selected_school')
      if (savedSchool) {
        selectedSchool.value = parseInt(savedSchool)
      }

      emit('schools',response.data)
      emit('selected_school',selectedSchool.value)
    })
    .catch(error => {
      console.error('Error loading schools:', error)
    })
    .finally(() => {
      loading.value = false
    })
}

const onSchoolSelected = (value) => {
  localStorage.setItem('selected_school', value)
  emit('selected_school', value)
}

onMounted(loadSchools)
</script>
