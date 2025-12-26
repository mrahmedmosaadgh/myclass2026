<template>

    <div class="q-pa-md">
      <!-- Header -->
      <div class="row items-center q-mb-lg">
        <div class="col">
          <h4 class="text-h4 q-my-none">Curriculum Management</h4>
          <p class="text-grey-6 q-mb-none">Manage curricula for your schools and subjects</p>
        </div>
        <div class="col-auto q-gutter-sm">
          <q-btn
            color="primary"
            icon="add"
            label="Add Curriculum"
            @click="openAddDialog"
            unelevated
          />
          <q-btn
            color="primary"
            icon="filter_list"
            label="Filter"
            @click="showFilterDialog = true"
            unelevated
          />
          <q-btn
            color="primary"
            icon="import_export"
            label="Import/Export"
            @click="showExcelDialog = true"
            unelevated
          />
        </div>
      </div>

      <!-- Curricula Table -->
      <q-card>
        <q-card-section>
          <q-table
            :rows="curricula"
            :columns="columns"
            row-key="id"
            :loading="loadingCurricula"
            :pagination="pagination"
            @request="onRequest"
            binary-state-sort
          >
            <!-- Status Column -->
            <template v-slot:body-cell-active="props">
              <q-td :props="props">
                <q-chip
                  :color="props.value === 1 ? 'green' : 'grey'"
                  :text-color="props.value === 1 ? 'white' : 'black'"
                  size="sm"
                >
                  {{ props.value === 1 ? 'Active' : 'Inactive' }}
                </q-chip>
              </q-td>
            </template>

            <!-- Actions Column -->
            <template v-slot:body-cell-actions="props">
              <q-td :props="props">
                <q-btn-group flat>
                  <q-btn
                    flat
                    dense
                    color="info"
                    icon="visibility"
                    @click="viewCurriculumLessons(props.row)"
                    size="sm"
                  >
                    <q-tooltip>View Lessons</q-tooltip>
                  </q-btn>
                  <q-btn
                    flat
                    dense
                    color="primary"
                    icon="edit"
                    @click="editCurriculum(props.row)"
                    size="sm"
                  >
                    <q-tooltip>Edit</q-tooltip>
                  </q-btn>
                  <q-btn
                    flat
                    dense
                    :color="props.row.active === 1 ? 'orange' : 'green'"
                    :icon="props.row.active === 1 ? 'pause' : 'play_arrow'"
                    @click="toggleActivation(props.row)"
                    size="sm"
                  >
                    <q-tooltip>{{ props.row.active === 1 ? 'Deactivate' : 'Activate' }}</q-tooltip>
                  </q-btn>
                  <q-btn
                    flat
                    dense
                    color="negative"
                    icon="delete"
                    @click="deleteCurriculum(props.row)"
                    size="sm"
                  >
                    <q-tooltip>Delete</q-tooltip>
                  </q-btn>
                </q-btn-group>
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>

      <!-- Filter Dialog -->
      <q-dialog v-model="showFilterDialog">
        <q-card style="min-width: 500px">
          <q-card-section>
            <div class="text-h6">Filter Curricula</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            <div class="q-gutter-md">
              <!-- School Selection -->
              <q-select
                v-model="filters.school_id"
                :options="schoolOptions"
                option-value="id"
                option-label="name"
                label="Select School"
                outlined
                clearable
                @update:model-value="onSchoolChange"
                :loading="loadingSchools"
              />

              <!-- Subject Selection -->
              <q-select
                v-model="filters.subject_id"
                :options="subjectOptions"
                option-value="id"
                option-label="name"
                label="Select Subject"
                outlined
                clearable
                :disable="!filters.school_id"
                :loading="loadingSubjects"
              />

              <!-- Grade Selection -->
              <q-select
                v-model="filters.grade_id"
                :options="gradeOptions"
                option-value="id"
                option-label="name"
                label="Select Grade"
                outlined
                clearable
                :disable="!filters.school_id"
                :loading="loadingGrades"
              />

              <!-- Status Selection -->
              <q-select
                v-model="filters.active"
                :options="statusOptions"
                label="Status"
                outlined
                clearable
              />
            </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Clear" @click="clearFilters" />
            <q-btn flat label="Cancel" @click="showFilterDialog = false" />
            <q-btn
              color="primary"
              label="Apply"
              @click="applyFilters"
              :loading="loadingCurricula"
              unelevated
            />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- Add/Edit Dialog -->
      <q-dialog v-model="showAddDialog" persistent>
        <q-card style="min-width: 500px">
          <q-card-section>
            <div class="text-h6">{{ editingCurriculum ? 'Edit Curriculum' : 'Add New Curriculum' }}</div>
          </q-card-section>

          <q-card-section class="q-pt-none">
            <div class="q-gutter-md">
              <!-- School Selection -->
              <q-select
                v-model="form.school_id"
                :options="schoolOptions"
                option-value="id"
                option-label="name"
                label="School *"
                outlined
                :rules="[val => !!val || 'School is required']"
                @update:model-value="onDialogSchoolChange"
                :loading="loadingSchools"
                :disable="editingCurriculum"
              />

              <!-- Subject Selection -->
              <q-select
                v-model="form.subject_id"
                :options="dialogSubjectOptions"
                option-value="id"
                option-label="name"
                label="Subject *"
                outlined
                :rules="[val => !!val || 'Subject is required']"
                :disable="!form.school_id || editingCurriculum"
                :loading="loadingDialogSubjects"
              />

              <!-- Grade Selection -->
              <q-select
                v-model="form.grade_id"
                :options="dialogGradeOptions"
                option-value="id"
                option-label="name"
                label="Grade *"
                outlined
                :rules="[val => !!val || 'Grade is required']"
                :disable="!form.school_id || editingCurriculum"
                :loading="loadingDialogGrades"
              />

              <!-- Curriculum Name -->
              <q-input
                v-model="form.name"
                label="Curriculum Name *"
                outlined
                :rules="[val => !!val || 'Name is required']"
              />

              <!-- Description -->
              <q-input
                v-model="form.description"
                label="Description"
                type="textarea"
                rows="3"
                outlined
              />

              <!-- Active Status -->
              <q-checkbox
                v-model="form.active"
                label="Set as Active (will deactivate other curricula for this subject and grade)"
                color="primary"
              />
            </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancel" @click="closeDialog" />
            <q-btn
              color="primary"
              label="Save"
              @click="saveCurriculum"
              :loading="saving"
              unelevated
            />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- Lessons Management Dialog -->
      <q-dialog v-model="showLessonsDialog" full-width full-height>
        <CurriculumLessonsManager
          v-if="selectedCurriculumForLessons"
          :curriculum="selectedCurriculumForLessons"
          @close="showLessonsDialog = false"
          @updated="loadCurricula"
        />
      </q-dialog>

      <!-- Excel Import/Export Dialog -->
      <q-dialog v-model="showExcelDialog" full-width>
        <q-card>
          <q-card-section class="row items-center q-pb-none">
            <div class="text-h6">Import/Export Curricula</div>
            <q-space />
            <q-btn icon="close" flat round dense @click="showExcelDialog = false" />
          </q-card-section>

          <q-separator />

          <q-card-section>
            <ExcelManager
              :export-data="curriculaForExport"
              export-file-name="curricula_template.xlsx"
              @imported-json="handleImportedCurricula"
              @exported="handleExported"
            />
          </q-card-section>
        </q-card>
      </q-dialog>
    </div>
 
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import CurriculumLessonsManager from './CurriculumLessonsManager.vue'
import ExcelManager from '@/Components/import_excel_sys/ExcelManager.vue'
import axios from 'axios'

const $q = useQuasar()

// Reactive data
const curricula = ref([])
const schoolOptions = ref([])
const subjectOptions = ref([])
const gradeOptions = ref([])
const dialogSubjectOptions = ref([])
const dialogGradeOptions = ref([])

// Loading states
const loadingSchools = ref(false)
const loadingSubjects = ref(false)
const loadingGrades = ref(false)
const loadingDialogSubjects = ref(false)
const loadingDialogGrades = ref(false)
const loadingCurricula = ref(false)
const saving = ref(false)

// Dialog states
const showAddDialog = ref(false)
const showFilterDialog = ref(false)
const showLessonsDialog = ref(false)
const showExcelDialog = ref(false)
const editingCurriculum = ref(null)
const selectedCurriculumForLessons = ref(null)

// Filters
const filters = reactive({
  school_id: null,
  subject_id: null,
  grade_id: null,
  active: null
})

// Form data
const form = reactive({
  school_id: null,
  subject_id: null,
  grade_id: null,
  name: '',
  description: '',
  active: false
})

// Table configuration
const columns = [
  {
    name: 'name',
    required: true,
    label: 'Curriculum Name',
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'school',
    label: 'School',
    align: 'left',
    field: row => row.school?.name || 'N/A',
    sortable: false
  },
  {
    name: 'subject',
    label: 'Subject',
    align: 'left',
    field: row => row.subject?.name || 'N/A',
    sortable: false
  },
  {
    name: 'grade',
    label: 'Grade',
    align: 'left',
    field: row => row.grade?.name || 'N/A',
    sortable: false
  },
  {
    name: 'active',
    label: 'Status',
    align: 'center',
    field: 'active',
    sortable: true
  },
  {
    name: 'created_at',
    label: 'Created',
    align: 'left',
    field: 'created_at',
    format: val => new Date(val).toLocaleDateString(),
    sortable: true
  },
  {
    name: 'actions',
    label: 'Actions',
    align: 'center',
    field: 'actions',
    sortable: false
  }
]

const pagination = ref({
  sortBy: 'created_at',
  descending: true,
  page: 1,
  rowsPerPage: 15,
  rowsNumber: 0
})

const statusOptions = [
  { label: 'Active', value: 1 },
  { label: 'Inactive', value: 0 }
]

// Computed
const curriculaForExport = computed(() => {
  return curricula.value.map(c => ({
    name: c.name,
    school: c.school?.name || '',
    subject: c.subject?.name || '',
    grade: c.grade?.name || '',
    description: c.description || '',
    active: c.active === 1 ? 'Yes' : 'No'
  }))
})

// Methods
const loadSchools = async () => {
  loadingSchools.value = true
  try {
    const response = await axios.get('/api/curriculum/user-schools')
    schoolOptions.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load schools'
    })
  } finally {
    loadingSchools.value = false
  }
}

const loadSubjects = async (schoolId) => {
  if (!schoolId) {
    subjectOptions.value = []
    return
  }

  loadingSubjects.value = true
  try {
    const response = await axios.get(`/api/curriculum/school/${schoolId}/subjects`)
    subjectOptions.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load subjects'
    })
  } finally {
    loadingSubjects.value = false
  }
}

const loadGrades = async (schoolId) => {
  if (!schoolId) {
    gradeOptions.value = []
    return
  }

  loadingGrades.value = true
  try {
    const response = await axios.get(`/api/curriculum/school/${schoolId}/grades`)
    gradeOptions.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load grades'
    })
  } finally {
    loadingGrades.value = false
  }
}

const loadDialogSubjects = async (schoolId) => {
  if (!schoolId) {
    dialogSubjectOptions.value = []
    return
  }

  loadingDialogSubjects.value = true
  try {
    const response = await axios.get(`/api/curriculum/school/${schoolId}/subjects`)
    dialogSubjectOptions.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load subjects'
    })
  } finally {
    loadingDialogSubjects.value = false
  }
}

const loadDialogGrades = async (schoolId) => {
  if (!schoolId) {
    dialogGradeOptions.value = []
    return
  }

  loadingDialogGrades.value = true
  try {
    const response = await axios.get(`/api/curriculum/school/${schoolId}/grades`)
    dialogGradeOptions.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load grades'
    })
  } finally {
    loadingDialogGrades.value = false
  }
}

const loadCurricula = async () => {
  loadingCurricula.value = true
  try {
    const params = new URLSearchParams()

    const schoolId = filters.school_id?.id || filters.school_id
    const subjectId = filters.subject_id?.id || filters.subject_id
    const gradeId = filters.grade_id?.id || filters.grade_id

    if (schoolId) params.append('school_id', schoolId)
    if (subjectId) params.append('subject_id', subjectId)
    if (gradeId) params.append('grade_id', gradeId)
    if (filters.active !== null) params.append('active', filters.active)

    const response = await axios.get(`/api/curriculum/curricula?${params}`)
    curricula.value = response.data.data
    pagination.value.rowsNumber = response.data.total
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load curricula'
    })
  } finally {
    loadingCurricula.value = false
  }
}

const onSchoolChange = (school) => {
  const schoolId = school?.id || school
  filters.school_id = schoolId
  filters.subject_id = null
  filters.grade_id = null
  if (schoolId) {
    loadSubjects(schoolId)
    loadGrades(schoolId)
  } else {
    subjectOptions.value = []
    gradeOptions.value = []
  }
}

const onDialogSchoolChange = (school) => {
  const schoolId = school?.id || school
  form.school_id = schoolId
  form.subject_id = null
  form.grade_id = null
  if (schoolId) {
    loadDialogSubjects(schoolId)
    loadDialogGrades(schoolId)
  } else {
    dialogSubjectOptions.value = []
    dialogGradeOptions.value = []
  }
}

const onRequest = (props) => {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  pagination.value.page = page
  pagination.value.rowsPerPage = rowsPerPage
  pagination.value.sortBy = sortBy
  pagination.value.descending = descending
  loadCurricula()
}

const applyFilters = () => {
  showFilterDialog.value = false
  loadCurricula()
}

const clearFilters = () => {
  filters.school_id = null
  filters.subject_id = null
  filters.grade_id = null
  filters.active = null
  subjectOptions.value = []
  gradeOptions.value = []
}

const viewCurriculumLessons = (curriculum) => {
  selectedCurriculumForLessons.value = curriculum
  showLessonsDialog.value = true
}

const handleImportedCurricula = async (data) => {
  try {
    // Validate imported data
    const validData = data.filter(item => item.name && item.school && item.subject && item.grade)
    
    if (validData.length === 0) {
      $q.notify({
        type: 'negative',
        message: 'No valid curricula found in imported data'
      })
      return
    }

    // Send to backend for bulk import
    const response = await axios.post('/api/curriculum/bulk-import', { curricula: validData })
    
    $q.notify({
      type: 'positive',
      message: `Successfully imported ${response.data.imported} curricula`
    })

    showExcelDialog.value = false
    loadCurricula()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to import curricula'
    })
  }
}

const handleExported = (info) => {
  $q.notify({
    type: 'positive',
    message: `Exported ${info.recordCount} curricula to ${info.fileName}`
  })
}

const openAddDialog = () => {
  resetForm()
  showAddDialog.value = true
}

const resetForm = () => {
  form.school_id = null
  form.subject_id = null
  form.grade_id = null
  form.name = ''
  form.description = ''
  form.active = false
  dialogSubjectOptions.value = []
  dialogGradeOptions.value = []
  editingCurriculum.value = null
}

const closeDialog = () => {
  showAddDialog.value = false
  resetForm()
}

const editCurriculum = (curriculum) => {
  editingCurriculum.value = curriculum
  
  // Set form values with full objects for q-select to display names
  form.school_id = curriculum.school || { id: curriculum.school_id, name: 'Unknown' }
  form.subject_id = curriculum.subject || { id: curriculum.subject_id, name: 'Unknown' }
  form.grade_id = curriculum.grade || { id: curriculum.grade_id, name: 'Unknown' }
  form.name = curriculum.name
  form.description = curriculum.description || ''
  form.active = curriculum.active === 1

  // Set dialog options with the current values
  dialogSubjectOptions.value = [curriculum.subject || { id: curriculum.subject_id, name: 'Unknown' }]
  dialogGradeOptions.value = [curriculum.grade || { id: curriculum.grade_id, name: 'Unknown' }]

  // Load subjects and grades for the dialog
  loadDialogSubjects(curriculum.school_id)
  loadDialogGrades(curriculum.school_id)

  showAddDialog.value = true
}

const saveCurriculum = async () => {
  // Validate required fields
  if (!form.school_id) {
    $q.notify({
      type: 'negative',
      message: 'Please select a school'
    })
    return
  }

  if (!form.subject_id) {
    $q.notify({
      type: 'negative',
      message: 'Please select a subject'
    })
    return
  }

  if (!form.grade_id) {
    $q.notify({
      type: 'negative',
      message: 'Please select a grade'
    })
    return
  }

  if (!form.name || form.name.trim() === '') {
    $q.notify({
      type: 'negative',
      message: 'Please enter a curriculum name'
    })
    return
  }

  saving.value = true
  try {
    const data = {
      name: form.name.trim(),
      description: form.description.trim(),
      school_id: form.school_id?.id || form.school_id,
      subject_id: form.subject_id?.id || form.subject_id,
      grade_id: form.grade_id?.id || form.grade_id,
      active: form.active ? 1 : 0
    }

    if (editingCurriculum.value) {
      // Update existing curriculum
      await axios.put(`/api/curriculum/curricula/${editingCurriculum.value.id}`, data)
      $q.notify({
        type: 'positive',
        message: 'Curriculum updated successfully'
      })
    } else {
      // Create new curriculum
      await axios.post('/api/curriculum/curricula', data)
      $q.notify({
        type: 'positive',
        message: 'Curriculum created successfully'
      })
    }

    closeDialog()
    loadCurricula()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to save curriculum'
    })
  } finally {
    saving.value = false
  }
}

const toggleActivation = async (curriculum) => {
  const action = curriculum.active === 1 ? 'deactivate' : 'activate'
  const actionText = curriculum.active === 1 ? 'deactivate' : 'activate'

  $q.dialog({
    title: 'Confirm Action',
    message: `Are you sure you want to ${actionText} this curriculum?${
      action === 'activate'
        ? ' This will deactivate other curricula for the same subject and grade.'
        : ''
    }`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await axios.post(`/api/curriculum/curricula/${curriculum.id}/${action}`)
      $q.notify({
        type: 'positive',
        message: `Curriculum ${actionText}d successfully`
      })
      loadCurricula()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: error.response?.data?.message || `Failed to ${actionText} curriculum`
      })
    }
  })
}

const deleteCurriculum = async (curriculum) => {
  $q.dialog({
    title: 'Confirm Deletion',
    message: `Are you sure you want to delete "${curriculum.name}"? This action cannot be undone.`,
    cancel: true,
    persistent: true,
    color: 'negative'
  }).onOk(async () => {
    try {
      await axios.delete(`/api/curriculum/curricula/${curriculum.id}`)
      $q.notify({
        type: 'positive',
        message: 'Curriculum deleted successfully'
      })
      loadCurricula()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: error.response?.data?.message || 'Failed to delete curriculum'
      })
    }
  })
}

// Initialize component
onMounted(() => {
  loadSchools()
  loadCurricula()
})
</script>

<style scoped>
.q-table th {
  font-weight: bold;
}

.q-chip {
  font-weight: 500;
}
</style>
