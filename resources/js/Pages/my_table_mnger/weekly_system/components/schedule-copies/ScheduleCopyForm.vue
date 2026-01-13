<template>
  <q-dialog v-model="dialogModel" persistent>
    <q-card style="min-width: 500px; max-width: 600px;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">
          <q-icon :name="isEdit ? 'edit' : 'add_circle'" class="q-mr-sm" />
          {{ isEdit ? 'Edit Schedule Copy' : 'Create Schedule Copy' }}
        </div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator class="q-my-md" />

      <q-card-section class="q-pt-none">
        <q-form @submit.prevent="handleSubmit" class="q-gutter-md">
          <!-- Name -->
          <q-input
            v-model="form.name"
            label="Copy Name *"
            outlined
            dense
            :rules="[val => !!val || 'Name is required']"
          >
            <template v-slot:prepend>
              <q-icon name="label" />
            </template>
          </q-input>

          <!-- Description -->
          <q-input
            v-model="form.description"
            label="Description"
            outlined
            dense
            type="textarea"
            rows="2"
          >
            <template v-slot:prepend>
              <q-icon name="description" />
            </template>
          </q-input>

          <!-- Week Number -->
          <q-input
            v-model.number="form.week_number"
            label="Starting Week Number"
            outlined
            dense
            type="number"
            min="1"
            max="52"
          >
            <template v-slot:prepend>
              <q-icon name="view_week" />
            </template>
          </q-input>

          <!-- Copy Date -->
          <q-input
            v-model="form.copy_date"
            label="Copy Date"
            outlined
            dense
            type="date"
          >
            <template v-slot:prepend>
              <q-icon name="event" />
            </template>
          </q-input>

          <!-- Status (only for edit) -->
          <q-select
            v-if="isEdit"
            v-model="form.status"
            :options="statusOptions"
            label="Status"
            outlined
            dense
            emit-value
            map-options
          >
            <template v-slot:prepend>
              <q-icon name="flag" />
            </template>
          </q-select>

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

          <!-- Auto-generate schedules option (only for create) -->
          <q-checkbox
            v-if="!isEdit"
            v-model="form.auto_generate"
            label="Auto-generate schedule entries from subject-teacher assignments"
            color="primary"
          >
            <q-tooltip>
              Creates schedule records based on classes_per_week for each subject-teacher assignment
            </q-tooltip>
          </q-checkbox>
        </q-form>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right" class="q-pa-md">
        <q-btn flat label="Cancel" color="grey" v-close-popup />
        <q-btn
          :label="isEdit ? 'Update' : 'Create'"
          color="primary"
          :loading="saving"
          @click="handleSubmit"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useSchoolDataStore } from '@/Stores/schoolData'

const schoolDataStore = useSchoolDataStore()

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  editData: { type: Object, default: null },
  schools: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  loadingSchools: { type: Boolean, default: false },
  loadingYears: { type: Boolean, default: false },
  loadingSemesters: { type: Boolean, default: false },
  saving: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'submit'])

const dialogModel = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const isEdit = computed(() => !!props.editData?.id)

const statusOptions = [
  { label: 'Draft', value: 'draft' },
  { label: 'Pending', value: 'pending' },
  { label: 'Active', value: 'active' },
  { label: 'Archived', value: 'archived' }
]

const defaultForm = () => ({
  name: '',
  description: '',
  school_id: schoolDataStore.schoolId || null,
  academic_year_id: schoolDataStore.academicYearId || null,
  semester_id: schoolDataStore.semesterId || null,
  week_number: null,
  copy_date: null,
  status: 'draft',
  notes: '',
  auto_generate: true
})

const form = ref(defaultForm())

// Watch for edit data changes
watch(() => props.editData, (newData) => {
  if (newData) {
    form.value = {
      ...defaultForm(),
      ...newData,
      copy_date: newData.copy_date ? newData.copy_date.split('T')[0] : null
    }
  } else {
    // Auto-populate from store when creating new
    form.value = defaultForm()
  }
}, { immediate: true })

// Reset form when dialog closes
watch(dialogModel, (isOpen) => {
  if (!isOpen) {
    form.value = defaultForm()
  } else if (!isEdit.value) {
    // Auto-populate from store when dialog opens for create
    form.value.school_id = schoolDataStore.schoolId || form.value.school_id
    form.value.academic_year_id = schoolDataStore.academicYearId || form.value.academic_year_id
    form.value.semester_id = schoolDataStore.semesterId || form.value.semester_id
  }
})

const handleSubmit = () => {
  if (!form.value.name) return
  emit('submit', { ...form.value, isEdit: isEdit.value })
}
</script>
