<template>
  <div class="q-pa-md">
    <!-- Page Header -->
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="content_copy" class="q-mr-sm" color="primary" />
          Schedule Copies Management
        </h4>
        <p class="text-grey-7 q-mb-none">
          Create and manage schedule copies for different academic periods
        </p>
      </div>
      <div class="col-auto">
        <q-btn
          color="primary"
          icon="add"
          label="New Schedule Copy"
          @click="openCreateDialog"
        />
      </div>
    </div>

    <!-- Filters -->
    <ScheduleCopyFilters
      v-model="filters"
      :schools="schools"
      :academic-years="academicYears"
      :semesters="semesters"
      :loading-schools="loadingSchools"
      :loading-years="loadingYears"
      :loading-semesters="loadingSemesters"
      @filter="handleFilter"
      class="q-mb-lg"
    />

    <!-- Loading State -->
    <div v-if="loading" class="row justify-center q-pa-xl">
      <q-spinner-dots size="50px" color="primary" />
    </div>

    <!-- Empty State -->
    <q-card v-else-if="!filteredCopies.length" flat bordered class="text-center q-pa-xl">
      <q-icon name="folder_open" size="64px" color="grey-5" />
      <p class="text-h6 text-grey-7 q-mt-md">No schedule copies found</p>
      <p class="text-grey-6">Create your first schedule copy to get started</p>
      <q-btn color="primary" icon="add" label="Create Schedule Copy" @click="openCreateDialog" class="q-mt-md" />
    </q-card>

    <!-- Schedule Copies Grid -->
    <div v-else class="row q-gutter-md">
      <div
        v-for="copy in filteredCopies"
        :key="copy.id"
        class="col-12 col-sm-6 col-md-4 col-lg-3"
      >
        <ScheduleCopyCard
          :copy="copy"
          @edit="openEditDialog"
          @activate="handleActivate"
          @archive="handleArchive"
          @delete="handleDelete"
        />
      </div>
    </div>

    <!-- Form Dialog -->
    <ScheduleCopyForm
      v-model="showFormDialog"
      :edit-data="editingCopy"
      :schools="schools"
      :academic-years="academicYears"
      :semesters="semesters"
      :loading-schools="loadingSchools"
      :loading-years="loadingYears"
      :loading-semesters="loadingSemesters"
      :saving="saving"
      @submit="handleSubmit"
    />

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="showDeleteDialog">
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center">
          <q-icon name="warning" color="negative" size="md" class="q-mr-sm" />
          <span class="text-h6">Delete Schedule Copy</span>
        </q-card-section>
        <q-card-section>
          Are you sure you want to delete "<strong>{{ deletingCopy?.name }}</strong>"?
          This action cannot be undone.
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" v-close-popup />
          <q-btn flat label="Delete" color="negative" :loading="deleting" @click="confirmDelete" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import ScheduleCopyCard from '../components/schedule-copies/ScheduleCopyCard.vue'
import ScheduleCopyForm from '../components/schedule-copies/ScheduleCopyForm.vue'
import ScheduleCopyFilters from '../components/schedule-copies/ScheduleCopyFilters.vue'

const $q = useQuasar()

// Data
const scheduleCopies = ref([])
const schools = ref([])
const academicYears = ref([])
const semesters = ref([])

// States
const loading = ref(false)
const loadingSchools = ref(false)
const loadingYears = ref(false)
const loadingSemesters = ref(false)
const saving = ref(false)
const deleting = ref(false)

// Dialog states
const showFormDialog = ref(false)
const showDeleteDialog = ref(false)
const editingCopy = ref(null)
const deletingCopy = ref(null)

// Filters
const filters = ref({
  school_id: null,
  academic_year_id: null,
  semester_id: null,
  status: null
})

// Computed
const filteredCopies = computed(() => {
  let result = [...scheduleCopies.value]
  
  if (filters.value.school_id) {
    result = result.filter(c => c.school_id === filters.value.school_id)
  }
  if (filters.value.academic_year_id) {
    result = result.filter(c => c.academic_year_id === filters.value.academic_year_id)
  }
  if (filters.value.semester_id) {
    result = result.filter(c => c.semester_id === filters.value.semester_id)
  }
  if (filters.value.status) {
    result = result.filter(c => c.status === filters.value.status)
  }
  
  return result
})

// Methods
const fetchScheduleCopies = async () => {
  loading.value = true
  try {
    const response = await axios.get('/hr/schedule-copies')
    scheduleCopies.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching schedule copies:', error)
    $q.notify({ type: 'negative', message: 'Failed to load schedule copies' })
  } finally {
    loading.value = false
  }
}

const fetchSchools = async () => {
  loadingSchools.value = true
  try {
    const response = await axios.get('/api/schools')
    schools.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching schools:', error)
  } finally {
    loadingSchools.value = false
  }
}

const fetchAcademicYears = async () => {
  loadingYears.value = true
  try {
    const response = await axios.get('/api/academic-years')
    academicYears.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching academic years:', error)
  } finally {
    loadingYears.value = false
  }
}

const fetchSemesters = async () => {
  loadingSemesters.value = true
  try {
    const response = await axios.get('/api/semesters')
    semesters.value = response.data.data || response.data || []
  } catch (error) {
    console.error('Error fetching semesters:', error)
  } finally {
    loadingSemesters.value = false
  }
}

const openCreateDialog = () => {
  editingCopy.value = null
  showFormDialog.value = true
}

const openEditDialog = (copy) => {
  editingCopy.value = { ...copy }
  showFormDialog.value = true
}

const handleSubmit = async (formData) => {
  saving.value = true
  try {
    if (formData.isEdit) {
      await axios.put(`/hr/schedule-copies/${editingCopy.value.id}`, formData)
      $q.notify({ type: 'positive', message: 'Schedule copy updated successfully' })
    } else {
      const response = await axios.post('/hr/schedule-copies', formData)
      
      // Auto-generate schedules if option is checked
      if (formData.auto_generate && response.data.id) {
        await axios.post('/admin/schedule-copies/create-entries', {
          schedule_copy_id: response.data.id
        })
        $q.notify({ type: 'positive', message: 'Schedule copy created with schedule slots' })
      } else {
        $q.notify({ type: 'positive', message: 'Schedule copy created successfully' })
      }
    }
    
    showFormDialog.value = false
    await fetchScheduleCopies()
  } catch (error) {
    console.error('Error saving schedule copy:', error)
    $q.notify({ type: 'negative', message: error.response?.data?.message || 'Failed to save schedule copy' })
  } finally {
    saving.value = false
  }
}

const handleActivate = async (copy) => {
  try {
    await axios.put(`/hr/schedule-copies/${copy.id}`, { status: 'active' })
    $q.notify({ type: 'positive', message: 'Schedule copy activated' })
    await fetchScheduleCopies()
  } catch (error) {
    console.error('Error activating:', error)
    $q.notify({ type: 'negative', message: 'Failed to activate schedule copy' })
  }
}

const handleArchive = async (copy) => {
  try {
    await axios.put(`/hr/schedule-copies/${copy.id}`, { status: 'archived' })
    $q.notify({ type: 'info', message: 'Schedule copy archived' })
    await fetchScheduleCopies()
  } catch (error) {
    console.error('Error archiving:', error)
    $q.notify({ type: 'negative', message: 'Failed to archive schedule copy' })
  }
}

const handleDelete = (copy) => {
  deletingCopy.value = copy
  showDeleteDialog.value = true
}

const confirmDelete = async () => {
  deleting.value = true
  try {
    await axios.delete(`/hr/schedule-copies/${deletingCopy.value.id}`)
    $q.notify({ type: 'positive', message: 'Schedule copy deleted' })
    showDeleteDialog.value = false
    await fetchScheduleCopies()
  } catch (error) {
    console.error('Error deleting:', error)
    $q.notify({ type: 'negative', message: 'Failed to delete schedule copy' })
  } finally {
    deleting.value = false
  }
}

const handleFilter = () => {
  // Filters are reactive, so filteredCopies will auto-update
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchScheduleCopies(),
    fetchSchools(),
    fetchAcademicYears(),
    fetchSemesters()
  ])
})
</script>

<style scoped>
h4 {
  font-size: 1.5rem;
}
</style>
