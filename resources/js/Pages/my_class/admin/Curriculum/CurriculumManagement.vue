<template>
  <AppLayoutDefault>
    <div class="q-pa-md">
      <!-- Header -->
      <div class="row items-center q-mb-lg">
        <div class="col">
          <h4 class="text-h4 q-my-none">Curriculum Management</h4>
          <p class="text-grey-6 q-mb-none">Manage curricula for your schools and subjects</p>
        </div>
        <div class="col-auto">
          <q-btn
            color="primary"
            icon="add"
            label="Add Curriculum"
            @click="showAddDialog = true"
            unelevated
          />
        </div>
      </div>

      <!-- Filters -->
      <q-card class="q-mb-lg">
        <q-card-section>
          <div class="row q-gutter-md">
            <div class="col-12 col-md-3">
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
            </div>
            <div class="col-12 col-md-3">
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
            </div>
            <div class="col-12 col-md-3">
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
            </div>
            <div class="col-12 col-md-2">
              <q-select
                v-model="filters.active"
                :options="statusOptions"
                label="Status"
                outlined
                clearable
              />
            </div>
            <div class="col-12 col-md-1">
              <q-btn
                color="primary"
                icon="search"
                label="Search"
                @click="loadCurricula"
                :loading="loadingCurricula"
                unelevated
              />
            </div>
          </div>
        </q-card-section>
      </q-card>

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
    </div>
  </AppLayoutDefault>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue'
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
const editingCurriculum = ref(null)

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

    if (filters.school_id) params.append('school_id', filters.school_id)
    if (filters.subject_id) params.append('subject_id', filters.subject_id)
    if (filters.grade_id) params.append('grade_id', filters.grade_id)
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

const onSchoolChange = (schoolId) => {
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

const onDialogSchoolChange = (schoolId) => {
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

const resetForm = () => {
  form.school_id = null
  form.subject_id = null
  form.grade_id = null
  form.name = ''
  form.description = ''
  form.active = false
  dialogSubjectOptions.value = []
  dialogGradeOptions.value = []
}

const closeDialog = () => {
  showAddDialog.value = false
  editingCurriculum.value = null
  resetForm()
}

const editCurriculum = (curriculum) => {
  editingCurriculum.value = curriculum
  form.school_id = curriculum.school_id
  form.subject_id = curriculum.subject_id
  form.grade_id = curriculum.grade_id
  form.name = curriculum.name
  form.description = curriculum.description || ''
  form.active = curriculum.active === 1

  // Load subjects and grades for the dialog
  loadDialogSubjects(curriculum.school_id)
  loadDialogGrades(curriculum.school_id)

  showAddDialog.value = true
}

const saveCurriculum = async () => {
  // Validate required fields
  if (!form.school_id || !form.subject_id || !form.grade_id || !form.name) {
    $q.notify({
      type: 'negative',
      message: 'Please fill in all required fields'
    })
    return
  }

  saving.value = true
  try {
    const data = {
      name: form.name,
      description: form.description,
      school_id: form.school_id,
      subject_id: form.subject_id,
      grade_id: form.grade_id,
      active: form.active
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
