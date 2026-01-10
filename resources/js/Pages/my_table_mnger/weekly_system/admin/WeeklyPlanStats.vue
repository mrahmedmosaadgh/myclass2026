<template>
  <div class="q-pa-md">
    <!-- Summary Stats Only (Filters moved to parent) -->
    <q-card flat bordered class="q-pa-md q-mb-lg">
      <div class="row items-center justify-between">
         <div class="text-subtitle1 text-grey-8">
            Progress Overview
         </div>
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
import CompletionProgressBar from '../components/weekly-plans/CompletionProgressBar.vue'
import StatusBadge from '../components/shared/StatusBadge.vue'
import { useWeeklyPlansStore } from '@/Stores/useWeeklyPlansStore'

const store = useWeeklyPlansStore()
const $q = useQuasar()

// Data
const teacherStats = ref([])
const teacherPlans = ref([])
const loading = ref(false)

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

const fetchTeacherStats = async () => {
  if (!store.selectedCopyId) return
  
  loading.value = true
  try {
    const response = await axios.get('/weekly-system/api/weekly-plans/teacher-stats', {
      params: {
        week_number: store.weekNumber,
        academic_year_id: store.selectedAcademicYearId,
        semester_number: store.semesterNumber
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
    const response = await axios.get('/weekly-system/api/weekly-plans', {
      params: {
        teacher_id: teacher.teacher_id,
        week_number: store.weekNumber,
        academic_year_id: store.selectedAcademicYearId,
        semester_number: store.semesterNumber
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

watch(() => [store.selectedCopyId, store.weekNumber, store.semesterNumber], () => {
  fetchTeacherStats()
}, { deep: true })

onMounted(() => {
  fetchTeacherStats()
})

defineExpose({
    refresh: fetchTeacherStats
})
</script>
