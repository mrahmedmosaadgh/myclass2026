<template>
  <q-card class="question-pool-stats rounded-xl shadow-1 bg-white">
    <!-- Header -->
    <q-card-section class="bg-blue-1 text-blue-8">
      <div class="row items-center justify-between">
        <div class="text-subtitle1 text-weight-bold">
          <q-icon name="analytics" class="q-mr-sm" />
          Pool Statistics
        </div>
        <q-btn 
          flat 
          round 
          dense 
          icon="refresh" 
          @click="$emit('refresh')"
        >
          <q-tooltip>Refresh statistics</q-tooltip>
        </q-btn>
      </div>
    </q-card-section>

    <q-card-section class="q-pa-md">
      <!-- Total Questions -->
      <div class="text-center q-mb-md">
        <div class="text-h4 text-primary text-weight-bold">{{ stats.totalQuestions }}</div>
        <div class="text-caption text-grey-6">Total Questions Available</div>
      </div>

      <!-- Difficulty Distribution -->
      <div class="q-mb-md">
        <div class="text-subtitle2 text-weight-bold q-mb-sm">
          <q-icon name="trending_up" class="q-mr-xs" />
          Difficulty Distribution
        </div>
        
        <div class="row q-col-gutter-sm q-mb-sm">
          <div class="col-4">
            <q-card flat bordered class="text-center q-pa-sm bg-green-1">
              <div class="text-h6 text-green-7 text-weight-bold">{{ stats.difficultyDistribution.easy }}</div>
              <div class="text-caption text-green-8">Easy</div>
              <div class="text-caption text-grey-6">{{ getDifficultyPercentage('easy') }}%</div>
            </q-card>
          </div>
          <div class="col-4">
            <q-card flat bordered class="text-center q-pa-sm bg-orange-1">
              <div class="text-h6 text-orange-7 text-weight-bold">{{ stats.difficultyDistribution.medium }}</div>
              <div class="text-caption text-orange-8">Medium</div>
              <div class="text-caption text-grey-6">{{ getDifficultyPercentage('medium') }}%</div>
            </q-card>
          </div>
          <div class="col-4">
            <q-card flat bordered class="text-center q-pa-sm bg-red-1">
              <div class="text-h6 text-red-7 text-weight-bold">{{ stats.difficultyDistribution.hard }}</div>
              <div class="text-caption text-red-8">Hard</div>
              <div class="text-caption text-grey-6">{{ getDifficultyPercentage('hard') }}%</div>
            </q-card>
          </div>
        </div>

        <!-- Difficulty Progress Bars -->
        <div class="q-gutter-y-xs">
          <div class="row items-center">
            <div class="col-2 text-caption text-green-7">Easy</div>
            <div class="col-10">
              <q-linear-progress
                :value="getDifficultyPercentage('easy') / 100"
                color="green"
                track-color="green-1"
                size="8px"
                rounded
              />
            </div>
          </div>
          <div class="row items-center">
            <div class="col-2 text-caption text-orange-7">Medium</div>
            <div class="col-10">
              <q-linear-progress
                :value="getDifficultyPercentage('medium') / 100"
                color="orange"
                track-color="orange-1"
                size="8px"
                rounded
              />
            </div>
          </div>
          <div class="row items-center">
            <div class="col-2 text-caption text-red-7">Hard</div>
            <div class="col-10">
              <q-linear-progress
                :value="getDifficultyPercentage('hard') / 100"
                color="red"
                track-color="red-1"
                size="8px"
                rounded
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Topic Distribution -->
      <div v-if="topicStats.length > 0" class="q-mb-md">
        <div class="text-subtitle2 text-weight-bold q-mb-sm">
          <q-icon name="topic" class="q-mr-xs" />
          Top Topics
        </div>
        
        <div class="q-gutter-y-xs">
          <div
            v-for="topic in topicStats.slice(0, 5)"
            :key="topic.id"
            class="row items-center"
          >
            <div class="col-6 text-caption">{{ topic.name }}</div>
            <div class="col-4">
              <q-linear-progress
                :value="topic.percentage / 100"
                color="primary"
                track-color="grey-3"
                size="6px"
                rounded
              />
            </div>
            <div class="col-2 text-caption text-right">{{ topic.count }}</div>
          </div>
        </div>
        
        <div v-if="Object.keys(stats.topicDistribution).length > 5" class="text-caption text-grey-6 q-mt-xs">
          ... and {{ Object.keys(stats.topicDistribution).length - 5 }} more topics
        </div>
      </div>

      <!-- Author Distribution -->
      <div v-if="authorStats.length > 0" class="q-mb-md">
        <div class="text-subtitle2 text-weight-bold q-mb-sm">
          <q-icon name="person" class="q-mr-xs" />
          Top Authors
        </div>
        
        <div class="q-gutter-y-xs">
          <div
            v-for="author in authorStats.slice(0, 3)"
            :key="author.id"
            class="row items-center"
          >
            <div class="col-6 text-caption">{{ author.name }}</div>
            <div class="col-4">
              <q-linear-progress
                :value="author.percentage / 100"
                color="secondary"
                track-color="grey-3"
                size="6px"
                rounded
              />
            </div>
            <div class="col-2 text-caption text-right">{{ author.count }}</div>
          </div>
        </div>
        
        <div v-if="Object.keys(stats.authorDistribution).length > 3" class="text-caption text-grey-6 q-mt-xs">
          ... and {{ Object.keys(stats.authorDistribution).length - 3 }} more authors
        </div>
      </div>

      <!-- Bloom's Taxonomy Distribution -->
      <div v-if="bloomsStats.length > 0" class="q-mb-md">
        <div class="text-subtitle2 text-weight-bold q-mb-sm">
          <q-icon name="psychology" class="q-mr-xs" />
          Bloom's Taxonomy
        </div>
        
        <div class="q-gutter-y-xs">
          <div
            v-for="bloom in bloomsStats"
            :key="bloom.level"
            class="row items-center"
          >
            <div class="col-6 text-caption">{{ getBloomLevelName(bloom.level) }}</div>
            <div class="col-4">
              <q-linear-progress
                :value="bloom.percentage / 100"
                color="purple"
                track-color="grey-3"
                size="6px"
                rounded
              />
            </div>
            <div class="col-2 text-caption text-right">{{ bloom.count }}</div>
          </div>
        </div>
      </div>

      <!-- Selection Algorithm Feedback -->
      <div v-if="selectionFeedback" class="q-mb-md">
        <div class="text-subtitle2 text-weight-bold q-mb-sm">
          <q-icon name="lightbulb" class="q-mr-xs" />
          Selection Insights
        </div>
        
        <q-banner
          :class="selectionFeedback.type === 'success' ? 'bg-green-1 text-green-8' : 
                  selectionFeedback.type === 'warning' ? 'bg-orange-1 text-orange-8' : 
                  'bg-blue-1 text-blue-8'"
          rounded
          dense
        >
          <template v-slot:avatar>
            <q-icon 
              :name="selectionFeedback.type === 'success' ? 'check_circle' : 
                    selectionFeedback.type === 'warning' ? 'warning' : 
                    'info'"
            />
          </template>
          <div class="text-caption">{{ selectionFeedback.message }}</div>
        </q-banner>
      </div>

      <!-- Pool Quality Indicators -->
      <div class="q-mb-md">
        <div class="text-subtitle2 text-weight-bold q-mb-sm">
          <q-icon name="verified" class="q-mr-xs" />
          Pool Quality
        </div>
        
        <div class="row q-col-gutter-sm">
          <div class="col-6">
            <q-card flat bordered class="text-center q-pa-sm">
              <q-icon 
                :name="getBalanceIcon()" 
                :color="getBalanceColor()" 
                size="24px"
              />
              <div class="text-caption q-mt-xs">{{ getBalanceText() }}</div>
            </q-card>
          </div>
          <div class="col-6">
            <q-card flat bordered class="text-center q-pa-sm">
              <q-icon 
                :name="getDiversityIcon()" 
                :color="getDiversityColor()" 
                size="24px"
              />
              <div class="text-caption q-mt-xs">{{ getDiversityText() }}</div>
            </q-card>
          </div>
        </div>
      </div>

      <!-- Filter Impact -->
      <div v-if="showFilterImpact" class="q-mb-md">
        <div class="text-subtitle2 text-weight-bold q-mb-sm">
          <q-icon name="filter_alt" class="q-mr-xs" />
          Filter Impact
        </div>
        
        <q-banner class="bg-grey-1" rounded dense>
          <div class="text-caption">
            Filters reduced pool from {{ originalPoolSize }} to {{ stats.totalQuestions }} questions
            ({{ Math.round((stats.totalQuestions / originalPoolSize) * 100) }}% remaining)
          </div>
        </q-banner>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { QuestionPoolStats } from '@/types/quiz-builder'

// Props
interface Props {
  stats: QuestionPoolStats
  selectionFeedback?: {
    type: 'success' | 'warning' | 'info'
    message: string
  } | null
  originalPoolSize?: number
  topicNames?: Record<string, string>
  authorNames?: Record<string, string>
}

const props = withDefaults(defineProps<Props>(), {
  selectionFeedback: null,
  originalPoolSize: 0,
  topicNames: () => ({}),
  authorNames: () => ({})
})

// Emits
interface Emits {
  (e: 'refresh'): void
}

const emit = defineEmits<Emits>()

// Computed
const showFilterImpact = computed(() => {
  return props.originalPoolSize > 0 && props.originalPoolSize !== props.stats.totalQuestions
})

const topicStats = computed(() => {
  return Object.entries(props.stats.topicDistribution)
    .map(([id, count]) => ({
      id,
      name: props.topicNames[id] || `Topic ${id}`,
      count,
      percentage: props.stats.totalQuestions > 0 ? Math.round((count / props.stats.totalQuestions) * 100) : 0
    }))
    .sort((a, b) => b.count - a.count)
})

const authorStats = computed(() => {
  return Object.entries(props.stats.authorDistribution)
    .map(([id, count]) => ({
      id,
      name: props.authorNames[id] || `Author ${id}`,
      count,
      percentage: props.stats.totalQuestions > 0 ? Math.round((count / props.stats.totalQuestions) * 100) : 0
    }))
    .sort((a, b) => b.count - a.count)
})

const bloomsStats = computed(() => {
  return Object.entries(props.stats.bloomsDistribution)
    .map(([level, count]) => ({
      level: parseInt(level),
      count,
      percentage: props.stats.totalQuestions > 0 ? Math.round((count / props.stats.totalQuestions) * 100) : 0
    }))
    .sort((a, b) => a.level - b.level)
})

// Methods
const getDifficultyPercentage = (difficulty: 'easy' | 'medium' | 'hard'): number => {
  if (props.stats.totalQuestions === 0) return 0
  return Math.round((props.stats.difficultyDistribution[difficulty] / props.stats.totalQuestions) * 100)
}

const getBlanceScore = (): number => {
  const { easy, medium, hard } = props.stats.difficultyDistribution
  const total = easy + medium + hard
  
  if (total === 0) return 0
  
  const easyRatio = easy / total
  const mediumRatio = medium / total
  const hardRatio = hard / total
  
  // Calculate how close to 33.33% each difficulty is
  const idealRatio = 1/3
  const easyDeviation = Math.abs(easyRatio - idealRatio)
  const mediumDeviation = Math.abs(mediumRatio - idealRatio)
  const hardDeviation = Math.abs(hardRatio - idealRatio)
  
  const averageDeviation = (easyDeviation + mediumDeviation + hardDeviation) / 3
  return Math.max(0, 1 - (averageDeviation * 3)) // Scale to 0-1
}

const getBalanceIcon = (): string => {
  const score = getBlanceScore()
  if (score > 0.8) return 'balance'
  if (score > 0.5) return 'trending_flat'
  return 'warning'
}

const getBalanceColor = (): string => {
  const score = getBlanceScore()
  if (score > 0.8) return 'green'
  if (score > 0.5) return 'orange'
  return 'red'
}

const getBalanceText = (): string => {
  const score = getBlanceScore()
  if (score > 0.8) return 'Well Balanced'
  if (score > 0.5) return 'Moderately Balanced'
  return 'Unbalanced'
}

const getDiversityScore = (): number => {
  const topicCount = Object.keys(props.stats.topicDistribution).length
  const authorCount = Object.keys(props.stats.authorDistribution).length
  
  // Normalize based on total questions
  const topicDiversity = Math.min(1, topicCount / Math.max(1, props.stats.totalQuestions * 0.1))
  const authorDiversity = Math.min(1, authorCount / Math.max(1, props.stats.totalQuestions * 0.2))
  
  return (topicDiversity + authorDiversity) / 2
}

const getDiversityIcon = (): string => {
  const score = getDiversityScore()
  if (score > 0.7) return 'diversity_3'
  if (score > 0.4) return 'diversity_1'
  return 'person'
}

const getDiversityColor = (): string => {
  const score = getDiversityScore()
  if (score > 0.7) return 'green'
  if (score > 0.4) return 'orange'
  return 'red'
}

const getDiversityText = (): string => {
  const score = getDiversityScore()
  if (score > 0.7) return 'High Diversity'
  if (score > 0.4) return 'Moderate Diversity'
  return 'Low Diversity'
}

const getBloomLevelName = (level: number): string => {
  const levels = {
    1: 'Remember',
    2: 'Understand',
    3: 'Apply',
    4: 'Analyze',
    5: 'Evaluate',
    6: 'Create'
  }
  return levels[level as keyof typeof levels] || `Level ${level}`
}
</script>

<style scoped lang="scss">
.question-pool-stats {
  // Custom styling for statistics display
}
</style>