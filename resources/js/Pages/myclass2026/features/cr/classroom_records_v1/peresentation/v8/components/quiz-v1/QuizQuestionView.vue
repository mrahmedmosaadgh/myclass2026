<script setup>
const props = defineProps({
  question: Object,
  isPresentMode: {
    type: Boolean,
    default: false
  },
  isInteractive: {
    type: Boolean,
    default: false
  },
  selectedAnswer: {
    type: Number,
    default: null
  },
  showCorrect: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['answer'])

function selectOption(index) {
  if (!props.isInteractive) return
  emit('answer', index)
}

function getOptionClass(index) {
  const classes = ['option-item']
  
  if (props.selectedAnswer === index) {
    classes.push('selected')
  }
  
  if (props.showCorrect) {
    if (props.question.correctAnswer === index) {
      classes.push('correct')
    } else if (props.selectedAnswer === index) {
      classes.push('incorrect')
    }
  }
  
  return classes.join(' ')
}
</script>

<template>
  <div class="quiz-question">
    <div class="question-text">
      {{ question.question }}
    </div>
    
    <div class="question-options">
      <div
        v-for="(option, index) in question.options"
        :key="index"
        :class="getOptionClass(index)"
        @click="selectOption(index)"
      >
        <span class="option-label">{{ String.fromCharCode(65 + index) }}</span>
        <span class="option-text">{{ option }}</span>
        <div class="option-indicator">
          <svg v-if="showCorrect && question.correctAnswer === index" class="check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <svg v-else-if="showCorrect && selectedAnswer === index && question.correctAnswer !== index" class="x-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </div>
      </div>
    </div>
    
    <div v-if="showCorrect && question.explanation" class="explanation">
      <div class="explanation-label">Explanation:</div>
      <div class="explanation-text">{{ question.explanation }}</div>
    </div>
  </div>
</template>

<style scoped>
.quiz-question {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.question-text {
  font-size: 16px;
  font-weight: 500;
  line-height: 1.5;
  color: white;
}

.question-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.option-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.option-item:hover {
  background: rgba(255, 255, 255, 0.15);
}

.option-item.selected {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.3);
}

.option-item.correct {
  background: rgba(34, 197, 94, 0.2);
  border-color: rgba(34, 197, 94, 0.5);
}

.option-item.incorrect {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.5);
}

.option-label {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  font-weight: 600;
  font-size: 14px;
  flex-shrink: 0;
}

.option-text {
  flex: 1;
  font-size: 14px;
  line-height: 1.4;
}

.option-indicator {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.check-icon {
  width: 20px;
  height: 20px;
  color: #22c55e;
  stroke-width: 3;
}

.x-icon {
  width: 20px;
  height: 20px;
  color: #ef4444;
  stroke-width: 3;
}

.explanation {
  padding: 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  border-left: 4px solid rgba(255, 255, 255, 0.3);
}

.explanation-label {
  font-size: 12px;
  font-weight: 600;
  opacity: 0.8;
  margin-bottom: 4px;
}

.explanation-text {
  font-size: 14px;
  line-height: 1.4;
}

/* Mobile-first responsive design */
@media (max-width: 767px) {
  .question-text {
    font-size: 14px;
  }
  
  .option-item {
    padding: 10px 12px;
    gap: 10px;
  }
  
  .option-label {
    width: 28px;
    height: 28px;
    font-size: 12px;
  }
  
  .option-text {
    font-size: 13px;
  }
  
  .explanation {
    padding: 10px;
  }
  
  .explanation-text {
    font-size: 13px;
  }
}

/* Touch optimizations */
.option-item {
  min-height: 44px; /* iOS touch target minimum */
  touch-action: manipulation;
}

.is-interactive .option-item:hover {
  transform: scale(1.02);
}

.is-interactive .option-item:active {
  transform: scale(0.98);
}
</style>
