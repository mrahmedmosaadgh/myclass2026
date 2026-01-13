<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" position="right" maximized>
    <q-card style="width: 900px; max-width: 95vw" class="column">
      <!-- Header -->
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <q-icon name="sync" size="sm" class="q-mr-sm" />
        <div class="text-h6">Schedule Sync Manager</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <!-- Main Content -->
      <q-card-section class="col scroll">
        <!-- Loading State -->
        <div v-if="loading" class="row justify-center q-pa-xl">
          <q-spinner-dots size="50px" color="primary" />
          <div class="text-grey-7 q-mt-md">Analyzing schedules...</div>
        </div>

        <!-- Error State -->
        <q-banner v-else-if="error" class="bg-negative text-white q-mb-md">
          <template v-slot:avatar>
            <q-icon name="error" color="white" />
          </template>
          {{ error }}
        </q-banner>

        <!-- Sync Content -->
        <template v-else-if="syncData">
          <!-- Summary Header -->
          <div class="q-mb-lg">
            <div class="text-h6 q-mb-sm">{{ syncData.copy_name }}</div>
            <div class="text-caption text-grey-7">{{ syncData.school_name }}</div>
          </div>

          <!-- Summary Stats Cards -->
          <div class="row q-gutter-md q-mb-lg">
            <q-card flat bordered class="col-12 col-sm">
              <q-card-section class="text-center">
                <div class="text-h4 text-positive">{{ syncData.summary.ok }}</div>
                <div class="text-caption text-grey-7">OK</div>
                <q-icon name="check_circle" color="positive" size="md" class="q-mt-sm" />
              </q-card-section>
            </q-card>
            <q-card flat bordered class="col-12 col-sm" :class="{'bg-orange-1': syncData.summary.missing > 0}">
              <q-card-section class="text-center">
                <div class="text-h4" :class="syncData.summary.missing > 0 ? 'text-warning' : 'text-grey-5'">
                  {{ syncData.summary.missing }}
                </div>
                <div class="text-caption text-grey-7">Missing</div>
                <q-icon name="arrow_downward" :color="syncData.summary.missing > 0 ? 'warning' : 'grey-5'" size="md" class="q-mt-sm" />
              </q-card-section>
            </q-card>
            <q-card flat bordered class="col-12 col-sm" :class="{'bg-orange-1': syncData.summary.extra > 0}">
              <q-card-section class="text-center">
                <div class="text-h4" :class="syncData.summary.extra > 0 ? 'text-warning' : 'text-grey-5'">
                  {{ syncData.summary.extra }}
                </div>
                <div class="text-caption text-grey-7">Extra</div>
                <q-icon name="arrow_upward" :color="syncData.summary.extra > 0 ? 'warning' : 'grey-5'" size="md" class="q-mt-sm" />
              </q-card-section>
            </q-card>
          </div>

          <!-- Overall Totals -->
          <div class="row q-gutter-sm q-mb-md">
            <q-chip dense icon="school" color="blue-2" text-color="blue-9">
              {{ syncData.summary.total_csts }} Subject Assignments
            </q-chip>
            <q-chip dense icon="apps" color="grey-3" text-color="grey-8">
              Expected: {{ syncData.summary.total_expected }} | Actual: {{ syncData.summary.total_actual }}
            </q-chip>
          </div>

          <!-- Quick Actions -->
          <div v-if="hasIssues" class="row q-gutter-sm q-mb-lg">
            <q-btn
              v-if="syncData.summary.missing > 0"
              color="positive"
              icon="add"
              label="Select All Missing"
              outline
              size="sm"
              @click="selectAllMissing"
            />
            <q-btn
              v-if="syncData.summary.extra > 0"
              color="negative"
              icon="remove"
              label="Select All Extra"
              outline
              size="sm"
              @click="selectAllExtra"
            />
            <q-btn
              v-if="selectedActions.length > 0"
              flat
              color="grey"
              label="Clear Selection"
              size="sm"
              @click="clearSelection"
            />
          </div>

          <!-- All OK Message -->
          <q-banner v-if="!hasIssues" class="bg-positive text-white q-mb-lg">
            <template v-slot:avatar>
              <q-icon name="check_circle" color="white" />
            </template>
            All schedules are in sync with the expected values!
          </q-banner>

          <!-- Classroom Breakdown -->
          <div v-if="hasIssues">
            <div class="text-subtitle2 text-grey-7 q-mb-sm">Classrooms with Issues</div>
            <q-list bordered separator class="rounded-borders">
              <template v-for="classroom in classroomsWithIssues" :key="classroom.classroom_id">
                <q-expansion-item
                  :label="classroom.classroom_name"
                  :caption="`${classroom.items.filter(i => i.status !== 'ok').length} issue(s)`"
                  icon="class"
                  header-class="bg-grey-1"
                >
                  <q-card>
                    <q-card-section class="q-pa-sm">
                      <q-table
                        :rows="classroom.items.filter(i => i.status !== 'ok')"
                        :columns="columns"
                        row-key="cst_id"
                        dense
                        flat
                        :rows-per-page-options="[0]"
                        hide-bottom
                      >
                        <template v-slot:body-cell-status="props">
                          <q-td :props="props">
                            <q-icon 
                              v-if="props.row.status === 'missing'" 
                              name="arrow_downward" 
                              color="warning" 
                              size="sm"
                            >
                              <q-tooltip>Missing {{ Math.abs(props.row.diff) }} record(s)</q-tooltip>
                            </q-icon>
                            <q-icon 
                              v-else-if="props.row.status === 'extra'" 
                              name="arrow_upward" 
                              color="warning" 
                              size="sm"
                            >
                              <q-tooltip>{{ props.row.diff }} extra record(s)</q-tooltip>
                            </q-icon>
                          </q-td>
                        </template>
                        <template v-slot:body-cell-action="props">
                          <q-td :props="props">
                            <q-checkbox
                              v-if="props.row.status === 'missing'"
                              v-model="selectedCstIds"
                              :val="{ cst_id: props.row.cst_id, action: 'create_missing' }"
                              color="positive"
                              dense
                            >
                              <q-tooltip>Create {{ Math.abs(props.row.diff) }} missing record(s)</q-tooltip>
                            </q-checkbox>
                            <q-checkbox
                              v-else-if="props.row.status === 'extra'"
                              v-model="selectedCstIds"
                              :val="{ cst_id: props.row.cst_id, action: 'delete_extra' }"
                              color="negative"
                              dense
                            >
                              <q-tooltip>Delete {{ props.row.diff }} extra record(s)</q-tooltip>
                            </q-checkbox>
                          </q-td>
                        </template>
                      </q-table>
                    </q-card-section>
                  </q-card>
                </q-expansion-item>
              </template>
            </q-list>
          </div>

          <!-- Apply Results -->
          <q-banner v-if="applyResult" class="q-mt-lg" :class="applyResult.success ? 'bg-positive text-white' : 'bg-negative text-white'">
            <template v-slot:avatar>
              <q-icon :name="applyResult.success ? 'check_circle' : 'error'" color="white" />
            </template>
            <div class="text-weight-bold">{{ applyResult.message }}</div>
            <div v-if="applyResult.results" class="text-caption q-mt-xs">
              <span v-if="applyResult.results.created">Created: {{ applyResult.results.created }}</span>
              <span v-if="applyResult.results.deleted" class="q-ml-md">Deleted: {{ applyResult.results.deleted }}</span>
            </div>
          </q-banner>
        </template>

        <!-- Initial State -->
        <div v-else class="text-center q-pa-xl">
          <q-icon name="sync" size="64px" color="grey-5" />
          <p class="text-h6 text-grey-7 q-mt-md">Ready to Check Sync Status</p>
          <p class="text-grey-6">Click the button below to analyze schedule synchronization</p>
          <q-btn
            color="primary"
            icon="sync"
            label="Check Sync Status"
            @click="fetchSyncStatus"
            class="q-mt-md"
          />
        </div>
      </q-card-section>

      <q-separator />

      <!-- Footer Actions -->
      <q-card-actions align="right">
        <q-btn
          v-if="syncData && hasIssues"
          flat
          icon="refresh"
          label="Refresh"
          color="primary"
          @click="fetchSyncStatus"
          :loading="loading"
        />
        <q-btn
          v-if="selectedActions.length > 0"
          color="primary"
          icon="check"
          :label="`Apply ${selectedActions.length} Fix(es)`"
          @click="applyFixes"
          :loading="applying"
        />
        <q-btn flat label="Close" color="grey" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const props = defineProps({
  modelValue: Boolean,
  copyId: Number,
  copyName: String
})

const emit = defineEmits(['update:modelValue', 'synced'])

const $q = useQuasar()

// State
const loading = ref(false)
const applying = ref(false)
const error = ref(null)
const syncData = ref(null)
const selectedCstIds = ref([])
const applyResult = ref(null)

// Table columns
const columns = [
  { name: 'subject_name', label: 'Subject', field: 'subject_name', align: 'left' },
  { name: 'teacher_name', label: 'Teacher', field: 'teacher_name', align: 'left' },
  { name: 'expected', label: 'Expected', field: 'expected', align: 'center' },
  { name: 'actual', label: 'Actual', field: 'actual', align: 'center' },
  { name: 'status', label: 'Status', align: 'center' },
  { name: 'action', label: 'Fix', align: 'center' }
]

// Computed
const hasIssues = computed(() => {
  if (!syncData.value) return false
  return syncData.value.summary.missing > 0 || syncData.value.summary.extra > 0
})

const classroomsWithIssues = computed(() => {
  if (!syncData.value) return []
  return syncData.value.by_classroom.filter(c => 
    c.items.some(i => i.status !== 'ok')
  )
})

const selectedActions = computed(() => {
  return selectedCstIds.value
})

// Methods
const fetchSyncStatus = async () => {
  if (!props.copyId) return
  
  loading.value = true
  error.value = null
  syncData.value = null
  applyResult.value = null

  try {
    const response = await axios.post(`/weekly-system/api/schedule-copies/${props.copyId}/sync-status`)
    
    if (response.data.success) {
      syncData.value = response.data.data
    } else {
      error.value = response.data.message || 'Failed to get sync status'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to get sync status'
  } finally {
    loading.value = false
  }
}

const selectAllMissing = () => {
  if (!syncData.value) return
  
  const missingItems = []
  syncData.value.by_classroom.forEach(c => {
    c.items.forEach(item => {
      if (item.status === 'missing') {
        missingItems.push({ cst_id: item.cst_id, action: 'create_missing' })
      }
    })
  })
  
  selectedCstIds.value = [...missingItems]
}

const selectAllExtra = () => {
  if (!syncData.value) return
  
  const extraItems = []
  syncData.value.by_classroom.forEach(c => {
    c.items.forEach(item => {
      if (item.status === 'extra') {
        extraItems.push({ cst_id: item.cst_id, action: 'delete_extra' })
      }
    })
  })
  
  // Add to existing selection without duplicates
  const existingCstIds = selectedCstIds.value.map(s => s.cst_id)
  const newItems = extraItems.filter(i => !existingCstIds.includes(i.cst_id))
  selectedCstIds.value = [...selectedCstIds.value, ...newItems]
}

const clearSelection = () => {
  selectedCstIds.value = []
}

const applyFixes = async () => {
  if (selectedActions.value.length === 0) return
  
  applying.value = true
  applyResult.value = null

  try {
    const response = await axios.post(`/weekly-system/api/schedule-copies/${props.copyId}/apply-sync`, {
      actions: selectedActions.value
    })

    applyResult.value = response.data

    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: response.data.message,
        position: 'top'
      })
      
      // Clear selection and refresh
      selectedCstIds.value = []
      await fetchSyncStatus()
      
      // Emit synced event
      emit('synced')
    }
  } catch (e) {
    applyResult.value = {
      success: false,
      message: e.response?.data?.message || 'Failed to apply fixes'
    }
    
    $q.notify({
      type: 'negative',
      message: e.response?.data?.message || 'Failed to apply fixes',
      position: 'top'
    })
  } finally {
    applying.value = false
  }
}

// Watch for dialog open
watch(() => props.modelValue, (newVal) => {
  if (newVal && props.copyId) {
    // Reset state when dialog opens
    syncData.value = null
    selectedCstIds.value = []
    error.value = null
    applyResult.value = null
    
    // Auto-fetch sync status
    fetchSyncStatus()
  }
})
</script>

<style scoped>
/* No custom styles needed */
</style>
