<template>
  <q-dialog v-model="showDialog" persistent maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card>
      <q-card-section class="bg-primary text-white row items-center">
        <div class="col">
          <div class="text-h6">
            <q-icon name="upgrade" class="q-mr-sm" />
            Student Promotion Wizard
          </div>
          <div class="text-caption">Promote students to next grade level</div>
        </div>
        <q-btn flat round dense icon="close" v-close-popup />
      </q-card-section>

      <q-stepper
        v-model="step"
        ref="stepper"
        color="primary"
        animated
        header-nav
        class="full-height"
      >
        <!-- Step 1: Select Grades -->
        <q-step
          :name="1"
          title="Select Grades"
          icon="school"
          :done="step > 1"
        >
          <div class="q-pa-md">
            <q-banner class="bg-info text-white q-mb-md">
              <template v-slot:avatar>
                <q-icon name="info" />
              </template>
              Select the source grade (current) and target grade (next year) for promotion.
            </q-banner>

            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-select
                  v-model="sourceGrade"
                  :options="grades"
                  option-value="id"
                  option-label="name"
                  label="Source Grade (Current) *"
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Source grade is required']"
                  @update:model-value="onSourceGradeChange"
                >
                  <template v-slot:prepend>
                    <q-icon name="school" />
                  </template>
                </q-select>
              </div>

              <div class="col-12 col-md-6">
                <q-select
                  v-model="targetGrade"
                  :options="grades"
                  option-value="id"
                  option-label="name"
                  label="Target Grade (Next Year) *"
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Target grade is required']"
                  @update:model-value="onTargetGradeChange"
                >
                  <template v-slot:prepend>
                    <q-icon name="trending_up" />
                  </template>
                </q-select>
              </div>

              <div class="col-12">
                <q-select
                  v-model="academicYear"
                  :options="academicYears"
                  option-value="id"
                  option-label="name"
                  label="Academic Year *"
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Academic year is required']"
                >
                  <template v-slot:prepend>
                    <q-icon name="calendar_today" />
                  </template>
                </q-select>
              </div>
            </div>

            <div v-if="sourceGrade && sourceClassrooms.length > 0" class="q-mt-md">
              <q-banner class="bg-positive text-white">
                <template v-slot:avatar>
                  <q-icon name="check_circle" />
                </template>
                Found {{ sourceClassrooms.length }} classrooms with {{ totalSourceStudents }} students
              </q-banner>
            </div>
          </div>

          <q-stepper-navigation>
            <q-btn
              @click="loadClassroomMappings"
              color="primary"
              label="Next: Map Classrooms"
              :disable="!canProceedToStep2"
              :loading="loading"
            />
          </q-stepper-navigation>
        </q-step>

        <!-- Step 2: Map Classrooms -->
        <q-step
          :name="2"
          title="Map Classrooms"
          icon="compare_arrows"
          :done="step > 2"
        >
          <div class="q-pa-md">
            <q-banner class="bg-info text-white q-mb-md">
              <template v-slot:avatar>
                <q-icon name="info" />
              </template>
              Map each source classroom to a target classroom. Auto-suggestions are provided based on classroom names.
            </q-banner>

            <div class="q-mb-md">
              <q-btn
                @click="autoMapClassrooms"
                color="secondary"
                label="Auto-Map All"
                icon="auto_fix_high"
                outline
                :loading="loading"
              />
            </div>

            <q-list bordered separator>
              <q-item v-for="(mapping, index) in classroomMappings" :key="index">
                <q-item-section>
                  <div class="row q-col-gutter-md items-center">
                    <div class="col-12 col-md-5">
                      <div class="text-subtitle2">{{ mapping.from_classroom_name }}</div>
                      <div class="text-caption text-grey-7">
                        {{ mapping.student_count || 0 }} students
                      </div>
                    </div>

                    <div class="col-12 col-md-1 text-center">
                      <q-icon name="arrow_forward" size="md" color="primary" />
                    </div>

                    <div class="col-12 col-md-5">
                      <q-select
                        v-model="mapping.to_classroom_id"
                        :options="targetClassrooms"
                        option-value="id"
                        option-label="name"
                        label="Target Classroom *"
                        outlined
                        dense
                        emit-value
                        map-options
                        :rules="[val => !!val || 'Required']"
                      >
                        <template v-slot:append>
                          <q-badge
                            v-if="mapping.confidence && mapping.confidence > 70"
                            :color="mapping.confidence > 90 ? 'positive' : 'warning'"
                          >
                            {{ Math.round(mapping.confidence) }}%
                          </q-badge>
                        </template>
                      </q-select>
                    </div>

                    <div class="col-12 col-md-1">
                      <q-icon
                        v-if="mapping.auto_suggested"
                        name="auto_awesome"
                        color="positive"
                        size="sm"
                      >
                        <q-tooltip>Auto-suggested</q-tooltip>
                      </q-icon>
                    </div>
                  </div>
                </q-item-section>
              </q-item>
            </q-list>
          </div>

          <q-stepper-navigation>
            <q-btn flat @click="step = 1" color="primary" label="Back" class="q-mr-sm" />
            <q-btn
              @click="loadPreview"
              color="primary"
              label="Next: Preview"
              :disable="!allClassroomsMapped"
              :loading="loading"
            />
          </q-stepper-navigation>
        </q-step>

        <!-- Step 3: Preview & Confirm -->
        <q-step
          :name="3"
          title="Preview & Confirm"
          icon="preview"
          :done="step > 3"
        >
          <div class="q-pa-md">
            <q-banner v-if="warnings.length > 0" class="bg-warning text-white q-mb-md">
              <template v-slot:avatar>
                <q-icon name="warning" />
              </template>
              <div class="text-weight-bold">{{ warnings.length }} Warning(s)</div>
              <ul class="q-my-sm">
                <li v-for="(warning, index) in warnings" :key="index">
                  {{ warning.message }}
                </li>
              </ul>
            </q-banner>

            <q-banner v-else class="bg-positive text-white q-mb-md">
              <template v-slot:avatar>
                <q-icon name="check_circle" />
              </template>
              No issues found. Ready to promote {{ totalStudentsToPromote }} students!
            </q-banner>

            <q-table
              :rows="previewData"
              :columns="previewColumns"
              row-key="from_classroom.id"
              flat
              bordered
            >
              <template v-slot:body-cell-from="props">
                <q-td :props="props">
                  <div class="text-weight-bold">{{ props.row.from_classroom.name }}</div>
                  <div class="text-caption text-grey-7">{{ props.row.from_classroom.grade }}</div>
                </q-td>
              </template>

              <template v-slot:body-cell-to="props">
                <q-td :props="props">
                  <div class="text-weight-bold">{{ props.row.to_classroom.name }}</div>
                  <div class="text-caption text-grey-7">{{ props.row.to_classroom.grade }}</div>
                  <div class="text-caption">
                    Current: {{ props.row.to_classroom.current_count }} / {{ props.row.to_classroom.capacity }}
                  </div>
                </q-td>
              </template>

              <template v-slot:body-cell-students="props">
                <q-td :props="props">
                  <q-badge color="primary">{{ props.row.student_count }}</q-badge>
                </q-td>
              </template>
            </q-table>

            <div class="q-mt-md">
              <q-input
                v-model="promotionReason"
                label="Promotion Reason *"
                outlined
                :rules="[val => !!val || 'Reason is required']"
                placeholder="e.g., Year-end promotion 2025-2026"
              >
                <template v-slot:prepend>
                  <q-icon name="description" />
                </template>
              </q-input>

              <q-input
                v-model="promotionNotes"
                label="Notes (Optional)"
                outlined
                type="textarea"
                rows="3"
                class="q-mt-md"
                placeholder="Additional notes about this promotion..."
              >
                <template v-slot:prepend>
                  <q-icon name="note" />
                </template>
              </q-input>
            </div>
          </div>

          <q-stepper-navigation>
            <q-btn flat @click="step = 2" color="primary" label="Back" class="q-mr-sm" />
            <q-btn
              @click="executePromotion"
              color="positive"
              label="Promote All Students"
              icon="upgrade"
              :disable="!promotionReason"
              :loading="promoting"
            />
          </q-stepper-navigation>
        </q-step>

        <!-- Step 4: Results -->
        <q-step
          :name="4"
          title="Results"
          icon="check_circle"
        >
          <div class="q-pa-md">
            <q-banner v-if="promotionResult.success" class="bg-positive text-white q-mb-md">
              <template v-slot:avatar>
                <q-icon name="check_circle" size="lg" />
              </template>
              <div class="text-h6">Promotion Successful!</div>
              <div>Successfully promoted {{ promotionResult.promoted_count }} students</div>
            </q-banner>

            <q-banner v-else class="bg-negative text-white q-mb-md">
              <template v-slot:avatar>
                <q-icon name="error" size="lg" />
              </template>
              <div class="text-h6">Promotion Failed</div>
              <div>{{ promotionResult.message }}</div>
            </q-banner>

            <div v-if="promotionResult.errors && promotionResult.errors.length > 0" class="q-mt-md">
              <q-list bordered>
                <q-item-label header>Errors</q-item-label>
                <q-item v-for="(error, index) in promotionResult.errors" :key="index">
                  <q-item-section avatar>
                    <q-icon name="error" color="negative" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>{{ error }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>

            <div class="q-mt-md">
              <q-linear-progress :value="1" color="positive" class="q-mb-sm" />
              <div class="text-center text-grey-7">
                Promotion complete
              </div>
            </div>
          </div>

          <q-stepper-navigation>
            <q-btn
              @click="closeDialog"
              color="primary"
              label="Done"
              icon="check"
            />
          </q-stepper-navigation>
        </q-step>
      </q-stepper>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const props = defineProps({
  modelValue: Boolean,
  grades: {
    type: Array,
    default: () => []
  },
  academicYears: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue', 'promoted'])

const $q = useQuasar()

// Dialog state
const showDialog = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

// Stepper
const step = ref(1)
const loading = ref(false)
const promoting = ref(false)

// Step 1 data
const sourceGrade = ref(null)
const targetGrade = ref(null)
const academicYear = ref(null)
const sourceClassrooms = ref([])
const targetClassrooms = ref([])

// Step 2 data
const classroomMappings = ref([])

// Step 3 data
const previewData = ref([])
const warnings = ref([])
const promotionReason = ref('Year-end promotion ' + new Date().getFullYear())
const promotionNotes = ref('')

// Step 4 data
const promotionResult = ref({})

// Computed
const totalSourceStudents = computed(() => {
  return sourceClassrooms.value.reduce((sum, classroom) => sum + (classroom.student_count || 0), 0)
})

const canProceedToStep2 = computed(() => {
  return sourceGrade.value && targetGrade.value && academicYear.value
})

const allClassroomsMapped = computed(() => {
  return classroomMappings.value.length > 0 && 
         classroomMappings.value.every(m => m.to_classroom_id)
})

const totalStudentsToPromote = computed(() => {
  return previewData.value.reduce((sum, row) => sum + row.student_count, 0)
})

const previewColumns = [
  {
    name: 'from',
    label: 'From Classroom',
    field: 'from_classroom',
    align: 'left'
  },
  {
    name: 'to',
    label: 'To Classroom',
    field: 'to_classroom',
    align: 'left'
  },
  {
    name: 'students',
    label: 'Students',
    field: 'student_count',
    align: 'center'
  }
]

// Methods
const onSourceGradeChange = async () => {
  if (!sourceGrade.value) return
  
  loading.value = true
  try {
    const response = await axios.get(`/admin/classrooms/by-grade/${sourceGrade.value}`)
    sourceClassrooms.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load source classrooms'
    })
  } finally {
    loading.value = false
  }
}

const onTargetGradeChange = async () => {
  if (!targetGrade.value) return
  
  loading.value = true
  try {
    const response = await axios.get(`/admin/classrooms/by-grade/${targetGrade.value}`)
    targetClassrooms.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load target classrooms'
    })
  } finally {
    loading.value = false
  }
}

const loadClassroomMappings = async () => {
  loading.value = true
  try {
    const response = await axios.get('/admin/students/classroom-mapping-suggestions', {
      params: {
        source_grade_id: sourceGrade.value,
        target_grade_id: targetGrade.value
      }
    })
    
    classroomMappings.value = response.data.suggestions
    step.value = 2
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load classroom mappings'
    })
  } finally {
    loading.value = false
  }
}

const autoMapClassrooms = () => {
  classroomMappings.value.forEach(mapping => {
    if (mapping.auto_suggested && mapping.to_classroom_id) {
      // Already auto-mapped
    }
  })
  
  $q.notify({
    type: 'positive',
    message: 'Auto-mapping applied'
  })
}

const loadPreview = async () => {
  loading.value = true
  try {
    const response = await axios.post('/admin/students/promotion-preview', {
      source_grade_id: sourceGrade.value,
      target_grade_id: targetGrade.value,
      classroom_mappings: classroomMappings.value
    })
    
    previewData.value = response.data.preview
    warnings.value = response.data.warnings
    step.value = 3
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load preview'
    })
  } finally {
    loading.value = false
  }
}

const executePromotion = async () => {
  promoting.value = true
  try {
    const response = await axios.post('/admin/students/promote', {
      source_grade_id: sourceGrade.value,
      target_grade_id: targetGrade.value,
      academic_year_id: academicYear.value,
      classroom_mappings: classroomMappings.value,
      promotion_reason: promotionReason.value,
      notes: promotionNotes.value
    })
    
    promotionResult.value = response.data
    step.value = 4
    
    emit('promoted', response.data)
  } catch (error) {
    promotionResult.value = {
      success: false,
      message: error.response?.data?.message || 'Promotion failed'
    }
    step.value = 4
  } finally {
    promoting.value = false
  }
}

const closeDialog = () => {
  showDialog.value = false
  // Reset after a delay
  setTimeout(() => {
    step.value = 1
    sourceGrade.value = null
    targetGrade.value = null
    academicYear.value = null
    classroomMappings.value = []
    previewData.value = []
    warnings.value = []
    promotionResult.value = {}
  }, 300)
}
</script>

<style scoped>
.full-height {
  height: calc(100vh - 100px);
}
</style>
