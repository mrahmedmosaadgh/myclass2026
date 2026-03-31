<template>
  <div class="question-type" :class="questionTypeClass">
    <div class="question-header">
      <div class="question-info">
        <span class="type-badge">{{ typeConfig.label }}</span>
        <span v-if="hasTimeLimit" class="time-badge">
          ⏱️ {{ timeLimit }}s
        </span>
        <span v-if="hasScoring" class="score-badge">
          🎯 {{ minScore }}-{{ maxScore }} points
        </span>
      </div>
      <div class="question-actions">
        <slot name="actions"></slot>
      </div>
    </div>
    
    <div class="question-content">
      <div class="question-title">
        <h3>{{ title }}</h3>
        <p v-if="instructions" class="instructions">{{ instructions }}</p>
      </div>
      
      <div class="question-body">
        <slot name="question-body"></slot>
      </div>
      
      <div v-if="showAnswerKey" class="answer-key">
        <strong>Answer Key:</strong> 
        <slot name="answer-key"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  instructions: {
    type: String,
    default: ''
  },
  timeLimit: {
    type: Number,
    default: null
  },
  minScore: {
    type: Number,
    default: 0
  },
  maxScore: {
    type: Number,
    default: 100
  },
  showAnswerKey: {
    type: Boolean,
    default: false
  }
})

const hasTimeLimit = computed(() => props.timeLimit && props.timeLimit > 0)
const hasScoring = computed(() => props.maxScore > 0 || props.minScore > 0)

const typeConfig = computed(() => {
  const configs = {
    'text': { label: 'Text Answer', color: '#3b82f6' },
    'multiple_choice': { label: 'Multiple Choice', color: '#10b981' },
    'multi_select': { label: 'Multiple Selection', color: '#f59e0b' },
    'number': { label: 'Number Answer', color: '#8b5cf6' },
    'rating': { label: 'Rating Scale', color: '#ef4444' },
    'true_false': { label: 'True/False', color: '#6b7280' }
  }
  return configs[props.type] || { label: 'Unknown Type', color: '#6b7280' }
})

const questionTypeClass = computed(() => `question-${props.type}`)
</script>

<style scoped>
.question-type {
  background: white;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  overflow: hidden;
  transition: all 0.3s ease;
}

.question-type:hover {
  border-color: #d1d5db;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.question-info {
  display: flex;
  gap: 8px;
  align-items: center;
}

.type-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  color: white;
  background: v-bind('typeConfig.color');
}

.time-badge, .score-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 500;
  background: #f3f4f6;
  color: #374151;
}

.question-content {
  padding: 20px;
}

.question-title h3 {
  margin: 0 0 8px;
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.question-title .instructions {
  margin: 0 0 16px;
  color: #6b7280;
  font-size: 14px;
  line-height: 1.5;
}

.question-body {
  margin-bottom: 16px;
}

.answer-key {
  padding: 12px;
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 8px;
  font-size: 14px;
  color: #0c4a6e;
}

/* Type-specific styles */
.question-text { border-left: 4px solid #3b82f6; }
.question-multiple_choice { border-left: 4px solid #10b981; }
.question-multi_select { border-left: 4px solid #f59e0b; }
.question-number { border-left: 4px solid #8b5cf6; }
.question-rating { border-left: 4px solid #ef4444; }
.question-true_false { border-left: 4px solid #6b7280; }
</style>
