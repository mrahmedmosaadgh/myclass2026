<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" position="right" maximized>
    <q-card style="width: 900px; max-width: 90vw" class="column">
      <!-- Header -->
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <q-icon name="shuffle" size="sm" class="q-mr-sm" />
        <div class="text-h6">Random Fill Missing Periods</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <!-- Main Content -->
      <q-card-section class="col scroll">
        <q-stepper v-model="step" vertical color="primary" animated>
          <!-- Step 1: Generate Preview -->
          <q-step :name="1" title="Generate Preview" icon="preview" :done="step > 1">
            <div class="text-body2 q-mb-md">
              Click the button below to generate a preview of random assignments for all empty periods in {{ classroomName }}.
            </div>
            
            <q-banner v-if="!generatedPreview" class="bg-blue-1 q-mb-md">
              <template v-slot:avatar>
                <q-icon name="info" color="blue" />
              </template>
              <div>
                The system will randomly assign available subject-teacher combinations to empty periods, avoiding conflicts where possible.
              </div>
            </q-banner>

            <div class="q-mt-md">
              <q-btn 
                color="primary" 
                icon="shuffle" 
                label="Generate Preview" 
                @click="generatePreview"
                :loading="generating"
                class="q-mr-sm"
              />
              <q-btn 
                v-if="generatedPreview"
                flat 
                color="primary" 
                label="Next: Review" 
                @click="step = 2"
              />
            </div>
          </q-step>

          <!-- Step 2: Review Preview -->
          <q-step :name="2" title="Review Assignments" icon="checklist" :done="step > 2">
            <div class="text-body2 q-mb-md">
              Review the proposed random assignments below.
            </div>

            <div v-if="previewData" class="q-mb-md">
              <!-- Summary Stats -->
              <div class="row q-gutter-sm q-mb-md">
                <q-chip icon="apps" color="grey-3" text-color="grey-9">
                  {{ previewData.summary.total_empty }} empty slots
                </q-chip>
                <q-chip icon="check_circle" color="green-2" text-color="green-9">
                  {{ previewData.summary.fillable }} can fill without conflicts
                </q-chip>
                <q-chip v-if="previewData.summary.conflicts > 0" icon="warning" color="orange-2" text-color="orange-9">
                  {{ previewData.summary.conflicts }} have conflicts
                </q-chip>
                <q-chip v-if="previewData.summary.unfillable > 0" icon="block" color="red-2" text-color="red-9">
                  {{ previewData.summary.unfillable }} unfillable
                </q-chip>
              </div>

              <!-- Force Fill Option -->
              <q-checkbox 
                v-if="previewData.summary.conflicts > 0"
                v-model="forceConflicts" 
                label="Force fill even with conflicts (teachers will be assigned to multiple classrooms)"
                class="q-mb-md"
              />

              <!-- Preview Tables -->
              <q-tabs v-model="tab" dense class="text-grey" active-color="primary" indicator-color="primary" align="left">
                <q-tab name="all" :label="`All (${allAssignments.length})`" />
                <q-tab v-if="previewData.filled.length > 0" name="clean" :label="`Clean (${previewData.filled.length})`" />
                <q-tab v-if="previewData.conflicts.length > 0" name="conflicts" :label="`Conflicts (${previewData.conflicts.length})`" />
                <q-tab v-if="previewData.unfillable.length > 0" name="unfillable" :label="`Unfillable (${previewData.unfillable.length})`" />
              </q-tabs>

              <q-separator class="q-mb-md" />

              <q-tab-panels v-model="tab" animated>
                <!-- All Assignments -->
                <q-tab-panel name="all">
                  <q-table
                    :rows="allAssignments"
                    :columns="previewColumns"
                    row-key="index"
                    flat
                    bordered
                    dense
                    :rows-per-page-options="[0]"
                  >
                    <template v-slot:body-cell-day="props">
                      <q-td :props="props">
                        <q-badge :color="getDayColor(props.row.day)">
                          {{ getDayName(props.row.day) }}
                        </q-badge>
                      </q-td>
                    </template>
                    <template v-slot:body-cell-period="props">
                      <q-td :props="props">
                        <strong>Period {{ props.row.period }}</strong>
                      </q-td>
                    </template>
                    <template v-slot:body-cell-status="props">
                      <q-td :props="props">
                        <q-icon 
                          v-if="props.row.has_conflict" 
                          name="warning" 
                          color="orange" 
                          size="sm"
                        >
                          <q-tooltip>{{ props.row.conflict_details?.message }}</q-tooltip>
                        </q-icon>
                        <q-icon 
                          v-else 
                          name="check_circle" 
                          color="green" 
                          size="sm"
                        >
                          <q-tooltip>No conflicts</q-tooltip>
                        </q-icon>
                      </q-td>
                    </template>
                  </q-table>
                </q-tab-panel>

                <!-- Clean Assignments -->
                <q-tab-panel name="clean">
                  <q-table
                    :rows="previewData.filled"
                    :columns="previewColumns"
                    row-key="index"
                    flat
                    bordered
                    dense
                    :rows-per-page-options="[0]"
                  >
                    <template v-slot:body-cell-day="props">
                      <q-td :props="props">
                        <q-badge :color="getDayColor(props.row.day)">
                          {{ getDayName(props.row.day) }}
                        </q-badge>
                      </q-td>
                    </template>
                    <template v-slot:body-cell-period="props">
                      <q-td :props="props">
                        <strong>Period {{ props.row.period }}</strong>
                      </q-td>
                    </template>
                    <template v-slot:body-cell-status="props">
                      <q-td :props="props">
                        <q-icon name="check_circle" color="green" size="sm">
                          <q-tooltip>No conflicts</q-tooltip>
                        </q-icon>
                      </q-td>
                    </template>
                  </q-table>
                </q-tab-panel>

                <!-- Conflict Assignments -->
                <q-tab-panel name="conflicts">
                  <q-table
                    :rows="previewData.conflicts"
                    :columns="conflictColumns"
                    row-key="index"
                    flat
                    bordered
                    dense
                    :rows-per-page-options="[0]"
                  >
                    <template v-slot:body-cell-day="props">
                      <q-td :props="props">
                        <q-badge :color="getDayColor(props.row.day)">
                          {{ getDayName(props.row.day) }}
                        </q-badge>
                      </q-td>
                    </template>
                    <template v-slot:body-cell-period="props">
                      <q-td :props="props">
                        <strong>Period {{ props.row.period }}</strong>
                      </q-td>
                    </template>
                    <template v-slot:body-cell-conflict="props">
                      <q-td :props="props">
                        <div class="text-caption text-orange-9">
                          <q-icon name="warning" color="orange" size="xs" class="q-mr-xs" />
                          {{ props.row.conflict_details?.message }}
                        </div>
                        <div class="text-caption text-grey-7">
                          Busy in: {{ props.row.conflict_details?.busy_in_classroom }}
                        </div>
                      </q-td>
                    </template>
                  </q-table>
                </q-tab-panel>

                <!-- Unfillable Slots -->
                <q-tab-panel name="unfillable">
                  <q-table
                    :rows="previewData.unfillable"
                    :columns="unfillableColumns"
                    row-key="index"
                    flat
                    bordered
                    dense
                    :rows-per-page-options="[0]"
                  >
                    <template v-slot:body-cell-day="props">
                      <q-td :props="props">
                        <q-badge :color="getDayColor(props.row.day)">
                          {{ getDayName(props.row.day) }}
                        </q-badge>
                      </q-td>
                    </template>
                    <template v-slot:body-cell-period="props">
                      <q-td :props="props">
                        <strong>Period {{ props.row.period }}</strong>
                      </q-td>
                    </template>
                  </q-table>
                </q-tab-panel>
              </q-tab-panels>
            </div>

            <div class="q-mt-md">
              <q-btn 
                flat 
                color="primary" 
                label="Back" 
                @click="step = 1"
                class="q-mr-sm"
              />
              <q-btn 
                color="primary" 
                label="Next: Apply" 
                @click="step = 3"
                :disable="allAssignments.length === 0"
              />
            </div>
          </q-step>

          <!-- Step 3: Apply Changes -->
          <q-step :name="3" title="Apply Changes" icon="save">
            <div class="text-body2 q-mb-md">
              Ready to apply the random fill assignments?
            </div>

            <q-banner class="bg-blue-1 q-mb-md">
              <template v-slot:avatar>
                <q-icon name="info" color="blue" />
              </template>
              <div>
                <strong>{{ allAssignments.length }} assignments</strong> will be created/updated in your timetable.
                <div v-if="forceConflicts && previewData?.summary.conflicts > 0" class="q-mt-xs text-orange-9">
                  <q-icon name="warning" size="xs" class="q-mr-xs" />
                  Force fill is enabled. {{ previewData.summary.conflicts }} conflicting assignments will be created.
                </div>
              </div>
            </q-banner>

            <div class="q-mt-md">
              <q-btn 
                flat 
                color="primary" 
                label="Back" 
                @click="step = 2"
                class="q-mr-sm"
                :disable="applying"
              />
              <q-btn 
                color="positive" 
                icon="check" 
                label="Apply Random Fill" 
                @click="applyRandomFill"
                :loading="applying"
              />
            </div>

            <!-- Apply Results -->
            <q-banner v-if="applyResult" class="q-mt-md" :class="applyResult.success ? 'bg-positive text-white' : 'bg-negative text-white'">
              <template v-slot:avatar>
                <q-icon :name="applyResult.success ? 'check_circle' : 'error'" color="white" />
              </template>
              <div>
                <div class="text-weight-bold">{{ applyResult.message }}</div>
                <div v-if="applyResult.summary" class="text-caption q-mt-xs">
                  <div v-if="applyResult.summary.created">Created: {{ applyResult.summary.created }}</div>
                  <div v-if="applyResult.summary.updated">Updated: {{ applyResult.summary.updated }}</div>
                  <div v-if="applyResult.summary.skipped">Skipped: {{ applyResult.summary.skipped }}</div>
                </div>
              </div>
            </q-banner>
          </q-step>
        </q-stepper>
      </q-card-section>

      <q-separator />

      <!-- Footer Actions -->
      <q-card-actions align="right">
        <q-btn flat label="Close" color="primary" v-close-popup />
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
  classroomId: Number,
  classroomName: String
})

const emit = defineEmits(['update:modelValue', 'applied'])

const $q = useQuasar()

// State
const step = ref(1)
const generating = ref(false)
const generatedPreview = ref(false)
const previewData = ref(null)
const forceConflicts = ref(false)
const tab = ref('all')
const applying = ref(false)
const applyResult = ref(null)

// Days mapping
const dayNames = ['', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']

// All assignments to apply (filled + conflicts if force enabled)
const allAssignments = computed(() => {
  if (!previewData.value) return []
  
  const assignments = [...previewData.value.filled]
  
  if (forceConflicts.value) {
    assignments.push(...previewData.value.conflicts)
  }
  
  return assignments
})

// Preview table columns
const previewColumns = [
  { name: 'day', label: 'Day', field: 'day', align: 'left' },
  { name: 'period', label: 'Period', field: 'period', align: 'center' },
  { name: 'subject_name', label: 'Subject', field: 'subject_name', align: 'left' },
  { name: 'teacher_name', label: 'Teacher', field: 'teacher_name', align: 'left' },
  { name: 'status', label: 'Status', align: 'center' }
]

const conflictColumns = [
  { name: 'day', label: 'Day', field: 'day', align: 'left' },
  { name: 'period', label: 'Period', field: 'period', align: 'center' },
  { name: 'subject_name', label: 'Subject', field: 'subject_name', align: 'left' },
  { name: 'teacher_name', label: 'Teacher', field: 'teacher_name', align: 'left' },
  { name: 'conflict', label: 'Conflict', align: 'left' }
]

const unfillableColumns = [
  { name: 'day', label: 'Day', field: 'day', align: 'left' },
  { name: 'period', label: 'Period', field: 'period', align: 'center' },
  { name: 'reason', label: 'Reason', field: 'reason', align: 'left' }
]

// Methods
const getDayName = (dayNumber) => {
  return dayNames[dayNumber] || `Day ${dayNumber}`
}

const getDayColor = (dayNumber) => {
  const colors = ['', 'red-4', 'blue-4', 'green-4', 'orange-4', 'purple-4', 'teal-4', 'pink-4']
  return colors[dayNumber] || 'grey-4'
}

const generatePreview = async () => {
  generating.value = true
  generatedPreview.value = false
  previewData.value = null

  try {
    const response = await axios.post('/weekly-system/api/random-fill/preview', {
      classroom_id: props.classroomId
    })

    if (response.data.success) {
      previewData.value = response.data.data
      generatedPreview.value = true
      
      $q.notify({
        type: 'positive',
        message: `Preview generated: ${response.data.data.summary.total_empty} empty slots found`,
        position: 'top'
      })

      // Auto-advance if we have results
      if (response.data.data.summary.total_empty > 0) {
        setTimeout(() => {
          step.value = 2
        }, 500)
      }
    } else {
      $q.notify({
        type: 'negative',
        message: response.data.message || 'Failed to generate preview',
        position: 'top'
      })
    }
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to generate preview',
      position: 'top'
    })
  } finally {
    generating.value = false
  }
}

const applyRandomFill = async () => {
  applying.value = true
  applyResult.value = null

  try {
    const response = await axios.post('/weekly-system/api/random-fill/apply', {
      classroom_id: props.classroomId,
      assignments: allAssignments.value,
      force_conflicts: forceConflicts.value
    })

    applyResult.value = response.data

    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: response.data.message,
        position: 'top'
      })
      
      // Emit applied event to refresh parent
      emit('applied')
    }
  } catch (error) {
    applyResult.value = {
      success: false,
      message: error.response?.data?.message || 'Failed to apply random fill',
      errors: error.response?.data?.errors
    }
    
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to apply random fill',
      position: 'top'
    })
  } finally {
    applying.value = false
  }
}

// Reset when dialog opens
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    step.value = 1
    generating.value = false
    generatedPreview.value = false
    previewData.value = null
    forceConflicts.value = false
    tab.value = 'all'
    applying.value = false
    applyResult.value = null
  }
})
</script>

<style scoped>
/* No custom styles needed */
</style>
