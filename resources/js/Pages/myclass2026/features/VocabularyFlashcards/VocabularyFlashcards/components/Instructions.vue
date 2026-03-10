<template>
  <div class="instructions mt-12 bg-blue-50 rounded-lg p-6">
    <h3 class="text-lg font-semibold text-blue-800 mb-3">How to use:</h3>
    <ul class="space-y-2 text-blue-700">
      <li class="flex items-center">
        <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
        Click on any card to flip and see the Arabic translation
      </li>
      <li class="flex items-center">
        <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
        Press the 🔊 Listen button to hear the English pronunciation
      </li>
      <li v-if="mode === 'quiz'" class="flex items-center">
        <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
        Rate your knowledge: <strong>Yes</strong> (1 point), <strong>Not Yet</strong> (0.5 points), <strong>No</strong> (0 points)
      </li>
      <li v-if="mode === 'quiz'" class="flex items-center">
        <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
        <strong>Yes</strong> cards are removed from the deck, <strong>No</strong> and <strong>Not Yet</strong> cards stay for more practice
      </li>
      <li v-if="mode === 'quiz'" class="flex items-center">
        <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
        Score 80% or higher to earn rewards and badges!
      </li>
      <li class="flex items-center">
        <span class="w-2 h-2 bg-blue-400 rounded-full mr-3"></span>
        Use keyboard (Enter/Space) to flip cards for accessibility
      </li>
    </ul>
  </div>
</template>

<script setup>
defineProps({
  mode: {
    type: String,
    required: true
  }
})
</script>

<style scoped>
.instructions {
  /* Additional instruction styles if needed */
}
</style>