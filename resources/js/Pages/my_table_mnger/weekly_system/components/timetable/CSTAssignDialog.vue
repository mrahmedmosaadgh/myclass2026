<template>
  <q-dialog v-model="dialogModel" persistent>
    <q-card style="min-width: 450px; max-width: 550px;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">
          <q-icon name="assignment" class="q-mr-sm" color="primary" />
          Assign Subject & Teacher
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <!-- Current Slot Info -->
      <q-card-section class="q-pt-sm">
        <div class="row q-gutter-sm text-caption text-grey-7">
          <q-chip dense icon="calendar_today" size="sm">
            {{ dayName }} - Period {{ period }}
          </q-chip>
          <q-chip v-if="classroomName" dense icon="meeting_room" size="sm">
            {{ classroomName }}
          </q-chip>
        </div>
      </q-card-section>

      <q-separator />

      <!-- Busy Teachers Info Banner -->
      <q-card-section v-if="loadingAvailability" class="q-py-sm">
        <q-skeleton type="text" width="60%" />
      </q-card-section>
      
      <q-card-section v-else-if="busyTeachersCount > 0" class="q-py-sm">
        <q-banner dense rounded class="bg-orange-1 text-orange-9">
          <template v-slot:avatar>
            <q-icon name="warning" color="orange" />
          </template>
          <span class="text-weight-medium">{{ busyTeachersCount }} teacher(s)</span> already assigned at this time slot in other classrooms.
          <div class="text-caption q-mt-xs">
            They are marked with ⚠️ in the dropdown below.
          </div>
        </q-banner>
      </q-card-section>

      <q-card-section>
        <q-form class="q-gutter-md">
          <!-- CST Selector (Main) -->
          <q-select
            v-model="form.cst_id"
            :options="enrichedCstOptions"
            option-value="id"
            option-label="display_label"
            label="Subject & Teacher *"
            outlined
            dense
            emit-value
            map-options
            clearable
            use-input
            input-debounce="300"
            @filter="filterCST"
            :loading="loadingCST || loadingAvailability"
          >
            <template v-slot:prepend>
              <q-icon name="menu_book" color="primary" />
            </template>
            <template v-slot:option="{ opt, itemProps }">
              <q-item 
                v-bind="itemProps" 
                :class="{ 'bg-orange-1': opt.is_busy }"
              >
                <q-item-section avatar>
                  <q-avatar
                    :style="{ backgroundColor: opt.c_bg || '#e0e0e0', color: opt.c_text || '#333' }"
                    size="32px"
                  >
                    {{ opt.subject_name?.charAt(0) || 'S' }}
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label :class="{ 'text-orange-9': opt.is_busy }">
                    {{ opt.subject_name }}
                    <q-icon v-if="opt.is_busy" name="warning" color="orange" size="xs" class="q-ml-xs" />
                  </q-item-label>
                  <q-item-label caption :class="{ 'text-orange-7': opt.is_busy }">
                    {{ opt.teacher_name }}
                  </q-item-label>
                  <q-item-label v-if="opt.is_busy" caption class="text-orange-8">
                    <q-icon name="info" size="xs" class="q-mr-xs" />
                    Busy in {{ opt.busy_info?.assigned_to_classroom }} ({{ opt.busy_info?.subject }})
                  </q-item-label>
                </q-item-section>
              </q-item>
            </template>
            <template v-slot:no-option>
              <q-item>
                <q-item-section class="text-grey">No options available</q-item-section>
              </q-item>
            </template>
          </q-select>

          <!-- Substitute Teacher (Optional) -->
          <q-select
            v-model="form.teacher_substitute_id"
            :options="teachers"
            option-value="id"
            option-label="name"
            label="Substitute Teacher (Optional)"
            outlined
            dense
            emit-value
            map-options
            clearable
            :loading="loadingTeachers"
          >
            <template v-slot:prepend>
              <q-icon name="swap_horiz" color="orange" />
            </template>
          </q-select>

          <!-- Co-Teacher (Optional) -->
          <q-select
            v-model="form.co_teacher_id"
            :options="teachers"
            option-value="id"
            option-label="name"
            label="Co-Teacher (Optional)"
            outlined
            dense
            emit-value
            map-options
            clearable
            :loading="loadingTeachers"
          >
            <template v-slot:prepend>
              <q-icon name="group" color="blue" />
            </template>
          </q-select>

          <!-- Co-Subject (Optional) -->
          <q-select
            v-model="form.co_subject_id"
            :options="subjects"
            option-value="id"
            option-label="name"
            label="Co-Subject (Optional)"
            outlined
            dense
            emit-value
            map-options
            clearable
            :loading="loadingSubjects"
          >
            <template v-slot:prepend>
              <q-icon name="library_books" color="teal" />
            </template>
          </q-select>

          <!-- Place/Location (Optional) -->
          <q-input
            v-model="form.place"
            label="Location/Room (Optional)"
            outlined
            dense
          >
            <template v-slot:prepend>
              <q-icon name="place" />
            </template>
          </q-input>

          <!-- Notes -->
          <q-input
            v-model="form.notes"
            label="Notes"
            outlined
            dense
            type="textarea"
            rows="2"
          >
            <template v-slot:prepend>
              <q-icon name="note" />
            </template>
          </q-input>
        </q-form>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right" class="q-pa-md">
        <q-btn flat label="Cancel" color="grey" v-close-popup />
        <q-btn
          label="Save Assignment"
          color="primary"
          :loading="saving"
          @click="handleSubmit"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  schedule: { type: Object, default: null },
  day: { type: Number, default: null },
  period: { type: Number, default: null },
  classroomName: { type: String, default: '' },
  cstOptions: { type: Array, default: () => [] },
  teachers: { type: Array, default: () => [] },
  subjects: { type: Array, default: () => [] },
  loadingCST: { type: Boolean, default: false },
  loadingTeachers: { type: Boolean, default: false },
  loadingSubjects: { type: Boolean, default: false },
  saving: { type: Boolean, default: false },
  slotAvailability: { type: Object, default: null },
  loadingAvailability: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'submit', 'filter-cst'])

const dialogModel = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const days = ['', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']
const dayName = computed(() => days[props.day] || 'Day')

// Enrich CST options with busy teacher information
const enrichedCstOptions = computed(() => {
  if (!props.slotAvailability) return props.cstOptions.map(cst => ({
    ...cst,
    is_busy: false,
    busy_info: null,
    display_label: `${cst.subject_name} - ${cst.teacher_name}`
  }))
  
  const busyTeachers = props.slotAvailability.busy_teachers || {}
  
  return props.cstOptions.map(cst => {
    const busyInfo = busyTeachers[cst.teacher_id]
    const isBusy = !!busyInfo && !busyInfo.is_current_classroom
    return {
      ...cst,
      is_busy: isBusy,
      busy_info: busyInfo,
      display_label: isBusy 
        ? `${cst.subject_name} - ${cst.teacher_name} ⚠️`
        : `${cst.subject_name} - ${cst.teacher_name}`
    }
  })
})

// Count of busy teachers
const busyTeachersCount = computed(() => {
  if (!props.slotAvailability) return 0
  const busyTeachers = props.slotAvailability.busy_teachers || {}
  // Count only teachers busy in OTHER classrooms
  return Object.values(busyTeachers).filter(t => !t.is_current_classroom).length
})

const defaultForm = () => ({
  cst_id: null,
  teacher_substitute_id: null,
  co_teacher_id: null,
  co_subject_id: null,
  place: '',
  notes: ''
})

const form = ref(defaultForm())

// Populate form when editing existing schedule
watch(() => props.schedule, (schedule) => {
  if (schedule) {
    form.value = {
      cst_id: schedule.cst_id,
      teacher_substitute_id: schedule.teacher_substitute_id,
      co_teacher_id: schedule.co_teacher_id,
      co_subject_id: schedule.co_subject_id,
      place: schedule.place || '',
      notes: schedule.notes || ''
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

const filterCST = (val, update, abort) => {
  emit('filter-cst', val)
  update()
}

const handleSubmit = () => {
  emit('submit', {
    ...form.value,
    schedule_id: props.schedule?.id,
    day: props.day,
    period: props.period
  })
}
</script>
