<template>
  <div class="app-header mb-8 text-center">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">
      Vocabulary Flashcards
    </h1>
    <p class="text-gray-600">
      {{ headerDescription }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentMode: {
    type: String,
    required: true
  }
})

const headerDescription = computed(() => {
  if (props.currentMode === 'practice') {
    return 'Practice mode: Click on cards to see translations • Press 🔊 to hear pronunciation'
  } else {
    return 'Quiz mode: Rate your knowledge and track your progress • "Yes" cards will be removed from the deck'
  }
})
</script>

<style scoped>
.app-header {
  /* Additional header styles if needed */
}
</style>