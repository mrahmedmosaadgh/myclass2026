<template>
  <div class="quiz-test-container">
    <h1>Quiz Configuration Test</h1>
    
    <div class="config-panel">
      <h2>Configuration Options</h2>
      <div class="config-options">
        <label>
          <input type="checkbox" v-model="config.allowReviewMode" />
          Allow Review Mode
        </label>
        <label>
          <input type="checkbox" v-model="config.autoAdvance" />
          Auto Advance on Correct
        </label>
        <label>
          <input type="checkbox" v-model="config.showRationaleOnCorrect" />
          Show Rationale on Correct
        </label>
        <label>
          <input type="checkbox" v-model="config.shuffleQuestions" />
          Shuffle Questions
        </label>
        <label>
          <input type="checkbox" v-model="config.shuffleOptions" />
          Shuffle Options
        </label>
        <label>
          <input type="checkbox" v-model="enableTimeLimit" />
          Enable Time Limit (60 seconds)
        </label>
      </div>
      <button @click="resetQuiz" class="reset-button">Reset Quiz</button>
    </div>

    <div class="quiz-container">
      <QuizEngine
        v-if="showQuiz"
        :quiz="sampleQuiz"
        :config="computedConfig"
        :attemptId="attemptId"
        @answer-selected="onAnswerSelected"
        @question-changed="onQuestionChanged"
        @quiz-completed="onQuizCompleted"
        @quiz-review-enter="onReviewEnter"
        @time-warning="onTimeWarning"
      />
    </div>

    <div class="event-log">
      <h2>Event Log</h2>
      <div class="log-entries">
        <div v-for="(event, index) in eventLog" :key="index" class="log-entry">
          <strong>{{ event.type }}:</strong> {{ event.message }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import QuizEngine from './QuizEngine.vue'
import type { QuizQuestion, QuizConfig, AnswerRecord, QuizResult } from '@/types/quiz'

// Configuration state
const config = ref<Partial<QuizConfig>>({
  allowReviewMode: false,
  autoAdvance: false,
  showRationaleOnCorrect: true,
  shuffleQuestions: false,
  shuffleOptions: false
})

const enableTimeLimit = ref(false)
const showQuiz = ref(true)
const attemptId = ref(`test-${Date.now()}`)
const eventLog = ref<Array<{ type: string; message: string }>>([])

const computedConfig = computed<Partial<QuizConfig>>(() => ({
  ...config.value,
  timeLimit: enableTimeLimit.value ? 60 : undefined
}))

// Sample quiz data
const sampleQuiz: QuizQuestion[] = [
  {
    id: 1,
    questionNumber: 1,
    questionTypeId: 1,
    questionType: {
      id: 1,
      slug: 'multiple_choice',
      name: 'Multiple Choice',
      hasOptions: true,
      supportsHints: true,
      supportsExplanation: true
    },
    question: 'What is 2 + 2?',
    answerOptions: [
      { id: 1, text: '3', isCorrect: false, rationale: 'Incorrect. 2 + 2 = 4' },
      { id: 2, text: '4', isCorrect: true, rationale: 'Correct! 2 + 2 = 4' },
      { id: 3, text: '5', isCorrect: false, rationale: 'Incorrect. 2 + 2 = 4' },
      { id: 4, text: '6', isCorrect: false, rationale: 'Incorrect. 2 + 2 = 4' }
    ],
    hints: ['Think about basic addition', 'Count on your fingers'],
    explanation: 'Basic arithmetic: 2 + 2 equals 4'
  },
  {
    id: 2,
    questionNumber: 2,
    questionTypeId: 1,
    questionType: {
      id: 1,
      slug: 'multiple_choice',
      name: 'Multiple Choice',
      hasOptions: true,
      supportsHints: true,
      supportsExplanation: true
    },
    question: 'What is the capital of France?',
    answerOptions: [
      { id: 5, text: 'London', isCorrect: false, rationale: 'London is the capital of England' },
      { id: 6, text: 'Paris', isCorrect: true, rationale: 'Correct! Paris is the capital of France' },
      { id: 7, text: 'Berlin', isCorrect: false, rationale: 'Berlin is the capital of Germany' },
      { id: 8, text: 'Madrid', isCorrect: false, rationale: 'Madrid is the capital of Spain' }
    ],
    explanation: 'Paris has been the capital of France for centuries'
  },
  {
    id: 3,
    questionNumber: 3,
    questionTypeId: 1,
    questionType: {
      id: 1,
      slug: 'multiple_choice',
      name: 'Multiple Choice',
      hasOptions: true,
      supportsHints: true,
      supportsExplanation: true
    },
    question: 'Which planet is closest to the Sun?',
    answerOptions: [
      { id: 9, text: 'Venus', isCorrect: false, rationale: 'Venus is the second planet' },
      { id: 10, text: 'Mercury', isCorrect: true, rationale: 'Correct! Mercury is closest to the Sun' },
      { id: 11, text: 'Earth', isCorrect: false, rationale: 'Earth is the third planet' },
      { id: 12, text: 'Mars', isCorrect: false, rationale: 'Mars is the fourth planet' }
    ],
    explanation: 'Mercury orbits closest to the Sun in our solar system'
  }
]

// Event handlers
const onAnswerSelected = (record: AnswerRecord) => {
  eventLog.value.push({
    type: 'Answer Selected',
    message: `Q${record.questionNumber}: ${record.correct ? 'Correct' : 'Incorrect'} (${record.timeSpentSec}s)`
  })
}

const onQuestionChanged = (index: number) => {
  eventLog.value.push({
    type: 'Question Changed',
    message: `Navigated to question ${index + 1}`
  })
}

const onQuizCompleted = (result: QuizResult) => {
  eventLog.value.push({
    type: 'Quiz Completed',
    message: `Score: ${result.correct}/${result.total} (${result.percentage}%)`
  })
}

const onReviewEnter = () => {
  eventLog.value.push({
    type: 'Review Mode',
    message: 'Entered review mode - all questions answered'
  })
}

const onTimeWarning = (remainingSeconds: number) => {
  eventLog.value.push({
    type: 'Time Warning',
    message: `${remainingSeconds} seconds remaining`
  })
}

const resetQuiz = () => {
  showQuiz.value = false
  attemptId.value = `test-${Date.now()}`
  eventLog.value = []
  setTimeout(() => {
    showQuiz.value = true
  }, 100)
}
</script>

<style scoped>
.quiz-test-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

h1 {
  text-align: center;
  margin-bottom: 2rem;
  color: #1f2937;
}

.config-panel {
  background-color: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.config-panel h2 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #374151;
  font-size: 1.25rem;
}

.config-options {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.config-options label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 0.25rem;
  transition: background-color 0.2s;
}

.config-options label:hover {
  background-color: #e5e7eb;
}

.config-options input[type="checkbox"] {
  width: 1.25rem;
  height: 1.25rem;
  cursor: pointer;
}

.reset-button {
  padding: 0.75rem 1.5rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.reset-button:hover {
  background-color: #2563eb;
}

.quiz-container {
  margin-bottom: 2rem;
}

.event-log {
  background-color: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1.5rem;
}

.event-log h2 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #374151;
  font-size: 1.25rem;
}

.log-entries {
  max-height: 300px;
  overflow-y: auto;
}

.log-entry {
  padding: 0.5rem;
  margin-bottom: 0.5rem;
  background-color: white;
  border-left: 3px solid #3b82f6;
  border-radius: 0.25rem;
  font-size: 0.875rem;
}

.log-entry strong {
  color: #3b82f6;
  margin-right: 0.5rem;
}
</style>
