<template> 
  <div
    class="flashcard-container"
    @click="$emit('toggle-card')"
    @keydown.enter="$emit('toggle-card')"
    @keydown.space.prevent="$emit('toggle-card')"
    tabindex="0"
    role="button"
    :aria-label="`Flashcard ${index + 1}: ${vocabularyItem.text}`"
  >
    <div 
      class="flashcard"
      :class="{ 'flipped': isFlipped }"
    >
      <!-- Front Face -->
       <FlashcardFace
        type="front"
        :vocabulary-item="vocabularyItem"
        :index="index"
        :is-playing="isPlaying"
        :mode="mode"
        :score="score"
        @play-audio="$emit('play-audio', $event)"
        @set-score="$emit('set-score', $event)"
      >
        <template #content>
          <div class="word-container">
            <h3 class="main-word">{{ vocabularyItem.text }}</h3>
            <div class="word-underline"></div>
          </div>
        </template>
        
        <template #actions>
          <AudioButton
            :text="vocabularyItem.text"
            :is-playing="isPlaying"
            @play-audio="$emit('play-audio', $event)"
          />
          
            <!-- <FlipIndicator text="Click to flip" /> -->
          
          <ScoringButtons
            v-if="mode === 'quiz'"
            :score="score"
            @set-score="$emit('set-score', $event)"
          />
 
        </template>
      </FlashcardFace>

      <!-- Back Face -->
      <FlashcardFace
        type="back"
        :vocabulary-item="vocabularyItem"
        :index="index"
        :is-playing="isPlaying"
        :mode="mode"
        :score="score"
        @play-audio="$emit('play-audio', $event)"
        @set-score="$emit('set-score', $event)"
      >
        <template #content>
          <div class="translation-container">
            <h3 class="main-translation" dir="rtl">{{ vocabularyItem.translation }}</h3>
            <div class="translation-underline"></div>
            <p class="original-text">{{ vocabularyItem.text }}</p>
          </div>
        </template>
        
        <template #actions>
          <AudioButton
            :text="vocabularyItem.text"
            :is-playing="isPlaying"
            @play-audio="$emit('play-audio', $event)"
          />
          
          <FlipIndicator text="Click to flip back" />
          <div class=" mb-20 bg-red-500  ">

 
            <ScoringButtons
            v-if="mode === 'quiz'"
            :score="score"
            @set-score="$emit('set-score', $event)"
            />
          </div>
        </template>
      </FlashcardFace>
    </div>
  </div>
</template>

<script setup>
import FlashcardFace from './FlashcardFace.vue'
import AudioButton from './AudioButton.vue'
import FlipIndicator from './FlipIndicator.vue'
import ScoringButtons from './ScoringButtons.vue'

defineProps({
  vocabularyItem: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    required: true
  },
  originalIndex: {
    type: Number,
    required: true
  },
  mode: {
    type: String,
    required: true
  },
  isFlipped: {
    type: Boolean,
    required: true
  },
  isPlaying: {
    type: Boolean,
    required: true
  },
  score: {
    type: Number,
    default: null
  }
})

defineEmits(['toggle-card', 'play-audio', 'set-score'])
</script>

<style scoped>
.flashcard-container {
  cursor: pointer;
  outline: none;
  border-radius: 1rem;
  perspective: 1200px;
  height: 280px;
  transition: all 0.3s ease;
}

.flashcard-container:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
  transform: translateY(-2px);
}

.flashcard-container:hover {
  transform: translateY(-4px);
}

.flashcard {
  position: relative;
  width: 100%;
  height: 100%;
  transition: transform 0.5s cubic-bezier(0.4, 0.0, 0.2, 1);
  transform-style: preserve-3d;
  -webkit-transform-style: preserve-3d;
  -moz-transform-style: preserve-3d;
}

.flashcard.flipped {
  transform: rotateY(180deg);
}

.flashcard:active {
  transition: transform 0.2s ease;
}

.word-container, .translation-container {
  width: 100%;
}

.main-word {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  line-height: 1.2;
}

.main-translation {
  font-size: 1.75rem;
  font-weight: 700;
  color: white;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  line-height: 1.2;
  font-family: 'Noto Sans Arabic', 'Arial Unicode MS', sans-serif;
}

.word-underline, .translation-underline {
  height: 3px;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
  border-radius: 2px;
  margin: 0 auto 1rem;
  width: 60%;
  animation: shimmer 2s ease-in-out infinite;
}

.original-text {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 0.5rem;
  font-style: italic;
}

@keyframes shimmer {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .flashcard-container {
    height: 240px;
  }
  
  .main-word {
    font-size: 1.25rem;
  }
  
  .main-translation {
    font-size: 1.5rem;
  }
}

@media (max-width: 640px) {
  .flashcard-container {
    height: 220px;
  }
  
  .main-word {
    font-size: 1.125rem;
  }
  
  .main-translation {
    font-size: 1.375rem;
  }
}
</style>