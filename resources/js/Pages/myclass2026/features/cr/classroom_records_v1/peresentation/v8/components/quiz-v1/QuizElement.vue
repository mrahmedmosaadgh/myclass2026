<script setup>
import { computed, ref } from 'vue'
import { usePresentationStore } from '../../stores/presentationStore.js'
import { useUIStore } from '../../stores/uiStore.js'
import QuizQuestionView from './QuizQuestionView.vue'
import QuizNavigation from './QuizNavigation.vue'
import QuizResults from './QuizResults.vue'

const props = defineProps({
  element: Object,
  isPresentMode: {
    type: Boolean,
    default: false
  }
})

const presentation = usePresentationStore()
const ui = useUIStore()

// Local state for quiz interaction
const selectedAnswer = ref(null)
const showCorrect = ref(false)

// Computed properties
const currentQuestion = computed(() => {
  return props.element.questions[props.element.currentQuestionIndex] || null
})

const isInteractive = computed(() => {
  return props.isPresentMode && props.element.isInteractive
})

// Methods
function handleAnswer(answerIndex) {
  if (!isInteractive.value) return
  
  selectedAnswer.value = answerIndex
  showCorrect.value = true
  
  // Store user answer
  presentation.updateQuizAnswer(props.element.id, props.element.currentQuestionIndex, answerIndex)
  
  // Auto-advance after showing correct answer
  setTimeout(() => {
    if (props.element.currentQuestionIndex < props.element.questions.length - 1) {
      nextQuestion()
    } else {
      // Show results if this is the last question
      presentation.updateQuizProperty(props.element.id, 'showResults', true)
    }
  }, 2000)
}

function previousQuestion() {
  if (props.element.currentQuestionIndex > 0) {
    presentation.updateQuizProperty(props.element.id, 'currentQuestionIndex', props.element.currentQuestionIndex - 1)
    resetQuestionState()
  }
}

function nextQuestion() {
  if (props.element.currentQuestionIndex < props.element.questions.length - 1) {
    presentation.updateQuizProperty(props.element.id, 'currentQuestionIndex', props.element.currentQuestionIndex + 1)
    resetQuestionState()
  }
}

function goToQuestion(index) {
  presentation.updateQuizProperty(props.element.id, 'currentQuestionIndex', index)
  resetQuestionState()
}

function showResults() {
  presentation.updateQuizProperty(props.element.id, 'showResults', true)
}

function resetQuestionState() {
  selectedAnswer.value = null
  showCorrect.value = false
  
  // Set selected answer if user has already answered this question
  const userAnswer = props.element.userAnswers?.[props.element.currentQuestionIndex]
  if (userAnswer !== undefined) {
    selectedAnswer.value = userAnswer
    showCorrect.value = true
  }
}

function isAnswered(index) {
  return props.element.userAnswers?.[index] !== undefined
}

// Initialize question state
resetQuestionState()
</script>

<template>
  <div 
    class="quiz-element" 
    :class="{ 
      'is-interactive': isInteractive,
      'is-present-mode': isPresentMode 
    }"
    :style="{
      left: element.x + 'px',
      top: element.y + 'px',
      width: element.width + 'px',
      height: element.height + 'px',
      backgroundColor: element.backgroundColor || '#6366f1',
      borderRadius: (element.borderRadius || '8px')
    }"
  >
    <div class="quiz-content">
      <!-- Quiz Header -->
      <div class="quiz-header">
        <h3 class="quiz-title">{{ element.title }}</h3>
        <div class="quiz-progress" v-if="!element.showResults">
          Question {{ element.currentQuestionIndex + 1 }} of {{ element.questions.length }}
        </div>
      </div>
      
      <!-- Question View -->
      <QuizQuestionView
        v-if="currentQuestion && !element.showResults"
        :question="currentQuestion"
        :is-present-mode="isPresentMode"
        :is-interactive="isInteractive"
        :selected-answer="selectedAnswer"
        :show-correct="showCorrect"
        @answer="handleAnswer"
      />
      
      <!-- Navigation -->
      <QuizNavigation
        v-if="!element.showResults"
        :current-index="element.currentQuestionIndex"
        :total="element.questions.length"
        :user-answers="element.userAnswers"
        @previous="previousQuestion"
        @next="nextQuestion"
        @go-to="goToQuestion"
        @show-results="showResults"
      />
      
      <!-- Results -->
      <QuizResults
        v-if="element.showResults"
        :questions="element.questions"
        :user-answers="element.userAnswers"
        :quiz-title="element.title"
      />
    </div>
  </div>
</template>

<style scoped>
.quiz-element {
  position: absolute;
  display: flex;
  flex-direction: column;
  background: #6366f1;
  color: white;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.quiz-element:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 12px -1px rgba(0, 0, 0, 0.15);
}

.quiz-content {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 16px;
}

.quiz-header {
  margin-bottom: 16px;
}

.quiz-title {
  margin: 0 0 8px 0;
  font-size: 18px;
  font-weight: 600;
  color: white;
}

.quiz-progress {
  font-size: 14px;
  opacity: 0.9;
}

.is-interactive {
  cursor: pointer;
}

.is-present-mode {
  cursor: default;
}

/* Mobile-first responsive design */
@media (max-width: 767px) {
  .quiz-content {
    padding: 12px;
  }
  
  .quiz-title {
    font-size: 16px;
  }
  
  .quiz-progress {
    font-size: 12px;
  }
}
</style>
