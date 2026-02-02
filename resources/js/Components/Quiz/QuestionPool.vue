<template>
  <q-card class="question-pool rounded-xl shadow-2 full-height bg-white">
    <!-- Header -->
    <q-card-section class="bg-blue-1 text-primary">
      <div class="row items-center justify-between">
        <div class="text-h6 text-weight-bold">Question Pool</div>
        <q-btn flat round dense icon="refresh" @click="$emit('refresh')">
          <q-tooltip>Refresh</q-tooltip>
        </q-btn>
      </div>
    </q-card-section>

    <!-- Filters Section -->
    <q-card-section class="q-pa-md q-gutter-y-md">
      <!-- Search Input -->
      <q-input
        :model-value="searchTerm"
        @update:model-value="$emit('update:search-term', $event)"
        outlined
        dense
        rounded
        placeholder="Search questions..."
        bg-color="grey-1"
      >
        <template v-slot:prepend>
          <q-icon name="search" color="primary" />
        </template>
      </q-input>

      <!-- Basic Filters Row -->
      <div class="row q-col-gutter-sm">
        <div class="col-6">
          <q-select
            :model-value="typeFilter"
            @update:model-value="$emit('update:type-filter', $event)"
            outlined
            dense
            rounded
            :options="questionTypes"
            label="Type"
            option-label="name"
            option-value="id"
            bg-color="grey-1"
            behavior="menu"
            clearable
          />
        </div>
        <div class="col-6">
          <q-select
            :model-value="difficultyFilter"
            @update:model-value="$emit('update:difficulty-filter', $event)"
            outlined
            dense
            rounded
            :options="['Easy', 'Medium', 'Hard']"
            label="Difficulty"
            bg-color="grey-1"
            behavior="menu"
            clearable
          />
        </div>
      </div>

      <!-- Advanced Filters Integration -->
      <slot name="advanced-filters" />
    </q-card-section>

    <q-separator />

    <!-- Bulk Operations -->
    <q-card-section class="q-pa-md">
      <BulkOperations
        :filtered-questions="filteredQuestions"
        :selected-questions="selectedQuestions"
        :bulk-state="bulkState"
        @add-all-filtered="$emit('add-all-filtered')"
        @add-selected="$emit('add-selected', $event)"
        @toggle-multi-select="$emit('toggle-multi-select', $event)"
        @clear-selection="$emit('clear-selection')"
        @select-all-filtered="$emit('select-all-filtered', $event)"
        @remove-all="$emit('remove-all')"
      />
    </q-card-section>

    <q-separator />

    <!-- Question List -->
    <q-card-section class="question-pool__list scroll" style="height: calc(100vh - 450px)">
      <!-- Loading State -->
      <div v-if="loading" class="row justify-center q-pa-lg">
        <q-spinner-dots color="primary" size="40px" />
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredQuestions.length === 0" class="column items-center justify-center text-grey-5 q-pa-xl">
        <q-icon name="sentiment_dissatisfied" size="48px" />
        <p class="q-mt-sm">No questions found</p>
        <p class="text-caption">Try adjusting your filters</p>
      </div>

      <!-- Question Items -->
      <div v-else class="q-gutter-y-sm">
        <QuestionPoolItem
          v-for="question in filteredQuestions"
          :key="`pool-${question.id}`"
          :question="question"
          :is-selected="isQuestionSelected(question.id)"
          :multi-select-mode="bulkState.multiSelectMode"
          :is-in-quiz="isQuestionInQuiz(question.id)"
          @click="handleQuestionClick(question)"
          @toggle-selection="handleToggleSelection(question.id)"
          @drag-start="handleDragStart($event, question)"
        />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { QuizQuestion, BulkOperationState, QuestionType } from '@/types/quiz-builder'
import BulkOperations from './BulkOperations.vue'
import QuestionPoolItem from './QuestionPoolItem.vue'

// Props
interface Props {
  filteredQuestions: QuizQuestion[]
  selectedQuestions: QuizQuestion[]
  questionTypes: QuestionType[]
  bulkState: BulkOperationState
  loading?: boolean
  searchTerm?: string
  typeFilter?: QuestionType | null
  difficultyFilter?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  searchTerm: '',
  typeFilter: null,
  difficultyFilter: null
})

// Emits
interface Emits {
  (e: 'refresh'): void
  (e: 'update:search-term', value: string): void
  (e: 'update:type-filter', value: QuestionType | null): void
  (e: 'update:difficulty-filter', value: string | null): void
  (e: 'question-clicked', question: QuizQuestion): void
  (e: 'question-drag-start', event: DragEvent, question: QuizQuestion): void
  (e: 'toggle-question-selection', questionId: string): void
  (e: 'add-all-filtered'): void
  (e: 'add-selected', questions: QuizQuestion[]): void
  (e: 'toggle-multi-select', enabled: boolean): void
  (e: 'clear-selection'): void
  (e: 'select-all-filtered', questions: QuizQuestion[]): void
  (e: 'remove-all'): void
}

const emit = defineEmits<Emits>()

// Computed
const selectedQuestionIds = computed(() => 
  new Set(props.selectedQuestions.map(q => q.id.toString()))
)

// Methods
const isQuestionSelected = (questionId: string | number): boolean => {
  return props.bulkState.selectedQuestionIds.includes(questionId.toString())
}

const isQuestionInQuiz = (questionId: string | number): boolean => {
  return selectedQuestionIds.value.has(questionId.toString())
}

const handleQuestionClick = (question: QuizQuestion) => {
  if (props.bulkState.multiSelectMode) {
    // In multi-select mode, clicking toggles selection
    handleToggleSelection(question.id)
  } else {
    // In normal mode, clicking adds to quiz
    emit('question-clicked', question)
  }
}

const handleToggleSelection = (questionId: string | number) => {
  emit('toggle-question-selection', questionId.toString())
}

const handleDragStart = (event: DragEvent, question: QuizQuestion) => {
  emit('question-drag-start', event, question)
}
</script>

<style scoped lang="scss">
.question-pool {
  &__list {
    // Custom scrollbar styling
    &::-webkit-scrollbar {
      width: 6px;
    }
    
    &::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 3px;
    }
    
    &::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 3px;
      
      &:hover {
        background: #a8a8a8;
      }
    }
  }
}
</style>