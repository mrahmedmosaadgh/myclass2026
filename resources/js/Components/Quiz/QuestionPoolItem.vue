<template>
  <div 
    class="question-pool-item"
    :class="{
      'question-pool-item--selected': isSelected,
      'question-pool-item--multi-select': multiSelectMode,
      'question-pool-item--in-quiz': isInQuiz,
      'question-pool-item--dragging': isDragging
    }"
    @click="handleClick"
    @dragstart="handleDragStart"
    @dragend="isDragging = false"
    draggable="true"
  >
    <!-- Multi-select Checkbox -->
    <div v-if="multiSelectMode" class="question-pool-item__checkbox">
      <q-checkbox
        :model-value="isSelected"
        @update:model-value="handleToggleSelection"
        color="primary"
        size="sm"
        @click.stop
      />
    </div>

    <!-- Question Content -->
    <div class="question-pool-item__content">
      <!-- Question Header -->
      <div class="question-pool-item__header">
        <div class="row items-center justify-between">
          <!-- Question Type & Difficulty -->
          <div class="row items-center q-gutter-x-xs">
            <q-chip
              :color="getDifficultyColor(question.difficulty)"
              text-color="white"
              size="sm"
              dense
              class="text-weight-bold"
            >
              {{ question.difficulty }}
            </q-chip>
            
            <q-chip
              color="grey-4"
              text-color="grey-8"
              size="sm"
              dense
              v-if="question.question_type_name"
            >
              {{ question.question_type_name }}
            </q-chip>
          </div>

          <!-- Status Indicators -->
          <div class="row items-center q-gutter-x-xs">
            <!-- In Quiz Indicator -->
            <q-icon
              v-if="isInQuiz"
              name="check_circle"
              color="positive"
              size="sm"
            >
              <q-tooltip>Already in quiz</q-tooltip>
            </q-icon>

            <!-- Usage Count -->
            <q-chip
              v-if="question.usage_count && question.usage_count > 0"
              color="blue-grey-2"
              text-color="blue-grey-8"
              size="xs"
              dense
            >
              Used {{ question.usage_count }}x
            </q-chip>
          </div>
        </div>
      </div>

      <!-- Question Text -->
      <div class="question-pool-item__text q-mt-sm">
        <div 
          class="text-body2 text-grey-8"
          v-html="truncateText(question.question_text, 120)"
        />
      </div>

      <!-- Question Metadata -->
      <div class="question-pool-item__metadata q-mt-sm">
        <div class="row items-center justify-between">
          <div class="row items-center q-gutter-x-sm text-caption text-grey-6">
            <!-- Topic -->
            <span v-if="question.topic_name">
              <q-icon name="topic" size="xs" />
              {{ question.topic_name }}
            </span>
            
            <!-- Author -->
            <span v-if="question.author_name">
              <q-icon name="person" size="xs" />
              {{ question.author_name }}
            </span>

            <!-- Success Rate -->
            <span v-if="question.avg_success_rate">
              <q-icon name="trending_up" size="xs" />
              {{ Math.round(question.avg_success_rate) }}% success
            </span>
          </div>

          <!-- Action Hint -->
          <div class="question-pool-item__action-hint text-caption text-grey-5">
            <span v-if="multiSelectMode">
              Click to select
            </span>
            <span v-else-if="isInQuiz">
              Already added
            </span>
            <span v-else>
              Click to add
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Drag Handle -->
    <div v-if="!multiSelectMode" class="question-pool-item__drag-handle">
      <q-icon name="drag_indicator" color="grey-5" size="sm" />
    </div>

    <!-- Hover Actions -->
    <div class="question-pool-item__actions">
      <q-btn
        flat
        round
        dense
        icon="visibility"
        color="primary"
        size="sm"
        @click.stop="$emit('preview', question)"
      >
        <q-tooltip>Preview question</q-tooltip>
      </q-btn>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { QuizQuestion } from '@/types/quiz-builder'

// Props
interface Props {
  question: QuizQuestion
  isSelected?: boolean
  multiSelectMode?: boolean
  isInQuiz?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  isSelected: false,
  multiSelectMode: false,
  isInQuiz: false
})

// Emits
interface Emits {
  (e: 'click', question: QuizQuestion): void
  (e: 'toggle-selection'): void
  (e: 'drag-start', event: DragEvent): void
  (e: 'preview', question: QuizQuestion): void
}

const emit = defineEmits<Emits>()

// State
const isDragging = ref(false)

// Methods
const handleClick = () => {
  if (!props.isInQuiz || props.multiSelectMode) {
    emit('click', props.question)
  }
}

const handleToggleSelection = () => {
  emit('toggle-selection')
}

const handleDragStart = (event: DragEvent) => {
  if (props.multiSelectMode) {
    event.preventDefault()
    return
  }
  
  isDragging.value = true
  emit('drag-start', event)
}

const getDifficultyColor = (difficulty: string): string => {
  switch (difficulty?.toLowerCase()) {
    case 'easy':
      return 'green'
    case 'medium':
      return 'orange'
    case 'hard':
      return 'red'
    default:
      return 'grey'
  }
}

const truncateText = (text: string, maxLength: number): string => {
  if (!text) return ''
  
  // Remove HTML tags for length calculation
  const plainText = text.replace(/<[^>]*>/g, '')
  
  if (plainText.length <= maxLength) {
    return text
  }
  
  // Find a good breaking point (end of word)
  const truncated = plainText.substring(0, maxLength)
  const lastSpace = truncated.lastIndexOf(' ')
  const breakPoint = lastSpace > maxLength * 0.8 ? lastSpace : maxLength
  
  return text.substring(0, breakPoint) + '...'
}
</script>

<style scoped lang="scss">
.question-pool-item {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;

  &:hover {
    border-color: #1976d2;
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.12);
    transform: translateY(-1px);

    .question-pool-item__actions {
      opacity: 1;
    }

    .question-pool-item__drag-handle {
      opacity: 1;
    }
  }

  &--selected {
    border-color: #1976d2;
    background: rgba(25, 118, 210, 0.04);
    box-shadow: 0 2px 8px rgba(25, 118, 210, 0.12);
  }

  &--multi-select {
    padding-left: 12px;

    &:hover {
      transform: none; // Disable hover lift in multi-select mode
    }
  }

  &--in-quiz {
    opacity: 0.6;
    border-color: #4caf50;
    background: rgba(76, 175, 80, 0.04);

    &:not(.question-pool-item--multi-select) {
      cursor: not-allowed;
    }
  }

  &--dragging {
    opacity: 0.5;
    transform: rotate(2deg);
  }

  &__checkbox {
    flex-shrink: 0;
    margin-top: 2px;
  }

  &__content {
    flex: 1;
    min-width: 0; // Allow text truncation
  }

  &__header {
    .q-chip {
      font-size: 10px;
      height: 20px;
    }
  }

  &__text {
    line-height: 1.4;
    
    // Ensure HTML content doesn't break layout
    :deep(p) {
      margin: 0;
    }
    
    :deep(img) {
      max-width: 100%;
      height: auto;
    }
  }

  &__metadata {
    font-size: 11px;
    
    .q-icon {
      margin-right: 2px;
    }
  }

  &__action-hint {
    font-style: italic;
    opacity: 0.8;
  }

  &__drag-handle {
    flex-shrink: 0;
    opacity: 0;
    transition: opacity 0.2s ease;
    cursor: grab;
    margin-top: 4px;

    &:active {
      cursor: grabbing;
    }
  }

  &__actions {
    position: absolute;
    top: 8px;
    right: 8px;
    opacity: 0;
    transition: opacity 0.2s ease;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 6px;
    padding: 2px;
  }
}

// Animation for selection state changes
.question-pool-item--selected {
  animation: selectPulse 0.3s ease-out;
}

@keyframes selectPulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
  100% {
    transform: scale(1);
  }
}
</style>