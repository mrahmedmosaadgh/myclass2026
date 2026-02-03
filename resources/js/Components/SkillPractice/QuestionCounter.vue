<template>
  <div class="flex items-center justify-between bg-white rounded-lg shadow px-4 py-2">
    <div class="flex items-center">
      <div class="text-sm font-medium text-gray-700">
        Question {{ currentQuestionNumber }} of {{ totalQuestions }}
      </div>
      <div class="ml-4 flex items-center">
        <div class="mr-2 text-lg">🔥</div>
        <div class="text-sm font-bold text-gray-800">{{ streak }}</div>
      </div>
    </div>
    <div class="text-sm text-gray-600">
      Accuracy: {{ accuracy }}%
    </div>
  </div>
</template>

<script>
export default {
  name: 'QuestionCounter',
  props: {
    currentQuestionNumber: {
      type: Number,
      required: true
    },
    totalQuestions: {
      type: Number,
      default: 10
    },
    streak: {
      type: Number,
      default: 0
    },
    accuracy: {
      type: Number,
      default: 0
    }
  }
};
</script>