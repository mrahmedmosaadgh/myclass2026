<template>
  <div class="question-renderer bg-white rounded-lg shadow-md p-6">
    <!-- Question Header -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-lg font-semibold text-gray-800">
          {{ questionData.title || 'Question' }}
        </h3>
        <div v-if="questionData.timeLimit" class="text-sm text-gray-500">
          <span class="inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ formatTime(questionData.timeLimit) }}
          </span>
        </div>
      </div>
      
      <!-- Question Metadata -->
      <div v-if="questionData.metadata" class="flex flex-wrap gap-2 text-xs text-gray-600">
        <span v-if="questionData.metadata.subject" class="px-2 py-1 bg-blue-100 text-blue-700 rounded">
          {{ questionData.metadata.subject }}
        </span>
        <span v-if="questionData.metadata.difficulty" class="px-2 py-1 bg-gray-100 text-gray-700 rounded">
          {{ questionData.metadata.difficulty }}
        </span>
        <span v-if="questionData.points" class="px-2 py-1 bg-green-100 text-green-700 rounded">
          {{ questionData.points }} points
        </span>
      </div>
    </div>

    <!-- Question Description -->
    <div v-if="questionData.description" class="mb-4 text-gray-700">
      {{ questionData.description }}
    </div>

    <!-- Multiple Choice - Single Selection -->
    <div v-if="questionData.type === 'multiple_choice'" class="space-y-3">
      <div
        v-for="(option, index) in questionData.options"
        :key="index"
        class="relative"
      >
        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
               :class="getOptionClass(index)"
               @click="handleOptionClick(index)"
        >
          <input
            type="radio"
            :name="`question-${questionData.id}`"
            :value="index"
            :checked="selectedAnswer === index"
            :disabled="readonly"
            class="sr-only"
            @change="handleOptionChange(index)"
          >
          <div class="flex items-center w-full">
            <div class="w-6 h-6 rounded-full border-2 mr-3 flex items-center justify-center transition-colors"
                 :class="selectedAnswer === index ? 'border-blue-500 bg-blue-500' : 'border-gray-300'"
            >
              <div v-if="selectedAnswer === index" class="w-2 h-2 bg-white rounded-full"></div>
            </div>
            <span class="text-gray-800">{{ option }}</span>
          </div>
        </label>
      </div>
    </div>

    <!-- Multiple Choice - Multiple Selection -->
    <div v-else-if="questionData.type === 'multi_select'" class="space-y-3">
      <div
        v-for="(option, index) in questionData.options"
        :key="index"
        class="relative"
      >
        <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
               :class="getOptionClass(index)"
               @click="handleMultiSelectClick(index)"
        >
          <input
            type="checkbox"
            :name="`question-${questionData.id}-${index}`"
            :value="index"
            :checked="selectedAnswers.includes(index)"
            :disabled="readonly"
            class="sr-only"
            @change="handleMultiSelectChange(index)"
          >
          <div class="flex items-center w-full">
            <div class="w-6 h-6 rounded border-2 mr-3 flex items-center justify-center transition-colors"
                 :class="selectedAnswers.includes(index) ? 'border-blue-500 bg-blue-500' : 'border-gray-300'"
            >
              <svg v-if="selectedAnswers.includes(index)" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <span class="text-gray-800">{{ option }}</span>
          </div>
        </label>
      </div>
    </div>

    <!-- Text Input -->
    <div v-else-if="questionData.type === 'text'" class="space-y-3">
      <textarea
        v-model="textAnswer"
        :disabled="readonly"
        :placeholder="questionData.placeholder || 'Enter your answer...'"
        :maxlength="questionData.rules?.maxLength"
        :minlength="questionData.rules?.minLength"
        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
        :class="{ 'bg-gray-50': readonly }"
        rows="4"
        @input="handleTextInput"
      ></textarea>
      <div v-if="questionData.rules?.maxLength" class="text-sm text-gray-500 text-right">
        {{ textAnswer.length }} / {{ questionData.rules.maxLength }} characters
      </div>
    </div>

    <!-- Number Input -->
    <div v-else-if="questionData.type === 'number'" class="space-y-3">
      <input
        v-model.number="numberAnswer"
        type="number"
        :disabled="readonly"
        :placeholder="questionData.placeholder || 'Enter a number...'"
        :min="questionData.rules?.min"
        :max="questionData.rules?.max"
        :step="questionData.rules?.step || 1"
        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        :class="{ 'bg-gray-50': readonly }"
        @input="handleNumberInput"
      >
      <div v-if="questionData.rules" class="text-sm text-gray-500">
        <span v-if="questionData.rules.min">Min: {{ questionData.rules.min }}</span>
        <span v-if="questionData.rules.max && questionData.rules.min"> | </span>
        <span v-if="questionData.rules.max">Max: {{ questionData.rules.max }}</span>
      </div>
    </div>

    <!-- Date Input -->
    <div v-else-if="questionData.type === 'date'" class="space-y-3">
      <input
        v-model="dateAnswer"
        type="date"
        :disabled="readonly"
        :min="questionData.rules?.minDate"
        :max="questionData.rules?.maxDate"
        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        :class="{ 'bg-gray-50': readonly }"
        @input="handleDateInput"
      >
    </div>

    <!-- Rating Scale -->
    <div v-else-if="questionData.type === 'rating'" class="space-y-3">
      <div class="flex items-center justify-center space-x-2">
        <button
          v-for="star in (questionData.maxRating || 5)"
          :key="star"
          type="button"
          :disabled="readonly"
          class="text-3xl transition-colors"
          :class="star <= ratingAnswer ? 'text-yellow-400' : 'text-gray-300'"
          @click="handleRatingClick(star)"
        >
          ★
        </button>
      </div>
      <div class="text-center text-sm text-gray-500">
        {{ ratingAnswer }} / {{ questionData.maxRating || 5 }}
      </div>
    </div>

    <!-- Custom Type -->
    <div v-else-if="questionData.type === 'custom'" class="space-y-3">
      <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-yellow-800">
          <strong>Custom Question Type:</strong> {{ questionData.customRenderer }}
        </p>
        <p class="text-sm text-yellow-700 mt-1">
          This question type requires a custom renderer component.
        </p>
      </div>
    </div>

    <!-- Unknown Type -->
    <div v-else class="space-y-3">
      <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
        <p class="text-red-800">
          <strong>Unknown Question Type:</strong> {{ questionData.type }}
        </p>
        <p class="text-sm text-red-700 mt-1">
          This question type is not supported by the renderer.
        </p>
      </div>
    </div>

    <!-- Validation Messages -->
    <div v-if="validationError" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-800 text-sm">{{ validationError }}</p>
    </div>

    <!-- Required Indicator -->
    <div v-if="questionData.rules?.required" class="mt-4 text-sm text-gray-500">
      * This question is required
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
  name: 'QuestionRenderer',
  props: {
    questionData: {
      type: Object,
      required: true
    },
    readonly: {
      type: Boolean,
      default: false
    },
    initialAnswer: {
      type: [Object, String, Number, Array],
      default: null
    }
  },
  emits: ['answer-changed'],
  setup(props, { emit }) {
    // Answer state based on question type
    const selectedAnswer = ref(null)
    const selectedAnswers = ref([])
    const textAnswer = ref('')
    const numberAnswer = ref(null)
    const dateAnswer = ref('')
    const ratingAnswer = ref(0)
    const validationError = ref('')

    // Initialize with initial answer if provided
    const initializeAnswer = () => {
      if (!props.initialAnswer) return

      switch (props.questionData.type) {
        case 'multiple_choice':
          selectedAnswer.value = props.initialAnswer.selectedIndex
          break
        case 'multi_select':
          selectedAnswers.value = props.initialAnswer.selectedIndexes || []
          break
        case 'text':
          textAnswer.value = props.initialAnswer.text || ''
          break
        case 'number':
          numberAnswer.value = props.initialAnswer.value
          break
        case 'date':
          dateAnswer.value = props.initialAnswer.date || ''
          break
        case 'rating':
          ratingAnswer.value = props.initialAnswer.rating || 0
          break
      }
    }

    // Get option styling class
    const getOptionClass = (index) => {
      const isSelected = props.questionData.type === 'multiple_choice' 
        ? selectedAnswer.value === index 
        : selectedAnswers.value.includes(index)
      
      return isSelected
        ? 'border-blue-500 bg-blue-50'
        : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
    }

    // Handle single selection
    const handleOptionClick = (index) => {
      if (props.readonly) return
      handleOptionChange(index)
    }

    const handleOptionChange = (index) => {
      selectedAnswer.value = index
      emitAnswerChanged()
    }

    // Handle multiple selection
    const handleMultiSelectClick = (index) => {
      if (props.readonly) return
      handleMultiSelectChange(index)
    }

    const handleMultiSelectChange = (index) => {
      const selectedIndex = selectedAnswers.value.indexOf(index)
      
      if (selectedIndex > -1) {
        selectedAnswers.value.splice(selectedIndex, 1)
      } else {
        selectedAnswers.value.push(index)
      }
      
      emitAnswerChanged()
    }

    // Handle text input
    const handleTextInput = () => {
      validationError.value = ''
      emitAnswerChanged()
    }

    // Handle number input
    const handleNumberInput = () => {
      validationError.value = ''
      emitAnswerChanged()
    }

    // Handle date input
    const handleDateInput = () => {
      emitAnswerChanged()
    }

    // Handle rating
    const handleRatingClick = (rating) => {
      if (props.readonly) return
      ratingAnswer.value = rating
      emitAnswerChanged()
    }

    // Validate answer
    const validateAnswer = () => {
      validationError.value = ''
      const rules = props.questionData.rules || {}

      // Required validation
      if (rules.required) {
        const hasAnswer = getAnswerValue()
        if (!hasAnswer) {
          validationError.value = 'This question is required'
          return false
        }
      }

      // Type-specific validation
      switch (props.questionData.type) {
        case 'multi_select':
          const selectionCount = selectedAnswers.value.length
          if (rules.minSelection && selectionCount < rules.minSelection) {
            validationError.value = `Select at least ${rules.minSelection} option(s)`
            return false
          }
          if (rules.maxSelection && selectionCount > rules.maxSelection) {
            validationError.value = `Select at most ${rules.maxSelection} option(s)`
            return false
          }
          break

        case 'text':
          if (rules.minLength && textAnswer.value.length < rules.minLength) {
            validationError.value = `Minimum ${rules.minLength} characters required`
            return false
          }
          if (rules.maxLength && textAnswer.value.length > rules.maxLength) {
            validationError.value = `Maximum ${rules.maxLength} characters allowed`
            return false
          }
          break

        case 'number':
          if (rules.min !== undefined && numberAnswer.value < rules.min) {
            validationError.value = `Minimum value is ${rules.min}`
            return false
          }
          if (rules.max !== undefined && numberAnswer.value > rules.max) {
            validationError.value = `Maximum value is ${rules.max}`
            return false
          }
          break
      }

      return true
    }

    // Get current answer value
    const getAnswerValue = () => {
      switch (props.questionData.type) {
        case 'multiple_choice':
          return selectedAnswer.value !== null
        case 'multi_select':
          return selectedAnswers.value.length > 0
        case 'text':
          return textAnswer.value.trim().length > 0
        case 'number':
          return numberAnswer.value !== null && !isNaN(numberAnswer.value)
        case 'date':
          return dateAnswer.value !== ''
        case 'rating':
          return ratingAnswer.value > 0
        default:
          return false
      }
    }

    // Get formatted answer data
    const getAnswerData = () => {
      switch (props.questionData.type) {
        case 'multiple_choice':
          return { selectedIndex: selectedAnswer.value }
        case 'multi_select':
          return { selectedIndexes: [...selectedAnswers.value] }
        case 'text':
          return { text: textAnswer.value.trim() }
        case 'number':
          return { value: numberAnswer.value }
        case 'date':
          return { date: dateAnswer.value }
        case 'rating':
          return { rating: ratingAnswer.value }
        default:
          return null
      }
    }

    // Emit answer changed
    const emitAnswerChanged = () => {
      const isValid = validateAnswer()
      const answerData = getAnswerData()
      
      emit('answer-changed', {
        questionId: props.questionData.id,
        answer: answerData,
        isValid,
        validationError: validationError.value
      })
    }

    // Format time helper
    const formatTime = (seconds) => {
      const mins = Math.floor(seconds / 60)
      const secs = seconds % 60
      return `${mins}:${secs.toString().padStart(2, '0')}`
    }

    // Watch for question data changes
    watch(() => props.questionData, () => {
      // Reset answer when question changes
      selectedAnswer.value = null
      selectedAnswers.value = []
      textAnswer.value = ''
      numberAnswer.value = null
      dateAnswer.value = ''
      ratingAnswer.value = 0
      validationError.value = ''
      
      initializeAnswer()
    }, { immediate: true })

    // Initialize on mount
    initializeAnswer()

    return {
      // State
      selectedAnswer,
      selectedAnswers,
      textAnswer,
      numberAnswer,
      dateAnswer,
      ratingAnswer,
      validationError,

      // Methods
      getOptionClass,
      handleOptionClick,
      handleOptionChange,
      handleMultiSelectClick,
      handleMultiSelectChange,
      handleTextInput,
      handleNumberInput,
      handleDateInput,
      handleRatingClick,
      formatTime
    }
  }
}
</script>

<style scoped>
.question-renderer {
  max-width: 100%;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
