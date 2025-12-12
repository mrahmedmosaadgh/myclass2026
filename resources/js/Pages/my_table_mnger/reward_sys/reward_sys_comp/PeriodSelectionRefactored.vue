<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- Date Picker -->
     
    <q-input
      filled
      v-model="localDate"
      type="date"
      label="Date"
      class="rounded-lg bg-white"
      dense
    />

    <!-- Subject Selector -->
    <q-select
      filled
      v-model="localSubject"
      :options="subjectOptionsResolved"
      option-value="value"
      option-label="label"
      emit-value
      map-options
      label="Subject"
      class="rounded-lg bg-white"
      dense
    />

    <!-- Semester Selector -->
    <q-select
      filled
      v-model="localSemester"
      :options="semesterOptionsResolved"
      option-value="value"
      option-label="label"
      emit-value
      map-options
      label="Semester"
      class="rounded-lg bg-white"
      dense
    />

    <!-- Week Selector -->
    <q-select
      filled
      v-model="localWeek"
      :options="weekOptionsResolved"
      option-value="value"
      option-label="label"
      emit-value
      map-options
      label="Week"
      class="rounded-lg bg-white"
      dense
    />

    <!-- Day Selector -->
    <q-select
      filled
      v-model="localDay"
      :options="dayOptionsResolved"
      option-value="value"
      option-label="label"
      emit-value
      map-options
      label="Day"
      class="rounded-lg bg-white"
      dense
    />

    <!-- Period Number Selector -->
    <q-select
      filled
      v-model="localPeriodNumber"
      :options="periodOptionsResolved"
      option-value="value"
      option-label="label"
      emit-value
      map-options
      label="Period Number"
      class="rounded-lg bg-white"
      dense
    />
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'

const props = defineProps({
  date: { type: String, default: () => new Date().toISOString().split('T')[0] },
  subject: { type: String, default: 'Math' },
  semester: { type: [Number, String], default: null },
  week: { type: [Number, String], default: null },
  day: { type: [Number, String], default: null },
  periodNumber: { type: [Number, String], default: null },
  
  // Option arrays with defaults
  subjectOptions: { type: Array, default: () => [
    { label: 'Math', value: 'Math' },
    { label: 'English', value: 'English' },
    { label: 'Science', value: 'Science' },
    { label: 'Social Studies', value: 'Social Studies' },
    { label: 'PE', value: 'PE' },
    { label: 'Art', value: 'Art' },
    { label: 'Music', value: 'Music' }
  ]},
  semesterOptions: { type: Array, default: () => Array.from({ length: 4 }, (_, i) => ({ label: `Semester ${i + 1}`, value: i + 1 })) },
  weekOptions: { type: Array, default: () => Array.from({ length: 16 }, (_, i) => ({ label: `Week ${i + 1}`, value: i + 1 })) },
  dayOptions: { type: Array, default: () => Array.from({ length: 7 }, (_, i) => ({ label: `Day ${i + 1}`, value: i + 1 })) },
  periodOptions: { type: Array, default: () => Array.from({ length: 8 }, (_, i) => ({ label: `Period ${i + 1}`, value: i + 1 })) },
  
  // optional persistence
  persist: { type: Boolean, default: false },
  persistKey: { type: String, default: 'reward-system-last-selection' }
})

const emit = defineEmits(['update:date', 'update:subject', 'update:semester', 'update:week', 'update:day', 'update:periodNumber', 'change'])

// Computed options to ensure we always have arrays
const subjectOptionsResolved = computed(() => Array.isArray(props.subjectOptions) ? props.subjectOptions : [])
const semesterOptionsResolved = computed(() => Array.isArray(props.semesterOptions) ? props.semesterOptions : [])
const weekOptionsResolved = computed(() => Array.isArray(props.weekOptions) ? props.weekOptions : [])
const dayOptionsResolved = computed(() => Array.isArray(props.dayOptions) ? props.dayOptions : [])
const periodOptionsResolved = computed(() => Array.isArray(props.periodOptions) ? props.periodOptions : [])

// Computed proxies for all fields
const localDate = computed({
  get: () => props.date,
  set: (v) => { emit('update:date', v); emitChange(v, 'date') }
})

const localSubject = computed({
  get: () => props.subject,
  set: (v) => { emit('update:subject', v); emitChange(v, 'subject') }
})

const localSemester = computed({ 
  get: () => props.semester, 
  set: (v) => { emit('update:semester', v); emitChange(v, 'semester') } 
})

const localWeek = computed({ 
  get: () => props.week, 
  set: (v) => { emit('update:week', v); emitChange(v, 'week') } 
})

const localDay = computed({ 
  get: () => props.day, 
  set: (v) => { emit('update:day', v); emitChange(v, 'day') } 
})

const localPeriodNumber = computed({ 
  get: () => props.periodNumber, 
  set: (v) => { emit('update:periodNumber', v); emitChange(v, 'periodNumber') } 
})

function emitChange(newValue, field) {
  // Construct the full object with the new value for the changed field
  // Note: We use props for the others because the computed setters update the parent via emit,
  // but the prop value might not have updated yet in this tick if the parent update is async.
  // However, for immediate local consistency, we can use the value passed in.
  
  const current = {
    date: props.date,
    subject: props.subject,
    semester: props.semester,
    week: props.week,
    day: props.day,
    periodNumber: props.periodNumber
  }
  
  // Override the changed field
  if (field) current[field] = newValue

  emit('change', current)

  if (props.persist) saveLastSelection(current)
}

function saveLastSelection(data) {
  try {
    localStorage.setItem(props.persistKey, JSON.stringify(data))
  } catch (e) {
    // ignore
  }
}

function loadLastSelection() {
  try {
    const raw = localStorage.getItem(props.persistKey)
    if (!raw) return
    const p = JSON.parse(raw)
    
    // Only restore if current values are defaults/empty
    const today = new Date().toISOString().split('T')[0]
    if (p.date != null && props.date === today) emit('update:date', p.date)
    if (p.subject != null && props.subject === 'Math') emit('update:subject', p.subject)
    if (p.semester != null && props.semester == null) emit('update:semester', p.semester)
    if (p.week != null && props.week == null) emit('update:week', p.week)
    if (p.day != null && props.day == null) emit('update:day', p.day)
    if (p.periodNumber != null && props.periodNumber == null) emit('update:periodNumber', p.periodNumber)
    
    // Emit change after restoring
    // We need to wait for next tick or just emit with the restored values
    // For simplicity, we rely on the updates above triggering the parent
  } catch (e) {
    // ignore
  }
}

onMounted(() => {
  if (props.persist) loadLastSelection()
})
</script>
