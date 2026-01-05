<template>
  <div class="q-pa-md">
    <!-- Controls -->
    <q-card flat bordered class="q-pa-md q-mb-lg">
      <div class="row q-gutter-md items-end">
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

        <!-- Semester -->
        <div class="col-12 col-sm-2">
          <q-select
            v-model="semesterNumber"
            :options="[{ label: 'Semester 1', value: 1 }, { label: 'Semester 2', value: 2 }]"
            label="Semester"
            outlined
            dense
            emit-value
            map-options
          />
        </div>

        <!-- Classroom Selector (Multiple) -->
        <div class="col-12 col-sm-6">
          <q-select
            v-model="selectedClassrooms"
            :options="classrooms"
            option-value="id"
            option-label="name"
            label="Select Classrooms"
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

        <!-- Filter by Day -->
        <div class="col-12 col-sm-4">
          <q-select
            v-model="selectedDays"
            :options="dayOptions"
            label="Filter by Day"
            outlined
            dense
            multiple
            use-chips
            clearable
            emit-value
            map-options
          />
        </div>
      </div>
    </q-card>

    <!-- Loading State -->
    <div v-if="loading" class="row justify-center q-pa-xl">
      <q-spinner-dots size="50px" color="primary" />
    </div>

    <!-- Empty State -->
    <q-card v-else-if="!classroomPlans.length" flat bordered class="text-center q-pa-xl">
      <q-icon name="meeting_room" size="64px" color="grey-5" />
      <p class="text-h6 text-grey-7 q-mt-md">No data available</p>
      <p class="text-grey-6">Select classrooms to view weekly plans</p>
    </q-card>

    <!-- Classroom View -->
    <div v-else class="classrooms-grid">
      <div 
        v-for="classroom in classroomPlans" 
        :key="classroom.id"
        class="classroom-card q-mb-lg"
      >
        <q-card flat bordered>
          <!-- Classroom Header -->
          <div class="classroom-header bg-primary text-white q-pa-md">
            <h5 class="q-ma-none flex items-center q-gutter-sm">
              <q-icon name="meeting_room" />
              {{ classroom.name }}
              <q-chip 
                dense 
                :label="`${classroom.plans.length} classes`"
                text-color="white" />
              <q-space />

              <q-btn
                flat
                dense
                round
                icon="picture_as_pdf"
                color="white"
                @click.stop="printClassroomPdf(classroom)"
                :disable="!classroom.plans || !classroom.plans.length"
              >
                <q-tooltip>Save as PDF</q-tooltip>
              </q-btn>

              <q-btn
                flat
                dense
                round
                icon="print"
                color="white"
                @click.stop="printClassroom(classroom)"
                :disable="!classroom.plans || !classroom.plans.length"
              >
                <q-tooltip>Print this classroom</q-tooltip>
              </q-btn>
            </h5>
          </div>

          <!-- Grouped Plans by Day -->
          <div class="plans-by-day">
            <div 
              v-for="dayGroup in groupPlansByDay(classroom.plans)" 
              :key="dayGroup.dayNumber"
              class="day-group"
            >
              <!-- Day Header -->
              <div class="day-header-row">
                <div class="day-info">
                  <div class="day-name">{{ dayGroup.dayName }}</div>
                  <div class="day-meta">
                    <span>Week {{ weekNumber }}</span>
                    <span class="separator">•</span>
                    <span>{{ getDayDate(dayGroup.dayNumber) }}</span>
                  </div>
                </div>
              </div>

              <!-- Day's Plans Table -->
              <q-table
                flat
                bordered
                :rows="dayGroup.plans"
                :columns="tableColumns"
                row-key="id"
                dense
                class="classroom-table day-table"
                hide-header
                hide-bottom
                :pagination.sync="defaultPagination"
                :rows-per-page-options="[]"
              >
                <!-- Period Column -->
                <template #body-cell-period="props">
                  <q-td :props="props" class="text-center period-cell">
                    <q-badge :label="`P${props.row.schedule?.period_number}`" color="primary" />
                  </q-td>
                </template>

                <!-- Subject & Teacher Column -->
                <template #body-cell-subject="props">
                  <q-td :props="props" class="subject-teacher-cell">
                    <div class="subject-teacher">
                      <div class="subject-name" :style="{ 
                        backgroundColor: props.row.schedule?.cst?.c_bg,
                        color: props.row.schedule?.cst?.c_text
                      }">
                        {{ props.row.schedule?.cst?.subject_name }}
                      </div>
                      <div class="teacher-name">
                        {{ props.row.schedule?.cst?.teacher_name || 'N/A' }}
                      </div>
                    </div>
                  </q-td>
                </template>

                <!-- Classwork Column -->
                <template #body-cell-cw="props">
                  <q-td :props="props" class="content-preview">
                    <div v-if="props.row.cw" class="text-info">
                      <q-icon name="school" size="xs" /> 
                      <span class="q-ml-xs">{{ truncateText(props.row.cw, 30) }}</span>
                    </div>
                    <span v-else class="text-grey-5">-</span>
                  </q-td>
                </template>

                <!-- Homework Column -->
                <template #body-cell-hw="props">
                  <q-td :props="props" class="content-preview">
                    <div v-if="props.row.hw" class="text-warning">
                      <q-icon name="home_work" size="xs" /> 
                      <span class="q-ml-xs">{{ truncateText(props.row.hw, 30) }}</span>
                    </div>
                    <span v-else class="text-grey-5">-</span>
                  </q-td>
                </template>

                <!-- Notes Column -->
                <template #body-cell-notes="props">
                  <q-td :props="props" class="content-preview">
                    <span v-if="props.row.notes" class="text-info">
                      {{ truncateText(props.row.notes, 20) }}
                    </span>
                    <span v-else class="text-grey-5">-</span>
                  </q-td>
                </template>
              </q-table>
            </div>
          </div>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import WeekSelector from '@/Pages/my_table_mnger/weekly_system/components/weekly-plans/WeekSelector.vue'

const props = defineProps({
  initialWeek: Number,
  initialCopyId: [Number, String]
})

const $q = useQuasar()

// Data
const activeCopies = ref([])
const classrooms = ref([])
const selectedClassrooms = ref([])
const selectedDays = ref([])
const selectedCopyId = ref(props.initialCopyId)
const weekNumber = ref(props.initialWeek || 1)
const semesterNumber = ref(1)
const maxWeeks = ref(18)
const currentWeek = ref(1)
const allPlans = ref([])
const loading = ref(false)
const loadingCopies = ref(false)
const loadingClassrooms = ref(false)

// Default pagination to show all records
const defaultPagination = ref({
  rowsPerPage: 0 // 0 means show all rows
})

const days = {
  1: 'Sunday',
  2: 'Monday',
  3: 'Tuesday',
  4: 'Wednesday',
  5: 'Thursday'
}

const dayOptions = Object.entries(days).map(([value, label]) => ({
  label,
  value: parseInt(value)
}))

const tableColumns = [
  { name: 'period', label: 'Period', field: 'schedule.period_number', align: 'center' },
  { name: 'subject', label: 'Subject', field: 'schedule.cst.subject_name', align: 'left' },
  { name: 'cw', label: 'Classwork (CW)', field: 'cw', align: 'left' },
  { name: 'hw', label: 'Homework (HW)', field: 'hw', align: 'left' },
  { name: 'notes', label: 'Notes', field: 'notes', align: 'left' }
]

const allSelected = computed(() => {
  return classrooms.value.length > 0 && selectedClassrooms.value.length === classrooms.value.length
})

const filteredPlans = computed(() => {
  let plans = allPlans.value

  if (selectedDays.value?.length) {
    plans = plans.filter(p => selectedDays.value.includes(p.schedule?.day))
  }

  return plans
})

const classroomPlans = computed(() => {
  const byClassroom = {}
  
  filteredPlans.value.forEach(plan => {
    const classroomId = plan.classroom_id
    if (!byClassroom[classroomId]) {
      const classroomData = classrooms.value.find(c => c.id === classroomId)
      byClassroom[classroomId] = {
        id: classroomId,
        name: classroomData?.name || 'Unknown',
        plans: []
      }
    }
    byClassroom[classroomId].plans.push(plan)
  })

  // Sort plans within each classroom by day and period
  Object.values(byClassroom).forEach(classroom => {
    classroom.plans.sort((a, b) => {
      const dayDiff = (a.schedule?.day || 0) - (b.schedule?.day || 0)
      if (dayDiff !== 0) return dayDiff
      return (a.schedule?.period_number || 0) - (b.schedule?.period_number || 0)
    })
  })

  return Object.values(byClassroom).sort((a, b) => a.name.localeCompare(b.name))
})

const toggleAllClassrooms = () => {
  if (allSelected.value) {
    selectedClassrooms.value = []
  } else {
    selectedClassrooms.value = classrooms.value.map(c => c.id)
  }
}

const truncateText = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const getDayDate = (dayNumber) => {
  // Calculate the date for a given day in the current week
  // Assuming week starts on Sunday (day 1)
  const currentYear = new Date().getFullYear()
  const currentMonth = new Date().getMonth()
  
  // Find the first Sunday of the year
  let firstDay = new Date(currentYear, 0, 1)
  let daysToSunday = firstDay.getDay() === 0 ? 0 : 7 - firstDay.getDay()
  let firstSunday = new Date(currentYear, 0, 1 + daysToSunday)
  
  // Calculate the start of the target week
  const weekStartDate = new Date(firstSunday)
  weekStartDate.setDate(firstSunday.getDate() + (weekNumber.value - 1) * 7)
  
  // Calculate the target day date
  const targetDate = new Date(weekStartDate)
  targetDate.setDate(weekStartDate.getDate() + (dayNumber - 1))
  
  // Format as "MMM D, YYYY"
  return targetDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const groupPlansByDay = (plans) => {
  const byDay = {}
  
  plans.forEach(plan => {
    const dayNum = plan.schedule?.day
    if (!byDay[dayNum]) {
      byDay[dayNum] = {
        dayNumber: dayNum,
        dayName: days[dayNum] || `Day ${dayNum}`,
        plans: []
      }
    }
    byDay[dayNum].plans.push(plan)
  })

  // Sort plans within each day by period
  Object.values(byDay).forEach(day => {
    day.plans.sort((a, b) => a.schedule?.period_number - b.schedule?.period_number)
  })

  return Object.values(byDay).sort((a, b) => a.dayNumber - b.dayNumber)
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

const fetchPlans = async () => {
  if (!selectedClassrooms.value.length) {
    allPlans.value = []
    return
  }

  loading.value = true
  try {
    const copy = activeCopies.value.find(c => c.id === selectedCopyId.value)
    if (!copy) return

    const promises = selectedClassrooms.value.map(classroomId =>
      axios.get('/weekly-system/api/weekly-plans', {
        params: {
          classroom_id: classroomId,
          week_number: weekNumber.value,
          semester_number: semesterNumber.value,
          academic_year_id: copy.academic_year_id
        }
      }).then(response => ({
        classroom_id: classroomId,
        plans: response.data.data || response.data || []
      }))
    )

    const results = await Promise.all(promises)
    allPlans.value = results.flatMap(result => 
      result.plans.map(plan => ({
        ...plan,
        classroom_id: result.classroom_id
      }))
    )
  } catch (error) {
    console.error('Error fetching plans:', error)
    $q.notify({ type: 'negative', message: 'Failed to load weekly plans' })
  } finally {
    loading.value = false
  }
}

const printClassroom = (classroom) => {
  if (!classroom || !classroom.plans || !classroom.plans.length) {
    $q.notify({ type: 'warning', message: 'No plans to print for this classroom' })
    return
  }

  const dayGroups = groupPlansByDay(classroom.plans)

  const styles = `
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
    .page { width: 210mm; padding: 12mm; box-sizing: border-box; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .title { font-size: 20px; color: #1976d2; font-weight:700 }
    .meta { font-size: 12px; color:#666 }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
    th { background: #f5f5f5; color: #1976d2; font-weight:600; }
    .period { width: 70px; text-align: center; font-weight:700 }
    .subject { width: 120px }
    .subject-badge { display:inline-block; padding:6px 10px; border-radius:6px; font-weight:700 }
    .teacher { font-size: 12px; color:#666; font-style:italic; margin-top:6px }
    .day-header { background:#1976d2; color:white; padding:8px; font-weight:700; margin-top:12px }
    @media print { @page { size:A4; margin:10mm } }
  `

  let content = `<div class="page"><div class="header"><div class="title">Weekly Plans - ${classroom.name}</div><div class="meta">Week ${weekNumber.value} • Semester ${semesterNumber.value} • Generated ${new Date().toLocaleString()}</div></div>`

  dayGroups.forEach(dg => {
    content += `<div class="day-header">${dg.dayName} - ${getDayDate(dg.dayNumber)}</div>`
    content += `<table><thead><tr><th class="period">Period</th><th class="subject">Subject</th><th>Classwork (CW)</th><th>Homework (HW)</th></tr></thead><tbody>`
    dg.plans.forEach(plan => {
      const subj = plan.schedule?.cst?.subject_name || '-' 
      const subjBg = plan.schedule?.cst?.c_bg || '#e0e0e0'
      const subjColor = plan.schedule?.cst?.c_text || '#111'
      const teacher = plan.schedule?.cst?.teacher_name || '-'
      const cw = plan.cw ? plan.cw.replace(/\n/g, '<br/>') : '-'
      const hw = plan.hw ? plan.hw.replace(/\n/g, '<br/>') : '-'

      content += `<tr>`
      content += `<td class="period">P${plan.schedule?.period_number || ''}</td>`
      content += `<td class="subject"><div class="subject-badge" style="background:${subjBg}; color:${subjColor}">${subj}</div><div class="teacher">${teacher}</div></td>`
      content += `<td>${cw}</td>`
      content += `<td>${hw}</td>`
      content += `</tr>`
    })
    content += `</tbody></table>`
  })

  content += `</div>`

  const win = window.open('', '_blank')
  if (!win) {
    $q.notify({ type: 'negative', message: 'Popup blocked: allow popups to print' })
    return
  }

  win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Print - ${classroom.name}</title><style>${styles}</style></head><body>${content}</body></html>`)
  win.document.close()
  win.onload = () => { setTimeout(() => { win.print(); win.close(); }, 200) }
}

// Save as PDF using html2pdf (loaded from CDN inside new window)
const printClassroomPdf = (classroom) => {
  if (!classroom || !classroom.plans || !classroom.plans.length) {
    $q.notify({ type: 'warning', message: 'No plans to save for this classroom' })
    return
  }

  const dayGroups = groupPlansByDay(classroom.plans)

  const styles = `
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
    .page { width: 210mm; padding: 12mm; box-sizing: border-box; }
    .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .title { font-size: 20px; color: #1976d2; font-weight:700 }
    .meta { font-size: 12px; color:#666 }
    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
    th { background: #f5f5f5; color: #1976d2; font-weight:600; }
    .period { width: 70px; text-align: center; font-weight:700 }
    .subject { width: 220px }
    .subject-badge { display:inline-block; padding:6px 10px; border-radius:6px; font-weight:700 }
    .teacher { font-size: 12px; color:#666; font-style:italic; margin-top:6px }
    .day-header { background:#1976d2; color:white; padding:8px; font-weight:700; margin-top:12px }
    @media print { @page { size:A4; margin:10mm } }
  `

  let content = `<div class="page"><div class="header"><div class="title">Weekly Plans - ${classroom.name}</div><div class="meta">Week ${weekNumber.value} • Semester ${semesterNumber.value} • Generated ${new Date().toLocaleString()}</div></div>`

  dayGroups.forEach(dg => {
    content += `<div class="day-header">${dg.dayName} - ${getDayDate(dg.dayNumber)}</div>`
    content += `<table><thead><tr><th class="period">Period</th><th class="subject">Subject</th><th>Classwork (CW)</th><th>Homework (HW)</th></tr></thead><tbody>`
    dg.plans.forEach(plan => {
      const subj = plan.schedule?.cst?.subject_name || '-' 
      const subjBg = plan.schedule?.cst?.c_bg || '#e0e0e0'
      const subjColor = plan.schedule?.cst?.c_text || '#111'
      const teacher = plan.schedule?.cst?.teacher_name || '-'
      const cw = plan.cw ? plan.cw.replace(/\n/g, '<br/>') : '-'
      const hw = plan.hw ? plan.hw.replace(/\n/g, '<br/>') : '-'

      content += `<tr>`
      content += `<td class="period">P${plan.schedule?.period_number || ''}</td>`
      content += `<td class="subject"><div class="subject-badge" style="background:${subjBg}; color:${subjColor}">${subj}</div><div class="teacher">${teacher}</div></td>`
      content += `<td>${cw}</td>`
      content += `<td>${hw}</td>`
      content += `</tr>`
    })
    content += `</tbody></table>`
  })

  content += `</div>`

  const cdn = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js'
  const filename = `WeeklyPlans - ${String(classroom.name).replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_\-\.]/g, '')} - Week_${weekNumber.value}.pdf`

  const win = window.open('', '_blank')
  if (!win) {
    $q.notify({ type: 'negative', message: 'Popup blocked: allow popups to save PDF' })
    return
  }

  // Build a document that loads html2pdf and triggers save
  const script = `
    var opt = { margin:0.5, filename: '${filename}', image:{type:'jpeg', quality:0.98}, html2canvas:{scale:2}, jsPDF:{unit:'in', format:'a4', orientation:'portrait'} };
    function doSave(){ html2pdf().set(opt).from(document.body).save().then(function(){ setTimeout(function(){ window.close(); }, 800); }); }
    if (typeof html2pdf === 'undefined'){
      var s = document.createElement('script'); s.src = '${cdn}'; s.onload = doSave; document.head.appendChild(s);
    } else { doSave(); }
  `

  win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>PDF - ${classroom.name}</title><style>${styles}</style></head><body>${content}<script>${script} <\/script></body></html>`)
  win.document.close()
}

watch(selectedCopyId, () => {
  fetchClassrooms()
  selectedClassrooms.value = []
})

watch([selectedClassrooms, weekNumber, semesterNumber], () => {
  fetchPlans()
})

onMounted(async () => {
  const now = new Date()
  const startOfYear = new Date(now.getFullYear(), 0, 1)
  const week = Math.ceil(((now - startOfYear) / 86400000 + startOfYear.getDay() + 1) / 7)
  currentWeek.value = week > maxWeeks.value ? 1 : week
  weekNumber.value = props.initialWeek || currentWeek.value
  
  await fetchActiveCopies()
})
</script>

<style scoped>
.classrooms-grid {
  display: grid;
  gap: 16px;
}

.classroom-card {
  cursor: default;
}

.classroom-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.classroom-header h5 {
  margin: 0;
  font-size: 1rem;
}

.plans-by-day {
  display: flex;
  flex-direction: column;
}

.day-group {
  border-bottom: 2px solid #e0e0e0;
}

.day-group:last-child {
  border-bottom: none;
}

.day-header-row {
  background-color: var(--q-primary);
  color: white;
  padding: 12px 16px;
  font-weight: 600;
  font-size: 1rem;
  display: flex;
  align-items: center;
  gap: 12px;
}

.day-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.day-name {
  font-size: 1.1rem;
  font-weight: 700;
}

.day-meta {
  font-size: 0.85rem;
  font-weight: 500;
  opacity: 0.95;
}

.day-meta .separator {
  margin: 0 8px;
}

.day-name {
  min-width: 100px;
}

.day-table {
  font-size: 0.9rem;
}

.day-table :deep(.q-table__card) {
  box-shadow: none;
  border: none;
  border-bottom: 1px solid #e0e0e0;
}

.day-table :deep(.q-table tbody tr) {
  transition: background-color 0.2s ease;
}

.day-table :deep(.q-table tbody tr:hover) {
  background-color: rgba(25, 118, 210, 0.05);
}

.day-table :deep(.q-table__container--dark) {
  background-color: white;
}

.period-cell {
  font-weight: 600;
  min-width: 70px;
  max-width: 70px;
  width: 70px;
}

.subject-teacher-cell {
  min-width: 200px;
  max-width: 250px;
  width: 220px;
}

.subject-teacher {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.subject-name {
  display: inline-block;
  padding: 6px 10px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.9rem;
  line-height: 1.3;
  text-align: center;
}

.teacher-name {
  font-size: 0.8rem;
  color: #666;
  font-weight: 500;
  text-align: center;
  font-style: italic;
}

.classroom-table {
  font-size: 0.9rem;
}

.classroom-table :deep(.q-table__card) {
  box-shadow: none;
}

.classroom-table :deep(tbody tr) {
  transition: background-color 0.2s ease;
}

.classroom-table :deep(tbody tr:hover) {
  background-color: rgba(25, 118, 210, 0.05);
}

.subject-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.85rem;
}

.content-preview {
  font-size: 0.85rem;
  max-width: 150px;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.content-preview .text-info {
  color: #0288d1;
}

.content-preview .text-warning {
  color: #ffa726;
}

.content-preview .text-grey-5 {
  color: #bdbdbd;
}

@media (max-width: 768px) {
  .classroom-table {
    font-size: 0.8rem;
  }
  
  .classroom-header h5 {
    font-size: 0.9rem;
  }
  
  .content-preview {
    max-width: 100px;
  }
}
</style>
