<template>
  <div class="q-pa-md">
    <!-- Controls Row (Sync with main state if needed, but here it can be independent) -->
    <q-card flat bordered class="q-pa-md q-mb-lg">
      <div class="row q-gutter-md items-end">
        <div class="col-12 col-sm-4">
           <q-select
            v-model="selectedCopyId"
            :options="activeCopies"
            option-value="id"
            option-label="name"
            label="Filter by Schedule"
            outlined
            dense
            emit-value
            map-options
            :loading="loadingCopies"
          />
        </div>
        <div class="col-12 col-sm-3">
          <WeekSelector
            v-model="weekNumber"
            :max-weeks="maxWeeks"
            :current-week="currentWeek"
          />
        </div>
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
      <p class="text-h6 text-grey-7 q-mt-md">No teacher progress data for this week</p>
      <p class="text-grey-6">Generate plans first to start tracking completion</p>
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

const props = defineProps({
  initialWeek: Number,
  initialCopyId: [Number, String]
})

const $q = useQuasar()

// Data
const teacherStats = ref([])
const teacherPlans = ref([])
const activeCopies = ref([])
const selectedCopyId = ref(props.initialCopyId)
const weekNumber = ref(props.initialWeek || 1)
const maxWeeks = ref(18)
const currentWeek = ref(1)
const loading = ref(false)
const loadingCopies = ref(false)

const planPagination = ref({
  rowsPerPage: 50
})

// Dialog state
const showTeacherDialog = ref(false)
const selectedTeacher = ref(null)

const planColumns = [
  { name: 'day', label: 'Day', field: row => row.schedule?.day, format: val => getDayName(val), sortable: true, align: 'left' },
  { name: 'period', label: 'Period', field: row => row.schedule?.period_number, sortable: true, align: 'center' },
  { name: 'subject', label: 'Subject', field: row => row.schedule?.cst?.subject_name, sortable: true, align: 'left' },
  { name: 'classroom', label: 'Classroom', field: row => row.schedule?.cst?.classroom_name, sortable: true, align: 'left' },
  { name: 'status', label: 'Status', field: 'status', sortable: true, align: 'center' },
  { name: 'cw', label: 'Classwork', field: 'cw', sortable: true, align: 'left' },
  { name: 'hw', label: 'Homework', field: 'hw', sortable: true, align: 'left' }
]

const summaryStats = computed(() => {
  return {
    completed: teacherStats.value.reduce((sum, t) => sum + (t.completed || 0), 0),
    partial: teacherStats.value.reduce((sum, t) => sum + (t.partial || 0), 0),
    empty: teacherStats.value.reduce((sum, t) => sum + (t.empty || 0), 0)
  }
})

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
    activeCopies.value = (response.data.data || response.data || []).filter(c => c.status === 'active')
    if (activeCopies.value.length && !selectedCopyId.value) {
      selectedCopyId.value = activeCopies.value[0].id
    }
  } catch (error) {
    console.error('Error fetching copies:', error)
  } finally {
    loadingCopies.value = false
  }
}

const fetchTeacherStats = async () => {
  if (!selectedCopyId.value) return
  
  loading.value = true
  try {
    const copy = activeCopies.value.find(c => c.id === selectedCopyId.value)
    const response = await axios.get('/weekly-system/api/weekly-plans/teacher-stats', {
      params: {
        week_number: weekNumber.value,
        academic_year_id: copy?.academic_year_id,
        semester_number: 1 // Default to 1 or you can add semester selector
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

const viewTeacherPlans = async (teacher) => {
  selectedTeacher.value = teacher
  showTeacherDialog.value = true
  
  try {
    const copy = activeCopies.value.find(c => c.id === selectedCopyId.value)
    const response = await axios.get('/weekly-system/api/weekly-plans', {
      params: {
        teacher_id: teacher.teacher_id,
        week_number: weekNumber.value,
        academic_year_id: copy?.academic_year_id,
        semester_number: 1
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

watch([selectedCopyId, weekNumber], () => {
  fetchTeacherStats()
})

onMounted(async () => {
  const now = new Date()
  const startOfYear = new Date(now.getFullYear(), 0, 1)
  currentWeek.value = Math.ceil(((now - startOfYear) / 86400000 + startOfYear.getDay() + 1) / 7)
  if (!props.initialWeek) {
    weekNumber.value = currentWeek.value > maxWeeks.value ? 1 : currentWeek.value
  }
  await fetchActiveCopies()
  fetchTeacherStats()
})

defineExpose({
    refresh: fetchTeacherStats
})
</script>
