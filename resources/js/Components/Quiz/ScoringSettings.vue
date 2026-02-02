<template>
  <q-card class="scoring-settings rounded-xl shadow-2 bg-white">
    <q-card-section class="bg-purple-1 text-purple-9">
      <div class="text-h6 text-weight-bold">
        <q-icon name="calculate" class="q-mr-sm" />
        Scoring Settings
      </div>
    </q-card-section>

    <q-card-section class="q-gutter-y-md">
      <!-- Default Points Configuration -->
      <div class="scoring-settings__section">
        <div class="text-subtitle2 text-weight-bold text-grey-8 q-mb-sm">
          Default Points by Difficulty
        </div>
        
        <div class="row q-col-gutter-sm">
          <div class="col-4">
            <q-input
              v-model.number="localScoringConfig.defaultPoints.easy"
              outlined
              dense
              type="number"
              label="Easy"
              min="1"
              max="10"
              bg-color="green-1"
              @update:model-value="updateDefaultPoints"
            >
              <template v-slot:prepend>
                <q-icon name="sentiment_satisfied" color="positive" />
              </template>
            </q-input>
          </div>
          
          <div class="col-4">
            <q-input
              v-model.number="localScoringConfig.defaultPoints.medium"
              outlined
              dense
              type="number"
              label="Medium"
              min="1"
              max="10"
              bg-color="orange-1"
              @update:model-value="updateDefaultPoints"
            >
              <template v-slot:prepend>
                <q-icon name="sentiment_neutral" color="warning" />
              </template>
            </q-input>
          </div>
          
          <div class="col-4">
            <q-input
              v-model.number="localScoringConfig.defaultPoints.hard"
              outlined
              dense
              type="number"
              label="Hard"
              min="1"
              max="10"
              bg-color="red-1"
              @update:model-value="updateDefaultPoints"
            >
              <template v-slot:prepend>
                <q-icon name="sentiment_dissatisfied" color="negative" />
              </template>
            </q-input>
          </div>
        </div>
      </div>

      <q-separator />

      <!-- Individual Question Points -->
      <div class="scoring-settings__section" v-if="questions.length > 0">
        <div class="text-subtitle2 text-weight-bold text-grey-8 q-mb-sm">
          Question Points
        </div>
        
        <div class="scoring-settings__questions-list">
          <div 
            v-for="(question, index) in questions" 
            :key="question.id"
            class="scoring-settings__question-item"
          >
            <div class="row items-center q-gutter-x-sm">
              <q-badge color="primary" rounded class="text-weight-bold">
                Q{{ index + 1 }}
              </q-badge>
              
              <div class="col text-body2 ellipsis">
                {{ truncateText(question.question_text, 50) }}
              </div>
              
              <q-badge 
                :color="getDifficultyColor(question.difficulty)" 
                :label="question.difficulty"
                class="q-mr-sm"
              />
              
              <q-input
                :model-value="question.points"
                outlined
                dense
                type="number"
                min="1"
                max="20"
                style="width: 80px"
                @update:model-value="(value) => updateQuestionPoints(question.id, value)"
              />
              
              <q-btn
                v-if="question.points !== getDefaultPointsForDifficulty(question.difficulty)"
                flat
                round
                dense
                size="sm"
                icon="refresh"
                color="grey-6"
                @click="resetQuestionPoints(question.id, question.difficulty)"
              >
                <q-tooltip>Reset to default</q-tooltip>
              </q-btn>
            </div>
          </div>
        </div>
      </div>

      <q-separator />

      <!-- Passing Score Configuration -->
      <div class="scoring-settings__section">
        <div class="text-subtitle2 text-weight-bold text-grey-8 q-mb-sm">
          Passing Score Threshold
        </div>
        
        <div class="row items-center q-gutter-x-sm">
          <q-toggle
            v-model="localScoringConfig.thresholdIsPercentage"
            label="Use percentage"
            color="purple"
            @update:model-value="updateThresholdType"
          />
        </div>
        
        <div class="row items-center q-gutter-x-sm q-mt-sm">
          <q-input
            v-model.number="localScoringConfig.passingScoreThreshold"
            outlined
            dense
            type="number"
            :label="localScoringConfig.thresholdIsPercentage ? 'Percentage (%)' : 'Points'"
            :min="0"
            :max="localScoringConfig.thresholdIsPercentage ? 100 : totalPoints"
            :suffix="localScoringConfig.thresholdIsPercentage ? '%' : 'pts'"
            :error="!passingScoreValidation.isValid"
            :error-message="passingScoreValidation.message"
            @update:model-value="updatePassingScore"
            class="col"
          >
            <template v-slot:prepend>
              <q-icon name="flag" color="purple" />
            </template>
          </q-input>
          
          <div class="text-caption text-grey-6">
            {{ passingScoreDisplay }}
          </div>
        </div>
      </div>

      <q-separator />

      <!-- Total Points Display -->
      <div class="scoring-settings__section">
        <div class="text-subtitle2 text-weight-bold text-grey-8 q-mb-sm">
          Quiz Summary
        </div>
        
        <div class="row q-col-gutter-sm">
          <div class="col-6 text-center">
            <div class="text-h5 text-weight-bold text-primary">{{ totalPoints }}</div>
            <div class="text-caption text-grey-6">Total Points</div>
          </div>
          
          <div class="col-6 text-center">
            <div class="text-h5 text-weight-bold text-secondary">{{ passingPointsDisplay }}</div>
            <div class="text-caption text-grey-6">Passing Score</div>
          </div>
        </div>
        
        <!-- Points Distribution -->
        <div class="q-mt-md">
          <div class="text-caption text-grey-7 q-mb-xs">Points Distribution</div>
          <div class="row q-col-gutter-xs">
            <div class="col-4 text-center">
              <q-linear-progress
                :value="pointsDistribution.easy / totalPoints"
                color="positive"
                size="8px"
                rounded
              />
              <div class="text-caption q-mt-xs">
                Easy: {{ pointsDistribution.easy }}
              </div>
            </div>
            
            <div class="col-4 text-center">
              <q-linear-progress
                :value="pointsDistribution.medium / totalPoints"
                color="warning"
                size="8px"
                rounded
              />
              <div class="text-caption q-mt-xs">
                Medium: {{ pointsDistribution.medium }}
              </div>
            </div>
            
            <div class="col-4 text-center">
              <q-linear-progress
                :value="pointsDistribution.hard / totalPoints"
                color="negative"
                size="8px"
                rounded
              />
              <div class="text-caption q-mt-xs">
                Hard: {{ pointsDistribution.hard }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import type { QuizQuestion, ScoringConfig } from '@/types/quiz-builder'
import { useScoringStore } from '@/composables/useScoringStore'

interface Props {
  questions: QuizQuestion[]
  scoringConfig: ScoringConfig
}

interface Emits {
  (e: 'points-updated', questionId: string, points: number): void
  (e: 'passing-score-changed', threshold: number): void
  (e: 'scoring-config-updated', config: ScoringConfig): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const { 
  calculateTotalPoints, 
  calculatePointsDistribution, 
  validatePassingScore,
  getDefaultPoints
} = useScoringStore()

// Local reactive copy of scoring config
const localScoringConfig = ref<ScoringConfig>({ ...props.scoringConfig })

// Watch for external changes to scoring config
watch(() => props.scoringConfig, (newConfig) => {
  localScoringConfig.value = { ...newConfig }
}, { deep: true })

// Computed properties
const totalPoints = computed(() => calculateTotalPoints(props.questions))

const pointsDistribution = computed(() => calculatePointsDistribution(props.questions))

const passingScoreValidation = computed(() => {
  if (!localScoringConfig.value.passingScoreThreshold) {
    return { isValid: true }
  }
  return validatePassingScore(localScoringConfig.value.passingScoreThreshold, totalPoints.value)
})

const passingScoreDisplay = computed(() => {
  if (!localScoringConfig.value.passingScoreThreshold) return 'Not set'
  
  if (localScoringConfig.value.thresholdIsPercentage) {
    const points = Math.ceil((localScoringConfig.value.passingScoreThreshold / 100) * totalPoints.value)
    return `${points} points (${localScoringConfig.value.passingScoreThreshold}%)`
  } else {
    const percentage = totalPoints.value > 0 
      ? Math.round((localScoringConfig.value.passingScoreThreshold / totalPoints.value) * 100)
      : 0
    return `${localScoringConfig.value.passingScoreThreshold} points (${percentage}%)`
  }
})

const passingPointsDisplay = computed(() => {
  if (!localScoringConfig.value.passingScoreThreshold) return 'N/A'
  
  if (localScoringConfig.value.thresholdIsPercentage) {
    return Math.ceil((localScoringConfig.value.passingScoreThreshold / 100) * totalPoints.value)
  } else {
    return localScoringConfig.value.passingScoreThreshold
  }
})

// Methods
const getDifficultyColor = (difficulty: string) => {
  const colors = {
    'Easy': 'positive',
    'Medium': 'warning', 
    'Hard': 'negative'
  }
  return colors[difficulty] || 'info'
}

const getDefaultPointsForDifficulty = (difficulty: 'Easy' | 'Medium' | 'Hard') => {
  return getDefaultPoints(difficulty)
}

const truncateText = (text: string, maxLength: number) => {
  if (!text) return ''
  const cleanText = text.replace(/<[^>]*>/g, '')
  return cleanText.length > maxLength ? cleanText.substring(0, maxLength) + '...' : cleanText
}

const updateQuestionPoints = (questionId: string, points: number) => {
  const numericPoints = Number(points)
  if (numericPoints > 0) {
    emit('points-updated', questionId, numericPoints)
  }
}

const resetQuestionPoints = (questionId: string, difficulty: 'Easy' | 'Medium' | 'Hard') => {
  const defaultPoints = getDefaultPointsForDifficulty(difficulty)
  emit('points-updated', questionId, defaultPoints)
}

const updateDefaultPoints = () => {
  emit('scoring-config-updated', { ...localScoringConfig.value })
}

const updatePassingScore = (threshold: number) => {
  const numericThreshold = Number(threshold)
  if (numericThreshold >= 0) {
    emit('passing-score-changed', numericThreshold)
  }
}

const updateThresholdType = () => {
  emit('scoring-config-updated', { ...localScoringConfig.value })
}
</script>

<style scoped lang="scss">
.scoring-settings {
  &__section {
    .q-separator + & {
      padding-top: 16px;
    }
  }

  &__questions-list {
    max-height: 300px;
    overflow-y: auto;
  }

  &__question-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;

    &:last-child {
      border-bottom: none;
    }
  }
}

.ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>