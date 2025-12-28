<template>
  <q-card flat bordered class="q-pa-md">
    <div class="row q-gutter-md items-end">
      <!-- School Filter -->
      <div class="col-12 col-sm-6 col-md-3">
        <SchoolSelect
          v-model="filters.school_id"
          :schools="schools"
          :loading="loadingSchools"
          @update:model-value="emitFilters"
        />
      </div>

      <!-- Academic Year Filter -->
      <div class="col-12 col-sm-6 col-md-3">
        <AcademicYearSelect
          v-model="filters.academic_year_id"
          :academic-years="academicYears"
          :loading="loadingYears"
          @update:model-value="emitFilters"
        />
      </div>

      <!-- Semester Filter -->
      <div class="col-12 col-sm-6 col-md-2">
        <SemesterSelect
          v-model="filters.semester_id"
          :semesters="semesters"
          :loading="loadingSemesters"
          @update:model-value="emitFilters"
        />
      </div>

      <!-- Status Filter -->
      <div class="col-12 col-sm-6 col-md-2">
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
import { ref, watch } from 'vue'
import SchoolSelect from '../shared/SchoolSelect.vue'
import AcademicYearSelect from '../shared/AcademicYearSelect.vue'
import SemesterSelect from '../shared/SemesterSelect.vue'

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
  school_id: null,
  academic_year_id: null,
  semester_id: null,
  status: null,
  ...props.modelValue
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
    school_id: null,
    academic_year_id: null,
    semester_id: null,
    status: null
  }
  emitFilters()
}
</script>
