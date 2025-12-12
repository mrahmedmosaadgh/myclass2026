<template>
  <!-- Skip Links for Keyboard Navigation -->
  <div class="skip-links">
    <a href="#quiz-content" class="skip-link">Skip to quiz content</a>
    <a href="#quiz-navigation" class="skip-link">Skip to navigation</a>
  </div>

  <div 
    class="quiz-engine" 
    role="main" 
    aria-label="Quiz Assessment"
  >
    <!-- Quiz Header Section -->
    <div class="quiz-header" role="banner">
      <!-- Progress Indicator -->
      <div 
        class="progress-bar-container"
        role="progressbar"
        :aria-valuenow="Math.round(progress)"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-label="a11yProgressBar(Math.round(progress))"
      >
        <div class="progress-info">
          <span class="question-counter">
            {{ progressText(currentIndex + 1, total) }}
          </span>
          <span class="progress-percentage">
            {{ completeText(Math.round(progress)) }}
          </span>
        </div>
        <div class="progress-bar">
          <div 
            class="progress-bar-fill" 
            :style="{ width: `${progress}%` }"
          />
        </div>
      </div>

      <!-- Time Remaining (if time limit is set) -->
      <div 
        v-if="timeRemaining !== undefined" 
        class="time-remaining"
        :class="{ 'time-warning': timeRemaining < 120 }"
        role="timer"
        :aria-label="timeRemaining(formatTime(timeRemaining))"
      >
        <span aria-hidden="true">{{ timeRemaining(formatTime(timeRemaining)) }}</span>
      </div>
      
      <!-- Live region for progress announcements -->
      <div 
        class="sr-only" 
        role="status" 
        aria-live="polite" 
        aria-atomic="true"
      >
        <span v-if="isAnswered">
          {{ a11yAnnounceProgress(currentIndex + 1, total) }}
        </span>
      </div>
    </div>

    <!-- Question Content Section -->
    <div id="quiz-content" class="question-content" role="region" aria-label="Question content">
      <!-- Question Number and Text -->
      <div class="question-header">
        <h2 class="question-number" id="current-question-heading">
          {{ questionNumberText(currentQuestion.questionNumber) }}
        </h2>
      </div>
      
      <div 
        class="question-text" 
        v-html="currentQuestion.question"
        role="heading"
        aria-level="3"
        :aria-labelledby="'current-question-heading'"
      />

      <!-- Hints (if available and not yet answered) -->
      <div 
        v-if="currentQuestion.hints && currentQuestion.hints.length > 0 && !isAnswered" 
        class="hints-section"
        role="complementary"
        :aria-label="hint()"
      >
        <details>
          <summary :aria-label="`${hint()}`">{{ hint() }}</summary>
          <ul class="hints-list" role="list">
            <li v-for="(hint, index) in currentQuestion.hints" :key="index" role="listitem">
              {{ hint }}
            </li>
          </ul>
        </details>
      </div>

      <!-- Options List -->
      <ol 
        class="options-list" 
        role="listbox"
        :aria-label="a11yQuestionOptions(currentQuestion.questionNumber)"
        :aria-activedescendant="selectedIndex !== null ? `option-${currentQuestion.id}-${selectedIndex}` : undefined"
        @keydown="handleOptionsKeydown"
      >
        <li
          v-for="(option, index) in currentQuestion.answerOptions"
          :key="option.id"
          :id="`option-${currentQuestion.id}-${index}`"
          class="option-item"
          :class="{
            'selected': selectedIndex === index,
            'correct': isAnswered && option.isCorrect,
            'incorrect': isAnswered && selectedIndex === index && !option.isCorrect,
            'unselected-correct': isAnswered && selectedIndex !== index && option.isCorrect
          }"
          role="option"
          :aria-selected="selectedIndex === index"
          :aria-disabled="isAnswered && !quizConfig.allowReviewMode"
          :tabindex="index === 0 ? 0 : -1"
          @click="selectOption(index)"
          @keydown.enter.prevent="selectOption(index)"
          @keydown.space.prevent="selectOption(index)"
          @keydown.up.prevent="focusPreviousOption(index)"
          @keydown.down.prevent="focusNextOption(index)"
        >
          <div class="option-content">
            <span class="option-label">{{ String.fromCharCode(65 + index) }}</span>
            <span class="option-text">{{ option.text }}</span>
          </div>

          <!-- Feedback (shown after answering) -->
          <div v-if="isAnswered" class="option-feedback">
            <span v-if="option.isCorrect" class="feedback-icon correct">✓</span>
            <span v-else-if="selectedIndex === index" class="feedback-icon incorrect">✗</span>
            
            <!-- Rationale (if available) -->
            <div 
              v-if="option.rationale && (selectedIndex === index || (option.isCorrect && quizConfig.showRationaleOnCorrect))" 
              class="rationale"
            >
              {{ option.rationale }}
            </div>
          </div>
        </li>
      </ol>

      <!-- Global Explanation (shown after answering) -->
      <div 
        v-if="isAnswered && currentQuestion.explanation" 
        class="explanation-panel"
        role="region"
        :aria-label="explanation()"
        aria-live="polite"
      >
        <strong id="explanation-heading">{{ explanation() }}</strong>
        <p aria-labelledby="explanation-heading">{{ currentQuestion.explanation }}</p>
      </div>
    </div>

    <!-- Quiz Footer Section -->
    <div class="quiz-footer" role="contentinfo">
      <!-- Navigation Controls -->
      <div id="quiz-navigation" class="navigation-controls" role="navigation" :aria-label="a11yNavigationControls()">
        <!-- Previous Button (only in review mode) -->
        <button
          v-if="quizConfig.allowReviewMode"
          class="nav-button prev-button"
          :disabled="currentIndex === 0"
          :aria-label="previous()"
          @click="goTo(currentIndex - 1)"
        >
          {{ isRtl ? `${previous()} ←` : `← ${previous()}` }}
        </button>

        <!-- Next/Finish Button -->
        <button
          class="nav-button next-button"
          :class="{ 'finish-button': isLast }"
          :disabled="!isAnswered && !quizConfig.allowReviewMode"
          :aria-label="isLast ? finish() : next()"
          @click="goNext"
        >
          {{ isLast ? finish() : (isRtl ? `${next()} ←` : `${next()} →`) }}
        </button>
      </div>

      <!-- Question Navigator (review mode only, shown when all questions answered) -->
      <div 
        v-if="quizConfig.allowReviewMode && answers.length === total" 
        class="question-navigator"
        role="navigation"
        :aria-label="a11yQuestionNavigator()"
      >
        <div class="navigator-label">{{ a11yQuestionNavigator() }}</div>
        <div class="navigator-dots">
          <button
            v-for="(question, index) in processedQuiz"
            :key="question.id"
            class="nav-dot"
            :class="{
              'current': index === currentIndex,
              'answered': answers.some(a => a.questionId === question.id)
            }"
            :aria-label="questionNumberText(index + 1)"
            :aria-current="index === currentIndex ? 'true' : 'false'"
            @click="goTo(index)"
          >
            {{ index + 1 }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useQuizI18n } from './composables/useQuizI18n'
import type { 
  QuizQuestion, 
  QuizConfig, 
  AnswerRecord, 
  QuizResult 
} from '@/types/quiz'

// Initialize i18n
const { 
  progress: progressText, 
  complete: completeText,
  questionNumber: questionNumberText,
  next,
  previous,
  finish,
  explanation,
  hint,
  timeRemaining: timeRemainingText,
  a11yQuizRegion,
  a11yProgressBar,
  a11yQuestionOptions,
  a11yAnnounceProgress,
  a11yNavigationControls,
  a11yQuestionNavigator,
  isRtl
} = useQuizI18n()

/**
 * Format time in seconds to a readable string
 * @param seconds - Time in seconds
 * @returns Formatted time string
 */
const formatTime = (seconds: number): string => {
  const minutes = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${minutes}:${String(secs).padStart(2, '0')}`
}

/**
 * QuizEngine Component
 * 
 * Main component for rendering and managing quiz interactions.
 * Supports multiple question types, progress tracking, and configurable behavior.
 * 
 * @component
 */

// ============================================================================
// Props Definition
// ============================================================================

interface Props {
  /** Array of questions to display in the quiz */
  quiz: QuizQuestion[]
  /** Optional configuration overrides for quiz behavior */
  config?: Partial<QuizConfig>
  /** Optional attempt ID for tracking this quiz session */
  attemptId?: string | number
}

const props = withDefaults(defineProps<Props>(), {
  config: () => ({}),
  attemptId: undefined
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
}

const emit = defineEmits<Emits>()

// ============================================================================
// Default Configuration
// ============================================================================

const defaultConfig: QuizConfig = {
  allowReviewMode: false,
  autoAdvance: false,
  showRationaleOnCorrect: true,
  timeLimit: undefined,
  shuffleQuestions: false,
  shuffleOptions: false
}

const quizConfig = computed<QuizConfig>(() => ({
  ...defaultConfig,
  ...props.config
}))

// ============================================================================
// Reactive State
// ============================================================================

/** Current question index (0-based) */
const currentIndex = ref<number>(0)

/** Array of all answer records */
const answers = ref<AnswerRecord[]>([])

/** Quiz start timestamp */
const startTime = ref<Date>(new Date())

/** Current question start timestamp */
const questionStartTime = ref<Date>(new Date())

/** Time remaining in seconds (if time limit is set) */
const timeRemaining = ref<number | undefined>(
  quizConfig.value.timeLimit
)

/** Timer interval ID for countdown */
let timerInterval: number | undefined

// ============================================================================
// Utility Functions
// ============================================================================

/**
 * Fisher-Yates shuffle algorithm for randomizing arrays
 * @param array - Array to shuffle
 * @returns A new shuffled array
 */
const shuffleArray = <T>(array: T[]): T[] => {
  const shuffled = [...array]
  for (let i = shuffled.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]]
  }
  return shuffled
}

/**
 * Processes the quiz questions based on configuration
 * Applies shuffling if enabled
 */
const processedQuiz = computed<QuizQuestion[]>(() => {
  let questions = [...props.quiz]
  
  // Shuffle questions if enabled
  if (quizConfig.value.shuffleQuestions) {
    questions = shuffleArray(questions)
  }
  
  // Shuffle options for each question if enabled
  if (quizConfig.value.shuffleOptions) {
    questions = questions.map(question => ({
      ...question,
      answerOptions: shuffleArray(question.answerOptions)
    }))
  }
  
  // Re-number questions sequentially
  return questions.map((question, index) => ({
    ...question,
    questionNumber: index + 1
  }))
})

// ============================================================================
// Computed Properties
// ============================================================================

/**
 * Returns the current question object based on currentIndex
 */
const currentQuestion = computed<QuizQuestion>(() => {
  return processedQuiz.value[currentIndex.value]
})

/**
 * Checks if the current question has been answered
 */
const isAnswered = computed<boolean>(() => {
  return answers.value.some(
    answer => answer.questionId === currentQuestion.value.id
  )
})

/**
 * Returns the selected option index for the current question, or null if not answered
 */
const selectedIndex = computed<number | null>(() => {
  const answer = answers.value.find(
    a => a.questionId === currentQuestion.value.id
  )
  return answer ? answer.selectedIndex : null
})

/**
 * Finds and returns the index of the correct option for the current question
 */
const correctIndex = computed<number>(() => {
  return currentQuestion.value.answerOptions.findIndex(
    option => option.isCorrect
  )
})

/**
 * Calculates the quiz completion percentage based on answered questions
 */
const progress = computed<number>(() => {
  const answeredCount = answers.value.length
  const totalQuestions = processedQuiz.value.length
  return totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0
})

/**
 * Checks if the current question is the last question in the quiz
 */
const isLast = computed<boolean>(() => {
  return currentIndex.value === processedQuiz.value.length - 1
})

/**
 * Returns the total number of questions in the quiz
 */
const total = computed<number>(() => {
  return processedQuiz.value.length
})

// ============================================================================
// Methods
// ============================================================================

/**
 * Calculates the time spent on the current question in seconds
 */
const calculateTimeSpent = (): number => {
  const now = new Date()
  const timeSpent = Math.floor(
    (now.getTime() - questionStartTime.value.getTime()) / 1000
  )
  return Math.max(0, timeSpent)
}

/**
 * Handles option selection for the current question
 * 
 * @param index - The index of the selected option
 */
const selectOption = (index: number): void => {
  // Check if already answered and review mode is disabled
  if (isAnswered.value && !quizConfig.value.allowReviewMode) {
    return
  }

  const question = currentQuestion.value
  const selectedOption = question.answerOptions[index]
  const correctOption = question.answerOptions[correctIndex.value]

  // Create answer record with all required fields
  const answerRecord: AnswerRecord = {
    questionId: question.id,
    questionNumber: question.questionNumber,
    selectedIndex: index,
    selectedOptionId: selectedOption.id,
    selectedText: selectedOption.text,
    correct: selectedOption.isCorrect,
    question: question.question,
    correctText: correctOption.text,
    timeSpentSec: calculateTimeSpent(),
    answeredAt: new Date()
  }

  // Update or add answer to answers array
  const existingAnswerIndex = answers.value.findIndex(
    a => a.questionId === question.id
  )

  if (existingAnswerIndex !== -1) {
    // Update existing answer
    answers.value[existingAnswerIndex] = answerRecord
  } else {
    // Add new answer
    answers.value.push(answerRecord)
  }

  // Emit answer-selected event
  emit('answer-selected', answerRecord)

  // Handle auto-advance if enabled and answer is correct
  if (quizConfig.value.autoAdvance && answerRecord.correct) {
    // Small delay for user to see feedback before advancing
    setTimeout(() => {
      goNext()
    }, 1000)
  }
}

/**
 * Navigates to the next question or completes the quiz
 */
const goNext = (): void => {
  if (isLast.value) {
    // Complete the quiz
    completeQuiz()
  } else {
    // Move to next question
    currentIndex.value++
    questionStartTime.value = new Date()
    emit('question-changed', currentIndex.value)
  }
}

/**
 * Navigates to a specific question by index
 * 
 * @param index - The target question index (0-based)
 */
const goTo = (index: number): void => {
  // Validate review mode or answered status
  if (!quizConfig.value.allowReviewMode) {
    // Only allow navigation to answered questions or the next unanswered question
    const maxAllowedIndex = answers.value.length
    if (index > maxAllowedIndex) {
      return
    }
  }

  // Validate index bounds
  if (index < 0 || index >= processedQuiz.value.length) {
    return
  }

  // Update current index
  currentIndex.value = index
  questionStartTime.value = new Date()
  emit('question-changed', index)
}

/**
 * Completes the quiz and emits the quiz-completed event with results
 */
const completeQuiz = (): void => {
  // Stop the timer if running
  if (timerInterval !== undefined) {
    clearInterval(timerInterval)
    timerInterval = undefined
  }

  const correctAnswers = answers.value.filter(a => a.correct).length
  const totalQuestions = processedQuiz.value.length
  const percentage = totalQuestions > 0 
    ? (correctAnswers / totalQuestions) * 100 
    : 0

  const result: QuizResult = {
    attemptId: props.attemptId || `attempt-${Date.now()}`,
    total: totalQuestions,
    correct: correctAnswers,
    percentage: Math.round(percentage * 100) / 100, // Round to 2 decimal places
    answers: [...answers.value],
    completedAt: new Date(),
    metadata: {
      startTime: startTime.value,
      totalTimeSec: Math.floor(
        (new Date().getTime() - startTime.value.getTime()) / 1000
      )
    }
  }

  emit('quiz-completed', result)
}

// ============================================================================
// Keyboard Navigation Methods
// ============================================================================

/**
 * Focus the previous option in the list (arrow up navigation)
 * 
 * @param currentIndex - Current option index
 */
const focusPreviousOption = (currentIndex: number): void => {
  if (currentIndex > 0) {
    const previousOption = document.getElementById(
      `option-${currentQuestion.value.id}-${currentIndex - 1}`
    )
    previousOption?.focus()
  }
}

/**
 * Focus the next option in the list (arrow down navigation)
 * 
 * @param currentIndex - Current option index
 */
const focusNextOption = (currentIndex: number): void => {
  const totalOptions = currentQuestion.value.answerOptions.length
  if (currentIndex < totalOptions - 1) {
    const nextOption = document.getElementById(
      `option-${currentQuestion.value.id}-${currentIndex + 1}`
    )
    nextOption?.focus()
  }
}

/**
 * Handle keyboard navigation for the options list
 * 
 * @param event - Keyboard event
 */
const handleOptionsKeydown = (event: KeyboardEvent): void => {
  const key = event.key
  
  // Home key - focus first option
  if (key === 'Home') {
    event.preventDefault()
    const firstOption = document.getElementById(
      `option-${currentQuestion.value.id}-0`
    )
    firstOption?.focus()
  }
  
  // End key - focus last option
  if (key === 'End') {
    event.preventDefault()
    const lastIndex = currentQuestion.value.answerOptions.length - 1
    const lastOption = document.getElementById(
      `option-${currentQuestion.value.id}-${lastIndex}`
    )
    lastOption?.focus()
  }
}

// ============================================================================
// Watchers and Event Emission Logic
// ============================================================================

/**
 * Watch for all questions being answered to trigger review mode
 * When all questions are answered and review mode is enabled, emit quiz-review-enter
 */
watch(
  () => answers.value.length,
  (newLength) => {
    // Check if all questions have been answered
    if (newLength === processedQuiz.value.length && quizConfig.value.allowReviewMode) {
      emit('quiz-review-enter')
    }
  }
)

/**
 * Watch for time limit countdown
 * Emit time-warning events at specific thresholds
 */
watch(
  timeRemaining,
  (remaining) => {
    if (remaining === undefined) return

    // Emit warnings at 5 minutes, 2 minutes, and 1 minute
    const warningThresholds = [300, 120, 60] // seconds
    if (warningThresholds.includes(remaining)) {
      emit('time-warning', remaining)
    }

    // Auto-complete quiz when time runs out
    if (remaining <= 0) {
      completeQuiz()
    }
  }
)

// ============================================================================
// Lifecycle and Timer Management
// ============================================================================

/**
 * Initialize timer countdown if time limit is set
 */
const initializeTimer = (): void => {
  if (quizConfig.value.timeLimit !== undefined && quizConfig.value.timeLimit > 0) {
    timeRemaining.value = quizConfig.value.timeLimit
    
    // Start countdown timer (updates every second)
    timerInterval = window.setInterval(() => {
      if (timeRemaining.value !== undefined && timeRemaining.value > 0) {
        timeRemaining.value--
      }
    }, 1000)
  }
}

/**
 * Cleanup timer on component unmount
 */
const cleanupTimer = (): void => {
  if (timerInterval !== undefined) {
    clearInterval(timerInterval)
    timerInterval = undefined
  }
}

// Initialize timer when component mounts
onMounted(() => {
  initializeTimer()
})

onUnmounted(() => {
  cleanupTimer()
})

// Note: answer-selected, question-changed, and quiz-completed events
// are emitted directly in their respective methods (selectOption, goNext, goTo, completeQuiz)

</script>

<style scoped>
/* ============================================================================
   Skip Links for Keyboard Navigation
   ============================================================================ */
.skip-links {
  position: relative;
}

.skip-link {
  position: absolute;
  top: -40px;
  left: 0;
  background-color: #3b82f6;
  color: #ffffff;
  padding: 0.5rem 1rem;
  text-decoration: none;
  border-radius: 0.25rem;
  font-weight: 500;
  z-index: 100;
  transition: top 0.2s ease;
}

.skip-link:focus {
  top: 0;
  outline: 2px solid #1e40af;
  outline-offset: 2px;
}

/* ============================================================================
   Quiz Engine Container
   ============================================================================ */
.quiz-engine {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  padding: 1.5rem;
  font-family: system-ui, -apple-system, sans-serif;
}

/* ============================================================================
   Screen Reader Only Content
   ============================================================================ */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

/* ============================================================================
   Quiz Header - Progress Tracking
   ============================================================================ */
.quiz-header {
  margin-bottom: 2rem;
}

.progress-bar-container {
  margin-bottom: 1rem;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  color: #4b5563;
}

.question-counter {
  font-weight: 600;
}

.progress-percentage {
  color: #059669;
  font-weight: 500;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background-color: #e5e7eb;
  border-radius: 9999px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background-color: #10b981;
  transition: width 0.4s ease;
  border-radius: 9999px;
}

.time-remaining {
  padding: 0.5rem 1rem;
  background-color: #dbeafe;
  border-radius: 0.375rem;
  text-align: center;
  font-weight: 500;
  color: #1e40af;
}

.time-remaining.time-warning {
  background-color: #fee2e2;
  color: #991b1b;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* ============================================================================
   Question Content
   ============================================================================ */
.question-content {
  margin-bottom: 2rem;
}

.question-header {
  margin-bottom: 1rem;
}

.question-number {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.question-text {
  font-size: 1.125rem;
  line-height: 1.75;
  color: #111827;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background-color: #f9fafb;
  border-left: 4px solid #3b82f6;
  border-radius: 0.375rem;
}

/* ============================================================================
   Hints Section
   ============================================================================ */
.hints-section {
  margin-bottom: 1.5rem;
}

.hints-section details {
  padding: 0.75rem;
  background-color: #fef3c7;
  border-radius: 0.375rem;
  border: 1px solid #fbbf24;
}

.hints-section summary {
  cursor: pointer;
  font-weight: 500;
  color: #92400e;
  user-select: none;
}

.hints-section summary:hover {
  color: #78350f;
}

.hints-list {
  margin-top: 0.75rem;
  margin-bottom: 0;
  padding-left: 1.5rem;
  color: #92400e;
}

.hints-list li {
  margin-bottom: 0.5rem;
}

/* ============================================================================
   Options List
   ============================================================================ */
.options-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.option-item {
  margin-bottom: 0.75rem;
  padding: 1rem;
  border: 2px solid #d1d5db;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: #ffffff;
}

.option-item:hover:not([aria-disabled="true"]) {
  border-color: #3b82f6;
  background-color: #eff6ff;
}

.option-item:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.2);
}

/* Ensure focus is visible on all backgrounds */
.option-item.correct:focus-visible {
  outline-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2);
}

.option-item.incorrect:focus-visible {
  outline-color: #ef4444;
  box-shadow: 0 0 0 5px rgba(239, 68, 68, 0.2);
}

.option-item[aria-disabled="true"] {
  cursor: not-allowed;
}

.option-item.selected {
  border-color: #3b82f6;
  background-color: #dbeafe;
}

.option-item.correct {
  border-color: #10b981;
  background-color: #d1fae5;
}

.option-item.incorrect {
  border-color: #ef4444;
  background-color: #fee2e2;
}

.option-item.unselected-correct {
  border-color: #10b981;
  background-color: #ecfdf5;
}

.option-content {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.option-label {
  flex-shrink: 0;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f3f4f6;
  border-radius: 50%;
  font-weight: 600;
  color: #4b5563;
}

.option-item.selected .option-label {
  background-color: #3b82f6;
  color: #ffffff;
}

.option-item.correct .option-label {
  background-color: #10b981;
  color: #ffffff;
}

.option-item.incorrect .option-label {
  background-color: #ef4444;
  color: #ffffff;
}

.option-text {
  flex: 1;
  line-height: 1.5;
  color: #1f2937;
}

/* ============================================================================
   Option Feedback
   ============================================================================ */
.option-feedback {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
}

.feedback-icon {
  display: inline-block;
  font-size: 1.25rem;
  font-weight: bold;
  margin-right: 0.5rem;
}

.feedback-icon.correct {
  color: #10b981;
}

.feedback-icon.incorrect {
  color: #ef4444;
}

.rationale {
  margin-top: 0.5rem;
  padding: 0.75rem;
  background-color: #f9fafb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #4b5563;
  line-height: 1.5;
}

/* ============================================================================
   Explanation Panel
   ============================================================================ */
.explanation-panel {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #eff6ff;
  border-left: 4px solid #3b82f6;
  border-radius: 0.375rem;
}

.explanation-panel strong {
  display: block;
  margin-bottom: 0.5rem;
  color: #1e40af;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.explanation-panel p {
  margin: 0;
  color: #1f2937;
  line-height: 1.6;
}

/* ============================================================================
   Quiz Footer - Navigation
   ============================================================================ */
.quiz-footer {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e5e7eb;
}

.navigation-controls {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.nav-button {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 500;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 44px;
  min-width: 44px;
}

.prev-button {
  background-color: #f3f4f6;
  color: #4b5563;
}

.prev-button:hover:not(:disabled) {
  background-color: #e5e7eb;
}

.next-button {
  background-color: #3b82f6;
  color: #ffffff;
  margin-left: auto;
}

.next-button:hover:not(:disabled) {
  background-color: #2563eb;
}

.finish-button {
  background-color: #10b981;
}

.finish-button:hover:not(:disabled) {
  background-color: #059669;
}

.nav-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.nav-button:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
  box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.2);
}

.finish-button:focus-visible {
  outline-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2);
}

.prev-button:focus-visible {
  outline-color: #6b7280;
  box-shadow: 0 0 0 5px rgba(107, 114, 128, 0.2);
}

/* ============================================================================
   Question Navigator (Review Mode)
   ============================================================================ */
.question-navigator {
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
}

.navigator-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.navigator-dots {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.nav-dot {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: #ffffff;
  font-size: 0.875rem;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-dot:hover {
  border-color: #3b82f6;
  background-color: #eff6ff;
}

.nav-dot.current {
  border-color: #3b82f6;
  background-color: #3b82f6;
  color: #ffffff;
}

.nav-dot.answered {
  border-color: #10b981;
  background-color: #d1fae5;
  color: #065f46;
}

.nav-dot.answered.current {
  background-color: #10b981;
  color: #ffffff;
}

/* ============================================================================
   Responsive Design
   ============================================================================ */
@media (max-width: 640px) {
  .quiz-engine {
    padding: 1rem;
  }

  .progress-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }

  .question-text {
    font-size: 1rem;
  }

  .navigation-controls {
    flex-direction: column;
  }

  .next-button {
    margin-left: 0;
  }

  .navigator-dots {
    justify-content: center;
  }
}

/* ============================================================================
   Accessibility - High Contrast Mode
   ============================================================================ */
@media (prefers-contrast: high) {
  .option-item {
    border-width: 3px;
  }

  .progress-bar-fill {
    background-color: #047857;
  }

  .option-item:focus-visible,
  .nav-button:focus-visible,
  .nav-dot:focus-visible {
    outline-width: 4px;
    outline-style: solid;
  }
}

/* ============================================================================
   Accessibility - Focus Visible for All Interactive Elements
   ============================================================================ */
details summary:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 2px;
  border-radius: 0.25rem;
}

a:focus-visible {
  outline: 3px solid #3b82f6;
  outline-offset: 2px;
  border-radius: 0.25rem;
}

/* ============================================================================
   Accessibility - Reduced Motion
   ============================================================================ */
@media (prefers-reduced-motion: reduce) {
  .progress-bar-fill,
  .option-item,
  .nav-button,
  .nav-dot {
    transition: none;
  }

  .time-remaining.time-warning {
    animation: none;
  }
}
</style>
