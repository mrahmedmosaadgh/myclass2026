<template>
  <div class="vocabulary-flashcards-app">
    <!-- Header -->
    <AppHeader 
      :current-mode="currentMode"
      @mode-change="handleModeChange"
    />

    <!-- Progress Board (only in quiz mode) -->
    <ProgressBoard 
      v-if="currentMode === 'quiz'"
      :total-score="totalScore"
      :vocabulary-count="filteredVocabulary.length"
      :answered-cards="answeredCards"
      :score-percentage="scorePercentage"
      :yes-count="yesCount"
      :no-count="noCount"
      :not-yet-count="notYetCount"
    />

    <!-- Mode Toggle -->
    <ModeToggle 
      :current-mode="currentMode"
      @mode-change="handleModeChange"
    />

    <!-- Empty State -->
    <EmptyState v-if="!filteredVocabulary || filteredVocabulary.length === 0" />

    <!-- Flashcards Grid -->
    <FlashcardsGrid 
      v-else
      :vocabulary="filteredVocabulary"
      :mode="currentMode"
      :flipped-cards="flippedCards"
      :is-playing="isPlaying"
      :card-scores="cardScores"
      @toggle-card="toggleCard"
      @play-audio="playAudio"
      @set-score="setScore"
    />

    <!-- Instructions -->
    <Instructions :mode="currentMode" />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import AppHeader from './AppHeader.vue'
import ProgressBoard from './ProgressBoard.vue'
import ModeToggle from './ModeToggle.vue'
import EmptyState from './EmptyState.vue'
import FlashcardsGrid from './FlashcardsGrid.vue'
import Instructions from './Instructions.vue'

// Props
const props = defineProps({
  vocabulary: {
    type: Array,
    required: true
  },
  initialMode: {
    type: String,
    default: 'practice'
  }
})

// Reactive state
const currentMode = ref(props.initialMode)
const flippedCards = ref(new Set())
const isPlaying = ref(new Set())
const cardScores = ref({})

// Computed properties for filtering vocabulary based on mode and scores
const filteredVocabulary = computed(() => {
  if (currentMode.value === 'practice') {
    return props.vocabulary
  }
  
  // In quiz mode, filter out cards that were answered "yes"
  return props.vocabulary.filter((_, index) => {
    return cardScores.value[index] !== 1
  })
})

// Scoring computed properties
const totalScore = computed(() => {
  return Object.values(cardScores.value).reduce((sum, score) => sum + (score || 0), 0)
})

const answeredCards = computed(() => {
  return Object.keys(cardScores.value).length
})

const scorePercentage = computed(() => {
  if (props.vocabulary.length === 0) return 0
  return Math.round((totalScore.value / props.vocabulary.length) * 100)
})

const yesCount = computed(() => {
  return Object.values(cardScores.value).filter(score => score === 1).length
})

const noCount = computed(() => {
  return Object.values(cardScores.value).filter(score => score === 0).length
})

const notYetCount = computed(() => {
  return Object.values(cardScores.value).filter(score => score === 0.5).length
})

// Methods
const handleModeChange = (newMode) => {
  currentMode.value = newMode
  // Reset flipped cards when changing modes
  flippedCards.value = new Set()
}

const toggleCard = (index) => {
  const newFlippedCards = new Set(flippedCards.value)
  if (newFlippedCards.has(index)) {
    newFlippedCards.delete(index)
  } else {
    newFlippedCards.add(index)
  }
  flippedCards.value = newFlippedCards
}

const playAudio = async (text, index) => {
  if (!('speechSynthesis' in window)) {
    console.warn('Speech synthesis not supported')
    return
  }

  try {
    const newIsPlaying = new Set(isPlaying.value)
    newIsPlaying.add(index)
    isPlaying.value = newIsPlaying

    speechSynthesis.cancel()

    const utterance = new SpeechSynthesisUtterance(text)
    utterance.lang = 'en-US'
    utterance.rate = 0.8
    utterance.pitch = 1

    utterance.onend = () => {
      const newIsPlaying = new Set(isPlaying.value)
      newIsPlaying.delete(index)
      isPlaying.value = newIsPlaying
    }

    utterance.onerror = () => {
      const newIsPlaying = new Set(isPlaying.value)
      newIsPlaying.delete(index)
      isPlaying.value = newIsPlaying
      console.warn('Speech synthesis error')
    }

    speechSynthesis.speak(utterance)
  } catch (error) {
    console.error('Error playing audio:', error)
    const newIsPlaying = new Set(isPlaying.value)
    newIsPlaying.delete(index)
    isPlaying.value = newIsPlaying
  }
}

const setScore = (originalIndex, score) => {
  cardScores.value[originalIndex] = score
  
  if (score === 1) {
    console.log(`Great job on word ${originalIndex + 1}!`)
  }
}

// Watch for mode changes to update filtered vocabulary
watch(currentMode, () => {
  // Additional logic when mode changes if needed
})
</script>

<style scoped>
.vocabulary-flashcards-app {
  width: 100%;
}
</style>