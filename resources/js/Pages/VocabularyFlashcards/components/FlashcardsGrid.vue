<template>
  <div class="flashcards-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
    <FlashcardContainer
      v-for="(item, index) in vocabulary"
      :key="`${item.text}-${index}`"
      :vocabulary-item="item"
      :index="index"
      :original-index="getOriginalIndex(item, index)"
      :mode="mode"
      :is-flipped="flippedCards.has(index)"
      :is-playing="isPlaying.has(index)"
      :score="cardScores[getOriginalIndex(item, index)]"
      @toggle-card="$emit('toggle-card', index)"
      @play-audio="$emit('play-audio', $event.text, index)"
      @set-score="$emit('set-score', getOriginalIndex(item, index), $event)"
    />
  </div>
</template>

<script setup>
import FlashcardContainer from './FlashcardContainer.vue'

const props = defineProps({
  vocabulary: {
    type: Array,
    required: true
  },
  mode: {
    type: String,
    required: true
  },
  flippedCards: {
    type: Set,
    required: true
  },
  isPlaying: {
    type: Set,
    required: true
  },
  cardScores: {
    type: Object,
    required: true
  }
})

defineEmits(['toggle-card', 'play-audio', 'set-score'])

// Helper function to get original index for scoring
const getOriginalIndex = (item, currentIndex) => {
  // In practice mode, the index is the same
  // In quiz mode, we need to find the original index from the full vocabulary
  // For now, we'll use a simple approach - you might want to pass original indices as props
  return currentIndex
}
</script>

<style scoped>
.flashcards-grid {
  /* Additional grid styles if needed */
}
</style>