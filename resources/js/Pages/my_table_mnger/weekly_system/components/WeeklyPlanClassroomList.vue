<template>
  <div class="classrooms-grid">
    <div 
      v-for="classroom in classroomPlans" 
      :key="classroom.name"
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
              text-color="white"
            />
          </h5>
        </div>

        <!-- Plans Table -->
        <q-table
          flat
          bordered
          :rows="classroom.plans"
          :columns="tableColumns"
          row-key="id"
          dense
          class="classroom-table"
          @row-click="(evt, row) => $emit('edit', row.data)"
        >
          <!-- Day Column -->
          <template #body-cell-day="props">
            <q-td :props="props">
              <strong>{{ days[props.row.data.schedule?.day] || 'N/A' }}</strong>
            </q-td>
          </template>

          <!-- Period Column -->
          <template #body-cell-period="props">
            <q-td :props="props" class="text-center">
              <q-badge :label="`P${props.row.data.schedule?.period_number}`" />
            </q-td>
          </template>

          <!-- Subject Column -->
          <template #body-cell-subject="props">
            <q-td :props="props">
              <span 
                class="subject-badge"
                :style="{ 
                  backgroundColor: props.row.data.schedule?.cst?.c_bg,
                  color: props.row.data.schedule?.cst?.c_text
                }"
              >
                {{ props.row.data.schedule?.cst?.subject_name }}
              </span>
            </q-td>
          </template>

          <!-- Status Column -->
          <template #body-cell-status="props">
            <q-td :props="props">
              <StatusBadge :status="props.row.data.status" />
            </q-td>
          </template>

          <!-- Classwork Column -->
          <template #body-cell-cw="props">
            <q-td :props="props" class="content-preview" @click.stop>
              <div v-if="inlineEditMode" class="inline-edit-cell-vertical">
                <div class="editor-header">
                  <q-icon name="school" size="xs" color="blue" />
                  <span class="text-caption text-weight-medium q-ml-xs">{{ t('weeklyPlans.classwork') }}</span>
                  <q-btn
                    v-if="copiedData?.cw"
                    icon="content_paste"
                    size="sm"
                    flat
                    dense
                    color="primary"
                    @click="pasteField(props.row.data, 'cw')"
                  >
                    <q-tooltip>{{ t('weeklyPlans.teacher.pasteCw') }}</q-tooltip>
                  </q-btn>
                </div>
                <q-editor
                  v-model="props.row.data.cw"
                  min-height="5rem"
                  :toolbar="editorToolbar"
                  @blur="savePlan(props.row.data)"
                  class="inline-editor"
                />
              </div>
              <div v-else>
                <div v-if="props.row.data.cw" class="text-info html-content">
                  <q-icon name="school" size="xs" /> 
                  <span class="q-ml-xs" v-html="truncateHtml(props.row.data.cw, 50)"></span>
                </div>
                <span v-else class="text-grey-5">-</span>
              </div>
            </q-td>
          </template>

          <!-- Homework Column -->
          <template #body-cell-hw="props">
            <q-td :props="props" class="content-preview" @click.stop>
              <div v-if="inlineEditMode" class="inline-edit-cell-vertical">
                <div class="editor-header">
                  <q-icon name="home_work" size="xs" color="orange" />
                  <span class="text-caption text-weight-medium q-ml-xs">{{ t('weeklyPlans.homework') }}</span>
                  <q-btn
                    v-if="copiedData?.hw"
                    icon="content_paste"
                    size="sm"
                    flat
                    dense
                    color="primary"
                    @click="pasteField(props.row.data, 'hw')"
                  >
                    <q-tooltip>{{ t('weeklyPlans.teacher.pasteHw') }}</q-tooltip>
                  </q-btn>
                </div>
                <q-editor
                  v-model="props.row.data.hw"
                  min-height="5rem"
                  :toolbar="editorToolbar"
                  @blur="savePlan(props.row.data)"
                  class="inline-editor"
                />
              </div>
              <div v-else>
                <div v-if="props.row.data.hw" class="text-warning html-content">
                  <q-icon name="home_work" size="xs" /> 
                  <span class="q-ml-xs" v-html="truncateHtml(props.row.data.hw, 50)"></span>
                </div>
                <span v-else class="text-grey-5">-</span>
              </div>
            </q-td>
          </template>

          <!-- Notes Column -->
          <template #body-cell-notes="props">
            <q-td :props="props" class="content-preview" @click.stop>
              <div v-if="inlineEditMode" class="inline-edit-cell-vertical">
                <div class="editor-header">
                  <q-icon name="note" size="xs" color="grey" />
                  <span class="text-caption text-weight-medium q-ml-xs">{{ t('weeklyPlans.notes') }}</span>
                  <q-btn
                    v-if="copiedData?.notes"
                    icon="content_paste"
                    size="sm"
                    flat
                    dense
                    color="primary"
                    @click="pasteField(props.row.data, 'notes')"
                  >
                    <q-tooltip>{{ t('weeklyPlans.teacher.pasteNotes') }}</q-tooltip>
                  </q-btn>
                </div>
                <q-editor
                  v-model="props.row.data.notes"
                  min-height="5rem"
                  :toolbar="editorToolbar"
                  @blur="savePlan(props.row.data)"
                  class="inline-editor"
                />
              </div>
              <div v-else>
                <span v-if="props.row.data.notes" class="text-info html-content" v-html="truncateHtml(props.row.data.notes, 40)">
                </span>
                <span v-else class="text-grey-5">-</span>
              </div>
            </q-td>
          </template>
        </q-table>
      </q-card>
    </div>

    <!-- No plans state -->
    <div v-if="!classroomPlans || classroomPlans.length === 0" class="text-center q-pa-xl">
      <q-icon name="meeting_room" size="64px" color="grey-5" />
      <p class="text-h6 text-grey-7 q-mt-md">{{ t('weeklyPlans.teacher.noClassrooms') }}</p>
      <p class="text-grey-6">{{ t('weeklyPlans.teacher.selectToView') }}</p>
    </div>

    <!-- Mobile Print Dialog -->
    <q-dialog v-model="showMobilePrint" maximized>
   


      <q-card class="mobile-print-card">
        <!-- Sticky Header -->
        <q-toolbar class="mobile-print-header   sticky-top">
          <q-toolbar-title class="text-center  ">
            <div class="text-h2 y-12">{{ classroomPlans[0]?.name || t('weeklyPlans.weeklyLearningPlan') }}</div>
            <div class="text-h6 text-grey-7">{{ t('weeklyPlans.week') }} {{ weekNumber }} - {{ t('weeklyPlans.semester') }} {{ semesterNumber }}</div>
          </q-toolbar-title>
   <div class="no-print  ">
          <q-btn icon="picture_as_pdf" class="mx-12" flat round dense @click="downloadPDF" color="red-7">
            <q-tooltip>{{ t('weeklyPlans.teacher.downloadPdf') }}</q-tooltip>
          </q-btn>
          <q-btn icon="print" class="mx-12  " flat round dense @click="printMobile">
            <q-tooltip>{{ t('weeklyPlans.print') }}</q-tooltip>
          </q-btn>
          <q-btn icon="close" flat round dense @click="showMobilePrint = false">
            <q-tooltip>{{ t('common.close') || t('weeklyPlans.close') || 'Close' }}</q-tooltip>
          </q-btn>
      </div>
        </q-toolbar>

        <!-- Mobile Cards Container -->
        <div class="mobile-cards-container q-pa-md">
          <!-- Loop through each day -->
          <div 
            v-for="dayGroup in mobilePlansByDay" 
            :key="dayGroup.dayNumber"
            class="day-group q-mb-lg"
          >
            <!-- Day Title -->
            <div class="day-title">
              {{ t('weeklyPlans.week') }} {{ weekNumber }} - {{ dayGroup.dayName }}
            </div>

            <!-- Day Table -->
            <table class="mobile-day-table">
              <thead>
                <tr>
                  <th class="period-col">{{ t('weeklyPlans.period') }}</th>
                  <th class="subject-col">{{ t('weeklyPlans.subject') }}</th>
                  <th class="cw-col">{{ t('weeklyPlans.classwork') }}</th>
                  <th class="hw-notes-col">{{ t('weeklyPlans.homework') }} / {{ t('weeklyPlans.notes') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="plan in dayGroup.plans" :key="plan.id">
                  <!-- Period Number -->
                  <td class="period-cell">
                    <div class="period-badge">{{ plan.schedule?.period_number }}</div>
                  </td>
                  
                  <!-- Subject -->
                  <td class="subject-cell">
                    <div class="subject-text">{{ plan.schedule?.cst?.subject_name }}</div>
                  </td>
                  
                  <!-- Classwork Column -->
                  <td class="content-cell-mobile">
                    <div v-if="plan.cw" class="content-text" v-html="plan.cw"></div>
                    <div v-else class="text-grey-5 text-center">-</div>
                  </td>
                  
                  <!-- HW / Notes Column -->
                  <td class="content-cell-mobile">
                    <!-- Homework -->
                    <div v-if="plan.hw" class="content-section">
                      <div class="content-label">
                        <q-icon name="home_work" size="xs" color="orange" />
                        <span>{{ t('weeklyPlans.teacher.hwLabel') }}</span>
                      </div>
                      <div class="content-text" v-html="plan.hw"></div>
                    </div>
                    
                    <!-- Notes -->
                    <div v-if="plan.notes" class="content-section">
                      <div class="content-label">
                        <q-icon name="note" size="xs" color="grey-7" />
                        <span>{{ t('weeklyPlans.teacher.notesLabel') }}</span>
                      </div>
                      <div class="content-text" v-html="plan.notes"></div>
                    </div>
                    
                    <!-- Empty state -->
                    <div v-if="!plan.hw && !plan.notes" class="text-grey-5 text-center">
                      -
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import StatusBadge from './shared/StatusBadge.vue'

const { t } = useI18n()

const props = defineProps({
  plans: {
    type: Array,
    required: true,
    default: () => []
  },
  days: {
    type: Object,
    required: true
  },
  inlineEditMode: {
    type: Boolean,
    default: false
  },
  copiedData: {
    type: Object,
    default: null
  },
  weekNumber: {
    type: [Number, String],
    default: 1
  },
  semesterNumber: {
    type: [Number, String],
    default: 1
  }
})

const emit = defineEmits(['edit', 'save', 'paste'])

const showMobilePrint = ref(false)

// Editor Config
const editorToolbar = [
  ['bold', 'italic', 'underline'],
  ['link'],
  ['unordered', 'ordered'],
  ['undo', 'redo']
]

// Computed Properties
const classroomPlans = computed(() => {
  const byClassroom = {}
  
  props.plans.forEach(plan => {
    const classroomName = plan.schedule?.cst?.classroom_name
    if (!byClassroom[classroomName]) {
      byClassroom[classroomName] = {
        name: classroomName,
        plans: []
      }
    }
    byClassroom[classroomName].plans.push({
      id: plan.id,
      data: plan
    })
  })

  // Sort plans within each classroom by day and period
  Object.values(byClassroom).forEach(classroom => {
    classroom.plans.sort((a, b) => {
      const dayDiff = (a.data.schedule?.day || 0) - (b.data.schedule?.day || 0)
      if (dayDiff !== 0) return dayDiff
      return (a.data.schedule?.period_number || 0) - (b.data.schedule?.period_number || 0)
    })
  })

  return Object.values(byClassroom).sort((a, b) => a.name.localeCompare(b.name))
})

const mobilePlansByDay = computed(() => {
  const byDay = {}
  
  props.plans.forEach(plan => {
    const dayNum = plan.schedule?.day
    if (!byDay[dayNum]) {
      byDay[dayNum] = {
        dayNumber: dayNum,
        dayName: props.days[dayNum] || t(`weeklyPlans.fullDays.${dayNum}`) || `Day ${dayNum}`,
        plans: []
      }
    }
    byDay[dayNum].plans.push(plan)
  })

  // Sort plans within each day by period
  Object.values(byDay).forEach(day => {
    day.plans.sort((a, b) => (a.schedule?.period_number || 0) - (b.schedule?.period_number || 0))
  })

  return Object.values(byDay).sort((a, b) => a.dayNumber - b.dayNumber)
})

const tableColumns = computed(() => [
  { name: 'day', label: t('weeklyPlans.day'), field: row => props.days[row.data.schedule?.day] || t(`weeklyPlans.fullDays.${row.data.schedule?.day}`) || 'N/A', align: 'left' },
  { name: 'period', label: t('weeklyPlans.period'), field: row => row.data.schedule?.period_number, align: 'center' },
  { name: 'subject', label: t('weeklyPlans.subject'), field: row => row.data.schedule?.cst?.subject_name, align: 'left' },
  { name: 'status', label: t('weeklyPlans.status'), field: row => row.data.status, align: 'left' },
  { name: 'cw', label: t('weeklyPlans.classwork'), field: row => row.data.cw, align: 'left' },
  { name: 'hw', label: t('weeklyPlans.homework'), field: row => row.data.hw, align: 'left' },
  { name: 'notes', label: t('weeklyPlans.notes'), field: row => row.data.notes, align: 'left' }
])

// Methods
const openMobilePrint = () => {
  showMobilePrint.value = true
}

const printMobile = () => {
  window.print()
}

const downloadPDF = async () => {
  // Dynamically import html2pdf
  const html2pdf = (await import('html2pdf.js')).default
  
  const element = document.querySelector('.mobile-cards-container')
  if (!element) return
  
  const classroomName = classroomPlans.value[0]?.name || 'Weekly_Plans'
  // Clean classroom name for filename (replace spaces with underscores, remove special chars)
  const cleanName = classroomName.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_-]/g, '')
  const filename = `${cleanName}_Week_${props.weekNumber}_Semester_${props.semesterNumber}.pdf`
  
  const opt = {
    margin: [10, 10],
    filename: filename,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { 
      scale: 2,
      useCORS: true,
      letterRendering: true
    },
    jsPDF: { 
      unit: 'mm', 
      format: 'a4', 
      orientation: 'portrait' 
    },
    pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
  }
  
  html2pdf().set(opt).from(element).save()
}

const savePlan = (plan) => {
  emit('save', plan)
}

const pasteField = (plan, field) => {
  if (!props.copiedData) return
  const content = props.copiedData[field]
  if (content !== undefined) {
    plan[field] = content
    emit('save', plan)
  }
}

const truncateHtml = (html, length) => {
  if (!html) return ''
  // Strip HTML tags for length calculation
  const stripped = html.replace(/<[^>]*>/g, '')
  if (stripped.length <= length) return html
  
  // Truncate and add ellipsis
  const truncated = stripped.substring(0, length)
  return truncated + '...'
}

// Expose openMobilePrint and downloadPDF for parent to call
defineExpose({
  openMobilePrint,
  downloadPDF
})
</script>

<style scoped>
.classroom-table :deep(.q-table__card) {
  box-shadow: none;
}

.classroom-table :deep(tbody tr) {
  cursor: pointer;
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
  max-width: 300px;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.html-content {
  line-height: 1.4;
}

.html-content :deep(a) {
  color: #1976d2;
  text-decoration: underline;
}

.inline-edit-cell-vertical {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: 100%;
}

.editor-header {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 0;
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

/* Mobile Print Styles */
.mobile-print-card {
  background-color: #f5f7fa;
  font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'SF Pro Display', 'Segoe UI', sans-serif;
}

.mobile-print-header {
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
  color: white;
  padding: 16px 20px;
}

.mobile-cards-container {
  max-width: 800px;
  margin: 0 auto;
  background-color: #f5f7fa;
}

.day-group {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  page-break-inside: avoid;
}

.day-title {
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
  color: white;
  padding: 16px 20px;
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.mobile-day-table {
  width: 100%;
  border-collapse: collapse;
}

.mobile-day-table thead {
  background-color: #e3f2fd;
}

.mobile-day-table th {
  padding: 12px;
  text-align: left;
  font-weight: 600;
  font-size: 0.85rem;
  color: #1565c0;
  border-bottom: 2px solid #1976d2;
}

.mobile-day-table th.period-col {
  width: 70px;
  text-align: center;
}

.mobile-day-table th.subject-col {
  width: 20%;
}

.mobile-day-table th.cw-col {
  width: 35%;
}

.mobile-day-table th.hw-notes-col {
  width: auto;
}

.mobile-day-table tbody tr {
  border-bottom: 1px solid #e8edf2;
}

.mobile-day-table tbody tr:last-child {
  border-bottom: none;
}

.mobile-day-table td {
  padding: 16px 12px;
  vertical-align: top;
}

.period-cell {
  text-align: center;
}

.period-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
  color: white;
  border-radius: 50%;
  font-weight: 700;
  font-size: 0.95rem;
}

.subject-cell {
  padding-right: 16px;
}

.subject-text {
  font-weight: 600;
  font-size: 0.95rem;
  color: #263238;
  line-height: 1.4;
}

.content-cell-mobile {
  font-size: 0.9rem;
}

.content-section {
  margin-bottom: 12px;
}

.content-section:last-child {
  margin-bottom: 0;
}

.content-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  font-size: 0.85rem;
  color: #37474f;
  margin-bottom: 6px;
}

.content-text {
  color: #546e7a;
  line-height: 1.6;
  padding-left: 20px;
}

.content-text :deep(a) {
  color: #1976d2;
  text-decoration: underline;
}

.content-text :deep(p) {
  margin: 6px 0;
}

.content-text :deep(ul),
.content-text :deep(ol) {
  margin: 6px 0;
  padding-left: 20px;
}

.content-text :deep(li) {
  margin: 3px 0;
}

.content-text :deep(strong) {
  font-weight: 600;
  color: #263238;
}

.content-text :deep(em) {
  font-style: italic;
}

/* Print Styles for Mobile View */
@media print {
  .no-print {
    display: none !important;
  }
  
  .mobile-print-header {
    position: static;
    box-shadow: none;
    border-bottom: 2px solid #1976d2;
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%) !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    color: white !important;
  }
  
  .mobile-cards-container {
    background-color: white;
    padding: 0 !important;
  }
  
  .day-group {
    box-shadow: none;
    border: 1px solid #e0e0e0;
    margin-bottom: 20px;
    page-break-inside: avoid;
  }
  
  .day-title {
    background: #1976d2 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  
  .period-badge {
    background: #1976d2 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  
  .mobile-day-table thead {
    background-color: #e3f2fd !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  
  .content-text :deep(a) {
    color: #1976d2 !important;
    text-decoration: underline !important;
  }
}
</style>
