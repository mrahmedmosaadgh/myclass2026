<template>
  <div class="demo-container">
    <h1>QuestionNavigator Component Demo</h1>
    
    <div class="demo-section">
      <h2>Interactive Demo</h2>
      <p>Click on any question number to navigate. Try answering questions to see the answered state.</p>
      
      <div class="demo-controls">
        <button @click="answerCurrentQuestion" class="demo-button">
          Answer Current Question
        </button>
        <button @click="answerAllQuestions" class="demo-button">
          Answer All Questions
        </button>
        <button @click="resetAnswers" class="demo-button">
          Reset Answers
        </button>
      </div>

      <div class="current-question-display">
        <strong>Current Question:</strong> {{ currentIndex + 1 }} of {{ mockQuestions.length }}
      </div>

      <QuestionNavigator
        :questions="mockQuestions"
        :current-index="currentIndex"
        :answers="mockAnswers"
        @navigate="handleNavigate"
      />
    </div>

    <div class="demo-section">
      <h2>States</h2>
      
      <h3>Default State (No answers)</h3>
      <QuestionNavigator
        :questions="mockQuestions.slice(0, 5)"
        :current-index="0"
        :answers="[]"
        @navigate="() => {}"
      />

      <h3>Some Questions Answered</h3>
      <QuestionNavigator
        :questions="mockQuestions.slice(0, 5)"
        :current-index="2"
        :answers="[
          createMockAnswer(mockQuestions[0].id, 0),
          createMockAnswer(mockQuestions[1].id, 1)
        ]"
        @navigate="() => {}"
      />

      <h3>All Questions Answered</h3>
      <QuestionNavigator
        :questions="mockQuestions.slice(0, 5)"
        :current-index="3"
        :answers="mockQuestions.slice(0, 5).map((q, i) => createMockAnswer(q.id, i))"
        @navigate="() => {}"
      />

      <h3>Many Questions (Wrapping)</h3>
      <QuestionNavigator
        :questions="mockQuestions"
        :current-index="7"
        :answers="mockQuestions.slice(0, 10).map((q, i) => createMockAnswer(q.id, i))"
        @navigate="() => {}"
      />
    </div>

    <div class="demo-section">
      <h2>Accessibility Features</h2>
      <ul>
        <li>✓ ARIA navigation role on container</li>
        <li>✓ ARIA labels on each navigation dot</li>
        <li>✓ ARIA current attribute on active question</li>
        <li>✓ Keyboard navigation support (Tab + Enter/Space)</li>
        <li>✓ Focus indicators for keyboard users</li>
        <li>✓ Answered status announced to screen readers</li>
        <li>✓ Touch-friendly sizing (44x44px minimum)</li>
      </ul>
    </div>

    <div class="demo-section">
      <h2>Props</h2>
      <table class="props-table">
        <thead>
          <tr>
            <th>Prop</th>
            <th>Type</th>
            <th>Required</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>questions</code></td>
            <td><code>QuizQuestion[]</code></td>
            <td>Yes</td>
            <td>Array of all quiz questions</td>
          </tr>
          <tr>
            <td><code>currentIndex</code></td>
            <td><code>number</code></td>
            <td>Yes</td>
            <td>Current question index (0-based)</td>
          </tr>
          <tr>
            <td><code>answers</code></td>
            <td><code>AnswerRecord[]</code></td>
            <td>Yes</td>
            <td>Array of answer records to determine answered status</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="demo-section">
      <h2>Events</h2>
      <table class="props-table">
        <thead>
          <tr>
            <th>Event</th>
            <th>Payload</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>navigate</code></td>
            <td><code>number</code></td>
            <td>Emitted when a navigation dot is clicked with the target question index</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import QuestionNavigator from './QuestionNavigator.vue'
import type { QuizQuestion, AnswerRecord, QuestionType } from '@/types/quiz'

// Mock question type
const mockQuestionType: QuestionType = {
  id: 1,
  slug: 'multiple_choice',
  name: 'Multiple Choice',
  hasOptions: true,
  supportsHints: true,
  supportsExplanation: true
}

// Create mock questions
const mockQuestions: QuizQuestion[] = Array.from({ length: 15 }, (_, i) => ({
  id: `q${i + 1}`,
  questionNumber: i + 1,
  questionTypeId: 1,
  questionType: mockQuestionType,
  question: `Sample question ${i + 1}`,
  answerOptions: [
    { id: `q${i + 1}-a`, text: 'Option A', isCorrect: true },
    { id: `q${i + 1}-b`, text: 'Option B', isCorrect: false },
    { id: `q${i + 1}-c`, text: 'Option C', isCorrect: false },
    { id: `q${i + 1}-d`, text: 'Option D', isCorrect: false }
  ]
}))

// Interactive demo state
const currentIndex = ref(0)
const mockAnswers = ref<AnswerRecord[]>([])

// Helper function to create mock answer
const createMockAnswer = (questionId: string | number, index: number): AnswerRecord => ({
  questionId,
  questionNumber: index + 1,
  selectedIndex: 0,
  selectedOptionId: `${questionId}-a`,
  selectedText: 'Option A',
  correct: true,
  question: `Sample question ${index + 1}`,
  correctText: 'Option A',
  timeSpentSec: 15,
  answeredAt: new Date()
})

// Handle navigation
const handleNavigate = (index: number) => {
  currentIndex.value = index
  console.log('Navigated to question:', index + 1)
}

// Answer current question
const answerCurrentQuestion = () => {
  const questionId = mockQuestions[currentIndex.value].id
  const existingIndex = mockAnswers.value.findIndex(a => a.questionId === questionId)
  
  if (existingIndex === -1) {
    mockAnswers.value.push(createMockAnswer(questionId, currentIndex.value))
  }
}

// Answer all questions
const answerAllQuestions = () => {
  mockAnswers.value = mockQuestions.map((q, i) => createMockAnswer(q.id, i))
}

// Reset answers
const resetAnswers = () => {
  mockAnswers.value = []
}
</script>

<style scoped>
.demo-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  font-family: system-ui, -apple-system, sans-serif;
}

h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 1rem;
}

h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-top: 2rem;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
}

.demo-section {
  margin-bottom: 3rem;
  padding: 1.5rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.demo-controls {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.demo-button {
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.demo-button:hover {
  background-color: #2563eb;
}

.current-question-display {
  padding: 1rem;
  background-color: #dbeafe;
  border-radius: 0.375rem;
  margin-bottom: 1rem;
  font-size: 1rem;
}

.props-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
  background-color: white;
  border-radius: 0.5rem;
  overflow: hidden;
}

.props-table th,
.props-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.props-table th {
  background-color: #f3f4f6;
  font-weight: 600;
  color: #374151;
}

.props-table code {
  background-color: #f3f4f6;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  color: #dc2626;
}

ul {
  list-style: none;
  padding: 0;
}

ul li {
  padding: 0.5rem 0;
  color: #374151;
}

@media (max-width: 640px) {
  .demo-container {
    padding: 1rem;
  }

  .demo-controls {
    flex-direction: column;
  }

  .demo-button {
    width: 100%;
  }
}
</style>
