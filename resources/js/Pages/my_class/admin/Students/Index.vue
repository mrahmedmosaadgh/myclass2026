<template>
  <div class="q-pa-md">
    <!-- Header Section -->
    <div class="row items-center q-mb-md">
      <div class="col">
        <div class="text-h4 text-weight-bold">
          <q-icon name="school" class="q-mr-sm" />
          Student Management
        </div>
        <div class="text-subtitle2 text-grey-7">
          Manage students, classrooms, and promotions
        </div>
      </div>
      <div class="col-auto">
        <q-btn-group push>
          <q-btn
            color="primary"
            icon="add"
            label="Add Student"
            @click="openStudentDialog()"
            :disable="!canAddStudent"
            unelevated
          >
            <q-tooltip v-if="!canAddStudent">
              Please select School, Grade, and Classroom first
            </q-tooltip>
          </q-btn>
          <q-btn
            color="secondary"
            icon="upgrade"
            label="Promote Students"
            @click="showPromotionDialog = true"
            unelevated
          />
          <q-btn
            color="info"
            icon="upload_file"
            label="Import"
            @click="triggerImport"
            :disable="!canAddStudent"
            unelevated
          >
            <q-tooltip v-if="!canAddStudent">
              Please select School, Grade, and Classroom first
            </q-tooltip>
          </q-btn>
          <q-btn
            color="purple"
            icon="cloud_upload"
            label="School-Wide Import"
            @click="triggerSchoolWideImport"
            :disable="!filters.school_id"
            unelevated
          >
            <q-tooltip v-if="!filters.school_id">
              Please select a School first
            </q-tooltip>
          </q-btn>
          <q-btn
            color="positive"
            icon="download"
            label="Export"
            @click="handleExport"
            unelevated
          />
        </q-btn-group>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-primary text-white">
            <div class="text-h6">{{ totalStudents }}</div>
            <div class="text-caption">Total Students</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-secondary text-white">
            <div class="text-h6">{{ selectedSchoolStudents }}</div>
            <div class="text-caption">In Selected School</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-positive text-white">
            <div class="text-h6">{{ filteredCount }}</div>
            <div class="text-caption">Filtered Results</div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-12 col-md-3">
        <q-card flat bordered>
          <q-card-section class="bg-info text-white">
            <div class="text-h6">{{ selectedCount }}</div>
            <div class="text-caption">Selected</div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Filters Section -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="row items-center q-mb-sm">
          <div class="col">
            <div class="text-h6">
              <q-icon name="filter_list" class="q-mr-sm" />
              Filters
            </div>
          </div>
          <div class="col-auto">
            <q-btn
              flat
              dense
              icon="clear"
              label="Clear All"
              @click="clearFilters"
              color="negative"
            />
          </div>
        </div>

        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.school_id"
              :options="schools"
              option-value="id"
              option-label="name"
              label="School"
              outlined
              dense
              clearable
              emit-value
              map-options
              @update:model-value="onSchoolChange"
            >
              <template v-slot:prepend>
                <q-icon name="business" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.stage_id"
              :options="stages"
              option-value="id"
              option-label="name"
              label="Stage"
              outlined
              dense
              clearable
              emit-value
              map-options
              :disable="!filters.school_id"
              @update:model-value="onStageChange"
            >
              <template v-slot:prepend>
                <q-icon name="layers" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.grade_id"
              :options="grades"
              option-value="id"
              option-label="name"
              label="Grade"
              outlined
              dense
              clearable
              emit-value
              map-options
              :disable="!filters.stage_id"
              @update:model-value="onGradeChange"
            >
              <template v-slot:prepend>
                <q-icon name="school" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-3">
            <q-select
              v-model="filters.classroom_id"
              :options="classrooms"
              option-value="id"
              option-label="name"
              label="Classroom"
              outlined
              dense
              clearable
              emit-value
              map-options
              :disable="!filters.grade_id"
              @update:model-value="applyFilters"
            >
              <template v-slot:prepend>
                <q-icon name="meeting_room" />
              </template>
            </q-select>
          </div>

          <div class="col-12 col-md-6">
            <q-input
              v-model="filters.search"
              label="Search by name or ID"
              outlined
              dense
              clearable
              @update:model-value="debouncedSearch"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Data Table -->
    <q-card flat bordered>
      <q-table
        :rows="students"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        selection="multiple"
        v-model:selected="selected"
        flat
        :rows-per-page-options="[10, 25, 50, 100]"
      >
        <template v-slot:top>
          <div class="col-12">
            <div class="text-h6">Students List</div>
            <div v-if="selected.length > 0" class="text-caption text-grey-7">
              {{ selected.length }} student(s) selected
            </div>
          </div>
        </template>

        <template v-slot:body-cell-avatar="props">
          <q-td :props="props">
            <q-avatar color="primary" text-color="white" size="md">
              {{ getInitials(props.row.name) }}
            </q-avatar>
          </q-td>
        </template>

        <template v-slot:body-cell-name="props">
          <q-td :props="props">
            <div class="text-weight-bold">{{ props.row.name }}</div>
            <div class="text-caption text-grey-7">{{ props.row.name_ar }}</div>
          </q-td>
        </template>

        <template v-slot:body-cell-s_id="props">
          <q-td :props="props">
            <q-badge color="grey-7" outline>
              {{ props.row.s_id }}
            </q-badge>
          </q-td>
        </template>

        <template v-slot:body-cell-school="props">
          <q-td :props="props">
            <div>{{ props.row.school?.name }}</div>
          </q-td>
        </template>

        <template v-slot:body-cell-classroom="props">
          <q-td :props="props">
            <q-badge color="secondary">
              {{ props.row.classroom?.name }}
            </q-badge>
          </q-td>
        </template>

        <template v-slot:body-cell-grade="props">
          <q-td :props="props">
            <div>{{ props.row.grade?.name }}</div>
          </q-td>
        </template>

        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <q-btn-group flat>
              <q-btn
                flat
                dense
                round
                icon="edit"
                color="primary"
                @click="openStudentDialog(props.row)"
              >
                <q-tooltip>Edit</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                round
                icon="history"
                color="info"
                @click="viewHistory(props.row)"
              >
                <q-tooltip>View History</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                round
                icon="delete"
                color="negative"
                @click="deleteStudent(props.row)"
              >
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
            </q-btn-group>
          </q-td>
        </template>

        <template v-slot:no-data>
          <div class="full-width row flex-center q-gutter-sm q-pa-lg">
            <q-icon size="2em" name="sentiment_dissatisfied" />
            <span>No students found. Try adjusting your filters.</span>
          </div>
        </template>
      </q-table>
    </q-card>

    <!-- Bulk Actions Toolbar (appears when students selected) -->
    <q-page-sticky position="bottom" :offset="[0, 18]" v-if="selected.length > 0">
      <q-toolbar class="bg-primary text-white shadow-up-2">
        <q-toolbar-title>
          {{ selected.length }} student(s) selected
        </q-toolbar-title>
        <q-btn
          flat
          label="Change Classroom"
          icon="meeting_room"
          @click="bulkChangeClassroom"
        />
        <q-btn
          flat
          label="Export Selected"
          icon="download"
          @click="exportSelected"
        />
        <q-btn
          flat
          label="Delete Selected"
          icon="delete"
          @click="bulkDelete"
        />
        <q-btn
          flat
          round
          dense
          icon="close"
          @click="selected = []"
        />
      </q-toolbar>
    </q-page-sticky>

    <!-- Student Form Dialog -->
    <q-dialog v-model="showStudentDialog" persistent>
      <q-card style="min-width: 600px">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">
            {{ editingStudent ? 'Edit Student' : 'Add New Student' }}
          </div>
          <div v-if="!editingStudent" class="text-caption">
            Adding to: {{ getSelectedContext() }}
          </div>
        </q-card-section>

        <q-card-section>
          <!-- Context Info Banner (for new students) -->
          <q-banner v-if="!editingStudent" class="bg-info text-white q-mb-md" rounded>
            <template v-slot:avatar>
              <q-icon name="info" />
            </template>
            <div class="text-weight-bold">Selected Context:</div>
            <div>{{ getSelectedSchoolName() }} → {{ getSelectedGradeName() }} → {{ getSelectedClassroomName() }}</div>
          </q-banner>

          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-input
                v-model="studentForm.name"
                label="Name *"
                outlined
                :rules="[val => !!val || 'Name is required']"
              />
            </div>
            <div class="col-12 col-md-6">
              <q-input
                v-model="studentForm.name_ar"
                label="Arabic Name"
                outlined
              />
            </div>
            <div class="col-12 col-md-6">
              <q-input
                v-model="studentForm.name_cute"
                label="Nickname"
                outlined
              />
            </div>
            
            <!-- Show these fields only when editing -->
            <template v-if="editingStudent">
              <div class="col-12 col-md-6">
                <q-select
                  v-model="studentForm.school_id"
                  :options="schools"
                  option-value="id"
                  option-label="name"
                  label="School *"
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'School is required']"
                  @update:model-value="onFormSchoolChange"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  v-model="studentForm.stage_id"
                  :options="formStages"
                  option-value="id"
                  option-label="name"
                  label="Stage *"
                  outlined
                  emit-value
                  map-options
                  :disable="!studentForm.school_id"
                  :rules="[val => !!val || 'Stage is required']"
                  @update:model-value="onFormStageChange"
                />
              </div>
              <div class="col-12 col-md-6">
                <q-select
                  v-model="studentForm.grade_id"
                  :options="formGrades"
                  option-value="id"
                  option-label="name"
                  label="Grade *"
                  outlined
                  emit-value
                  map-options
                  :disable="!studentForm.stage_id"
                  :rules="[val => !!val || 'Grade is required']"
                  @update:model-value="onFormGradeChange"
                />
              </div>
              <div class="col-12">
                <q-select
                  v-model="studentForm.classroom_id"
                  :options="formClassrooms"
                  option-value="id"
                  option-label="name"
                  label="Classroom *"
                  outlined
                  emit-value
                  map-options
                  :disable="!studentForm.grade_id"
                  :rules="[val => !!val || 'Classroom is required']"
                />
              </div>
            </template>
            <div class="col-12">
              <q-input
                v-model="studentForm.notes"
                label="Notes"
                outlined
                type="textarea"
                rows="3"
              />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" v-close-popup />
          <q-btn
            unelevated
            label="Save"
            color="primary"
            @click="saveStudent"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Student Promotion Dialog -->
    <StudentPromotionDialog
      v-model="showPromotionDialog"
      :grades="allGrades"
      :academic-years="academicYears"
      @promoted="onPromotionComplete"
    />

    <!-- Import Dialog (Filtered - to selected classroom) -->
    <q-dialog v-model="showImportDialog" persistent>
      <q-card style="min-width: 700px; max-width: 90vw">
        <q-card-section class="bg-info text-white">
          <div class="text-h6">Import Students</div>
          <div class="text-caption">
            Importing to: {{ getSelectedContext() }}
          </div>
        </q-card-section>

        <q-card-section v-if="!importPreviewData.length">
          <q-file
            v-model="importFile"
            label="Select Excel file"
            accept=".xlsx,.xls"
            outlined
            @update:model-value="handleFileUpload"
          >
            <template v-slot:prepend>
              <q-icon name="attach_file" />
            </template>
          </q-file>
          
          <q-banner class="bg-grey-2 q-mt-md" rounded>
            <template v-slot:avatar>
              <q-icon name="info" color="info" />
            </template>
            <div class="text-caption">
              <strong>Required columns:</strong> name<br>
              <strong>Optional columns:</strong> name_ar, name_cute, notes
            </div>
            <template v-slot:action>
              <q-btn
                flat
                dense
                label="Download Template"
                icon="download"
                color="primary"
                @click="downloadTemplate"
              />
            </template>
          </q-banner>
        </q-card-section>

        <q-card-section v-else class="q-pt-none" style="max-height: 400px; overflow-y: auto">
          <div class="text-subtitle2 q-mb-sm">Preview ({{ importPreviewData.length }} students)</div>
          <q-table
            :rows="importPreviewData"
            :columns="importPreviewColumns"
            row-key="index"
            flat
            dense
            :pagination="{ rowsPerPage: 10 }"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" @click="closeImportDialog" />
          <q-btn
            v-if="importPreviewData.length"
            unelevated
            label="Import All"
            color="primary"
            @click="executeImport"
            :loading="importing"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- School-Wide Import Dialog -->
    <q-dialog v-model="showSchoolWideImportDialog" persistent>
      <q-card style="min-width: 700px; max-width: 90vw">
        <q-card-section class="bg-purple text-white">
          <div class="text-h6">School-Wide Import</div>
          <div class="text-caption">
            Importing to: {{ getSelectedSchoolName() }}
          </div>
        </q-card-section>

        <q-card-section v-if="!importWithClassroomPreview.length">
          <q-file
            v-model="importWithClassroomFile"
            label="Select Excel file with classroom column"
            accept=".xlsx,.xls"
            outlined
            @update:model-value="handleSchoolWideFileUpload"
          >
            <template v-slot:prepend>
              <q-icon name="attach_file" />
            </template>
          </q-file>
          
          <q-banner class="bg-grey-2 q-mt-md" rounded>
            <template v-slot:avatar>
              <q-icon name="info" color="info" />
            </template>
            <div class="text-caption">
              <strong>Required columns:</strong> name, classroom<br>
              <strong>Optional columns:</strong> name_ar, name_cute, notes<br>
              <strong>Classroom formats:</strong> "4A", "Grade 4 - A", "4-A"
            </div>
            <template v-slot:action>
              <q-btn
                flat
                dense
                label="Download Template"
                icon="download"
                color="primary"
                @click="downloadTemplateWithClassroom"
              />
            </template>
          </q-banner>
        </q-card-section>

        <q-card-section v-else class="q-pt-none" style="max-height: 400px; overflow-y: auto">
          <div class="text-subtitle2 q-mb-sm">Preview ({{ importWithClassroomPreview.length }} students)</div>
          <q-table
            :rows="importWithClassroomPreview"
            :columns="importWithClassroomColumns"
            row-key="index"
            flat
            dense
            :pagination="{ rowsPerPage: 10 }"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="negative" @click="closeSchoolWideImportDialog" />
          <q-btn
            v-if="importWithClassroomPreview.length"
            unelevated
            label="Import All"
            color="purple"
            @click="executeSchoolWideImport"
            :loading="importing"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import * as XLSX from 'xlsx'
import StudentPromotionDialog from './components/StudentPromotionDialog.vue'

const props = defineProps({
  records: Object,
  schools: Array,
  grades: Array,
  academicYears: Array,
  userRoles: Array,
  permissions: Object
})

const $q = useQuasar()

// State
const loading = ref(false)
const students = ref([])
const selected = ref([])
const showStudentDialog = ref(false)
const showPromotionDialog = ref(false)
const showImportDialog = ref(false)
const editingStudent = ref(null)
const saving = ref(false)

// Import state
const importFile = ref(null)
const importPreviewData = ref([])
const importing = ref(false)

// School-wide import state
const showSchoolWideImportDialog = ref(false)
const importWithClassroomFile = ref(null)
const importWithClassroomPreview = ref([])

const importPreviewColumns = [
  { name: 'name', label: 'Name', field: 'name', align: 'left' },
  { name: 'name_ar', label: 'Arabic Name', field: 'name_ar', align: 'left' },
  { name: 'name_cute', label: 'Nickname', field: 'name_cute', align: 'left' },
  { name: 'notes', label: 'Notes', field: 'notes', align: 'left' }
]

const importWithClassroomColumns = [
  { name: 'name', label: 'Name', field: 'name', align: 'left' },
  { name: 'name_ar', label: 'Arabic Name', field: 'name_ar', align: 'left' },
  { name: 'name_cute', label: 'Nickname', field: 'name_cute', align: 'left' },
  { name: 'classroom', label: 'Classroom', field: 'classroom', align: 'left' },
  { name: 'notes', label: 'Notes', field: 'notes', align: 'left' }
]

// Filters
const filters = ref({
  school_id: null,
  stage_id: null,
  grade_id: null,
  classroom_id: null,
  search: ''
})

// Cascading data
const stages = ref([])
const grades = ref([])
const classrooms = ref([])
const allGrades = ref(props.grades || [])
const academicYears = ref(props.academicYears || [])

// Form data
const formStages = ref([])
const formGrades = ref([])
const formClassrooms = ref([])

const studentForm = ref({
  name: '',
  name_ar: '',
  name_cute: '',
  school_id: null,
  stage_id: null,
  grade_id: null,
  classroom_id: null,
  notes: ''
})

// Pagination
const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 25,
  rowsNumber: 0
})

// Table columns
const columns = [
  {
    name: 'avatar',
    label: '',
    field: 'avatar',
    align: 'center',
    style: 'width: 60px'
  },
  {
    name: 's_id',
    label: 'ID',
    field: 's_id',
    align: 'left',
    sortable: true
  },
  {
    name: 'name',
    label: 'Name',
    field: 'name',
    align: 'left',
    sortable: true
  },
  {
    name: 'school',
    label: 'School',
    field: row => row.school?.name,
    align: 'left',
    sortable: true
  },
  {
    name: 'grade',
    label: 'Grade',
    field: row => row.grade?.name,
    align: 'left',
    sortable: true
  },
  {
    name: 'classroom',
    label: 'Classroom',
    field: row => row.classroom?.name,
    align: 'left',
    sortable: true
  },
  {
    name: 'actions',
    label: 'Actions',
    field: 'actions',
    align: 'center'
  }
]

// Computed
const totalStudents = computed(() => pagination.value.rowsNumber || 0)
const selectedSchoolStudents = computed(() => {
  if (!filters.value.school_id) return 0
  return students.value.filter(s => s.school_id === filters.value.school_id).length
})
const filteredCount = computed(() => students.value.length)
const selectedCount = computed(() => selected.value.length)

// Check if user can add student (must have school, grade, and classroom selected)
const canAddStudent = computed(() => {
  return filters.value.school_id && filters.value.grade_id && filters.value.classroom_id
})

// Helper methods to get selected names
const getSelectedSchoolName = () => {
  const school = props.schools.find(s => s.id === filters.value.school_id)
  return school?.name || 'N/A'
}

const getSelectedGradeName = () => {
  const grade = grades.value.find(g => g.id === filters.value.grade_id)
  return grade?.name || 'N/A'
}

const getSelectedClassroomName = () => {
  const classroom = classrooms.value.find(c => c.id === filters.value.classroom_id)
  return classroom?.name || 'N/A'
}

const getSelectedContext = () => {
  return `${getSelectedSchoolName()} → ${getSelectedGradeName()} → ${getSelectedClassroomName()}`
}

// Methods
const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
}

const onSchoolChange = async () => {
  filters.value.stage_id = null
  filters.value.grade_id = null
  filters.value.classroom_id = null
  stages.value = []
  grades.value = []
  classrooms.value = []

  if (filters.value.school_id) {
    await loadStages(filters.value.school_id)
  }
  applyFilters()
}

const onStageChange = async () => {
  filters.value.grade_id = null
  filters.value.classroom_id = null
  grades.value = []
  classrooms.value = []

  if (filters.value.stage_id) {
    await loadGrades(filters.value.stage_id)
  }
  applyFilters()
}

const onGradeChange = async () => {
  filters.value.classroom_id = null
  classrooms.value = []

  if (filters.value.grade_id) {
    await loadClassrooms(filters.value.grade_id)
  }
  applyFilters()
}

const loadStages = async (schoolId) => {
  try {
    const response = await axios.get(`/admin/stages/by-school/${schoolId}`)
    stages.value = response.data
  } catch (error) {
    console.error('Error loading stages:', error)
  }
}

const loadGrades = async (stageId) => {
  try {
    const response = await axios.get(`/admin/grades/by-stage/${stageId}`)
    grades.value = response.data
  } catch (error) {
    console.error('Error loading grades:', error)
  }
}

const loadClassrooms = async (gradeId) => {
  try {
    const response = await axios.get(`/admin/classrooms/by-grade/${gradeId}`)
    classrooms.value = response.data
  } catch (error) {
    console.error('Error loading classrooms:', error)
  }
}

const applyFilters = async () => {
  loading.value = true
  try {
    const params = {
      school_id: filters.value.school_id,
      stage_id: filters.value.stage_id,
      grade_id: filters.value.grade_id,
      classroom_id: filters.value.classroom_id,
      search: filters.value.search
    }

    const response = await axios.get('/admin/students/filtered', { params })
    students.value = response.data.records.data || []
    pagination.value.rowsNumber = response.data.records.total || 0
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load students'
    })
  } finally {
    loading.value = false
  }
}

const clearFilters = () => {
  filters.value = {
    school_id: null,
    stage_id: null,
    grade_id: null,
    classroom_id: null,
    search: ''
  }
  stages.value = []
  grades.value = []
  classrooms.value = []
  applyFilters()
}

let searchTimeout
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const onRequest = (props) => {
  // Handle pagination, sorting, etc.
  applyFilters()
}

const openStudentDialog = (student = null) => {
  if (student) {
    editingStudent.value = student
    studentForm.value = {
      name: student.name,
      name_ar: student.name_ar,
      name_cute: student.name_cute,
      school_id: student.school_id,
      stage_id: student.stage_id,
      grade_id: student.grade_id,
      classroom_id: student.classroom_id,
      notes: student.notes
    }
    // Load cascading data for form
    if (student.school_id) onFormSchoolChange()
    if (student.stage_id) onFormStageChange()
    if (student.grade_id) onFormGradeChange()
  } else {
    // New student - pre-fill with selected filters
    editingStudent.value = null
    studentForm.value = {
      name: '',
      name_ar: '',
      name_cute: '',
      school_id: filters.value.school_id,
      stage_id: filters.value.stage_id,
      grade_id: filters.value.grade_id,
      classroom_id: filters.value.classroom_id,
      notes: ''
    }
  }
  showStudentDialog.value = true
}

const onFormSchoolChange = async () => {
  studentForm.value.stage_id = null
  studentForm.value.grade_id = null
  studentForm.value.classroom_id = null
  
  if (studentForm.value.school_id) {
    const response = await axios.get(`/admin/stages/by-school/${studentForm.value.school_id}`)
    formStages.value = response.data
  }
}

const onFormStageChange = async () => {
  studentForm.value.grade_id = null
  studentForm.value.classroom_id = null
  
  if (studentForm.value.stage_id) {
    const response = await axios.get(`/admin/grades/by-stage/${studentForm.value.stage_id}`)
    formGrades.value = response.data
  }
}

const onFormGradeChange = async () => {
  studentForm.value.classroom_id = null
  
  if (studentForm.value.grade_id) {
    const response = await axios.get(`/admin/classrooms/by-grade/${studentForm.value.grade_id}`)
    formClassrooms.value = response.data
  }
}

const saveStudent = async () => {
  saving.value = true
  try {
    const url = editingStudent.value
      ? `/admin/students/${editingStudent.value.id}`
      : '/admin/students'

    const data = {
      ...studentForm.value,
      ...(editingStudent.value && { _method: 'PUT' })
    }

    await axios.post(url, data)

    $q.notify({
      type: 'positive',
      message: editingStudent.value ? 'Student updated successfully' : 'Student created successfully'
    })

    showStudentDialog.value = false
    applyFilters()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to save student'
    })
  } finally {
    saving.value = false
  }
}

const deleteStudent = async (student) => {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete ${student.name}?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await axios.delete(`/admin/students/${student.id}`)
      $q.notify({
        type: 'positive',
        message: 'Student deleted successfully'
      })
      applyFilters()
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: 'Failed to delete student'
      })
    }
  })
}

const viewHistory = async (student) => {
  try {
    const response = await axios.get(`/admin/students/${student.id}/classroom-history`)
    $q.dialog({
      title: `Classroom History - ${student.name}`,
      message: JSON.stringify(response.data.history, null, 2)
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load history'
    })
  }
}

const handleExport = () => {
  $q.notify({
    type: 'info',
    message: 'Export functionality coming soon!'
  })
}

const bulkChangeClassroom = () => {
  $q.notify({
    type: 'info',
    message: 'Bulk classroom change coming in Phase 4!'
  })
}

const exportSelected = () => {
  $q.notify({
    type: 'info',
    message: `Exporting ${selected.value.length} students...`
  })
}

const bulkDelete = () => {
  $q.dialog({
    title: 'Confirm Bulk Delete',
    message: `Are you sure you want to delete ${selected.value.length} students?`,
    cancel: true,
    persistent: true
  }).onOk(() => {
    $q.notify({
      type: 'info',
      message: 'Bulk delete coming soon!'
    })
  })
}

const onPromotionComplete = (result) => {
  $q.notify({
    type: 'positive',
    message: `Successfully promoted ${result.promoted_count} students!`
  })
  applyFilters()
}

const triggerImport = () => {
  if (!canAddStudent.value) {
    $q.notify({
      type: 'negative',
      message: 'Please select School, Grade, and Classroom before importing'
    })
    return
  }
  showImportDialog.value = true
}

const handleFileUpload = async (file) => {
  if (!file) return

  try {
    const data = await readExcelFile(file)
    importPreviewData.value = data.map((row, index) => ({
      index,
      name: row.name || row.Name || '',
      name_ar: row.name_ar || row['Arabic Name'] || '',
      name_cute: row.name_cute || row.Nickname || '',
      notes: row.notes || row.Notes || ''
    }))
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error reading Excel file: ' + error.message
    })
  }
}

const readExcelFile = (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    
    reader.onload = (e) => {
      try {
        const data = new Uint8Array(e.target.result)
        const workbook = XLSX.read(data, { type: 'array' })
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]]
        const jsonData = XLSX.utils.sheet_to_json(firstSheet)
        resolve(jsonData)
      } catch (error) {
        reject(error)
      }
    }
    
    reader.onerror = reject
    reader.readAsArrayBuffer(file)
  })
}

const executeImport = async () => {
  importing.value = true
  let successCount = 0
  let errorCount = 0

  try {
    for (const row of importPreviewData.value) {
      try {
        const studentData = {
          name: row.name,
          name_ar: row.name_ar || '',
          name_cute: row.name_cute || '',
          school_id: filters.value.school_id,
          stage_id: filters.value.stage_id,
          grade_id: filters.value.grade_id,
          classroom_id: filters.value.classroom_id,
          notes: row.notes || ''
        }

        await axios.post('/admin/students', studentData)
        successCount++
      } catch (error) {
        errorCount++
        console.error('Import error:', error)
      }
    }

    $q.notify({
      type: successCount > 0 ? 'positive' : 'negative',
      message: `Import complete: ${successCount} students added${errorCount > 0 ? `, ${errorCount} errors` : ''}`,
      timeout: 3000
    })

    closeImportDialog()
    await applyFilters()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Import failed: ' + (error.message || 'Unknown error')
    })
  } finally {
    importing.value = false
  }
}

const downloadTemplate = () => {
  window.location.href = '/admin/students/download-template'
}

const downloadTemplateWithClassroom = () => {
  window.location.href = '/admin/students/download-template-with-classroom'
}

const handleSchoolWideFileUpload = async (file) => {
  if (!file) return

  try {
    const data = await readExcelFile(file)
    
    // Check if classroom column exists
    if (data.length > 0 && !('classroom' in data[0]) && !('Classroom' in data[0])) {
      $q.notify({
        type: 'negative',
        message: 'Invalid file: Missing "classroom" column. Please download the correct template.'
      })
      importWithClassroomFile.value = null
      return
    }

    const validRows = []
    const invalidRows = []

    data.forEach((row, index) => {
      const name = row.name || row.Name || ''
      const classroom = row.classroom || row.Classroom || ''

      // Check if required fields are empty
      if (!name.trim() || !classroom.trim()) {
        invalidRows.push({
          row: index + 2, // Excel row number (1-indexed + header)
          reason: !name.trim() ? 'Missing name' : 'Missing classroom'
        })
      } else {
        validRows.push({
          index,
          name: name.trim(),
          name_ar: (row.name_ar || row['Arabic Name'] || '').trim(),
          name_cute: (row.name_cute || row.Nickname || '').trim(),
          classroom: classroom.trim(),
          notes: (row.notes || row.Notes || '').trim()
        })
      }
    })

    if (invalidRows.length > 0) {
      const errorMessages = invalidRows.map(r => `Row ${r.row}: ${r.reason}`).join('\n')
      $q.dialog({
        title: 'Invalid Records Found',
        message: `Found ${invalidRows.length} invalid record(s):\n\n${errorMessages}\n\nThese rows will be skipped.`,
        html: true
      })
    }

    importWithClassroomPreview.value = validRows
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Error reading Excel file: ' + error.message
    })
  }
}

const executeSchoolWideImport = async () => {
  if (!filters.value.school_id) {
    $q.notify({
      type: 'negative',
      message: 'Please select a school'
    })
    return
  }

  const totalRecords = importWithClassroomPreview.value.length
  let processed = 0
  let created = 0
  let duplicates = 0
  let failed = 0
  const errors = []

  // Show progress dialog
  const progressDialog = $q.dialog({
    title: 'Importing Students',
    message: `Processing 0 of ${totalRecords} students...`,
    progress: {
      spinner: false,
      value: 0,
      color: 'primary'
    },
    persistent: true,
    ok: false
  })

  importing.value = true

  try {
    // Process each record one by one
    for (const [index, row] of importWithClassroomPreview.value.entries()) {
      try {
        const response = await axios.post('/admin/students/import-with-classroom', {
          school_id: filters.value.school_id,
          name: row.name,
          name_ar: row.name_ar,
          name_cute: row.name_cute,
          classroom: row.classroom,
          notes: row.notes
        })

        processed++

        // Track status
        if (response.data.status === 'created') {
          created++
        } else if (response.data.status === 'duplicate') {
          duplicates++
        }

        // Update progress
        progressDialog.update({
          message: `Processing ${processed} of ${totalRecords} students...\nCreated: ${created} | Duplicates: ${duplicates} | Failed: ${failed}`,
          progress: {
            value: processed / totalRecords
          }
        })

      } catch (error) {
        processed++
        failed++
        errors.push({
          row: index + 2,
          name: row.name,
          error: error.response?.data?.message || error.message
        })

        // Update progress
        progressDialog.update({
          message: `Processing ${processed} of ${totalRecords} students...\nCreated: ${created} | Duplicates: ${duplicates} | Failed: ${failed}`,
          progress: {
            value: processed / totalRecords
          }
        })
      }
    }

    // Close progress dialog
    progressDialog.hide()

    // Show results
    const resultMessage = `Import Complete!\n\n✅ Created: ${created}\n⏭️ Duplicates: ${duplicates}\n❌ Failed: ${failed}`

    if (failed > 0) {
      $q.dialog({
        title: 'Import Completed with Errors',
        message: resultMessage + '\n\nCheck console for error details.',
        html: true
      })
      console.log('Import Errors:', errors)
    } else {
      $q.notify({
        type: 'positive',
        message: resultMessage,
        html: true,
        timeout: 5000
      })
    }

    closeSchoolWideImportDialog()
    await applyFilters()

  } catch (error) {
    progressDialog.hide()
    $q.notify({
      type: 'negative',
      message: 'Import failed: ' + (error.message || 'Unknown error')
    })
  } finally {
    importing.value = false
  }
}

const triggerSchoolWideImport = () => {
  if (!filters.value.school_id) {
    $q.notify({
      type: 'negative',
      message: 'Please select a School first'
    })
    return
  }
  showSchoolWideImportDialog.value = true
}

const closeImportDialog = () => {
  showImportDialog.value = false
  importFile.value = null
  importPreviewData.value = []
}

const closeSchoolWideImportDialog = () => {
  showSchoolWideImportDialog.value = false
  importWithClassroomFile.value = null
  importWithClassroomPreview.value = []
}

// Initialize
onMounted(async () => {
  if (props.records?.data) {
    students.value = props.records.data
    pagination.value.rowsNumber = props.records.total || 0
  }
  
  // Auto-select first school if available
  if (props.schools && props.schools.length > 0) {
    filters.value.school_id = props.schools[0].id
    await onSchoolChange()
  }
})
</script>

<style scoped>
.shadow-up-2 {
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
}
</style>
