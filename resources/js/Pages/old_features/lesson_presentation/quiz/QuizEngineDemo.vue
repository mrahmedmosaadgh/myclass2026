<template>
  <div class="demo-container">
    <h1>Quiz Engine Demo</h1>
    
    <QuizEngine
      :quiz="sampleQuiz"
      :config="quizConfig"
      attempt-id="demo-attempt-1"
      @answer-selected="handleAnswerSelected"
      @question-changed="handleQuestionChanged"
      @quiz-completed="handleQuizCompleted"
      @quiz-review-enter="handleReviewEnter"
    />

    <!-- Event Log -->
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
import { ref } from 'vue'
import QuizEngine from './QuizEngine.vue'
import type { QuizQuestion, QuizConfig, AnswerRecord, QuizResult } from '@/types/quiz'

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
    question: 'What is the capital of France?',
    answerOptions: [
      { id: 1, text: 'London', isCorrect: false, rationale: 'London is the capital of the United Kingdom.' },
      { id: 2, text: 'Paris', isCorrect: true, rationale: 'Correct! Paris is the capital and largest city of France.' },
      { id: 3, text: 'Berlin', isCorrect: false, rationale: 'Berlin is the capital of Germany.' },
      { id: 4, text: 'Madrid', isCorrect: false, rationale: 'Madrid is the capital of Spain.' }
    ],
    explanation: 'Paris has been the capital of France since the 12th century.',
    hints: ['Think of the Eiffel Tower', 'It\'s known as the City of Light'],
    bloomLevel: 1,
    difficultyLevel: 1
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
    question: 'Which planet is known as the Red Planet?',
    answerOptions: [
      { id: 5, text: 'Venus', isCorrect: false },
      { id: 6, text: 'Mars', isCorrect: true, rationale: 'Mars appears red due to iron oxide on its surface.' },
      { id: 7, text: 'Jupiter', isCorrect: false },
      { id: 8, text: 'Saturn', isCorrect: false }
    ],
    explanation: 'Mars is called the Red Planet because of its reddish appearance caused by iron oxide.',
    difficultyLevel: 2
  },
  {
    id: 3,
    questionNumber: 3,
    questionTypeId: 2,
    questionType: {
      id: 2,
      slug: 'true_false',
      name: 'True/False',
      hasOptions: true,
      supportsHints: false,
      supportsExplanation: true
    },
    question: 'The Earth is flat.',
    answerOptions: [
      { id: 9, text: 'True', isCorrect: false, rationale: 'The Earth is an oblate spheroid, not flat.' },
      { id: 10, text: 'False', isCorrect: true, rationale: 'Correct! The Earth is roughly spherical in shape.' }
    ],
    explanation: 'Scientific evidence overwhelmingly supports that Earth is a sphere.',
    difficultyLevel: 1
  }
]

// Quiz configuration
const quizConfig: Partial<QuizConfig> = {
  allowReviewMode: true,
  autoAdvance: false,
  showRationaleOnCorrect: true
}

// Event log
interface LogEvent {
  type: string
  message: string
}

const eventLog = ref<LogEvent[]>([])

const addLog = (type: string, message: string) => {
  eventLog.value.push({ type, message })
}

const handleAnswerSelected = (record: AnswerRecord) => {
  addLog('answer-selected', `Question ${record.questionNumber}: ${record.correct ? 'Correct' : 'Incorrect'}`)
}

const handleQuestionChanged = (index: number) => {
  addLog('question-changed', `Navigated to question ${index + 1}`)
}

const handleQuizCompleted = (result: QuizResult) => {
  addLog('quiz-completed', `Score: ${result.correct}/${result.total} (${result.percentage}%)`)
}

const handleReviewEnter = () => {
  addLog('quiz-review-enter', 'Entered review mode')
}
</script>

<style scoped>
.demo-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

h1 {
  text-align: center;
  margin-bottom: 2rem;
  color: #1f2937;
}

.event-log {
  margin-top: 3rem;
  padding: 1.5rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.event-log h2 {
  margin-top: 0;
  margin-bottom: 1rem;
  font-size: 1.25rem;
  color: #374151;
}

.log-entries {
  max-height: 300px;
  overflow-y: auto;
}

.log-entry {
  padding: 0.5rem;
  margin-bottom: 0.5rem;
  background-color: #ffffff;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  font-family: monospace;
}

.log-entry strong {
  color: #3b82f6;
}
</style>
