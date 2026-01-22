<template>
  <q-dialog v-model="internalModel">
    <q-card style="min-width: 500px">
      <q-card-section class="bg-primary text-white">
        <div class="text-h6">Sync Class Periods</div>
        <div class="text-subtitle2">From: {{ sourceClassroom?.classroom_name }}</div>
      </q-card-section>

      <q-card-section class="q-pt-md">
        <p class="text-body2 text-grey-8">
          This will update the <strong>classes per week</strong> for matching subjects in selected classrooms to match 
          <strong>{{ sourceClassroom?.classroom_name }}</strong>.
        </p>
        
        <q-banner dense class="bg-blue-1 text-primary q-mb-md rounded-borders">
          <template v-slot:avatar>
            <q-icon name="info" color="primary" />
          </template>
          Only subjects that already exist in the target classroom will be updated. No new subjects will be assigned.
        </q-banner>

        <div class="text-subtitle2 q-mb-sm">Select Target Classrooms:</div>
        
        <q-list bordered separator class="rounded-borders scroll" style="max-height: 300px">
          <q-item v-if="availableTargets.length === 0" class="text-center text-grey">
            <q-item-section>No other classrooms available</q-item-section>
          </q-item>
          
          <q-item tag="label" v-for="cls in availableTargets" :key="cls.classroom_id" v-ripple>
            <q-item-section avatar>
              <q-checkbox v-model="selectedTargets" :val="cls.classroom_id" />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ cls.classroom_name }}</q-item-label>
              <q-item-label caption>{{ cls.cst_count }} subjects assigned</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>

      <q-card-actions align="right" class="bg-grey-1">
        <q-btn flat label="Cancel" color="primary" v-close-popup />
        <q-btn 
          unelevated
          label="Sync Selected" 
          color="secondary" 
          icon="sync_alt"
          @click="executeSync"
          :loading="syncing"
          :disable="selectedTargets.length === 0"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { useSchoolDataStore } from '@/Stores/schoolData'
import { useQuasar } from 'quasar'

const props = defineProps({
  modelValue: Boolean,
  sourceClassroom: Object,
  allClassrooms: Array
})

const emit = defineEmits(['update:modelValue', 'synced'])

const $q = useQuasar()
const schoolDataStore = useSchoolDataStore()
const syncing = ref(false)
const selectedTargets = ref([])

const internalModel = computed({
  get: () => props.modelValue,
  set: (val) => {
    emit('update:modelValue', val)
    if (!val) selectedTargets.value = [] // Reset on close
  }
})

const availableTargets = computed(() => {
  if (!props.allClassrooms || !props.sourceClassroom) return []
  return props.allClassrooms.filter(c => c.classroom_id !== props.sourceClassroom.classroom_id)
})

const executeSync = async () => {
  if (selectedTargets.value.length === 0) return

  syncing.value = true
  try {
    const response = await axios.post('/weekly-system/api/cst-sync-classes-per-week', {
      school_id: schoolDataStore.schoolId,
      source_classroom_id: props.sourceClassroom.classroom_id,
      target_classroom_ids: selectedTargets.value
    })

    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: response.data.message
      })
      emit('synced')
      internalModel.value = false
    }
  } catch (error) {
    console.error('Sync failed:', error)
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Sync failed'
    })
  } finally {
    syncing.value = false
  }
}
</script>
