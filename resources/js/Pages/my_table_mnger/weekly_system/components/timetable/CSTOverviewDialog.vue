<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" position="right" maximized>
    <q-card style="width: 900px; max-width: 95vw" class="column">
      <!-- Header -->
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <q-icon name="preview" size="sm" class="q-mr-sm" />
        <div class="text-h6">Subject-Teacher Assignments Overview</div>
        <q-space />
        <!-- Edit Mode Toggle -->
        <q-toggle
          v-if="data"
          v-model="editMode"
          label="Edit Mode"
          color="white"
          dark
          class="q-mr-md"
        />
        
        <!-- Save Changes Button -->
        <q-btn
          v-if="editMode && hasModifications"
          color="positive"
          icon="save"
          label="Save Changes"
          class="q-mr-md"
          @click="saveAllChanges"
          :loading="savingAll"
        />
        <!-- Show Deleted Toggle -->
        <q-toggle
          v-if="data"
          v-model="showDeleted"
          label="Show Deleted"
          color="white"
          dark
          class="q-mr-md"
          @update:model-value="handleShowDeletedChange"
        />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <!-- Main Content -->
      <q-card-section class="col scroll">
        <!-- Loading State -->
        <div v-if="loading && !data" class="row justify-center q-pa-xl">
          <q-spinner-dots size="50px" color="primary" />
          <div class="text-grey-7 q-mt-md">Loading data...</div>
        </div>

        <!-- Error State -->
        <q-banner v-else-if="error" class="bg-negative text-white q-mb-md">
          <template v-slot:avatar>
            <q-icon name="error" color="white" />
          </template>
          {{ error }}
        </q-banner>

        <!-- Data View -->
        <template v-else-if="data">
          <!-- Loading Overlay when refreshing -->
          <q-inner-loading :showing="loading">
             <q-spinner-gears size="50px" color="primary" />
          </q-inner-loading>
          <!-- Warning Banner (Edit Mode) -->
          <q-banner v-if="editMode || hasModifications" class="bg-warning text-white q-mb-lg">
            <template v-slot:avatar>
              <q-icon name="warning" color="white" />
            </template>
            <div class="text-weight-bold">Modifications Active</div>
            <div class="text-body2">
              Changes to classes per week may require updates to the timetable.
            </div>
          </q-banner>

          <!-- Info Banner -->
          <q-banner v-else class="bg-blue-1 q-mb-lg">
            <template v-slot:avatar>
              <q-icon name="info" color="blue" />
            </template>
            <div class="text-body2">
              This shows all subject-teacher assignments for the selected school and academic year.
              The <strong>Total Expected Schedules</strong> is how many schedule records will be created when you auto-generate.
            </div>
          </q-banner>

          <!-- Summary Cards -->
          <div class="row q-gutter-md q-mb-lg">
            <q-card flat bordered class="col-12 col-sm">
              <q-card-section class="text-center">
                <div class="text-h4 text-primary">{{ data.summary.total_classrooms }}</div>
                <div class="text-caption text-grey-7">Classrooms</div>
                <q-icon name="class" color="primary" size="md" class="q-mt-sm" />
              </q-card-section>
            </q-card>
            <q-card flat bordered class="col-12 col-sm">
              <q-card-section class="text-center">
                <div class="text-h4 text-secondary">{{ data.summary.total_csts }}</div>
                <div class="text-caption text-grey-7">Active Assignments</div>
                <q-icon name="assignment" color="secondary" size="md" class="q-mt-sm" />
              </q-card-section>
            </q-card>
            <q-card flat bordered class="col-12 col-sm" v-if="showDeleted && data.summary.total_deleted_csts > 0">
              <q-card-section class="text-center">
                <div class="text-h4 text-negative">{{ data.summary.total_deleted_csts }}</div>
                <div class="text-caption text-grey-7">Deleted</div>
                <q-icon name="delete" color="negative" size="md" class="q-mt-sm" />
              </q-card-section>
            </q-card>
            <q-card flat bordered class="col-12 col-sm">
              <q-card-section class="text-center">
                <div class="text-h4 text-positive">{{ data.summary.total_expected_schedules }}</div>
                <div class="text-caption text-grey-7">Expected Schedules</div>
                <q-icon name="event" color="positive" size="md" class="q-mt-sm" />
                <q-tooltip>Total classes_per_week across all active assignments</q-tooltip>
              </q-card-section>
            </q-card>
          </div>

          <!-- Empty State -->
          <div v-if="activeClassrooms.length === 0 && (!showDeleted || deletedClassrooms.length === 0)" class="text-center q-pa-xl">
            <q-icon name="inbox" size="64px" color="grey-5" />
            <p class="text-h6 text-grey-7 q-mt-md">No Data Found</p>
            <p class="text-grey-6">No subject-teacher assignments for this school and academic year</p>
          </div>

          <!-- Active Classrooms Breakdown -->
          <div v-if="activeClassrooms.length > 0">
            <div class="text-subtitle2 text-grey-7 q-mb-sm">Active Assignments by Classroom</div>
            <q-list bordered separator class="rounded-borders q-mb-lg">
              <template v-for="classroom in activeClassrooms" :key="classroom.classroom_id">
                <q-expansion-item
                  v-model="expandedStates[classroom.classroom_id]"
                  :label="classroom.classroom_name"
                  :caption="`${classroom.cst_count} subjects → ${classroom.total_classes_per_week} schedule entries`"
                  icon="class"
                  header-class="bg-grey-1"
                >
                  <q-card>
                    <q-card-section class="q-pa-sm">
                      <!-- Summary Chips -->
                      <div class="row q-gutter-sm q-mb-md">
                        <q-chip dense icon="subject" color="blue-2" text-color="blue-9">
                          {{ classroom.cst_count }} Subjects
                        </q-chip>
                        <q-chip dense icon="schedule" color="green-2" text-color="green-9">
                          {{ classroom.total_classes_per_week }} Periods/Week
                        </q-chip>
                      </div>

                      <!-- Subjects Table -->
                      <q-table
                        :rows="classroom.subjects.filter(s => !s.is_deleted)"
                        :columns="editMode ? editColumns : subjectColumns"
                        row-key="cst_id"
                        dense
                        flat
                        :rows-per-page-options="[0]"
                        hide-bottom
                      >
                        <template v-slot:body-cell-subject_name="props">
                          <q-td :props="props">
                            <q-badge color="blue-2" text-color="blue-9">
                              {{ props.row.subject_name }}
                            </q-badge>
                          </q-td>
                        </template>
                        <template v-slot:body-cell-classes_per_week="props">
                          <q-td :props="props">
                            <q-input
                              v-if="editMode"
                              v-model.number="props.row.classes_per_week"
                              type="number"
                              dense
                              outlined
                              min="1"
                              max="20"
                              style="max-width: 80px"
                              :class="{ 'bg-yellow-1': props.row.modified }"
                              @keyup.enter="saveAllChanges"
                            />
                            <q-chip v-else dense size="sm" color="green-2" text-color="green-9">
                              {{ props.row.classes_per_week }}
                            </q-chip>
                          </q-td>
                        </template>
                        <template v-slot:body-cell-actions="props">
                          <q-td :props="props">
                            <q-btn
                              v-if="props.row.modified"
                              flat
                              dense
                              round
                              icon="save"
                              color="positive"
                              size="sm"
                              @click="saveCST(props.row)"
                              :loading="props.row.saving"
                            >
                              <q-tooltip>Save changes</q-tooltip>
                            </q-btn>
                            <q-btn
                              flat
                              dense
                              round
                              icon="delete"
                              color="negative"
                              size="sm"
                              @click="deleteCST(props.row)"
                            >
                              <q-tooltip>Delete</q-tooltip>
                            </q-btn>
                          </q-td>
                        </template>
                      </q-table>

                      <!-- Calculation Display -->
                      <div class="q-mt-md q-pa-sm bg-green-1 rounded-borders">
                        <div class="text-caption text-grey-7">Calculation:</div>
                        <div class="text-body2 text-green-9">
                          {{ classroom.cst_count }} subjects × avg {{ (classroom.total_classes_per_week / classroom.cst_count).toFixed(1) }} periods 
                          = <strong>{{ classroom.total_classes_per_week }} schedule entries</strong>
                        </div>
                      </div>
                      
                      <!-- Sync Button -->
                      <div class="row justify-end q-mt-md" v-if="editMode">
                         <q-btn
                          dense
                          unelevated
                          color="secondary"
                          icon="sync_alt"
                          label="Sync classes/week to other classrooms"
                          size="sm"
                          @click.stop="openSyncDialog(classroom)"
                        />
                      </div>
                    </q-card-section>
                  </q-card>
                </q-expansion-item>
              </template>
            </q-list>
          </div>

          <!-- Deleted Classrooms Breakdown -->
          <div v-if="showDeleted && deletedClassrooms.length > 0">
            <div class="text-subtitle2 text-grey-7 q-mb-sm">Deleted Assignments</div>
            <q-list bordered separator class="rounded-borders">
              <template v-for="classroom in deletedClassrooms" :key="'deleted-' + classroom.classroom_id">
                <q-expansion-item
                  :label="classroom.classroom_name"
                  :caption="`${classroom.cst_deleted_count} deleted subject(s)`"
                  icon="delete"
                  header-class="bg-red-1"
                >
                  <q-card>
                    <q-card-section class="q-pa-sm">
                      <q-table
                        :rows="classroom.subjects.filter(s => s.is_deleted)"
                        :columns="deletedColumns"
                        row-key="cst_id"
                        dense
                        flat
                        :rows-per-page-options="[0]"
                        hide-bottom
                      >
                        <template v-slot:body-cell-subject_name="props">
                          <q-td :props="props" class="text-strike text-grey-6">
                            {{ props.row.subject_name }}
                          </q-td>
                        </template>
                        <template v-slot:body-cell-teacher_name="props">
                          <q-td :props="props" class="text-strike text-grey-6">
                            {{ props.row.teacher_name }}
                          </q-td>
                        </template>
                        <template v-slot:body-cell-deleted_at="props">
                          <q-td :props="props">
                            <div class="text-caption text-grey-6">
                              {{ formatDate(props.row.deleted_at) }}
                            </div>
                          </q-td>
                        </template>
                        <template v-slot:body-cell-restore="props">
                          <q-td :props="props">
                            <q-btn
                              flat
                              dense
                              round
                              icon="restore"
                              color="positive"
                              size="sm"
                              @click="restoreCST(props.row)"
                            >
                              <q-tooltip>Restore</q-tooltip>
                            </q-btn>
                          </q-td>
                        </template>
                      </q-table>
                    </q-card-section>
                  </q-card>
                </q-expansion-item>
              </template>
            </q-list>
          </div>
        </template>

        <!-- Initial State -->
        <div v-else class="text-center q-pa-xl">
          <q-icon name="preview" size="64px" color="grey-5" />
          <p class="text-h6 text-grey-7 q-mt-md">Ready to Load Overview</p>
          <p class="text-grey-6">Click the button below to view subject-teacher assignments</p>
          <q-btn
            color="primary"
            icon="preview"
            label="Load Overview"
            @click="fetchOverview"
            class="q-mt-md"
          />
        </div>
      </q-card-section>

      <q-separator />

      <!-- Footer Actions -->
      <q-card-actions align="right">
        <q-btn
          v-if="data"
          flat
          icon="refresh"
          label="Refresh"
          color="primary"
          @click="fetchOverview"
          :loading="loading"
        />
        <q-btn flat label="Close" color="grey" v-close-popup />
      </q-card-actions>
    </q-card>

    <!-- Sync Dialog -->
    <CSTSyncDialog
      v-model="showSyncDialog"
      :source-classroom="selectedSourceClassroom"
      :all-classrooms="activeClassrooms"
      @synced="fetchOverview"
    />
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar, date } from 'quasar'
import { useSchoolDataStore } from '@/Stores/schoolData'
import axios from 'axios'
import CSTSyncDialog from './CSTSyncDialog.vue'

const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue'])

const $q = useQuasar()
const schoolDataStore = useSchoolDataStore()

// State
const loading = ref(false)
const error = ref(null)
const data = ref(null)
const editMode = ref(false)
const showDeleted = ref(false)
const hasModifications = ref(false)
const savingAll = ref(false)
const showSyncDialog = ref(false)
const selectedSourceClassroom = ref(null)

// Table columns
const subjectColumns = [
  { name: 'subject_name', label: 'Subject', field: 'subject_name', align: 'left' },
  { name: 'teacher_name', label: 'Teacher', field: 'teacher_name', align: 'left' },
  { name: 'classes_per_week', label: 'Classes/Week', field: 'classes_per_week', align: 'center' }
]

const editColumns = [
  { name: 'subject_name', label: 'Subject', field: 'subject_name', align: 'left' },
  { name: 'teacher_name', label: 'Teacher', field: 'teacher_name', align: 'left' },
  { name: 'classes_per_week', label: 'Classes/Week', field: 'classes_per_week', align: 'center' },
  { name: 'actions', label: 'Actions', align: 'center' }
]

const deletedColumns = [
  { name: 'subject_name', label: 'Subject', field: 'subject_name', align: 'left' },
  { name: 'teacher_name', label: 'Teacher', field: 'teacher_name', align: 'left' },
  { name: 'deleted_at', label: 'Deleted At', align: 'center' },
  { name: 'restore', label: '', align: 'center' }
]

// Computed
const activeClassrooms = computed(() => {
  if (!data.value) return []
  return data.value.by_classroom.filter(c => c.cst_count > 0)
})

const deletedClassrooms = computed(() => {
  if (!data.value) return []
  return data.value.by_classroom.filter(c => c.cst_deleted_count > 0)
})

const expandedStates = ref({})

// Methods
const fetchOverview = async () => {
  if (!schoolDataStore.schoolId || !schoolDataStore.academicYearId) {
    error.value = 'School and Academic Year must be selected'
    return
  }

  loading.value = true
  error.value = null
  // Only clear data if we don't have it (first load) to prevent UI flash/collapse
  if (!data.value) {
    data.value = null
  }
  hasModifications.value = false

  try {
    const response = await axios.post('/weekly-system/api/cst-overview', {
      school_id: schoolDataStore.schoolId,
      academic_year_id: schoolDataStore.academicYearId,
      include_deleted: showDeleted.value
    })

    if (response.data.success) {
      data.value = response.data.data
      
      // Add reactive properties to each subject
      data.value.by_classroom.forEach(classroom => {
        classroom.subjects.forEach(subject => {
          subject.original_classes_per_week = subject.classes_per_week
          subject.modified = false
          subject.saving = false
        })
      })
      
      if (response.data.message) {
        $q.notify({
          type: 'info',
          message: response.data.message,
          position: 'top'
        })
      }
    } else {
      error.value = response.data.message || 'Failed to load overview'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to load overview'
  } finally {
    loading.value = false
  }
}

const handleShowDeletedChange = () => {
  fetchOverview()
}

const saveCST = async (cst) => {
  cst.saving = true
  try {
    const response = await axios.put(`/weekly-system/api/cst/${cst.cst_id}/classes-per-week`, {
      classes_per_week: cst.classes_per_week
    })

    if (response.data.success) {
      cst.original_classes_per_week = cst.classes_per_week
      cst.modified = false
      hasModifications.value = true
      
      $q.notify({
        type: 'positive',
        message: response.data.message,
        position: 'top'
      })

      // Refresh to update totals
      await fetchOverview()
    }
  } catch (e) {
    $q.notify({
      type: 'negative',
      message: e.response?.data?.message || 'Failed to save',
      position: 'top'
    })
  } finally {
    cst.saving = false
  }
}

const deleteCST = async (cst) => {
  $q.dialog({
    title: 'Confirm Delete',
    message: `Are you sure you want to delete ${cst.subject_name} (${cst.teacher_name})? This will soft-delete the assignment. You can restore it later.`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      const response = await axios.delete(`/weekly-system/api/cst/${cst.cst_id}`)

      if (response.data.success) {
        $q.notify({
          type: 'positive',
          message: response.data.message,
          position: 'top'
        })
        
        // Refresh
        await fetchOverview()
      }
    } catch (e) {
      $q.notify({
        type: 'negative',
        message: e.response?.data?.message || 'Failed to delete',
        position: 'top'
      })
    }
  })
}

const restoreCST = async (cst) => {
  $q.dialog({
    title: 'Confirm Restore',
    message: `Restore ${cst.subject_name} (${cst.teacher_name})? You may need to sync schedules afterwards.`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      const response = await axios.post(`/weekly-system/api/cst/${cst.cst_id}/restore`)

      if (response.data.success) {
        hasModifications.value = true
        
        $q.notify({
          type: 'positive',
          message: response.data.message,
          position: 'top'
        })
        
        // Refresh
        await fetchOverview()
      }
    } catch (e) {
      $q.notify({
        type: 'negative',
        message: e.response?.data?.message || 'Failed to restore',
        position: 'top'
      })
    }
  })
}

const saveAllChanges = async () => {
  savingAll.value = true
  try {
    const updates = []
    
    // Collect updates
    if (data.value && data.value.by_classroom) {
      data.value.by_classroom.forEach(classroom => {
        classroom.subjects.forEach(subject => {
          if (!subject.is_deleted && subject.modified) {
             updates.push({
               cst_id: subject.cst_id,
               classes_per_week: subject.classes_per_week
             })
          }
        })
      })
    }
    
    if (updates.length === 0) {
      savingAll.value = false
      return
    }

    const response = await axios.post('/weekly-system/api/cst-bulk-update-classes-per-week', {
      updates
    })

    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: response.data.message,
        position: 'top'
      })
      
      // Refresh to update totals and reset modified state
      await fetchOverview()
    }
  } catch (e) {
    console.error(e)
    $q.notify({
      type: 'negative',
      message: e.response?.data?.message || 'Failed to save changes',
      position: 'top'
    })
  } finally {
    savingAll.value = false
  }
}

const openSyncDialog = (classroom) => {
  selectedSourceClassroom.value = classroom
  showSyncDialog.value = true
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return date.formatDate(dateStr, 'MMM D, YYYY HH:mm')
}

// Watch for classes_per_week changes to mark as modified
watch(() => data.value, (newData) => {
  if (newData && editMode.value) {
    newData.by_classroom.forEach(classroom => {
      classroom.subjects.forEach(subject => {
        if (!subject.is_deleted) {
          subject.modified = subject.classes_per_week !== subject.original_classes_per_week
        }
      })
    })
  }
}, { deep: true })

// Auto-fetch when dialog opens
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    // Reset state
    data.value = null
    error.value = null
    editMode.value = false
    showDeleted.value = false
    hasModifications.value = false
    
    // Auto-fetch
    fetchOverview()
  }
})
</script>

<style scoped>
.text-strike {
  text-decoration: line-through;
}
</style>
