<template>
  <div class="q-pa-md">
    <q-card flat bordered class="q-pa-md no-print">
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
          />
        </div>

        <!-- Week Selector -->
        <div class="col-12 col-sm-3">
          <WeekSelector
            v-model="weekNumber"
            :max-weeks="maxWeeks"
            :current-week="currentWeek"
          />
        </div>

        <!-- Classroom Selector (Multiple) -->
        <div class="col-12 col-sm-5">
          <q-select
            v-model="selectedClassrooms"
            :options="classrooms"
            option-value="id"
            option-label="name"
            label="Select Classrooms to Print"
            outlined
            dense
            multiple
            use-chips
            emit-value
            map-options
            :loading="loadingClassrooms"
          >
            <template v-slot:before-options>
              <q-item clickable @click="toggleAllClassrooms">
                <q-item-section>
                  <q-item-label>{{ allSelected ? 'Deselect All' : 'Select All' }}</q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>
      </div>

      <div class="row q-mt-lg justify-center">
        <q-btn
          color="primary"
          icon="print"
          label="Print Review / Preview"
          @click="preparePrint"
          :loading="loadingPlans"
          :disable="!selectedClassrooms.length"
          class="q-px-xl"
        />
      </div>
    </q-card>

    <!-- Print Preview Documentation -->
    <div class="q-mt-md no-print text-grey-7 text-center">
      <p><q-icon name="info" /> Select classrooms and click Print Preview. A specialized A4 layout will be generated.</p>
    </div>

    <!-- Print Content (Hidden on screen, visible on print) -->
    <div id="print-area" class="print-only">
      <div v-for="classroom in printData" :key="classroom.id" class="a4-page">
        <div class="print-header">
          <div class="school-info">
            <div class="text-h5 text-weight-bold">Weekly Plan</div>
            <div class="text-subtitle1">{{ classroom.name }}</div>
          </div>
          <div class="week-info text-right">
            <div>Week: {{ weekNumber }}</div>
            <div>Semester: {{ semesterNumber }}</div>
          </div>
        </div>

        <table class="weekly-table">
          <thead>
            <tr>
              <th style="width: 80px;">Day</th>
              <th style="width: 60px;">Period</th>
              <th style="width: 120px;">Subject</th>
              <th>Classwork</th>
              <th>Homework</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(plan, index) in classroom.plans" :key="index">
              <td class="text-center">{{ getDayName(plan.schedule?.day) }}</td>
              <td class="text-center">{{ plan.schedule?.period_number }}</td>
              <td>{{ plan.schedule?.cst?.subject_name }}</td>
              <td class="content-cell" v-html="plan.cw || '-'"></td>
              <td class="content-cell" v-html="plan.hw || '-'"></td>
            </tr>
          </tbody>
        </table>
        
        <div class="print-footer">
          <div class="row justify-between q-mt-md">
            <div>School Principal: ___________________</div>
            <div>Academic Supervisor: ___________________</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import WeekSelector from '../components/weekly-plans/WeekSelector.vue'

const props = defineProps({
  initialWeek: Number,
  initialCopyId: [Number, String]
})

const $q = useQuasar()

// Data
const activeCopies = ref([])
const classrooms = ref([])
const selectedClassrooms = ref([])
const selectedCopyId = ref(props.initialCopyId)
const weekNumber = ref(props.initialWeek || 1)
const semesterNumber = ref(1) // Should probably be dynamic
const maxWeeks = ref(18)
const currentWeek = ref(1)
const printData = ref([])

// Loading states
const loadingCopies = ref(false)
const loadingClassrooms = ref(false)
const loadingPlans = ref(false)

const allSelected = computed(() => {
  return classrooms.value.length > 0 && selectedClassrooms.value.length === classrooms.value.length
})

const toggleAllClassrooms = () => {
  if (allSelected.value) {
    selectedClassrooms.value = []
  } else {
    selectedClassrooms.value = classrooms.value.map(c => c.id)
  }
}

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

const fetchClassrooms = async () => {
  if (!selectedCopyId.value) return
  
  const copy = activeCopies.value.find(c => c.id === selectedCopyId.value)
  if (!copy) return

  loadingClassrooms.value = true
  try {
    const response = await axios.get(`/api/classrooms?school_id=${copy.school_id}`)
    classrooms.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching classrooms:', error)
  } finally {
    loadingClassrooms.value = false
  }
}

const preparePrint = async () => {
    if (!selectedClassrooms.value.length) return
    
    loadingPlans.value = true
    printData.value = []
    
    try {
        const copy = activeCopies.value.find(c => c.id === selectedCopyId.value)
        
        // Fetch plans for each classroom
        // We might want a single API call for efficiency, but let's see if there's one.
        // For now, multiple calls or check if API supports multiple classroom IDs.
        
        const promises = selectedClassrooms.value.map(async (classroomId) => {
            const classroom = classrooms.value.find(c => c.id === classroomId)
            const response = await axios.get('/weekly-system/api/weekly-plans', {
                params: {
                    classroom_id: classroomId,
                    week_number: weekNumber.value,
                    academic_year_id: copy?.academic_year_id,
                    semester_number: 1
                }
            })
            return {
                ...classroom,
                plans: response.data.data || response.data || []
            }
        })
        
        printData.value = await Promise.all(promises)
        
        // Wait for DOM to update then print
        setTimeout(() => {
            window.print()
        }, 500)
        
    } catch (error) {
        console.error('Error preparing print data:', error)
        $q.notify({ type: 'negative', message: 'Failed to load plans for printing' })
    } finally {
        loadingPlans.value = false
    }
}

watch(selectedCopyId, () => {
  fetchClassrooms()
})

onMounted(async () => {
  const now = new Date()
  const startOfYear = new Date(now.getFullYear(), 0, 1)
  currentWeek.value = Math.ceil(((now - startOfYear) / 86400000 + startOfYear.getDay() + 1) / 7)
  if (!props.initialWeek) {
    weekNumber.value = currentWeek.value > maxWeeks.value ? 1 : currentWeek.value
  }
  await fetchActiveCopies()
  fetchClassrooms()
})
</script>

<style scoped>
/* Print Styles */
@media print {
  .no-print {
    display: none !important;
  }
  
  .print-only {
    display: block !important;
  }

  body {
    background: white !important;
  }

  .a4-page {
    width: 210mm;
    min-height: 297mm;
    padding: 15mm;
    margin: 0 auto;
    background: white;
    page-break-after: always;
    box-sizing: border-box;
    font-family: 'Times New Roman', serif;
  }

  .print-header {
    border-bottom: 2px solid #333;
    margin-bottom: 20px;
    padding-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
  }

  .weekly-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    table-layout: fixed;
  }

  .weekly-table th, .weekly-table td {
    border: 1px solid #333;
    padding: 8px;
    font-size: 12px;
    word-wrap: break-word;
  }

  .weekly-table th {
    background-color: #f0f0f0 !important;
    font-weight: bold;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .content-cell {
    font-size: 11px;
    line-height: 1.4;
  }

  .print-footer {
    margin-top: 30px;
    font-size: 12px;
  }
}

/* Screen Styles */
.print-only {
  display: none;
}
</style>
