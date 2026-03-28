<template>
  <div class="answer-preview">
    <!-- Multiple Choice Preview -->
    <div v-if="questionData.type === 'multiple_choice'" class="space-y-2">
      <div 
        v-for="(option, index) in questionData.options" 
        :key="index"
        class="flex items-center p-2 rounded"
        :class="answerData.selectedIndex === index ? 'bg-blue-50' : 'bg-gray-50'"
      >
        <div class="w-6 h-6 rounded-full border-2 mr-3 flex items-center justify-center"
             :class="answerData.selectedIndex === index ? 'border-blue-500 bg-blue-500' : 'border-gray-300'"
        >
          <div v-if="answerData.selectedIndex === index" class="w-2 h-2 bg-white rounded-full"></div>
        </div>
        <span :class="answerData.selectedIndex === index ? 'text-blue-800 font-medium' : 'text-gray-600'">
          {{ option }}
        </span>
      </div>
    </div>

    <!-- Multi Select Preview -->
    <div v-else-if="questionData.type === 'multi_select'" class="space-y-2">
      <div 
        v-for="(option, index) in questionData.options" 
        :key="index"
        class="flex items-center p-2 rounded"
        :class="answerData.selectedIndexes?.includes(index) ? 'bg-blue-50' : 'bg-gray-50'"
      >
        <div class="w-6 h-6 rounded border-2 mr-3 flex items-center justify-center"
             :class="answerData.selectedIndexes?.includes(index) ? 'border-blue-500 bg-blue-500' : 'border-gray-300'"
        >
          <svg v-if="answerData.selectedIndexes?.includes(index)" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <span :class="answerData.selectedIndexes?.includes(index) ? 'text-blue-800 font-medium' : 'text-gray-600'">
          {{ option }}
        </span>
      </div>
    </div>

    <!-- Text Answer Preview -->
    <div v-else-if="questionData.type === 'text'" class="p-3 bg-gray-50 rounded-lg">
      <p class="text-gray-800 whitespace-pre-wrap">{{ answerData.text || 'No answer provided' }}</p>
    </div>

    <!-- Number Answer Preview -->
    <div v-else-if="questionData.type === 'number'" class="p-3 bg-gray-50 rounded-lg">
      <p class="text-2xl font-semibold text-gray-800">{{ answerData.value || 'No answer provided' }}</p>
    </div>

    <!-- Date Answer Preview -->
    <div v-else-if="questionData.type === 'date'" class="p-3 bg-gray-50 rounded-lg">
      <p class="text-gray-800">{{ formatDate(answerData.date) || 'No answer provided' }}</p>
    </div>

    <!-- Rating Preview -->
    <div v-else-if="questionData.type === 'rating'" class="flex items-center space-x-1">
      <span
        v-for="star in (questionData.maxRating || 5)"
        :key="star"
        class="text-2xl"
        :class="star <= answerData.rating ? 'text-yellow-400' : 'text-gray-300'"
      >
        ★
      </span>
      <span class="ml-2 text-gray-600">({{ answerData.rating || 0 }}/{{ questionData.maxRating || 5 }})</span>
    </div>

    <!-- Custom Answer Preview -->
    <div v-else-if="questionData.type === 'custom'" class="p-3 bg-yellow-50 rounded-lg">
      <p class="text-yellow-800">
        <strong>Custom Answer:</strong>
      </p>
      <pre class="text-sm text-yellow-700 mt-1 whitespace-pre-wrap">{{ JSON.stringify(answerData, null, 2) }}</pre>
    </div>

    <!-- Unknown Type -->
    <div v-else class="p-3 bg-red-50 rounded-lg">
      <p class="text-red-800">
        <strong>Unknown answer type:</strong> {{ questionData.type }}
      </p>
      <pre class="text-sm text-red-700 mt-1 whitespace-pre-wrap">{{ JSON.stringify(answerData, null, 2) }}</pre>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AnswerPreview',
  props: {
    questionData: {
      type: Object,
      required: true
    },
    answerData: {
      type: Object,
      required: true
    }
  },
  methods: {
    formatDate(dateString) {
      if (!dateString) return null
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        })
      } catch (error) {
        return dateString
      }
    }
  }
}
</script>

<style scoped>
.answer-preview {
  max-width: 100%;
}
</style>
