<template>
  <div class="sync-dashboard q-pa-md">
    <!-- Loading State -->
    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner-dots size="50px" color="primary" />
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Hero Stats Cards -->
      <div class="row q-col-gutter-md q-mb-lg">
        <!-- Total Slots -->
        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered class="stat-card">
            <q-card-section>
              <div class="stat-icon bg-blue-1 text-blue">
                <q-icon name="grid_on" size="32px" />
              </div>
              <div class="stat-value text-h4">{{ analysis?.summary?.total_slots || 0 }}</div>
              <div class="stat-label text-grey-7">Total Slots</div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Complete -->
        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered class="stat-card">
            <q-card-section>
              <div class="stat-icon bg-green-1 text-green">
                <q-icon name="check_circle" size="32px" />
              </div>
              <div class="stat-value text-h4 text-positive">{{ analysis?.summary?.complete || 0 }}</div>
              <div class="stat-label text-grey-7">Complete</div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Missing -->
        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered class="stat-card">
            <q-card-section>
              <div class="stat-icon bg-orange-1 text-orange">
                <q-icon name="error" size="32px" />
              </div>
              <div class="stat-value text-h4 text-warning">{{ analysis?.summary?.missing || 0 }}</div>
              <div class="stat-label text-grey-7">Missing</div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Progress -->
        <div class="col-12 col-sm-6 col-md-3">
          <q-card flat bordered class="stat-card">
            <q-card-section>
              <div class="stat-icon bg-purple-1 text-purple">
                <q-icon name="trending_up" size="32px" />
              </div>
              <div class="stat-value text-h4 text-primary">{{ analysis?.summary?.percentage || 0 }}%</div>
              <div class="stat-label text-grey-7">Progress</div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Global Action -->
      <div class="row q-mb-lg">
        <div class="col-12">
          <q-btn
            v-if="analysis?.summary?.missing > 0"
            color="primary"
            icon="sync"
            label="Sync All Missing Plans"
            :loading="syncing"
            @click="syncAll"
            unelevated
            size="lg"
          />
        </div>
      </div>

      <!-- Classroom Cards -->
      <div class="row q-col-gutter-md">
        <div
          v-for="classroom in analysis?.classrooms"
          :key="classroom.id"
          class="col-12 col-md-6 col-lg-4"
        >
          <q-card flat bordered class="classroom-card">
            <q-card-section>
              <!-- Header -->
              <div class="row items-center q-mb-md">
                <div class="col">
                  <div class="text-h6">{{ classroom.name }}</div>
                  <div class="text-caption text-grey-7">
                    {{ classroom.complete }}/{{ classroom.total_slots }} slots complete
                  </div>
                </div>
                <div class="col-auto">
                  <!-- Circular Progress -->
                  <q-circular-progress
                    :value="classroom.percentage"
                    size="80px"
                    :thickness="0.15"
                    :color="getProgressColor(classroom.percentage)"
                    track-color="grey-3"
                    show-value
                  >
                    <div class="text-caption">{{ classroom.percentage }}%</div>
                  </q-circular-progress>
                </div>
              </div>

              <!-- Day Breakdown -->
              <q-expansion-item
                v-if="classroom.days?.length"
                icon="calendar_today"
                label="View by Day"
                dense
                header-class="text-primary"
              >
                <q-list dense separator>
                  <q-item v-for="day in classroom.days" :key="day.day_number">
                    <q-item-section>
                      <q-item-label>{{ day.day }}</q-item-label>
                      <q-item-label caption>
                        {{ day.complete }}/{{ day.total }} complete
                        <span v-if="day.missing > 0" class="text-warning">
                          (Missing: P{{ day.missing_periods.join(', P') }})
                        </span>
                      </q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-badge
                        :color="day.missing > 0 ? 'warning' : 'positive'"
                        :label="day.missing > 0 ? `${day.missing} missing` : 'Complete'"
                      />
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-expansion-item>

              <!-- Action Button -->
              <q-btn
                v-if="classroom.missing > 0"
                color="primary"
                icon="sync"
                :label="`Sync ${classroom.missing} Missing`"
                @click="syncClassroom(classroom.id)"
                outline
                class="full-width q-mt-md"
                size="sm"
              />
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Sync Preview Dialog -->
      <q-dialog v-model="previewDialog" full-width>
        <q-card>
          <q-card-section>
            <div class="text-h6">Sync Preview - {{ getDialogTitle() }}</div>
            <div class="text-caption text-grey-7">Select the missing plans you want to create</div>
          </q-card-section>

          <q-card-section class="q-pa-none">
            <q-table
              :rows="previewItems"
              :columns="previewColumns"
              row-key="id"
              selection="multiple"
              v-model:selected="selectedItems"
              flat
              bordered
              :pagination="{ rowsPerPage: 0 }"
              virtual-scroll
              style="height: 400px"
            >
              <template v-slot:body-cell-status="props">
                <q-td :props="props">
                  <q-chip color="negative" text-color="white" size="sm" icon="warning">
                    Missing
                  </q-chip>
                </q-td>
              </template>
            </q-table>
          </q-card-section>

          <q-card-actions align="right" class="q-pa-md">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn 
              unelevated 
              color="primary" 
              :label="`Create ${selectedItems.length} Plans`" 
              :disable="selectedItems.length === 0"
              :loading="syncing"
              @click="confirmSync"
            />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- Empty State -->
      <q-card v-if="!analysis?.classrooms?.length" flat bordered class="text-center q-pa-xl q-mt-lg">
        <q-icon name="check_circle" size="64px" color="positive" />
        <p class="text-h6 text-grey-7 q-mt-md">All Weekly Plans Synced!</p>
        <p class="text-grey-6">No missing plans for this week.</p>
      </q-card>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const props = defineProps({
  copyId: {
    type: Number,
    required: true
  },
  weekNumber: {
    type: Number,
    required: true
  },
  academicYearId: {
    type: Number,
    required: true
  },
  semesterNumber: {
    type: Number,
    required: true
  }
})

const $q = useQuasar()
const loading = ref(false)
const syncing = ref(false)
const analysis = ref(null)

// Dialog State
const previewDialog = ref(false)
const previewItems = ref([])
const selectedItems = ref([])
const previewSource = ref('all') // 'all' or classroomId

const previewColumns = [
  { name: 'day', label: 'Day', field: 'day', align: 'left', sortable: true },
  { name: 'period', label: 'Period', field: 'period', align: 'center', sortable: true },
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left', sortable: true },
  { name: 'teacher', label: 'Teacher', field: 'teacher', align: 'left', sortable: true },
  { name: 'status', label: 'Status', field: 'status', align: 'center' }
]

// Fetch analysis
const fetchAnalysis = async () => {
  loading.value = true
  try {
    const response = await axios.get('/weekly-system/api/sync-analysis', {
      params: {
        copy_id: props.copyId,
        week_number: props.weekNumber,
        academic_year_id: props.academicYearId,
        semester_number: props.semesterNumber
      }
    })
    
    analysis.value = response.data
  } catch (error) {
    console.error('Error fetching sync analysis:', error)
    $q.notify({
      type: 'negative',
      message: 'Failed to load sync analysis'
    })
  } finally {
    loading.value = false
  }
}

// Get progress color
const getProgressColor = (percentage) => {
  if (percentage >= 80) return 'positive'
  if (percentage >= 50) return 'warning'
  return 'negative'
}

// Open Sync Preview for ALL
const syncAll = () => {
  previewSource.value = 'all'
  previewItems.value = []
  
  // Aggregate all missing items
  analysis.value.classrooms.forEach(classroom => {
    if (classroom.days) {
      classroom.days.forEach(day => {
        if (day.missing_periods?.length > 0) {
          day.missing_periods.forEach(item => {
            previewItems.value.push({
              id: item.id, // schedule_id
              day: day.day,
              period: item.period,
              subject: item.subject,
              teacher: item.teacher,
              status: 'missing',
              classroom_name: classroom.name
            })
          })
        }
      })
    }
  })
  
  selectedItems.value = [...previewItems.value]
  previewDialog.value = true
}

// Open Sync Preview for ONE Classroom
const syncClassroom = (classroomId) => {
  previewSource.value = classroomId
  previewItems.value = []
  
  const classroom = analysis.value.classrooms.find(c => c.id === classroomId)
  if (classroom && classroom.days) {
    classroom.days.forEach(day => {
      if (day.missing_periods?.length > 0) {
        day.missing_periods.forEach(item => {
          previewItems.value.push({
            id: item.id, // schedule_id
            day: day.day,
            period: item.period,
            subject: item.subject,
            teacher: item.teacher,
            status: 'missing',
            classroom_name: classroom.name
          })
        })
      }
    })
  }
  
  selectedItems.value = [...previewItems.value]
  previewDialog.value = true
}

// Get Dialog Title
const getDialogTitle = () => {
  if (previewSource.value === 'all') return 'All Classrooms'
  const classroom = analysis.value?.classrooms?.find(c => c.id === previewSource.value)
  return classroom ? `Classroom ${classroom.name}` : ''
}

// Execute Sync
const confirmSync = async () => {
  syncing.value = true
  try {
    // If selecting specific items, we need a new endpoint OR logic to sync specific items
    // For now, since backend mostly supports 'sync week' logic, we might need a batch create endpoint
    // But wait! The 'sync-week' endpoint logic in WeeklyPlanService usually iterates and creates what's missing.
    // If we want selective sync, we need a new endpoint that accepts schedule_ids.
    
    // Let's assume for now we MUST sync all selected. 
    // I need to enable "Batch Sync" in the backend or reuse logic.
    // Quick win: create a loop here? No, better one batch request.
    
    // Since I don't have a 'batch sync by IDs' endpoint yet, and user asked for "Check boxes related",
    // I need to implement a batch sync endpoint or use an existing one slightly modified.
    // Let's implement a 'batch-create' endpoint call.
    
    const scheduleIds = selectedItems.value.map(item => item.id)
    
    const response = await axios.post('/weekly-system/api/weekly-plans/batch-create', {
        schedule_ids: scheduleIds,
        week_number: props.weekNumber,
        academic_year_id: props.academicYearId,
        semester_number: props.semesterNumber
    })
    
    $q.notify({
      type: 'positive',
      message: response.data.message || `Created ${scheduleIds.length} weekly plans`
    })
    
    previewDialog.value = false
    await fetchAnalysis()
  } catch (error) {
    console.error(error)
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to create plans'
    })
  } finally {
    syncing.value = false
  }
}

// Watch for prop changes
watch(() => [props.copyId, props.weekNumber, props.academicYearId, props.semesterNumber], () => {
  fetchAnalysis()
})

onMounted(() => {
  fetchAnalysis()
})
</script>

<style scoped lang="scss">
.sync-dashboard {
  .stat-card {
    transition: all 0.3s ease;
    
    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
      width: 64px;
      height: 64px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
    }

    .stat-value {
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 4px;
    }

    .stat-label {
      font-size: 14px;
      font-weight: 500;
    }
  }

  .classroom-card {
    transition: all 0.3s ease;
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
  }
}
</style>
