<template>
  <q-card class="live-stats rounded-xl shadow-2 bg-white">
    <q-card-section class="bg-blue-1 text-blue-9">
      <div class="text-h6 text-weight-bold">
        <q-icon name="analytics" class="q-mr-sm" />
        Live Statistics
      </div>
    </q-card-section>

    <q-card-section class="q-pa-md">
      <!-- Main Stats Grid -->
      <div class="row q-col-gutter-sm q-mb-md">
        <div class="col-6 text-center">
          <div class="live-stats__stat-card bg-primary text-white">
            <div class="text-h4 text-weight-bold">{{ stats.questionCount }}</div>
            <div class="text-caption">Questions</div>
          </div>
        </div>
        
        <div class="col-6 text-center">
          <div class="live-stats__stat-card bg-secondary text-white">
            <div class="text-h4 text-weight-bold">{{ stats.totalPoints }}</div>
            <div class="text-caption">Total Points</div>
          </div>
        </div>
        
        <div class="col-6 text-center">
          <div class="live-stats__stat-card bg-orange text-white">
            <div class="text-h5 text-weight-bold">{{ stats.estimatedTimeMinutes }}m</div>
            <div class="text-caption">Est. Time</div>
          </div>
        </div>
        
        <div class="col-6 text-center">
          <div class="live-stats__stat-card" :class="difficultyCardClass">
            <div class="text-h5 text-weight-bold">{{ stats.averageDifficulty }}</div>
            <div class="text-caption">Difficulty</div>
          </div>
        </div>
      </div>

      <!-- Points Distribution -->
      <div v-if="stats.totalPoints > 0" class="live-stats__section q-mb-md">
        <div class="text-subtitle2 text-weight-bold text-grey-8 q-mb-sm">
          Points Distribution
        </div>
        
        <div class="live-stats__distribution">
          <!-- Easy Points -->
          <div class="live-stats__distribution-item">
            <div class="row items-center justify-between q-mb-xs">
              <span class="text-body2 text-positive">
                <q-icon name="sentiment_satisfied" size="16px" class="q-mr-xs" />
                Easy
              </span>
              <span class="text-weight-bold">{{ stats.pointsDistribution.easy }} pts</span>
            </div>
            <q-linear-progress
              :value="stats.pointsDistribution.easy / stats.totalPoints"
              color="positive"
              size="8px"
              rounded
            />
            <div class="text-caption text-grey-6 text-center q-mt-xs">
              {{ getPercentage(stats.pointsDistribution.easy, stats.totalPoints) }}%
            </div>
          </div>
          
          <!-- Medium Points -->
          <div class="live-stats__distribution-item">
            <div class="row items-center justify-between q-mb-xs">
              <span class="text-body2 text-warning">
                <q-icon name="sentiment_neutral" size="16px" class="q-mr-xs" />
                Medium
              </span>
              <span class="text-weight-bold">{{ stats.pointsDistribution.medium }} pts</span>
            </div>
            <q-linear-progress
              :value="stats.pointsDistribution.medium / stats.totalPoints"
              color="warning"
              size="8px"
              rounded
            />
            <div class="text-caption text-grey-6 text-center q-mt-xs">
              {{ getPercentage(stats.pointsDistribution.medium, stats.totalPoints) }}%
            </div>
          </div>
          
          <!-- Hard Points -->
          <div class="live-stats__distribution-item">
            <div class="row items-center justify-between q-mb-xs">
              <span class="text-body2 text-negative">
                <q-icon name="sentiment_dissatisfied" size="16px" class="q-mr-xs" />
                Hard
              </span>
              <span class="text-weight-bold">{{ stats.pointsDistribution.hard }} pts</span>
            </div>
            <q-linear-progress
              :value="stats.pointsDistribution.hard / stats.totalPoints"
              color="negative"
              size="8px"
              rounded
            />
            <div class="text-caption text-grey-6 text-center q-mt-xs">
              {{ getPercentage(stats.pointsDistribution.hard, stats.totalPoints) }}%
            </div>
          </div>
        </div>
      </div>

      <!-- Passing Score Status -->
      <div v-if="passingScore && stats.totalPoints > 0" class="live-stats__section q-mb-md">
        <div class="text-subtitle2 text-weight-bold text-grey-8 q-mb-sm">
          Passing Score Threshold
        </div>
        
        <div class="live-stats__passing-score">
          <div class="row items-center justify-between q-mb-sm">
            <span class="text-body2">Required Score:</span>
            <span class="text-weight-bold text-purple">
              {{ passingScorePoints }} pts ({{ passingScorePercentage }}%)
            </span>
          </div>
          
          <q-linear-progress
            :value="passingScorePoints / stats.totalPoints"
            color="purple"
            size="12px"
            rounded
            class="q-mb-xs"
          />
          
          <div class="text-caption text-grey-6 text-center">
            {{ passingScoreStatus }}
          </div>
        </div>
      </div>

      <!-- Sections Info -->
      <div v-if="stats.sectionCount > 0" class="live-stats__section">
        <div class="text-subtitle2 text-weight-bold text-grey-8 q-mb-sm">
          Organization
        </div>
        
        <div class="row items-center q-gutter-x-sm">
          <q-icon name="view_module" color="indigo" size="20px" />
          <span class="text-body2">{{ stats.sectionCount }} sections</span>
          <q-separator vertical />
          <span class="text-caption text-grey-6">
            Avg {{ Math.round(stats.questionCount / stats.sectionCount) }} questions per section
          </span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="stats.questionCount === 0" class="live-stats__empty text-center q-py-lg">
        <q-icon name="bar_chart" size="48px" color="grey-4" class="q-mb-md" />
        <div class="text-h6 text-grey-5">No Questions Yet</div>
        <div class="text-body2 text-grey-6">
          Add questions to see live statistics
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { LiveStats, ScoringConfig } from '@/types/quiz-builder'

interface Props {
  stats: LiveStats
  passingScore?: number
  passingScoreIsPercentage?: boolean
  scoringConfig?: ScoringConfig
}

const props = withDefaults(defineProps<Props>(), {
  passingScoreIsPercentage: true
})

// Computed properties
const difficultyCardClass = computed(() => {
  const difficulty = props.stats.averageDifficulty
  if (difficulty === 'Easy') return 'bg-positive text-white'
  if (difficulty === 'Medium') return 'bg-warning text-white'
  if (difficulty === 'Hard') return 'bg-negative text-white'
  return 'bg-grey-5 text-white'
})

const passingScorePoints = computed(() => {
  if (!props.passingScore) return 0
  
  if (props.passingScoreIsPercentage) {
    return Math.ceil((props.passingScore / 100) * props.stats.totalPoints)
  }
  
  return props.passingScore
})

const passingScorePercentage = computed(() => {
  if (!props.passingScore) return 0
  
  if (props.passingScoreIsPercentage) {
    return props.passingScore
  }
  
  if (props.stats.totalPoints === 0) return 0
  
  return Math.round((props.passingScore / props.stats.totalPoints) * 100)
})

const passingScoreStatus = computed(() => {
  if (!props.passingScore || props.stats.totalPoints === 0) {
    return 'No passing score set'
  }
  
  const percentage = passingScorePercentage.value
  
  if (percentage < 50) {
    return 'Low threshold - Most students should pass'
  } else if (percentage < 70) {
    return 'Moderate threshold - Balanced difficulty'
  } else if (percentage < 85) {
    return 'High threshold - Challenging for students'
  } else {
    return 'Very high threshold - Only top performers will pass'
  }
})

// Methods
const getPercentage = (value: number, total: number): number => {
  if (total === 0) return 0
  return Math.round((value / total) * 100)
}
</script>

<style scoped lang="scss">
.live-stats {
  &__stat-card {
    padding: 16px;
    border-radius: 12px;
    text-align: center;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  &__section {
    & + & {
      border-top: 1px solid #f0f0f0;
      padding-top: 16px;
    }
  }

  &__distribution {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  &__distribution-item {
    padding: 12px;
    background: #fafafa;
    border-radius: 8px;
  }

  &__passing-score {
    padding: 12px;
    background: #f3e8ff;
    border-radius: 8px;
    border: 1px solid #e9d5ff;
  }

  &__empty {
    color: #9ca3af;
  }
}
</style>