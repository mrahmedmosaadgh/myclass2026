<template>
  <Head title="Weekly Plans Manager" />
  <div class="q-pa-md">
    <WeeklyPlanMenu />
    <!-- Page Header -->
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="view_week" class="q-mr-sm" color="primary" />
          Weekly Plans Management
        </h4>
        <p class="text-grey-7 q-mb-none">
          Monitor and generate weekly plans for teachers
        </p>
      </div>
      <div class="col-auto">
        <q-btn
          color="primary"
          icon="auto_fix_high"
          label="Generate Plans"
          :loading="generating"
          @click="generatePlans"
        />
      </div>
    </div>

    <!-- Controls Row -->
    <q-card flat bordered class="q-pa-md q-mb-lg">
      <div class="row q-gutter-md items-end">
        <!-- Schedule Copy -->
        <div class="col-12 col-sm-4 col-md-3">
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
        <div class="col-12 col-sm-3 col-md-2">
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

        <!-- Week Selector & Sync -->
        <div class="col-12 col-sm-5 col-md-5">
          <div class="row q-gutter-sm items-center">
            <WeekSelector
              v-model="weekNumber"
              :max-weeks="maxWeeks"
              :current-week="currentWeek"
            />
            <q-btn
              flat
              dense
              round
              icon="sync"
              color="secondary"
              @click="syncCurrentWeek"
            >
              <q-tooltip>Sync Current Week with Active Schedule</q-tooltip>
            </q-btn>
          </div>
        </div>

        <!-- Stats Summary -->
        <div class="col-auto q-ml-auto">
          <div class="row q-gutter-sm">
            <q-chip dense color="green-2" text-color="green-9" icon="check_circle">
              {{ summaryStats.completed }} done
            </q-chip>
            <q-chip dense color="amber-2" text-color="amber-9" icon="timelapse">
              {{ summaryStats.partial }} partial
            </q-chip>
            <q-chip dense color="red-1" text-color="red-8" icon="hourglass_empty">
              {{ summaryStats.empty }} empty
            </q-chip>
          </div>
        </div>
      </div>
    </q-card>

    <!-- Loading State -->
    <div v-if="loading" class="row justify-center q-pa-xl">
      <q-spinner-dots size="50px" color="primary" />
    </div>

    <!-- Empty State -->
    <q-card v-else-if="!teacherStats.length" flat bordered class="text-center q-pa-xl">
      <q-icon name="playlist_add" size="64px" color="grey-5" />
      <p class="text-h6 text-grey-7 q-mt-md">No weekly plans for this week</p>
      <p class="text-grey-6">Generate plans to start tracking teacher completion</p>
      <q-btn color="primary" icon="auto_fix_high" label="Generate Plans" @click="generatePlans" class="q-mt-md" />
    </q-card>

    <!-- Teacher Completion List -->
    <div v-else class="q-gutter-md">
      <CompletionProgressBar
        v-for="teacher in teacherStats"
        :key="teacher.teacher_id"
        :teacher="teacher"
        @view="viewTeacherPlans"
      />
    </div>

    <!-- Teacher Plans Dialog -->
    <q-dialog v-model="showTeacherDialog" maximized>
      <q-card>
        <q-card-section class="row items-center">
          <div class="text-h6">{{ selectedTeacher?.teacher_name }}'s Weekly Plans</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section class="scroll" style="max-height: 80vh">
          <q-table
            v-model:pagination="planPagination"
            :rows="teacherPlans"
            :columns="planColumns"
            row-key="id"
            dense
            flat
            bordered
          >
            <template v-slot:body-cell-status="props">
              <q-td :props="props">
                <StatusBadge :status="props.row.status" />
              </q-td>
            </template>
            <template v-slot:body-cell-cw="props">
              <q-td :props="props">
                <div v-html="props.row.cw || '-'" class="text-caption" style="max-width: 200px; overflow: hidden;"></div>
              </q-td>
            </template>
            <template v-slot:body-cell-hw="props">
              <q-td :props="props">
                <div v-html="props.row.hw || '-'" class="text-caption" style="max-width: 200px; overflow: hidden;"></div>
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import WeekSelector from '../components/weekly-plans/WeekSelector.vue'
import CompletionProgressBar from '../components/weekly-plans/CompletionProgressBar.vue'
import StatusBadge from '../components/shared/StatusBadge.vue'
import WeeklyPlanMenu from '../WeeklyPlanMenu.vue'

const $q = useQuasar()

// Data
const activeCopies = ref([])
const teacherStats = ref([])
const teacherPlans = ref([])
const planPagination = ref({
  rowsPerPage: 50
})

// Selected values
const selectedCopyId = ref(null)
const semesterNumber = ref(1)
const weekNumber = ref(1)
const maxWeeks = ref(18)
const currentWeek = ref(1)

// Dialog state
const showTeacherDialog = ref(false)
const selectedTeacher = ref(null)

// Loading states
const loadingCopies = ref(false)
const loading = ref(false)
const generating = ref(false)

// Options
const semesterOptions = [
  { label: 'Semester 1', value: 1 },
  { label: 'Semester 2', value: 2 }
]

const planColumns = [
  { name: 'day', label: 'Day', field: row => row.schedule?.day, format: val => getDayName(val), sortable: true, align: 'left' },
  { name: 'period', label: 'Period', field: row => row.schedule?.period_number, sortable: true, align: 'center' },
  { name: 'subject', label: 'Subject', field: row => row.schedule?.cst?.subject_name, sortable: true, align: 'left' },
  { name: 'classroom', label: 'Classroom', field: row => row.schedule?.cst?.classroom_name, sortable: true, align: 'left' },
  { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'center' },
  { name: 'cw', label: 'Classwork', field: 'cw', sortable: true, align: 'left' },
  { name: 'hw', label: 'Homework', field: 'hw', sortable: true, align: 'left' }
]

// Computed
const summaryStats = computed(() => {
  return {
    completed: teacherStats.value.reduce((sum, t) => sum + (t.completed || 0), 0),
    partial: teacherStats.value.reduce((sum, t) => sum + (t.partial || 0), 0),
    empty: teacherStats.value.reduce((sum, t) => sum + (t.empty || 0), 0)
  }
})

const selectedCopy = computed(() => {
  return activeCopies.value.find(c => c.id === selectedCopyId.value)
})

// Methods
const getDayName = (dayNum) => {
  const days = ['', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu']
  return days[dayNum] || dayNum
}

const fetchActiveCopies = async () => {
  loadingCopies.value = true
  try {
    const response = await axios.get('/admin/schedule-copies', {
      params: { status: 'active' }
    })
    const copies = response.data.data || response.data || []
    activeCopies.value = copies.filter(c => c.status === 'active')
    if (activeCopies.value.length) {
      selectedCopyId.value = activeCopies.value[0].id
    }
  } catch (error) {
    console.error('Error fetching copies:', error)
    $q.notify({ type: 'negative', message: 'Failed to load schedule copies' })
  } finally {
    loadingCopies.value = false
  }
}

const fetchTeacherStats = async () => {
  if (!selectedCopyId.value) return
  
  loading.value = true
  try {
    const response = await axios.get('/weekly-system/api/weekly-plans/teacher-stats', {
      params: {
        week_number: weekNumber.value,
        academic_year_id: selectedCopy.value?.academic_year_id,
        semester_number: semesterNumber.value
      }
    })
    teacherStats.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching stats:', error)
    teacherStats.value = []
  } finally {
    loading.value = false
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
    
    await fetchTeacherStats()
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
    
    await fetchTeacherStats()
  } catch (error) {
    console.error('Sync error:', error)
    $q.notify({ type: 'negative', message: 'Failed to sync week' })
  } finally {
    $q.loading.hide()
  }
}

const viewTeacherPlans = async (teacher) => {
  selectedTeacher.value = teacher
  showTeacherDialog.value = true
  
  try {
    const response = await axios.get('/weekly-system/api/weekly-plans', {
      params: {
        teacher_id: teacher.teacher_id,
        week_number: weekNumber.value,
        academic_year_id: selectedCopy.value?.academic_year_id,
        semester_number: semesterNumber.value
      }
    })
    teacherPlans.value = (response.data.data || response.data || []).map(plan => ({
      ...plan,
      status: getStatus(plan)
    }))
  } catch (error) {
    console.error('Error fetching teacher plans:', error)
  }
}

const getStatus = (plan) => {
  const hasCw = !!plan.cw?.trim()
  const hasHw = !!plan.hw?.trim()
  if (!hasCw && !hasHw) return 'empty'
  if (hasCw && hasHw) return 'completed'
  return 'partial'
}

// Watchers
watch([selectedCopyId, semesterNumber, weekNumber], () => {
  if (selectedCopyId.value) {
    fetchTeacherStats()
  }
})

// Lifecycle
onMounted(async () => {
  // Calculate current week
  const now = new Date()
  const startOfYear = new Date(now.getFullYear(), 0, 1)
  currentWeek.value = Math.ceil(((now - startOfYear) / 86400000 + startOfYear.getDay() + 1) / 7)
  weekNumber.value = currentWeek.value > maxWeeks.value ? 1 : currentWeek.value

  await fetchActiveCopies()
})
</script>

<style scoped>
h4 {
  font-size: 1.5rem;
}
</style>
