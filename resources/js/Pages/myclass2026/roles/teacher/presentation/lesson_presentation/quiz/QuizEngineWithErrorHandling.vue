<template>
  <div class="quiz-engine-wrapper">
    <!-- Loading State -->
    <LoadingIndicator
      :is-loading="isLoading"
      :message="loadingMessage"
    />

    <!-- Error Display -->
    <ErrorDisplay
      v-if="!isLoading"
      :error="errorState.error"
      :can-retry="isRetryable()"
      :is-retrying="isRetrying"
      :show-details="isDevelopment"
      @retry="handleRetry"
      @dismiss="clearError"
    />

    <!-- Quiz Engine (only show when loaded and no critical errors) -->
    <QuizEngine
      v-if="!isLoading && !hasCriticalError && quiz.length > 0"
      :quiz="quiz"
      :config="config"
      :attempt-id="attemptId"
      @answer-selected="handleAnswerSelected"
      @question-changed="handleQuestionChanged"
      @quiz-completed="handleQuizCompleted"
      @quiz-review-enter="handleQuizReviewEnter"
      @time-warning="handleTimeWarning"
    />

    <!-- Offline Indicator -->
    <div
      v-if="!isOnline"
      class="offline-indicator"
      role="status"
      aria-live="polite"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
        class="offline-icon"
        aria-hidden="true"
      >
        <path
          fill-rule="evenodd"
          d="M1.371 8.143c5.858-5.857 15.356-5.857 21.213 0a.75.75 0 010 1.061l-.53.53a.75.75 0 01-1.06 0c-4.98-4.979-13.053-4.979-18.032 0a.75.75 0 01-1.06 0l-.53-.53a.75.75 0 010-1.06zm3.182 3.182c4.1-4.1 10.749-4.1 14.85 0a.75.75 0 010 1.061l-.53.53a.75.75 0 01-1.062 0 8.25 8.25 0 00-11.667 0 .75.75 0 01-1.06 0l-.53-.53a.75.75 0 010-1.061zm3.204 3.182a6 6 0 018.486 0 .75.75 0 010 1.061l-.53.53a.75.75 0 01-1.061 0 3.75 3.75 0 00-5.304 0 .75.75 0 01-1.06 0l-.53-.53a.75.75 0 010-1.061zm3.182 3.182a1.5 1.5 0 012.122 0 .75.75 0 010 1.061l-.53.53a.75.75 0 01-1.061 0l-.53-.53a.75.75 0 010-1.061z"
          clip-rule="evenodd"
        />
        <path
          d="M3 3l18 18"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
        />
      </svg>
      <span>You are offline. Answers will be saved and submitted when connection is restored.</span>
    </div>

    <!-- Queued Answers Indicator -->
    <div
      v-if="hasQueuedAnswers()"
      class="queued-indicator"
      role="status"
      aria-live="polite"
    >
      <span>You have unsaved answers. They will be submitted when connection is restored.</span>
      <button
        v-if="isOnline"
        class="sync-button"
        @click="syncQueuedAnswers"
        :disabled="isSyncing"
      >
        {{ isSyncing ? 'Syncing...' : 'Sync Now' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import QuizEngine from './QuizEngine.vue'
import ErrorDisplay from './components/ErrorDisplay.vue'
import LoadingIndicator from './components/LoadingIndicator.vue'
import { useErrorHandler } from '@/composables/useErrorHandler'
import { useQuizApi } from '@/composables/useQuizApi'
import type {
  QuizQuestion,
  QuizConfig,
  AnswerRecord,
  QuizResult,
} from '@/types/quiz'

/**
 * QuizEngineWithErrorHandling Component
 * 
 * Wrapper component that adds error handling, loading states, and offline support
 * to the QuizEngine component.
 */

// ============================================================================
// Props Definition
// ============================================================================

interface Props {
  /** Quiz questions (can be provided directly or fetched) */
  quiz?: QuizQuestion[]
  /** Parameters for fetching quiz from API */
  fetchParams?: {
    question_ids?: number[]
    grade_level_id?: number
    subject_id?: number
    topic_id?: number
    difficulty_level?: number
    bloom_level?: number
    limit?: number
    shuffle?: boolean
  }
  /** Quiz configuration */
  config?: Partial<QuizConfig>
  /** Attempt ID for tracking */
  attemptId?: string | number
  /** Whether to auto-start attempt on mount */
  autoStartAttempt?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  quiz: () => [],
  fetchParams: undefined,
  config: () => ({}),
  attemptId: undefined,
  autoStartAttempt: false,
})

// ============================================================================
// Emits Definition
// ============================================================================

interface Emits {
  (e: 'answer-selected', record: AnswerRecord): void
  (e: 'question-changed', index: number): void
  (e: 'quiz-completed', result: QuizResult): void
  (e: 'quiz-review-enter'): void
  (e: 'time-warning', remainingSeconds: number): void
  (e: 'attempt-started', attemptId: number): void
  (e: 'error', error: any): void
}

const emit = defineEmits<Emits>()

// ============================================================================
// Composables
// ============================================================================

const { errorState, handleError, clearError, isRetryable } = useErrorHandler()
const {
  isLoading: apiLoading,
  isSaving: apiSaving,
  fetchQuiz,
  startAttempt,
  submitAnswer,
  queueAnswer,
  processQueuedAnswers,
  hasQueuedAnswers,
} = useQuizApi()

// ============================================================================
// Reactive State
// ============================================================================

const quiz = ref<QuizQuestion[]>(props.quiz || [])
const attemptId = ref<number | undefined>(
  typeof props.attemptId === 'number' ? props.attemptId : undefined
)
const isRetrying = ref(false)
const isSyncing = ref(false)
const isOnline = ref(navigator.onLine)
const isDevelopment = import.meta.env.DEV

// ============================================================================
// Computed Properties
// ============================================================================

const isLoading = computed(() => apiLoading.value || apiSaving.value)

const loadingMessage = computed(() => {
  if (apiSaving.value) return 'Saving your answer...'
  if (apiLoading.value) return 'Loading quiz...'
  return 'Loading...'
})

const hasCriticalError = computed(() => {
  if (!errorState.value.hasError) return false

  const criticalCodes = [
    'UNAUTHORIZED_ACCESS',
    'QUIZ_NOT_FOUND',
    'INVALID_QUESTIONS',
  ]

  return criticalCodes.includes(errorState.value.error?.code || '')
})

// ============================================================================
// Methods
// ============================================================================

/**
 * Initialize the quiz (fetch questions if needed)
 */
const initializeQuiz = async (): Promise<void> => {
  try {
    // If quiz questions are provided, use them
    if (props.quiz && props.quiz.length > 0) {
      quiz.value = props.quiz
      return
    }

    // Otherwise, fetch from API
    if (props.fetchParams) {
      const questions = await fetchQuiz(props.fetchParams)
      quiz.value = questions

      // Auto-start attempt if enabled
      if (props.autoStartAttempt && questions.length > 0) {
        await createAttempt(questions.map(q => q.id as number))
      }
    }
  } catch (error) {
    emit('error', error)
  }
}

/**
 * Create a new quiz attempt
 */
const createAttempt = async (questionIds: number[]): Promise<void> => {
  try {
    const response = await startAttempt(questionIds)
    attemptId.value = response.attempt_id
    emit('attempt-started', response.attempt_id)
  } catch (error) {
    emit('error', error)
  }
}

/**
 * Handle answer selection
 */
const handleAnswerSelected = async (record: AnswerRecord): Promise<void> => {
  emit('answer-selected', record)

  // Submit answer to backend if attempt ID exists
  if (attemptId.value) {
    try {
      if (isOnline.value) {
        await submitAnswer(
          attemptId.value,
          record.questionId as number,
          record.selectedOptionId as number,
          record.selectedText,
          record.timeSpentSec
        )
      } else {
        // Queue answer for later submission
        queueAnswer(attemptId.value, record.questionId as number, record)
      }
    } catch (error) {
      // If submission fails, queue the answer
      queueAnswer(attemptId.value, record.questionId as number, record)
      emit('error', error)
    }
  }
}

/**
 * Handle question change
 */
const handleQuestionChanged = (index: number): void => {
  emit('question-changed', index)
}

/**
 * Handle quiz completion
 */
const handleQuizCompleted = (result: QuizResult): void => {
  emit('quiz-completed', result)
}

/**
 * Handle quiz review enter
 */
const handleQuizReviewEnter = (): void => {
  emit('quiz-review-enter')
}

/**
 * Handle time warning
 */
const handleTimeWarning = (remainingSeconds: number): void => {
  emit('time-warning', remainingSeconds)
}

/**
 * Handle retry action
 */
const handleRetry = async (): Promise<void> => {
  isRetrying.value = true
  clearError()

  try {
    await initializeQuiz()
  } catch (error) {
    // Error is already handled by initializeQuiz
  } finally {
    isRetrying.value = false
  }
}

/**
 * Sync queued answers
 */
const syncQueuedAnswers = async (): Promise<void> => {
  if (!isOnline.value) return

  isSyncing.value = true

  try {
    await processQueuedAnswers()
  } catch (error) {
    handleError(error)
  } finally {
    isSyncing.value = false
  }
}

/**
 * Handle online/offline status changes
 */
const handleOnlineStatusChange = (): void => {
  isOnline.value = navigator.onLine

  // Try to sync queued answers when coming back online
  if (isOnline.value && hasQueuedAnswers()) {
    syncQueuedAnswers()
  }
}

// ============================================================================
// Lifecycle Hooks
// ============================================================================

onMounted(async () => {
  // Initialize quiz
  await initializeQuiz()

  // Listen for online/offline events
  window.addEventListener('online', handleOnlineStatusChange)
  window.addEventListener('offline', handleOnlineStatusChange)
})

onUnmounted(() => {
  // Clean up event listeners
  window.removeEventListener('online', handleOnlineStatusChange)
  window.removeEventListener('offline', handleOnlineStatusChange)
})

// Watch for quiz prop changes
watch(
  () => props.quiz,
  (newQuiz) => {
    if (newQuiz && newQuiz.length > 0) {
      quiz.value = newQuiz
    }
  }
)
</script>

<style scoped>
.quiz-engine-wrapper {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

.offline-indicator,
.queued-indicator {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  margin-bottom: 1rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.offline-indicator {
  background-color: #fef3c7;
  border: 1px solid #fbbf24;
  color: #92400e;
}

.queued-indicator {
  background-color: #dbeafe;
  border: 1px solid #3b82f6;
  color: #1e40af;
}

.offline-icon {
  flex-shrink: 0;
  width: 1.5rem;
  height: 1.5rem;
}

.sync-button {
  margin-left: auto;
  padding: 0.375rem 0.75rem;
  background-color: #3b82f6;
  color: #ffffff;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.sync-button:hover:not(:disabled) {
  background-color: #2563eb;
}

.sync-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 640px) {
  .offline-indicator,
  .queued-indicator {
    flex-direction: column;
    align-items: flex-start;
  }

  .sync-button {
    margin-left: 0;
    width: 100%;
  }
}
</style>
