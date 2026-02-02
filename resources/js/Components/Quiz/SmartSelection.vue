<template>
  <q-card class="smart-selection rounded-xl shadow-1 bg-white">
    <!-- Header -->
    <q-card-section class="bg-green-1 text-green-8">
      <div class="row items-center justify-between">
        <div class="text-subtitle1 text-weight-bold">
          <q-icon name="psychology" class="q-mr-sm" />
          Smart Selection
        </div>
        <q-btn 
          flat 
          round 
          dense 
          icon="help_outline" 
          @click="showHelp = !showHelp"
        >
          <q-tooltip>Show help</q-tooltip>
        </q-btn>
      </div>
    </q-card-section>

    <!-- Help Section -->
    <q-slide-transition>
      <q-card-section v-show="showHelp" class="bg-blue-1 text-blue-8">
        <div class="text-caption">
          <strong>Random Selection:</strong> Randomly selects questions from the filtered pool.<br>
          <strong>Balanced Selection:</strong> Maintains balanced difficulty distribution.
        </div>
      </q-card-section>
    </q-slide-transition>

    <q-card-section class="q-pa-md">
      <!-- Selection Count Input -->
      <div class="q-mb-md">
        <q-input
          v-model.number="selectionCount"
          type="number"
          outlined
          dense
          label="Number of questions"
          :min="1"
          :max="availableQuestions.length"
          :hint="`Available: ${availableQuestions.length} questions`"
          @update:model-value="updatePreview"
        >
          <template v-slot:append>
            <q-btn
              flat
              dense
              size="sm"
              label="Recommended"
              color="primary"
              @click="useRecommendedCount"
            />
          </template>
        </q-input>
      </div>

      <!-- Algorithm Selection -->
      <div class="q-mb-md">
        <q-option-group
          v-model="selectedAlgorithm"
          :options="algorithmOptions"
          color="primary"
          inline
          @update:model-value="updatePreview"
        />
      </div>

      <!-- Algorithm Recommendation -->
      <div v-if="algorithmRecommendation" class="q-mb-md">
        <q-banner 
          :class="algorithmRecommendation.algorithm === selectedAlgorithm ? 'bg-green-1 text-green-8' : 'bg-orange-1 text-orange-8'"
          rounded
          dense
        >
          <template v-slot:avatar>
            <q-icon :name="algorithmRecommendation.algorithm === selectedAlgorithm ? 'check_circle' : 'info'" />
          </template>
          <div class="text-caption">
            <strong>Recommended:</strong> {{ algorithmRecommendation.algorithm === 'random' ? 'Random' : 'Balanced' }} selection<br>
            {{ algorithmRecommendation.reason }}
          </div>
        </q-banner>
      </div>

      <!-- Balanced Selection Options -->
      <div v-if="selectedAlgorithm === 'balanced'" class="q-mb-md">
        <q-expansion-item
          label="Custom Distribution"
          icon="tune"
          header-class="text-primary"
        >
          <q-card-section class="q-pt-none">
            <div class="text-caption q-mb-sm">Customize difficulty distribution:</div>
            <div class="row q-col-gutter-sm">
              <div class="col-4">
                <q-input
                  v-model.number="customDistribution.easy"
                  type="number"
                  outlined
                  dense
                  label="Easy"
                  :min="0"
                  :max="selectionCount"
                  @update:model-value="updateCustomDistribution"
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model.number="customDistribution.medium"
                  type="number"
                  outlined
                  dense
                  label="Medium"
                  :min="0"
                  :max="selectionCount"
                  @update:model-value="updateCustomDistribution"
                />
              </div>
              <div class="col-4">
                <q-input
                  v-model.number="customDistribution.hard"
                  type="number"
                  outlined
                  dense
                  label="Hard"
                  :min="0"
                  :max="selectionCount"
                  @update:model-value="updateCustomDistribution"
                />
              </div>
            </div>
            <div class="text-caption text-grey-6 q-mt-xs">
              Total: {{ customDistribution.easy + customDistribution.medium + customDistribution.hard }} / {{ selectionCount }}
            </div>
          </q-card-section>
        </q-expansion-item>
      </div>

      <!-- Selection Preview -->
      <div v-if="selectionPreview" class="q-mb-md">
        <q-card flat bordered class="bg-grey-1">
          <q-card-section class="q-pa-sm">
            <div class="text-caption text-weight-bold q-mb-xs">Selection Preview:</div>
            <div class="row q-col-gutter-xs text-caption">
              <div class="col-4 text-center">
                <div class="text-green-7">Easy: {{ selectionPreview.distribution.easy }}</div>
              </div>
              <div class="col-4 text-center">
                <div class="text-orange-7">Medium: {{ selectionPreview.distribution.medium }}</div>
              </div>
              <div class="col-4 text-center">
                <div class="text-red-7">Hard: {{ selectionPreview.distribution.hard }}</div>
              </div>
            </div>
            <div class="text-center q-mt-xs">
              <strong>Total: {{ selectionPreview.wouldSelect }} questions</strong>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Warnings -->
      <div v-if="validationResult.warnings.length > 0" class="q-mb-md">
        <q-banner
          v-for="warning in validationResult.warnings"
          :key="warning"
          class="bg-orange-1 text-orange-8 q-mb-xs"
          rounded
          dense
        >
          <template v-slot:avatar>
            <q-icon name="warning" />
          </template>
          <div class="text-caption">{{ warning }}</div>
        </q-banner>
      </div>

      <!-- Action Buttons -->
      <div class="row q-gutter-sm">
        <q-btn
          color="primary"
          :label="`Select ${selectionCount} Questions`"
          :disable="!canSelect"
          @click="performSelection"
          class="col"
        />
        <q-btn
          flat
          color="primary"
          label="Preview"
          @click="showPreviewDialog = true"
          :disable="!canSelect"
        />
      </div>

      <!-- Selection Feedback -->
      <div v-if="lastSelectionFeedback" class="q-mt-md">
        <q-banner class="bg-green-1 text-green-8" rounded dense>
          <template v-slot:avatar>
            <q-icon name="check_circle" />
          </template>
          <div class="text-caption">
            {{ lastSelectionFeedback }}
          </div>
        </q-banner>
      </div>
    </q-card-section>

    <!-- Preview Dialog -->
    <q-dialog v-model="showPreviewDialog">
      <q-card style="min-width: 400px">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">Selection Preview</div>
        </q-card-section>

        <q-card-section>
          <div v-if="previewQuestions.length > 0">
            <div class="text-subtitle2 q-mb-md">
              {{ selectedAlgorithm === 'random' ? 'Random' : 'Balanced' }} selection would choose:
            </div>
            
            <!-- Preview Statistics -->
            <q-card flat bordered class="q-mb-md">
              <q-card-section class="q-pa-sm">
                <div class="row q-col-gutter-sm text-center">
                  <div class="col-4">
                    <div class="text-green-7 text-weight-bold">{{ previewStats.difficultyDistribution.easy }}</div>
                    <div class="text-caption">Easy</div>
                  </div>
                  <div class="col-4">
                    <div class="text-orange-7 text-weight-bold">{{ previewStats.difficultyDistribution.medium }}</div>
                    <div class="text-caption">Medium</div>
                  </div>
                  <div class="col-4">
                    <div class="text-red-7 text-weight-bold">{{ previewStats.difficultyDistribution.hard }}</div>
                    <div class="text-caption">Hard</div>
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- Preview Questions List -->
            <div class="text-caption text-grey-6 q-mb-sm">Sample questions:</div>
            <div style="max-height: 200px; overflow-y: auto;">
              <div
                v-for="(question, index) in previewQuestions.slice(0, 5)"
                :key="question.id"
                class="q-pa-xs border-bottom"
              >
                <div class="text-caption">
                  <q-badge
                    :color="getDifficultyColor(question.difficulty)"
                    :label="question.difficulty"
                    class="q-mr-xs"
                  />
                  {{ question.question_text.substring(0, 60) }}...
                </div>
              </div>
              <div v-if="previewQuestions.length > 5" class="text-caption text-grey-6 q-pa-xs">
                ... and {{ previewQuestions.length - 5 }} more questions
              </div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
          <q-btn 
            label="Select These Questions" 
            color="primary" 
            @click="confirmPreviewSelection"
            v-close-popup
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import type { QuizQuestion, SmartSelectionCriteria, QuestionPoolStats } from '@/types/quiz-builder'
import { useSmartSelection } from '@/composables/useSmartSelection'

// Props
interface Props {
  availableQuestions: QuizQuestion[]
  currentFilters: any
}

const props = defineProps<Props>()

// Emits
interface Emits {
  (e: 'questions-selected', questions: QuizQuestion[]): void
  (e: 'selection-feedback', feedback: string): void
}

const emit = defineEmits<Emits>()

// Composables
const {
  calculatePoolStats,
  smartSelection,
  validateSelectionCriteria,
  getRecommendedCount,
  getAlgorithmRecommendations,
  previewSelection
} = useSmartSelection()

// Reactive state
const showHelp = ref(false)
const showPreviewDialog = ref(false)
const selectionCount = ref(5)
const selectedAlgorithm = ref<'random' | 'balanced'>('random')
const customDistribution = ref({ easy: 0, medium: 0, hard: 0 })
const useCustomDistribution = ref(false)
const lastSelectionFeedback = ref('')
const previewQuestions = ref<QuizQuestion[]>([])
const previewStats = ref<QuestionPoolStats>({
  totalQuestions: 0,
  difficultyDistribution: { easy: 0, medium: 0, hard: 0 },
  topicDistribution: {},
  authorDistribution: {},
  bloomsDistribution: {}
})

// Computed
const algorithmOptions = computed(() => [
  { label: 'Random', value: 'random' },
  { label: 'Balanced', value: 'balanced' }
])

const poolStats = computed(() => calculatePoolStats(props.availableQuestions))

const algorithmRecommendation = computed(() => {
  if (props.availableQuestions.length === 0) return null
  return getAlgorithmRecommendations(poolStats.value)
})

const selectionCriteria = computed((): SmartSelectionCriteria => ({
  count: selectionCount.value,
  algorithm: selectedAlgorithm.value,
  filters: props.currentFilters,
  targetDistribution: useCustomDistribution.value ? customDistribution.value : undefined
}))

const validationResult = computed(() => {
  return validateSelectionCriteria(selectionCriteria.value, props.availableQuestions)
})

const canSelect = computed(() => {
  return validationResult.value.isValid && 
         selectionCount.value > 0 && 
         selectionCount.value <= props.availableQuestions.length
})

const selectionPreview = computed(() => {
  if (!canSelect.value) return null
  return previewSelection(props.availableQuestions, selectionCriteria.value)
})

// Methods
const useRecommendedCount = () => {
  selectionCount.value = getRecommendedCount(props.availableQuestions.length)
  updatePreview()
}

const updateCustomDistribution = () => {
  useCustomDistribution.value = true
  updatePreview()
}

const updatePreview = () => {
  if (!canSelect.value) return
  
  // Generate preview selection
  const result = smartSelection(props.availableQuestions, selectionCriteria.value)
  previewQuestions.value = result.selected
  previewStats.value = result.stats
}

const performSelection = () => {
  if (!canSelect.value) return

  const result = smartSelection(props.availableQuestions, selectionCriteria.value)
  
  // Generate feedback message
  const algorithm = selectedAlgorithm.value === 'random' ? 'Random' : 'Balanced'
  const feedback = `${algorithm} selection added ${result.selected.length} questions (Easy: ${result.stats.difficultyDistribution.easy}, Medium: ${result.stats.difficultyDistribution.medium}, Hard: ${result.stats.difficultyDistribution.hard})`
  
  lastSelectionFeedback.value = feedback
  
  // Clear feedback after 5 seconds
  setTimeout(() => {
    lastSelectionFeedback.value = ''
  }, 5000)

  emit('questions-selected', result.selected)
  emit('selection-feedback', feedback)
}

const confirmPreviewSelection = () => {
  emit('questions-selected', previewQuestions.value)
  
  const algorithm = selectedAlgorithm.value === 'random' ? 'Random' : 'Balanced'
  const feedback = `${algorithm} selection added ${previewQuestions.value.length} questions from preview`
  lastSelectionFeedback.value = feedback
  emit('selection-feedback', feedback)
  
  setTimeout(() => {
    lastSelectionFeedback.value = ''
  }, 5000)
}

const getDifficultyColor = (difficulty: string): string => {
  switch (difficulty) {
    case 'Easy': return 'green'
    case 'Medium': return 'orange'
    case 'Hard': return 'red'
    default: return 'grey'
  }
}

// Watchers
watch(() => props.availableQuestions.length, (newLength) => {
  if (selectionCount.value > newLength) {
    selectionCount.value = Math.min(selectionCount.value, newLength)
  }
  updatePreview()
})

watch(selectedAlgorithm, (newAlgorithm) => {
  if (newAlgorithm === 'balanced' && !useCustomDistribution.value) {
    // Reset custom distribution when switching to balanced
    const defaultEasy = Math.floor(selectionCount.value * 0.33)
    const defaultMedium = Math.floor(selectionCount.value * 0.34)
    const defaultHard = Math.floor(selectionCount.value * 0.33)
    
    // Adjust for exact count
    const total = defaultEasy + defaultMedium + defaultHard
    const adjustment = selectionCount.value - total
    
    customDistribution.value = {
      easy: defaultEasy,
      medium: defaultMedium + adjustment,
      hard: defaultHard
    }
  }
  updatePreview()
})

// Lifecycle
onMounted(() => {
  if (props.availableQuestions.length > 0) {
    selectionCount.value = getRecommendedCount(props.availableQuestions.length)
    updatePreview()
  }
})
</script>

<style scoped lang="scss">
.smart-selection {
  .border-bottom {
    border-bottom: 1px solid #e0e0e0;
  }
}
</style>