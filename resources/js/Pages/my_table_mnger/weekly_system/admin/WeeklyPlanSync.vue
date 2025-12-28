<template>
  <div class="q-pa-md">
    <q-card flat bordered class="q-pa-md">
      <div class="row q-col-gutter-md items-end">
        <!-- Schedule Copy -->
        <div class="col-12 col-sm-4">
          <q-select
            v-model="selectedCopyId"
            :options="activeCopies"
            option-value="id"
            option-label="name"
            label="Active Schedule"
            outlined
            dense
            emit-value
            map-options
            :loading="loadingCopies"
          >
            <template v-slot:prepend>
              <q-icon name="content_copy" color="primary" />
            </template>
          </q-select>
        </div>

        <!-- Semester -->
        <div class="col-12 col-sm-4">
          <q-select
            v-model="semesterNumber"
            :options="semesterOptions"
            label="Semester"
            outlined
            dense
            emit-value
            map-options
          >
            <template v-slot:prepend>
              <q-icon name="date_range" color="secondary" />
            </template>
          </q-select>
        </div>

        <!-- Week Selector -->
        <div class="col-12 col-sm-4">
          <WeekSelector
            v-model="weekNumber"
            :max-weeks="maxWeeks"
            :current-week="currentWeek"
          />
        </div>
      </div>

      <q-separator class="q-my-lg" />

      <div class="row q-gutter-md justify-center">
        <q-btn
          color="primary"
          icon="auto_fix_high"
          label="Generate Plans"
          :loading="generating"
          @click="generatePlans"
          size="lg"
          class="q-px-xl"
        >
          <q-tooltip>Generate weekly plans for all teachers for this week</q-tooltip>
        </q-btn>
        
        <q-btn
          outline
          color="secondary"
          icon="sync"
          label="Sync Current Week"
          @click="syncCurrentWeek"
          size="lg"
          class="q-px-xl"
        >
          <q-tooltip>Sync current week plans with any schedule changes</q-tooltip>
        </q-btn>
      </div>

      <div class="q-mt-xl text-center text-grey-7">
        <q-icon name="info" color="info" size="xs" class="q-mr-sm" />
        Generating plans will create empty weekly plan entries based on the active schedule.
        Syncing will ensure any changes in the schedule (like new classes) are reflected in the current week's plans.
      </div>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import WeekSelector from '../components/weekly-plans/WeekSelector.vue'

const props = defineProps({
  initialCopyId: [Number, String],
  initialSemester: Number,
  initialWeek: Number
})

const emit = defineEmits(['update:week', 'update:copy', 'update:semester', 'refreshed'])

const $q = useQuasar()

// Data
const activeCopies = ref([])
const generating = ref(false)

// Internal state or sync with props
const selectedCopyId = ref(props.initialCopyId)
const semesterNumber = ref(props.initialSemester || 1)
const weekNumber = ref(props.initialWeek || 1)
const maxWeeks = ref(18)
const currentWeek = ref(1)

const semesterOptions = [
  { label: 'Semester 1', value: 1 },
  { label: 'Semester 2', value: 2 }
]

const selectedCopy = computed(() => {
  return activeCopies.value.find(c => c.id === selectedCopyId.value)
})

// Loading states
const loadingCopies = ref(false)

const fetchActiveCopies = async () => {
  loadingCopies.value = true
  try {
    const response = await axios.get('/admin/schedule-copies', {
      params: { status: 'active' }
    })
    const copies = response.data.data || response.data || []
    activeCopies.value = copies.filter(c => c.status === 'active')
    if (activeCopies.value.length && !selectedCopyId.value) {
      selectedCopyId.value = activeCopies.value[0].id
    }
  } catch (error) {
    console.error('Error fetching copies:', error)
  } finally {
    loadingCopies.value = false
  }
}

const generatePlans = async () => {
  if (!selectedCopyId.value) {
    $q.notify({ type: 'warning', message: 'Please select a schedule copy first' })
    return
  }

  generating.value = true
  try {
    const response = await axios.post('/weekly-system/api/weekly-plans/generate', {
      copy_id: selectedCopyId.value,
      week_number: weekNumber.value,
      semester_number: semesterNumber.value
    })
    
    const result = response.data
    $q.notify({
      type: 'positive',
      message: `Generated ${result.created} plans (${result.skipped} already existed)`
    })
    emit('refreshed')
  } catch (error) {
    console.error('Error generating plans:', error)
    $q.notify({ type: 'negative', message: error.response?.data?.message || 'Failed to generate plans' })
  } finally {
    generating.value = false
  }
}

const syncCurrentWeek = async () => {
  if (!selectedCopy.value) {
    $q.notify({ type: 'warning', message: 'No active schedule found' })
    return
  }

  $q.loading.show({ message: 'Syncing week plans...' })
  try {
    const response = await axios.post('/weekly-system/api/weekly-plans/sync-week', {
      academic_year_id: selectedCopy.value.academic_year_id,
      semester_number: semesterNumber.value,
      week_number: weekNumber.value
    })
    
    $q.notify({ 
      type: 'positive', 
      message: response.data.message || 'Week synced successfully' 
    })
    emit('refreshed')
  } catch (error) {
    console.error('Sync error:', error)
    $q.notify({ type: 'negative', message: 'Failed to sync week' })
  } finally {
    $q.loading.hide()
  }
}

onMounted(() => {
  fetchActiveCopies()
  // Calculate current week
  const now = new Date()
  const startOfYear = new Date(now.getFullYear(), 0, 1)
  currentWeek.value = Math.ceil(((now - startOfYear) / 86400000 + startOfYear.getDay() + 1) / 7)
  if (!props.initialWeek) {
    weekNumber.value = currentWeek.value > maxWeeks.value ? 1 : currentWeek.value
  }
})
</script>
