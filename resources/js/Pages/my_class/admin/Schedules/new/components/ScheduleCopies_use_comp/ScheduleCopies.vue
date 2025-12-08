<template>
  <div>:{{ schoolId }}
    <q-select
      v-model="selectedSchedule"
      :options="scheduleOptions"
      label="Select Schedule Copy"
      outlined
      emit-value
      map-options
      :loading="loading"
      option-value="value"
      option-label="label"
      @update:model-value="handleSelection"
    />
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

const emit = defineEmits(['selected','scheduleCopyId'])

const selectedSchedule = ref(null)
const scheduleOptions = ref([])
const loading = ref(false)

const fetchSchedules = () => {
  if (!props.schoolId) return

  loading.value = true
  axios.get(`/admin/school/${props.schoolId}/schedule-copies`)
    .then(response => {
      scheduleOptions.value = response.data.map(schedule => ({
        label: schedule.name,
        value: schedule.id,
        description: schedule.description,
        active: schedule.active
      }))

      // Auto-select active schedule if exists
      const activeSchedule = response.data.find(s => s.active)
      if (activeSchedule) {
        selectedSchedule.value = activeSchedule.id
        emit('selected', activeSchedule.id)
        emit('scheduleCopyId', activeSchedule.id)

      }
    })
    .catch(error => {
      console.error('Error loading schedules:', error)
    })
    .finally(() => {
      loading.value = false
    })
}

const handleSelection = (value) => {
  emit('selected', value)
  emit('scheduleCopyId', value)

}

watch(() => props.schoolId, fetchSchedules, { immediate: true })
</script>
