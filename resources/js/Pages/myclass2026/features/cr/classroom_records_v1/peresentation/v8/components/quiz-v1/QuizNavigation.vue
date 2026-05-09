<script setup>
const props = defineProps({
  currentIndex: {
    type: Number,
    required: true
  },
  total: {
    type: Number,
    required: true
  },
  userAnswers: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['previous', 'next', 'goTo', 'showResults'])

function isAnswered(index) {
  return props.userAnswers?.[index] !== undefined
}

function goToQuestion(index) {
  emit('goTo', index)
}

function previous() {
  emit('previous')
}

function next() {
  emit('next')
}

function showResults() {
  emit('showResults')
}
</script>

<template>
  <div class="quiz-navigation">
    <!-- Previous Button -->
    <button
      @click="previous"
      :disabled="currentIndex === 0"
      class="nav-btn prev-btn"
      title="Previous Question"
    >
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="15 18 9 12 15 6"></polyline>
      </svg>
      <span>Previous</span>
    </button>
    
    <!-- Question Indicators -->
    <div class="question-indicators">
      <div
        v-for="(_, index) in total"
        :key="index"
        class="indicator"
        :class="{
          'active': index === currentIndex,
          'answered': isAnswered(index)
        }"
        @click="goToQuestion(index)"
        :title="`Question ${index + 1}${isAnswered(index) ? ' (Answered)' : ''}`"
      >
        {{ index + 1 }}
      </div>
    </div>
    
    <!-- Next/Results Button -->
    <button
      v-if="currentIndex < total - 1"
      @click="next"
      class="nav-btn next-btn"
      title="Next Question"
    >
      <span>Next</span>
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="9 18 15 12 9 6"></polyline>
      </svg>
    </button>
    
    <button
      v-else
      @click="showResults"
      class="nav-btn results-btn"
      title="Show Results"
    >
      <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 11l3 3L22 4"></path>
        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
      </svg>
      <span>Results</span>
    </button>
  </div>
</template>

<style scoped>
.quiz-navigation {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 0;
  margin-top: auto;
}

.nav-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 6px;
  color: white;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 36px;
  touch-action: manipulation;
}

.nav-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

.nav-btn:active:not(:disabled) {
  transform: translateY(0);
}

.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.nav-btn.results-btn {
  background: rgba(34, 197, 94, 0.2);
  border-color: rgba(34, 197, 94, 0.4);
}

.nav-btn.results-btn:hover:not(:disabled) {
  background: rgba(34, 197, 94, 0.3);
  border-color: rgba(34, 197, 94, 0.5);
}

.nav-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.question-indicators {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: center;
  max-width: 200px;
}

.indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  font-size: 11px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  touch-action: manipulation;
}

.indicator:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.indicator.active {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.4);
  transform: scale(1.15);
}

.indicator.answered {
  background: rgba(34, 197, 94, 0.2);
  border-color: rgba(34, 197, 94, 0.4);
}

.indicator.answered.active {
  background: rgba(34, 197, 94, 0.3);
  border-color: rgba(34, 197, 94, 0.5);
}

/* Mobile-first responsive design */
@media (max-width: 767px) {
  .quiz-navigation {
    flex-direction: column;
    gap: 16px;
    padding: 8px 0;
  }
  
  .nav-btn {
    width: 100%;
    justify-content: center;
    padding: 10px 16px;
    font-size: 14px;
    min-height: 44px; /* iOS touch target minimum */
  }
  
  .nav-btn span {
    display: none; /* Hide text on mobile, show only icons */
  }
  
  .nav-icon {
    width: 18px;
    height: 18px;
  }
  
  .question-indicators {
    max-width: 100%;
    order: -1; /* Move indicators to top on mobile */
  }
  
  .indicator {
    width: 32px;
    height: 32px;
    font-size: 12px;
  }
}

@media (max-width: 480px) {
  .question-indicators {
    gap: 4px;
  }
  
  .indicator {
    width: 28px;
    height: 28px;
    font-size: 11px;
  }
}

/* Touch optimizations */
.nav-btn, .indicator {
  -webkit-tap-highlight-color: transparent;
}

.nav-btn:hover:not(:disabled),
.indicator:hover {
  transform: scale(1.05);
}

.nav-btn:active:not(:disabled),
.indicator:active {
  transform: scale(0.95);
}
</style>
