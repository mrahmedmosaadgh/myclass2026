<template>
    <div>
      <q-btn
        label="Create Schedule Entries"
        color="primary"
        @click="createEntries"
        :loading="loading"
      />
      <q-dialog v-model="showDialog">
        <q-card>
          <q-card-section>
            <div class="text-h6">Create Schedule Entries</div>
          </q-card-section>
          <q-card-section>
            <div v-if="error" class="text-negative">{{ error }}</div>
            <div v-if="success" class="text-positive">Entries created successfully!</div>
          </q-card-section>
        </q-card>
      </q-dialog>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue'
  import axios from 'axios'

  const props = defineProps({
  schoolId: {
    type: Number,
    required: true
  },
  scheduleCopyId: {
    type: Number,
    required: false,
    default: null
  }
})

  const loading = ref(false)
  const showDialog = ref(false)
  const error = ref(null)
  const success = ref(false)

  const createEntries = async () => {
    loading.value = true
    error.value = null
    success.value = false

    try {
      await axios.post('/admin/schedule-copies/create-entries', {
        school_id: props.schoolId,
        schedule_copy_id: props.scheduleCopyId
      })
      success.value = true
      showDialog.value = true
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create schedule entries'
      showDialog.value = true
    } finally {
      loading.value = false
    }
  }
  </script>
