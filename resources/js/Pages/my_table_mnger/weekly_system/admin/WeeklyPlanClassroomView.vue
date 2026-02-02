<template>
  <div class="q-pa-md">
    <!-- Controls -->
    <q-card flat bordered class="q-pa-md q-mb-lg">
      <div class="row q-gutter-md items-end">
        <!-- Classroom Selector (Multiple) -->
        <div class="col-12 col-sm-6">
          <q-select
            v-model="selectedClassrooms"
            :options="classrooms"
            option-value="id"
            option-label="name"
            :label="t('weeklyPlans.filterClassrooms')"
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
                  <q-item-label>{{ allSelected ? t('weeklyPlans.deselectAll') : t('weeklyPlans.selectAll') }}</q-item-label>
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
            :label="t('weeklyPlans.filterByDay')"
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
      <p class="text-h6 text-grey-7 q-mt-md">{{ t('weeklyPlans.noDataAvailable') }}</p>
      <p class="text-grey-6">{{ t('weeklyPlans.selectClassroomsToView') }}</p>
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
                :label="`${classroom.plans.length} ${t('weeklyPlans.classes')}`"
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
                <q-tooltip>{{ t('weeklyPlans.savePdf') }}</q-tooltip>
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
                <q-tooltip>{{ t('weeklyPlans.print') }}</q-tooltip>
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
                    <span>{{ t('weeklyPlans.week') }} {{ store.weekNumber }}</span>
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
                    <div v-if="props.row.cw" class="text-info ellipsis-2-lines" v-html="props.row.cw"></div>
                    <span v-else class="text-grey-5">-</span>
                  </q-td>
                </template>

                <!-- Homework Column -->
                <template #body-cell-hw="props">
                  <q-td :props="props" class="content-preview">
                    <div v-if="props.row.hw" class="text-warning ellipsis-2-lines" v-html="props.row.hw"></div>
                    <span v-else class="text-grey-5">-</span>
                  </q-td>
                </template>

                <!-- Notes Column -->
                <template #body-cell-notes="props">
                  <q-td :props="props" class="content-preview">
                    <div v-if="props.row.notes" class="text-info ellipsis-2-lines" v-html="props.row.notes"></div>
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
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import { useWeeklyPlansStore } from '@/Stores/useWeeklyPlansStore'

const { t, locale } = useI18n()
const store = useWeeklyPlansStore()
const $q = useQuasar()

// Data
const classrooms = ref([])
const selectedClassrooms = ref([])
const selectedDays = ref([])
// Store manages: weekNumber, semesterNumber, maxWeeks, currentWeek
const allPlans = ref([])
const loading = ref(false)
const loadingClassrooms = ref(false)

// Default pagination to show all records
const defaultPagination = ref({
  rowsPerPage: 0 // 0 means show all rows
})

const dayOptions = computed(() => [
  { label: t('weeklyPlans.shortDays.1'), value: 1 },
  { label: t('weeklyPlans.shortDays.2'), value: 2 },
  { label: t('weeklyPlans.shortDays.3'), value: 3 },
  { label: t('weeklyPlans.shortDays.4'), value: 4 },
  { label: t('weeklyPlans.shortDays.5'), value: 5 }
])

const tableColumns = computed(() => [
  { name: 'period', label: t('weeklyPlans.period'), field: 'schedule.period_number', align: 'center' },
  { name: 'subject', label: t('weeklyPlans.subject'), field: 'schedule.cst.subject_name', align: 'left' },
  { name: 'cw', label: t('weeklyPlans.classwork'), field: 'cw', align: 'left' },
  { name: 'hw', label: t('weeklyPlans.homework'), field: 'hw', align: 'left' },
  { name: 'notes', label: t('weeklyPlans.notes'), field: 'notes', align: 'left' }
])

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
  
  // Find the first Sunday of the year
  let firstDay = new Date(currentYear, 0, 1)
  let daysToSunday = firstDay.getDay() === 0 ? 0 : 7 - firstDay.getDay()
  let firstSunday = new Date(currentYear, 0, 1 + daysToSunday)
  
  // Calculate the start of the target week
  const weekStartDate = new Date(firstSunday)
  weekStartDate.setDate(firstSunday.getDate() + (store.weekNumber - 1) * 7)
  
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
        dayName: t(`weeklyPlans.shortDays.${dayNum}`) || `Day ${dayNum}`,
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

const fetchClassrooms = async () => {
  if (!store.selectedSchoolId) return
  
  loadingClassrooms.value = true
  try {
    const response = await axios.get(`/api/classrooms?school_id=${store.selectedSchoolId}`)
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
    const promises = selectedClassrooms.value.map(classroomId =>
      axios.get('/weekly-system/api/weekly-plans', {
        params: {
          classroom_id: classroomId,
          week_number: store.weekNumber,
          semester_number: store.semesterNumber,
          academic_year_id: store.selectedAcademicYearId
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

  // Modern, Parent-Friendly Design
  const styles = `
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
    body { font-family: 'Nunito', 'Tajawal', 'Segoe UI', sans-serif; color: #374151; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page { max-width: 210mm; margin: 0 auto; padding: 15mm; }
    
    .header-box { 
      background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); 
      color: white; 
      padding: 20px; 
      border-radius: 12px;
      margin-bottom: 25px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .header-title { font-size: 24px; font-weight: 800; margin: 0; letter-spacing: -0.5px; }
    .header-meta { margin-top: 8px; font-size: 14px; opacity: 0.9; font-weight: 500; }
    
    .day-container { margin-bottom: 25px; page-break-inside: avoid; }
    .day-header { 
      display: flex; 
      align-items: center; 
      margin-bottom: 1px; /* connected to table */
    }
    .day-pill {
      background: #4f46e5;
      color: white;
      padding: 8px 16px;
      border-radius: 12px 12px 0 0;
      font-weight: 700;
      font-size: 15px;
      display: inline-block;
    }
    .date-label {
      color: #6b7280;
      font-size: 13px;
      margin-left: 12px;
      font-weight: 600;
    }

    table { width: 100%; border-collapse: collapse; border-radius: 0 8px 8px 8px; overflow: hidden; border: 1px solid #e5e7eb; }
    th { 
      background: #f3f4f6; 
      color: #374151; 
      font-weight: 700; 
      padding: 10px 12px; 
      text-align: left; 
      font-size: 13px;
      border-bottom: 2px solid #e5e7eb;
    }
    td { 
      padding: 10px 12px; 
      border-bottom: 1px solid #e5e7eb; 
      font-size: 13px; 
      vertical-align: top;
      line-height: 1.5;
    }
    tr:last-child td { border-bottom: none; }
    tr:nth-child(even) { background-color: #f9fafb; }
    
    .period-col { width: 60px; text-align: center; color: #6b7280; font-weight: 700; }
    .subject-col { width: 180px; }
    .cw-col { width: 35%; }
    .hw-col { width: 35%; }
    
    .subject-badge { 
      display: inline-block; 
      padding: 4px 10px; 
      border-radius: 6px; 
      font-weight: 700; 
      font-size: 12px;
      margin-bottom: 4px;
    }
    .teacher-name { color: #6b7280; font-size: 12px; font-style: italic; display: flex; align-items: center; gap: 4px; }
    
    .label-tag {
      display: inline-block;
      font-size: 10px;
      text-transform: uppercase;
      font-weight: 700;
      letter-spacing: 0.5px;
      margin-right: 6px;
      padding: 2px 6px;
      border-radius: 4px;
    }
    .cw-tag { background: #dbeafe; color: #1e40af; }
    .hw-tag { background: #fef3c7; color: #92400e; }
    
    @media print { 
      @page { margin: 10mm; } 
      body { -webkit-print-color-adjust: exact; }
    }
  `

  let content = `
    <div class="page" dir="${locale.value === 'ar' ? 'rtl' : 'ltr'}">
      <div class="header-box">
        <div class="header-title">${t('weeklyPlans.weeklyLearningPlan')} - ${classroom.name}</div>
        <div class="header-meta">${t('weeklyPlans.week')} ${store.weekNumber} • ${t('weeklyPlans.semester')} ${store.semesterNumber} • ${new Date().toLocaleDateString(locale.value, {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})}</div>
      </div>
  `

  dayGroups.forEach(dg => {
    content += `
      <div class="day-container">
        <div class="day-header">
          <div class="day-pill">${dg.dayName}</div>
          <div class="date-label">${getDayDate(dg.dayNumber)}</div>
        </div>
        <table>
          <thead>
            <tr>
              <th class="period-col">${t('weeklyPlans.period')}</th>
              <th class="subject-col">${t('weeklyPlans.subject')}</th>
              <th class="cw-col">${t('weeklyPlans.classwork')}</th>
              <th class="hw-col">${t('weeklyPlans.homework')}</th>
            </tr>
          </thead>
          <tbody>
    `
    dg.plans.forEach(plan => {
      const subj = plan.schedule?.cst?.subject_name || '-' 
      const subjBg = plan.schedule?.cst?.c_bg || '#e0e0e0'
      const subjColor = plan.schedule?.cst?.c_text || '#111'
      const teacher = plan.schedule?.cst?.teacher_name || ''
      const cw = plan.cw ? plan.cw : '-'
      const hw = plan.hw ? plan.hw : '-'

      content += `
        <tr>
          <td class="period-col">${plan.schedule?.period_number || ''}</td>
          <td class="subject-col">
            <div class="subject-badge" style="background:${subjBg}; color:${subjColor}">${subj}</div>
            ${teacher ? `<div class="teacher-name">👤 ${teacher}</div>` : ''}
          </td>
          <td>${cw !== '-' ? `<span class="label-tag cw-tag">CW</span>`+cw : '<span style="color:#9ca3af">-</span>'}</td>
          <td>${hw !== '-' ? `<span class="label-tag hw-tag">HW</span>`+hw : '<span style="color:#9ca3af">-</span>'}</td>
        </tr>
      `
    })
    content += `</tbody></table></div>`
  })

  content += `</div>`

  const win = window.open('', '_blank')
  if (!win) {
    $q.notify({ type: 'negative', message: 'Popup blocked: allow popups to print' })
    return
  }

  win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Plan - ${classroom.name}</title><style>${styles}</style></head><body>${content}</body></html>`)
  win.document.close()
  win.onload = () => { setTimeout(() => { win.print(); win.close(); }, 500) }
}

// Save as PDF (reusing the same improved design)
const printClassroomPdf = (classroom) => {
  if (!classroom || !classroom.plans || !classroom.plans.length) {
    $q.notify({ type: 'warning', message: 'No plans to save for this classroom' })
    return
  }

  const dayGroups = groupPlansByDay(classroom.plans)

  // Use same styles as print, maybe slightly adjusted for PDF rendering limits if needed
  // Nunito might need time to load in html2pdf... relying on safe font fallback if needed, but trying Google Fonts import.
  const styles = `
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
    body { font-family: 'Nunito', 'Tajawal', 'Segoe UI', sans-serif; color: #374151; }
    .page { width: 100%; padding: 20px; box-sizing: border-box; }
    
    .header-box { 
      background: #4f46e5; 
      color: white; 
      padding: 20px; 
      border-radius: 12px;
      margin-bottom: 25px;
    }
    .header-title { font-size: 24px; font-weight: 800; margin: 0; }
    .header-meta { margin-top: 8px; font-size: 14px; opacity: 0.9; }
    
    .day-container { margin-bottom: 20px; page-break-inside: avoid; }
    .day-header { 
      display: flex; 
      align-items: center; 
      margin-bottom: 0px;
    }
    .day-pill {
      background: #4f46e5;
      color: white;
      padding: 6px 14px;
      border-radius: 12px 12px 0 0;
      font-weight: 700;
      font-size: 14px;
      display: inline-block;
    }

    table { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; border-radius: 0 8px 8px 8px; overflow: hidden; }
    th { 
      background: #f3f4f6; 
      color: #374151; 
      font-weight: 700; 
      padding: 8px 10px; 
      text-align: left; 
      font-size: 12px;
      border-bottom: 2px solid #e5e7eb;
    }
    td { 
      padding: 8px 10px; 
      border-bottom: 1px solid #e5e7eb; 
      font-size: 12px; 
      vertical-align: top;
    }
    tr:nth-child(even) { background-color: #f9fafb; }
    
    .period-col { width: 50px; text-align: center; color: #6b7280; font-weight: 700; }
    .subject-col { width: 160px; }
    
    .subject-badge { 
      display: inline-block; 
      padding: 3px 8px; 
      border-radius: 4px; 
      font-weight: 700; 
      font-size: 11px;
    }
    .teacher-name { color: #6b7280; font-size: 11px; font-style: italic; margin-top: 2px; }
    
    .label-tag {
      font-size: 9px;
      font-weight: 700;
      padding: 1px 4px;
      border-radius: 3px;
      margin-right: 4px;
    }
    .cw-tag { background: #dbeafe; color: #1e40af; }
    .hw-tag { background: #fef3c7; color: #92400e; }
  `

  // Similar content construction but optimized for PDF size
  let content = `
    <div class="page" dir="${locale.value === 'ar' ? 'rtl' : 'ltr'}">
      <div class="header-box">
        <div class="header-title">${t('weeklyPlans.weeklyLearningPlan')}</div>
        <div class="header-meta">${classroom.name} • ${t('weeklyPlans.week')} ${store.weekNumber} • ${t('weeklyPlans.semester')} ${store.semesterNumber}</div>
      </div>
  `

  dayGroups.forEach(dg => {
    content += `
      <div class="day-container">
        <div class="day-header">
          <div class="day-pill">${dg.dayName}</div>
        </div>
        <table>
          <thead><tr><th class="period-col">${t('weeklyPlans.period')}</th><th class="subject-col">${t('weeklyPlans.subject')}</th><th>${t('weeklyPlans.classwork')}</th><th>${t('weeklyPlans.homework')}</th></tr></thead>
          <tbody>
    `
    dg.plans.forEach(plan => {
      const subj = plan.schedule?.cst?.subject_name || '-' 
      const subjBg = plan.schedule?.cst?.c_bg || '#e0e0e0'
      const subjColor = plan.schedule?.cst?.c_text || '#111'
      const teacher = plan.schedule?.cst?.teacher_name || ''
      
      // For PDF, we remove complex HTML that might break it, sticking to breaks
      // But we can try basic formatting
      const cw = plan.cw || '-'
      const hw = plan.hw || '-'

      content += `
        <tr>
          <td class="period-col">${plan.schedule?.period_number || ''}</td>
          <td class="subject-col">
            <div class="subject-badge" style="background:${subjBg}; color:${subjColor}">${subj}</div>
            ${teacher ? `<div class="teacher-name">${teacher}</div>` : ''}
          </td>
          <td>${cw !== '-' ? `<span class="label-tag cw-tag">CW</span>`+cw : '-'}</td>
          <td>${hw !== '-' ? `<span class="label-tag hw-tag">HW</span>`+hw : '-'}</td>
        </tr>
      `
    })
    content += `</tbody></table></div>`
  })

  content += `</div>`

  const cdn = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js'
  const filename = `WeeklyPlans_${String(classroom.name).replace(/\s+/g, '_')}_Week${store.weekNumber}.pdf`

  const win = window.open('', '_blank')
  if (!win) {
    $q.notify({ type: 'negative', message: 'Popup blocked: allow popups to save PDF' })
    return
  }

  const script = `
    var opt = { margin:0.3, filename: '${filename}', image:{type:'jpeg', quality:0.98}, html2canvas:{scale:2, useCORS:true}, jsPDF:{unit:'in', format:'a4', orientation:'portrait'} };
    function doSave(){ html2pdf().set(opt).from(document.body).save().then(function(){ setTimeout(function(){ window.close(); }, 1000); }); }
    if (typeof html2pdf === 'undefined'){
      var s = document.createElement('script'); s.src = '${cdn}'; s.onload = doSave; document.head.appendChild(s);
    } else { doSave(); }
  `

  win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>PDF</title><style>${styles}</style></head><body>${content}<script>${script} <\/script></body></html>`)
  win.document.close()
}

watch(() => store.selectedSchoolId, () => {
  fetchClassrooms()
  selectedClassrooms.value = []
})

watch(() => [selectedClassrooms.value, store.weekNumber, store.semesterNumber, store.selectedAcademicYearId], () => {
  fetchPlans()
}, { deep: true })

onMounted(async () => {
  // Store initialization handled by MainSchoolData or Store itself
  if (store.selectedSchoolId) {
      fetchClassrooms()
  }
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
