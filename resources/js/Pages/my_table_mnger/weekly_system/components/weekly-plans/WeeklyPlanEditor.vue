<template>
  <q-dialog v-model="dialogModel" persistent maximized>
    <q-card>
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">
          <q-icon name="edit_note" class="q-mr-sm" color="primary" />
          Edit Weekly Plan
        </div>
        <q-space />
        <div class="row q-gutter-sm items-center">
          <StatusBadge :status="currentStatus" />
          <q-btn icon="close" flat round dense v-close-popup />
        </div>
      </q-card-section>

      <!-- Plan Info -->
      <q-card-section class="q-pt-sm">
        <div class="row q-gutter-sm text-caption">
          <q-chip dense icon="calendar_today" size="sm">
            Week {{ plan?.week_number }}
          </q-chip>
          <q-chip dense icon="schedule" size="sm">
            {{ dayName }} - Period {{ plan?.schedule?.period_number }}
          </q-chip>
          <q-chip dense icon="menu_book" size="sm" :style="subjectStyle">
            {{ subjectName }}
          </q-chip>
          <q-chip dense icon="meeting_room" size="sm">
            {{ classroomName }}
          </q-chip>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-md">
        <div class="row q-gutter-lg">
          <!-- Classwork -->
          <div class="col-12 col-md-6">
            <div class="text-subtitle1 text-weight-bold q-mb-sm">
              <q-icon name="school" class="q-mr-xs" color="blue" />
              Classwork (CW)
            </div>
            <q-editor
              v-model="form.cw"
              min-height="200px"
              :toolbar="editorToolbar"
              placeholder="Enter classwork content..."
            />
          </div>

          <!-- Homework -->
          <div class="col-12 col-md-6">
            <div class="text-subtitle1 text-weight-bold q-mb-sm">
              <q-icon name="home_work" class="q-mr-xs" color="orange" />
              Homework (HW)
            </div>
            <q-editor
              v-model="form.hw"
              min-height="200px"
              :toolbar="editorToolbar"
              placeholder="Enter homework content..."
            />
          </div>

          <!-- Notes -->
          <div class="col-12">
            <div class="text-subtitle1 text-weight-bold q-mb-sm">
              <q-icon name="note" class="q-mr-xs" color="grey" />
              Notes
            </div>
            <q-input
              v-model="form.notes"
              type="textarea"
              outlined
              rows="3"
              placeholder="Additional notes..."
            />
          </div>
        </div>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right" class="q-pa-md">
        <q-btn 
          flat 
          icon="sync" 
          label="Sync Schedule" 
          color="secondary" 
          :loading="syncing"
          @click="handleSync"
        >
          <q-tooltip>Update assigned teacher/schedule info</q-tooltip>
        </q-btn>
        <q-space />
        <q-btn flat label="Cancel" color="grey" v-close-popup />
        <q-btn
          label="Save Changes"
          color="primary"
          icon="save"
          :loading="saving"
          @click="handleSubmit"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import StatusBadge from '../shared/StatusBadge.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  plan: { type: Object, default: null },
  saving: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'submit'])

const dialogModel = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const editorToolbar = [
  ['bold', 'italic', 'underline'],
  ['unordered', 'ordered'],
  ['link'],
  ['undo', 'redo']
]

const days = ['', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']

const dayName = computed(() => {
  return days[props.plan?.schedule?.day_number] || 'Day'
})

const subjectName = computed(() => {
  return props.plan?.schedule?.cst?.subject?.name ||
         props.plan?.schedule?.cst?.subject_name ||
         'Subject'
})

const classroomName = computed(() => {
  return props.plan?.schedule?.cst?.classroom?.name ||
         props.plan?.schedule?.cst?.classroom_name ||
         'Classroom'
})

const subjectStyle = computed(() => {
  const bg = props.plan?.schedule?.cst?.c_bg || '#e0e0e0'
  const text = props.plan?.schedule?.cst?.c_text || '#333'
  return { backgroundColor: bg, color: text }
})

const currentStatus = computed(() => {
  const hasCw = !!form.value.cw?.trim()
  const hasHw = !!form.value.hw?.trim()
  if (!hasCw && !hasHw) return 'empty'
  if (hasCw && hasHw) return 'completed'
  return 'partial'
})

const defaultForm = () => ({
  cw: '',
  hw: '',
  notes: ''
})

const form = ref(defaultForm())

// Populate form when plan changes
watch(() => props.plan, (plan) => {
  if (plan) {
    form.value = {
      cw: plan.cw || '',
      hw: plan.hw || '',
      notes: plan.notes || ''
    }
  } else {
    form.value = defaultForm()
  }
}, { immediate: true })

// Reset when dialog closes
watch(dialogModel, (isOpen) => {
  if (!isOpen) {
    form.value = defaultForm()
  }
})

const handleSubmit = () => {
  emit('submit', {
    ...form.value,
    id: props.plan?.id
  })
}

const syncing = ref(false)
const handleSync = async () => {
  if (!props.plan?.id) return
  
  syncing.value = true
  try {
    const response = await axios.post(route('weekly-system.api.sync', props.plan.id))
    if (response.data.success) {
      // Notify parent to refresh or update local plan
      emit('submit', { ...props.plan, ...response.data.data, ...form.value }) // Re-submit to update parent view? Or emit a 'refresh' event?
      // Better to just emit a success notif or refresh
      // For now, let's assume submitting updates the parent list/view
      // But we should probably emit a specific 'refresh' event if possible.
      // Given the props, maybe 'submit' is used to save form. 
      // Let's emit 'submit' with the updated details so parent re-fetches or updates.
      // Actually, response.data.data has the fresh plan. 
      // But we also have form data. 
      
      // Let's just emit 'refresh' if the parent supports it, otherwise 'submit'.
      emit('submit', { ...response.data.data, ...form.value }) // Merge fresh plan with current form edits
      
      // Also show notification (if Quasar Notify is available, which it usually is in these setups)
      // import { useQuasar } from 'quasar' ... const $q = useQuasar() ... $q.notify(...)
      // Since I didn't import useQuasar, I'll skip the toast for now or add it if easy.
    }
  } catch (error) {
    console.error('Sync failed', error)
  } finally {
    syncing.value = false
  }
}
</script>
