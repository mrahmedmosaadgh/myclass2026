<template>
  <div class="student-answer-input">
    <div v-if="!questionData" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading question...</p>
    </div>
    
    <div v-else-if="hasAnswered" class="answered-state">
      <div class="success-icon">✅</div>
      <h3>Answer Submitted!</h3>
      <p>Your response has been recorded.</p>
      
      <div v-if="showAnswerReview" class="answer-review">
        <h4>Your Answer:</h4>
        <div class="review-content">
          <component :is="reviewComponent" :answer="submittedAnswer" />
        </div>
      </div>
    </div>
    
    <div v-else class="question-state">
      <!-- Time Display -->
      <div v-if="timeLimit > 0" class="timer-display">
        <div class="timer" :class="{ 'warning': timeRemaining <= 10, 'danger': timeRemaining <= 5 }">
          ⏱️ {{ formatTime(timeRemaining) }}
        </div>
      </div>
      
      <!-- Question Type Component -->
      <component 
        :is="questionComponent"
        :title="questionData.title"
        :instructions="questionData.instructions"
        :time-limit="questionData.timeLimit"
        :min-score="questionData.minScore"
        :max-score="questionData.maxScore"
        :is-edit-mode="false"
        :show-answer-key="false"
        :initial-data="questionData.questionData"
        @answer-change="onAnswerChange"
      />
      
      <!-- Submit Button -->
      <div class="submit-section">
        <button 
          @click="submitAnswer"
          :disabled="!canSubmit || isSubmitting"
          class="submit-btn"
          :class="{ 'disabled': !canSubmit, 'loading': isSubmitting }"
        >
          <span v-if="isSubmitting" class="loading-text">
            <div class="spinner"></div>
            Submitting...
          </span>
          <span v-else>Submit Answer</span>
        </button>
        
        <p v-if="!canSubmit" class="validation-message">
          {{ validationMessage }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import TextQuestion from './QuestionTypes/TextQuestion.vue'
import MultipleChoiceQuestion from './QuestionTypes/MultipleChoiceQuestion.vue'
import RatingQuestion from './QuestionTypes/RatingQuestion.vue'

const props = defineProps({
  questionData: Object,
  onSubmit: Function,
  showAnswerReview: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['answer-submitted'])

// State
const currentAnswer = ref(null)
const hasAnswered = ref(false)
const isSubmitting = ref(false)
const submittedAnswer = ref(null)
const timeRemaining = ref(0)
const timerInterval = ref(null)

// Computed
const questionComponent = computed(() => {
  const components = {
    'text': TextQuestion,
    'multiple_choice': MultipleChoiceQuestion,
    'rating': RatingQuestion
    // Add more types as needed
  }
  return components[props.questionData?.type] || TextQuestion
})

const timeLimit = computed(() => props.questionData?.timeLimit || 0)

const canSubmit = computed(() => {
  if (!currentAnswer.value) return false
  if (currentAnswer.value.type === 'text') {
    return currentAnswer.value.isValid || false
  }
  return true
})

const validationMessage = computed(() => {
  if (!currentAnswer.value) return 'Please provide an answer'
  if (currentAnswer.value.type === 'text' && !currentAnswer.value.isValid) {
    return 'Answer is too short'
  }
  return ''
})

const reviewComponent = computed(() => {
  // Return appropriate review component based on answer type
  return 'div' // Simple div for now, can be expanded
})

// Methods
const onAnswerChange = (answer) => {
  currentAnswer.value = answer
}

const submitAnswer = async () => {
  if (!canSubmit.value || isSubmitting.value) return
  
  isSubmitting.value = true
  
  try {
    const answerData = {
      ...currentAnswer.value,
      timestamp: new Date().toISOString(),
      timeSpent: timeLimit.value - timeRemaining.value
    }
    
    await props.onSubmit(answerData)
    
    submittedAnswer.value = answerData
    hasAnswered.value = true
    emit('answer-submitted', answerData)
    
  } catch (error) {
    console.error('Failed to submit answer:', error)
    // Show error message to user
  } finally {
    isSubmitting.value = false
  }
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const startTimer = () => {
  if (timeLimit.value <= 0) return
  
  timeRemaining.value = timeLimit.value
  
  timerInterval.value = setInterval(() => {
    timeRemaining.value--
    
    if (timeRemaining.value <= 0) {
      clearInterval(timerInterval.value)
      // Auto-submit if time runs out
      if (currentAnswer.value) {
        submitAnswer()
      }
    }
  }, 1000)
}

const stopTimer = () => {
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
    timerInterval.value = null
  }
}

// Lifecycle
onMounted(() => {
  startTimer()
})

onUnmounted(() => {
  stopTimer()
})
</script>

<style scoped>
.student-answer-input {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

.loading-state {
  text-align: center;
  padding: 40px;
  color: #6b7280;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top: 3px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.answered-state {
  text-align: center;
  padding: 40px;
  background: #f0fdf4;
  border-radius: 12px;
  border: 2px solid #bbf7d0;
}

.success-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.answered-state h3 {
  margin: 0 0 8px;
  color: #166534;
  font-size: 20px;
}

.answered-state p {
  margin: 0 0 24px;
  color: #15803d;
}

.answer-review {
  text-align: left;
  padding: 16px;
  background: white;
  border-radius: 8px;
  border: 1px solid #d1fae5;
}

.answer-review h4 {
  margin: 0 0 8px;
  color: #166534;
  font-size: 14px;
}

.review-content {
  padding: 8px;
  background: #f8fafc;
  border-radius: 4px;
}

.timer-display {
  text-align: center;
  margin-bottom: 24px;
}

.timer {
  display: inline-block;
  padding: 8px 16px;
  background: #f3f4f6;
  border-radius: 20px;
  font-weight: 600;
  color: #374151;
  font-size: 16px;
}

.timer.warning {
  background: #fef3c7;
  color: #d97706;
}

.timer.danger {
  background: #fee2e2;
  color: #dc2626;
  animation: pulse 1s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.submit-section {
  margin-top: 24px;
  text-align: center;
}

.submit-btn {
  padding: 12px 32px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 160px;
}

.submit-btn:hover:not(.disabled) {
  background: #2563eb;
  transform: translateY(-1px);
}

.submit-btn.disabled {
  background: #d1d5db;
  cursor: not-allowed;
  transform: none;
}

.submit-btn.loading {
  background: #60a5fa;
}

.loading-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.validation-message {
  margin-top: 8px;
  color: #ef4444;
  font-size: 14px;
}
</style>
