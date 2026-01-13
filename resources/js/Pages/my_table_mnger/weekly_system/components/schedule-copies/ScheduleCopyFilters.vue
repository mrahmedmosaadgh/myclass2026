<template>
  <q-card flat bordered class="q-pa-md">
    <div class="row q-gutter-md items-end">
      <!-- Status Filter -->
      <div class="col-12 col-sm-6 col-md-3">
        <q-select
          v-model="filters.status"
          :options="statusOptions"
          label="Status"
          outlined
          dense
          clearable
          emit-value
          map-options
          @update:model-value="emitFilters"
        >
          <template v-slot:prepend>
            <q-icon name="flag" color="primary" />
          </template>
        </q-select>
      </div>

      <!-- Clear Filters -->
      <div class="col-auto">
        <q-btn
          flat
          dense
          color="grey"
          icon="clear_all"
          label="Clear"
          @click="clearFilters"
        />
      </div>
    </div>
  </q-card>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useSchoolDataStore } from '@/Stores/schoolData'

const schoolDataStore = useSchoolDataStore()

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  schools: { type: Array, default: () => [] },
  academicYears: { type: Array, default: () => [] },
  semesters: { type: Array, default: () => [] },
  loadingSchools: { type: Boolean, default: false },
  loadingYears: { type: Boolean, default: false },
  loadingSemesters: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'filter'])

const statusOptions = [
  { label: 'Draft', value: 'draft' },
  { label: 'Pending', value: 'pending' },
  { label: 'Active', value: 'active' },
  { label: 'Archived', value: 'archived' }
]

const filters = ref({
  school_id: schoolDataStore.schoolId || null,
  academic_year_id: schoolDataStore.academicYearId || null,
  semester_id: schoolDataStore.semesterId || null,
  status: null,
  ...props.modelValue
})

// Auto-populate from store on mount
onMounted(() => {
  if (!filters.value.school_id && schoolDataStore.schoolId) {
    filters.value.school_id = schoolDataStore.schoolId
  }
  if (!filters.value.academic_year_id && schoolDataStore.academicYearId) {
    filters.value.academic_year_id = schoolDataStore.academicYearId
  }
  if (!filters.value.semester_id && schoolDataStore.semesterId) {
    filters.value.semester_id = schoolDataStore.semesterId
  }
  // Emit initial filters
  emitFilters()
})

watch(() => props.modelValue, (newVal) => {
  filters.value = { ...filters.value, ...newVal }
}, { deep: true })

const emitFilters = () => {
  emit('update:modelValue', { ...filters.value })
  emit('filter', { ...filters.value })
}

const clearFilters = () => {
  filters.value = {
    school_id: schoolDataStore.schoolId || null,
    academic_year_id: schoolDataStore.academicYearId || null,
    semester_id: schoolDataStore.semesterId || null,
    status: null
  }
  emitFilters()
}
</script>
