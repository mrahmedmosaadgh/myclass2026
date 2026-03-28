<template>
  <div class="answer-input">
    <!-- Question Display -->
    <div class="mb-6">
      <QuestionRenderer
        :question-data="questionData"
        :readonly="isSubmitted"
        :initial-answer="currentAnswer"
        @answer-changed="handleAnswerChanged"
      />
    </div>

    <!-- Answer Actions -->
    <div class="space-y-4">
      <!-- Submit Button -->
      <button
        v-if="!isSubmitted && canSubmit"
        @click="handleSubmit"
        :disabled="!isValid || isSubmitting"
        class="w-full py-3 px-4 bg-blue-600 text-white rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        :class="{
          'bg-blue-600 hover:bg-blue-700': isValid && !isSubmitting,
          'bg-gray-300 cursor-not-allowed': !isValid || isSubmitting
        }"
      >
        <span v-if="isSubmitting" class="flex items-center justify-center">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Submitting...
        </span>
        <span v-else>
          {{ submitButtonText }}
        </span>
      </button>

      <!-- Validation Feedback -->
      <div v-if="!isValid && currentAnswer" class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-yellow-800 text-sm">
          <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
          {{ validationError || 'Please complete the question before submitting' }}
        </p>
      </div>

      <!-- Success Message -->
      <div v-if="isSubmitted && !hasError" class="p-4 bg-green-50 border border-green-200 rounded-lg">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <p class="text-green-800 font-medium">Answer Submitted Successfully!</p>
            <p class="text-green-700 text-sm mt-1">
              Your response has been recorded. Thank you for participating.
            </p>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="hasError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <p class="text-red-800 font-medium">Submission Failed</p>
            <p class="text-red-700 text-sm mt-1">
              {{ errorMessage || 'There was an error submitting your answer. Please try again.' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Retry Button (for error state) -->
      <button
        v-if="hasError"
        @click="handleRetry"
        class="w-full py-2 px-4 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
      >
        Try Again
      </button>

      <!-- Edit Button (for submitted state) -->
      <button
        v-if="isSubmitted && allowEdit && !hasError"
        @click="handleEdit"
        class="w-full py-2 px-4 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
      >
        Edit Answer
      </button>

      <!-- Time Remaining (if applicable) -->
      <div v-if="timeRemaining > 0 && !isSubmitted" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
          <span class="text-blue-800 text-sm font-medium">Time Remaining</span>
          <span class="text-blue-600 font-mono">{{ formatTime(timeRemaining) }}</span>
        </div>
        <div class="mt-2 bg-blue-200 rounded-full h-2">
          <div 
            class="bg-blue-600 h-2 rounded-full transition-all duration-1000"
            :style="{ width: `${(timeRemaining / totalTime) * 100}%` }"
          ></div>
        </div>
      </div>

      <!-- Time Expired -->
      <div v-if="timeExpired && !isSubmitted" class="p-3 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800 text-sm font-medium">
          <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Time expired! You can no longer submit an answer.
        </p>
      </div>
    </div>

    <!-- Answer Preview (for submitted state) -->
    <div v-if="isSubmitted && currentAnswer" class="mt-6 p-4 bg-gray-50 rounded-lg">
      <h4 class="text-sm font-medium text-gray-700 mb-2">Your Answer:</h4>
      <div class="text-gray-900">
        <AnswerPreview :question-data="questionData" :answer-data="currentAnswer" />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import QuestionRenderer from './QuestionRenderer.vue'
import AnswerPreview from './AnswerPreview.vue'

export default {
  name: 'AnswerInput',
  components: {
    QuestionRenderer,
    AnswerPreview
  },
  props: {
    questionData: {
      type: Object,
      required: true
    },
    disabled: {
      type: Boolean,
      default: false
    },
    allowEdit: {
      type: Boolean,
      default: true
    },
    autoSubmit: {
      type: Boolean,
      default: false
    },
    submitButtonText: {
      type: String,
      default: 'Submit Answer'
    }
  },
  emits: ['answer-submitted', 'answer-changed', 'edit-requested'],
  setup(props, { emit }) {
    // State
    const currentAnswer = ref(null)
    const isValid = ref(false)
    const validationError = ref('')
    const isSubmitted = ref(false)
    const isSubmitting = ref(false)
    const hasError = ref(false)
    const errorMessage = ref('')
    const timeRemaining = ref(0)
    const totalTime = ref(0)
    const timeExpired = ref(false)
    let timerInterval = null

    // Computed
    const canSubmit = computed(() => {
      return !props.disabled && 
             !isSubmitted.value && 
             !timeExpired.value && 
             isValid.value
    })

    // Initialize timer if question has time limit
    const initializeTimer = () => {
      if (props.questionData.timeLimit) {
        totalTime.value = props.questionData.timeLimit
        timeRemaining.value = props.questionData.timeLimit
        
        timerInterval = setInterval(() => {
          timeRemaining.value--
          
          if (timeRemaining.value <= 0) {
            clearInterval(timerInterval)
            timeExpired.value = true
            
            // Auto-submit if enabled and we have a valid answer
            if (props.autoSubmit && isValid.value) {
              handleSubmit()
            }
          }
        }, 1000)
      }
    }

    // Handle answer changes from QuestionRenderer
    const handleAnswerChanged = (answerData) => {
      currentAnswer.value = answerData.answer
      isValid.value = answerData.isValid
      validationError.value = answerData.validationError
      
      // Emit answer changed event
      emit('answer-changed', answerData)
    }

    // Handle submission
    const handleSubmit = async () => {
      if (!isValid.value || isSubmitting.value) return

      isSubmitting.value = true
      hasError.value = false
      errorMessage.value = ''

      try {
        // Emit submission event
        const submissionData = {
          questionId: props.questionData.id,
          answer: currentAnswer.value,
          timestamp: new Date().toISOString(),
          timeTaken: totalTime.value - timeRemaining.value
        }

        emit('answer-submitted', submissionData)
        
        // Mark as submitted (parent component might override this)
        setTimeout(() => {
          isSubmitted.value = true
          isSubmitting.value = false
        }, 500)
        
      } catch (error) {
        hasError.value = true
        errorMessage.value = error.message || 'Failed to submit answer'
        isSubmitting.value = false
      }
    }

    // Handle retry after error
    const handleRetry = () => {
      hasError.value = false
      errorMessage.value = ''
      isSubmitting.value = false
    }

    // Handle edit request
    const handleEdit = () => {
      isSubmitted.value = false
      hasError.value = false
      errorMessage.value = ''
      emit('edit-requested', {
        questionId: props.questionData.id,
        currentAnswer: currentAnswer.value
      })
    }

    // Format time helper
    const formatTime = (seconds) => {
      const mins = Math.floor(seconds / 60)
      const secs = seconds % 60
      return `${mins}:${secs.toString().padStart(2, '0')}`
    }

    // Reset component state
    const reset = () => {
      currentAnswer.value = null
      isValid.value = false
      validationError.value = ''
      isSubmitted.value = false
      isSubmitting.value = false
      hasError.value = false
      errorMessage.value = ''
      timeRemaining.value = totalTime.value
      timeExpired.value = false
      
      // Clear and restart timer
      if (timerInterval) {
        clearInterval(timerInterval)
      }
      initializeTimer()
    }

    // Watch for question data changes
    watch(() => props.questionData, () => {
      reset()
    }, { deep: true })

    // Initialize on mount
    onMounted(() => {
      initializeTimer()
    })

    // Cleanup on unmount
    onUnmounted(() => {
      if (timerInterval) {
        clearInterval(timerInterval)
      }
    })

    return {
      // State
      currentAnswer,
      isValid,
      validationError,
      isSubmitted,
      isSubmitting,
      hasError,
      errorMessage,
      timeRemaining,
      totalTime,
      timeExpired,

      // Computed
      canSubmit,

      // Methods
      handleAnswerChanged,
      handleSubmit,
      handleRetry,
      handleEdit,
      formatTime,
      reset
    }
  }
}
</script>

<style scoped>
.answer-input {
  max-width: 100%;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
