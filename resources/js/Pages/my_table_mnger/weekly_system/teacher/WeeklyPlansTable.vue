<template>
  <div>
    <!-- Filters -->
    <div class="row q-gutter-sm q-mb-md items-end">
      <q-select v-model="filters.day" :options="dayOptions" dense outlined clearable emit-value map-options label="Day" style="min-width: 160px" />
      <q-select v-model="filters.classroom" :options="classroomOptions" dense outlined clearable emit-value map-options label="Classroom" style="min-width: 200px" />
      <q-select v-model="filters.grade" :options="gradeOptions" dense outlined clearable emit-value map-options label="Grade" style="min-width: 160px" />
      <q-select v-model="filters.subject" :options="subjectOptions" dense outlined clearable emit-value map-options label="Subject" style="min-width: 200px" />
      <q-btn flat dense icon="refresh" color="primary" @click="clearFilters" label="Clear" />
      <q-space />
      <q-btn color="primary" dense unelevated icon="content_copy" label="Copy" @click="openCopyDialog" />
      <q-btn :color="groupByClassroom ? 'primary' : 'grey-7'" :outline="!groupByClassroom" dense icon="table_rows" label="Group by classroom" @click="groupByClassroom = !groupByClassroom" />
      <q-btn color="primary" dense unelevated icon="print" label="Print" @click="printMainTable" />
      <q-toggle v-model="periodOrderEdit" label="Period Order edit" color="primary" class="q-ml-md" />
    </div>

    <div id="plans-table-print">
    <div class="screen-only">
    <q-table v-if="!groupByClassroom"
      flat bordered
      :rows="filteredRows"
      :columns="columns"
      row-key="id"
      :pagination="{ rowsPerPage: 10 }"
    >
      <template #body-cell-dayName="props">
        <q-td :props="props">{{ dayName(props.row.schedule?.day) }}</q-td>
      </template>
      <template #body-cell-classroom="props">
        <q-td :props="props">
          {{ props.row.schedule?.cst?.classroom?.name || '-' }}
          <div class="subject-sub">
            {{ props.row.schedule?.cst?.subject?.name || '-' }} ({{ props.row.schedule?.period_order ?? '-' }})
          </div>
        </q-td>
      </template>
      <template #body-cell-grade="props">
        <q-td :props="props">{{ gradeLabel(props.row.schedule?.grade_id) }}</q-td>
      </template>
      <template #body-cell-subject="props">
        <q-td :props="props">{{ props.row.schedule?.cst?.subject?.name || '-' }}</q-td>
      </template>
      <template #body-cell-periodOrder="props">
        <q-td :props="props">
          <template v-if="props.row.schedule?.id">
            <q-input
              v-if="periodOrderEdit"
              dense outlined type="number" min="1" style="max-width: 90px"
              v-model.number="props.row.schedule.period_order"
              @blur="onUpdatePeriodOrder(props.row)"
            />
            <span v-else>{{ props.row.schedule?.period_order ?? '-' }}</span>
          </template>
          <span v-else>-</span>
        </q-td>
      </template>
      <template #body-cell-status="props">
        <q-td :props="props">
          <q-chip dense :color="statusColor(props.row.status)" text-color="white">{{ statusLabel(props.row.status) }}</q-chip>
        </q-td>
      </template>
      <template #body-cell-actions="props">
        <q-td :props="props" class="text-right">
          <q-btn flat dense round icon="edit" color="primary" @click="onEdit(props.row)">
            <q-tooltip>Edit</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <!-- Grouped by classroom view -->
    <div v-else>
      <div v-for="g in groupedByClassroom" :key="g.id" class="q-mb-xl">
        <div class="text-h6 text-center q-mb-sm">Classroom: {{ g.name }}</div>
        <q-markup-table dense flat bordered>
          <thead>
            <tr>
              <th>Day</th>
              <th>Period</th>
              <th>Period Order</th>
              <th>Weekly Plan (CW, HW, Notes)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in g.rows" :key="p.id">
              <td>{{ dayName(p?.schedule?.day) }}</td>
              <td>{{ p?.schedule?.period_number ?? '-' }}</td>
              <td>{{ p?.schedule?.period_order ?? '-' }}</td>
              <td>
                <div><strong>CW:</strong> {{ p?.cw || '-' }}</div>
                <div><strong>HW:</strong> {{ p?.hw || '-' }}</div>
                <div><strong>Notes:</strong> {{ p?.notes || '-' }}</div>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </div>
    </div>
    </div>
    
    <!-- Print-only condensed table -->
    <div class="print-only">
      <div class="print-header">
        <h1>Weekly Plans</h1>
        <div class="meta">Week {{ weekNumber }} • Semester {{ semesterNumber }}</div>
        <div class="meta">
          Filters:
          <span>Day: {{ filters.day ? dayName(filters.day) : 'All' }}</span>
          | <span>Classroom: {{ filters.classroom ? (classroomOptions.find(o=>o.value===filters.classroom)?.label || filters.classroom) : 'All' }}</span>
          | <span>Subject: {{ filters.subject ? (subjectOptions.find(o=>o.value===filters.subject)?.label || filters.subject) : 'All' }}</span>
          | <span>Grade: {{ filters.grade ? gradeLabel(filters.grade) : 'All' }}</span>
        </div>
        <div class="meta">Printed: {{ printTimestamp }}</div>
      </div>
      <q-markup-table flat bordered dense>
        <thead>
          <tr>
            <th>Classroom</th>
            <th>Day</th>
            <th>Period</th>
            <th>Weekly Plan (CW, HW, Notes)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in filteredRows" :key="p.id">
            <td>
              {{ p?.schedule?.cst?.classroom?.name || '-' }}
              <div class="subject-sub">{{ p?.schedule?.cst?.subject?.name || '-' }} ({{ p?.schedule?.period_order ?? '-' }})</div>
            </td>
            <td>{{ dayName(p?.schedule?.day) }}</td>
            <td>{{ p?.schedule?.period_number ?? '-' }}</td>
            <td>
              <div><strong>CW:</strong> {{ p?.cw || '-' }}</div>
              <div><strong>HW:</strong> {{ p?.hw || '-' }}</div>
              <div><strong>Notes:</strong> {{ p?.notes || '-' }}</div>
            </td>
          </tr>
        </tbody>
      </q-markup-table>
    </div>
    </div>

    <!-- Copy Dialog -->
    <q-dialog v-model="showCopyDialog">
      <q-card style="min-width: 520px; max-width: 90vw">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Copy plans to other classrooms</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section>
          <div class="q-gutter-md">
            <q-select v-model="copyFrom" :options="classroomOptions" emit-value map-options outlined label="From classroom" :disable="classroomOptions.length===0" />
            <q-select v-model="copySubject" :options="copySubjectOptions" emit-value map-options outlined label="Subject" :disable="!copyFrom || copySubjectOptions.length===0" />
            <q-select v-model="copyTo" :options="toClassroomOptions" use-chips multiple emit-value map-options outlined label="To classrooms" :disable="!copyFrom || toClassroomOptions.length===0" />
            <div class="text-caption text-grey-7">Week {{ weekNumber }} • Semester {{ semesterNumber }}</div>
          </div>
        </q-card-section>
        <!-- Preview Section -->
        <q-separator />
        <q-card-section v-if="copyPreview">
          <div class="text-subtitle2 q-mb-sm">Source records</div>
          <q-markup-table dense flat bordered>
            <thead>
              <tr><th>Day</th><th>Period Order</th><th>CW</th><th>HW</th><th>Notes</th></tr>
            </thead>
            <tbody>
              <tr v-for="s in copyPreview.source" :key="s.weekly_plan_id">
                <td>{{ dayName(s.day) }}</td>
                <td>{{ s.period_order ?? '-' }}</td>
                <td style="max-width: 220px">{{ s.cw }}</td>
                <td style="max-width: 220px">{{ s.hw }}</td>
                <td style="max-width: 220px">{{ s.notes }}</td>
              </tr>
            </tbody>
          </q-markup-table>

          <div class="q-mt-md text-subtitle2">Targets</div>
          <div v-for="t in copyPreview.targets" :key="t.to_classroom_id" class="q-mt-sm">
            <div class="text-caption text-grey-7 q-mb-xs">Classroom #{{ t.to_classroom_id }}</div>
            <q-markup-table dense flat bordered>
              <thead>
                <tr>
                  <th>Src Day</th>
                  <th>Src Order</th>
                  <th>Target Order</th>
                  <th>Orders Match?</th>
                  <th>Will Update?</th>
                  <th>Target CW</th>
                  <th>Target HW</th>
                  <th>Target Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in t.matches" :key="m.source_weekly_plan_id">
                  <td>{{ dayName(m.source_day ?? copyPreview.source.find(x=>x.weekly_plan_id===m.source_weekly_plan_id)?.day) }}</td>
                  <td>{{ m.source_period_order ?? copyPreview.source.find(x=>x.weekly_plan_id===m.source_weekly_plan_id)?.period_order }}</td>
                  <td>{{ m.target_period_order ?? '-' }}</td>
                  <td>
                    <span :class="(m.source_period_order != null && m.target_period_order != null)
                                   ? (m.source_period_order === m.target_period_order ? 'text-positive' : 'text-negative')
                                   : 'text-grey-7'">
                      {{ (m.source_period_order != null && m.target_period_order != null)
                          ? (m.source_period_order === m.target_period_order ? 'Yes' : 'No')
                          : '-' }}
                    </span>
                  </td>
                  <td>{{ m.has_target_plan ? 'Yes' : 'No (skipped)' }}</td>
                  <td style="max-width: 220px">{{ m.target_cw ?? '-' }}</td>
                  <td style="max-width: 220px">{{ m.target_hw ?? '-' }}</td>
                  <td style="max-width: 220px">{{ m.target_notes ?? '-' }}</td>
                </tr>
              </tbody>
            </q-markup-table>
          </div>
        </q-card-section>
        <q-separator />
        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn v-if="!copyPreview" color="primary" :loading="previewing" :disable="!canSubmitCopy" label="Preview" @click="submitPreview" />
          <q-btn v-else color="primary" :loading="committing" :disable="!(copyPreview?.operations?.length)" label="Save" @click="submitCommit" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  plans: { type: Array, default: () => [] },
  weekNumber: { type: Number, required: true },
  semesterNumber: { type: Number, required: true }
})
const emit = defineEmits(['edit', 'copied'])

const dayMap = { 1: 'Sunday', 2: 'Monday', 3: 'Tuesday', 4: 'Wednesday', 5: 'Thursday', 6: 'Friday', 7: 'Saturday' }
const dayName = (d) => dayMap[d] || `Day ${d ?? '-'}`
const gradeLabel = (g) => (g ? `Grade ${g}` : '-')

const filters = reactive({ day: null, classroom: null, grade: null, subject: null })
const clearFilters = () => { filters.day = filters.classroom = filters.grade = filters.subject = null }
  const groupByClassroom = ref(false)

const columns = [
  { name: 'dayName', label: 'Day', field: 'dayName', align: 'left', sortable: true },
  { name: 'period', label: 'Period', field: row => row.schedule?.period_number ?? '-', align: 'left', sortable: true },
  { name: 'classroom', label: 'Classroom', field: row => row.schedule?.cst?.classroom?.name ?? '-', align: 'left', sortable: true, style: 'width: 1%;', headerStyle: 'width: 1%;' },
  { name: 'grade', label: 'Grade', field: row => gradeLabel(row.schedule?.grade_id), align: 'left' },
  { name: 'subject', label: 'Subject', field: row => row.schedule?.cst?.subject?.name ?? '-', align: 'left', sortable: true },
  { name: 'cw', label: 'CW', field: 'cw', align: 'left' },
  { name: 'hw', label: 'HW', field: 'hw', align: 'left' },
  { name: 'notes', label: 'Notes', field: 'notes', align: 'left' },
  { name: 'status', label: 'Status', field: 'status', align: 'left' },
  { name: 'actions', label: 'Actions', field: row => row.id, align: 'right' }
]

const uniqueBy = (arr, key) => {
  const seen = new Set();
  return arr.filter((x) => { const k = key(x); if (k == null || seen.has(k)) return false; seen.add(k); return true })
}

const dayOptions = computed(() =>
  uniqueBy(props.plans.filter(p => p?.schedule?.day != null), p => p.schedule.day)
  .map(p => ({ label: dayName(p.schedule.day), value: p.schedule.day }))
  .sort((a, b) => a.value - b.value)
)

const classroomOptions = computed(() =>
  uniqueBy(props.plans.filter(p => p?.schedule?.cst?.classroom), p => p.schedule.cst.classroom.id)
  .map(p => ({ label: p.schedule.cst.classroom.name, value: p.schedule.cst.classroom.id }))
  .sort((a, b) => a.label.localeCompare(b.label))
)

const gradeOptions = computed(() =>
  uniqueBy(props.plans.filter(p => p?.schedule?.grade_id != null), p => p.schedule.grade_id)
  .map(p => ({ label: gradeLabel(p.schedule.grade_id), value: p.schedule.grade_id }))
  .sort((a, b) => (a.value ?? 0) - (b.value ?? 0))
)

const subjectOptions = computed(() =>
  uniqueBy(props.plans.filter(p => p?.schedule?.cst?.subject), p => p.schedule.cst.subject.id)
  .map(p => ({ label: p.schedule.cst.subject.name, value: p.schedule.cst.subject.id }))
  .sort((a, b) => a.label.localeCompare(b.label))
)

const filteredRows = computed(() =>
  props.plans.filter((p) => (
    (!filters.day || p?.schedule?.day === filters.day) &&
    (!filters.classroom || p?.schedule?.cst?.classroom?.id === filters.classroom) &&
    (!filters.grade || p?.schedule?.grade_id === filters.grade) &&
    (!filters.subject || p?.schedule?.cst?.subject?.id === filters.subject)
  ))
)

  const groupedByClassroom = computed(() => {
    const map = new Map()
    for (const p of filteredRows.value) {
      const id = p?.schedule?.cst?.classroom?.id ?? 'unknown'
      const name = p?.schedule?.cst?.classroom?.name ?? 'Unknown classroom'
      if (!map.has(id)) map.set(id, { id, name, rows: [] })
      map.get(id).rows.push(p)
    }
    // sort rows inside each group (day, period, id)
    for (const g of map.values()) {
      g.rows.sort((a,b)=>{
        const da=(a?.schedule?.day||0)-(b?.schedule?.day||0)
        if(da!==0) return da
        const pa=(a?.schedule?.period_number||0)-(b?.schedule?.period_number||0)
        if(pa!==0) return pa
        return (a?.id||0)-(b?.id||0)
      })
    }
    return Array.from(map.values()).sort((a,b)=> String(a.name).localeCompare(String(b.name)))
  })

const statusColor = (s) => ({ empty: 'grey', partial: 'amber', completed: 'green' }[s] || 'primary')
const statusLabel = (s) => ({ empty: 'Empty', partial: 'Partial', completed: 'Completed' }[s] || (s || '-'))

const onEdit = (row) => emit('edit', row)

// ===== Copy Dialog state =====
const showCopyDialog = ref(false)
const copyFrom = ref(null)
const copySubject = ref(null)
const copyTo = ref([])
const copyLoading = ref(false)

const openCopyDialog = () => {
  // if a Classroom filter is selected, prefill as source
  copyFrom.value = filters.classroom || null
  // if a Subject filter is selected, prefill as subject
  copySubject.value = filters.subject || null
  copyTo.value = []
  copyPreview.value = null
  showCopyDialog.value = true
}

const toClassroomOptions = computed(() =>
  classroomOptions.value.filter(o => o.value !== copyFrom.value)
)

const canSubmitCopy = computed(() => !!copyFrom.value && !!copySubject.value && copyTo.value.length > 0)

const weekNumber = computed(() => props.weekNumber)
const semesterNumber = computed(() => props.semesterNumber)

// Staged copy: preview then commit
const copyPreview = ref(null)
const previewing = ref(false)
const committing = ref(false)
  const periodOrderEdit = ref(false)
  const printTimestamp = ref('')

const submitPreview = async () => {
  if (!canSubmitCopy.value) return
  try {
    previewing.value = true
    const { data } = await axios.post('/weekly-system/api/teacher/copy-plans-classrooms/preview', {
      from_classroom_id: copyFrom.value,
      subject_id: copySubject.value,
      to_classroom_ids: copyTo.value,
      week_number: weekNumber.value,
      semester_number: semesterNumber.value
    })
    copyPreview.value = data
  } catch (e) {
    console.error('Preview failed', e)
  } finally {
    previewing.value = false
  }
}

const submitCommit = async () => {
  if (!copyPreview.value?.operations?.length) { showCopyDialog.value = false; return }
  try {
    committing.value = true
    await axios.post('/weekly-system/api/teacher/copy-plans-classrooms/commit', {
      operations: copyPreview.value.operations
    })
    showCopyDialog.value = false
    emit('copied')
  } catch (e) {
    console.error('Commit failed', e)
  } finally {
    committing.value = false
  }
}

// Subject options limited to the selected source classroom
const copySubjectOptions = computed(() => {
  if (!copyFrom.value) return []
  const subjects = uniqueBy(
    props.plans.filter(p => p?.schedule?.cst?.classroom?.id === copyFrom.value && p?.schedule?.cst?.subject),
    p => p.schedule.cst.subject.id
  )
  return subjects
    .map(p => ({ label: p.schedule.cst.subject.name, value: p.schedule.cst.subject.id }))
    .sort((a, b) => a.label.localeCompare(b.label))
})

// Auto-preview as soon as enough info is selected
watch([copyFrom, copySubject], async ([fromVal, subjVal]) => {
  if (showCopyDialog.value && fromVal && subjVal) {
    // Preview only source if no targets selected
    await submitPreview()
  } else {
    copyPreview.value = null
  }
})

watch(copyTo, async (toVal) => {
  if (showCopyDialog.value && copyFrom.value && copySubject.value) {
    await submitPreview()
  }
})

// Inline update of schedule period_order
const onUpdatePeriodOrder = async (row) => {
  try {
    if (!row?.schedule?.id || !row?.schedule?.period_order) return
    await axios.put(`/weekly-system/api/schedules/${row.schedule.id}/period-order`, {
      period_order: row.schedule.period_order
    })
  } catch (e) {
    console.error('Failed to update period_order', e)
  }
}
  
  // Print via a clean pop-up window to avoid layout/CSS conflicts
  const escapeHtml = (s) => String(s ?? '').replace(/&/g, '&amp;')
    .replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;')

  const printMainTable = () => {
    const q = new URLSearchParams({
      week: String(weekNumber.value ?? ''),
      semester: String(semesterNumber.value ?? ''),
      day: filters.day != null ? String(filters.day) : '',
      classroom: filters.classroom != null ? String(filters.classroom) : '',
      subject: filters.subject != null ? String(filters.subject) : '',
      grade: filters.grade != null ? String(filters.grade) : ''
    })
    if (groupByClassroom.value) q.set('group','classroom')
    window.open(`/weekly-plans-print.html?${q.toString()}`, '_blank', 'noopener,noreferrer')
  }
</script>

<style>
@media print {
  @page { size: A4 portrait; margin: 12mm; }
  /* Do not hide ancestors; keep simple so the page is not blank if printed directly */
  #plans-table-print { position: static !important; width: auto; margin: 0; padding: 0; }
  /* Center the print block and fit it within printable width */
  #plans-table-print .print-only { max-width: 186mm; margin: 0 auto; }
  /* better pagination for tables */
  #plans-table-print .print-only thead { display: table-header-group; }
  #plans-table-print .print-only tfoot { display: table-footer-group; }
  #plans-table-print .print-only tr,
  #plans-table-print .print-only td,
  #plans-table-print .print-only th { break-inside: avoid; page-break-inside: avoid; }

  /* print typography */
  #plans-table-print .print-header { text-align: center; margin-bottom: 8mm; }
  #plans-table-print .print-header h1 { font-size: 18pt; margin: 0 0 3mm; }
  #plans-table-print .print-header .meta { font-size: 10pt; color: #000; }

  #plans-table-print .print-only table { width: 100%; max-width: 186mm; margin: 0 auto; table-layout: auto; border-collapse: collapse; }
  #plans-table-print .print-only th, #plans-table-print .print-only td { font-size: 10pt; padding: 4px 6px; vertical-align: top; }
  #plans-table-print .print-only th { background: #f0f0f0; }
  /* Classroom: minimal width; allow wrapping to keep it narrow */
  #plans-table-print .print-only td:nth-child(1), #plans-table-print .print-only th:nth-child(1) { width: 1%; }
  #plans-table-print .print-only td:nth-child(2), #plans-table-print .print-only th:nth-child(2) { width: 18mm; }
  #plans-table-print .print-only td:nth-child(3), #plans-table-print .print-only th:nth-child(3) { width: 18mm; }
  /* no explicit width for column 4 so it expands */
  #plans-table-print .print-only .subject-sub { font-size: 9pt; color: #444; }
  #plans-table-print .print-only td div { white-space: pre-wrap; word-break: break-word; }
}

/* default screen behavior */
#plans-table-print .print-only { display: none; }
#plans-table-print .screen-only { display: block; }
</style>

