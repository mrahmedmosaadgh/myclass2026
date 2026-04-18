<template>
  <div class="question-display">
    <div class="question-header">
      <span class="question-number">Question {{ questionNumber }}</span>
      <span class="question-marks" v-if="question.marks">{{ question.marks }} marks</span>
    </div>
    
    <div class="question-content" v-html="renderedContent"></div>
    
    <div class="answer-area" v-if="showAnswerArea">
      <div class="answer-lines">
        <div class="answer-line" v-for="n in answerLines" :key="n"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { renderToString } from 'katex'

const props = defineProps({
  question: {
    type: Object,
    required: true
  },
  questionNumber: {
    type: Number,
    required: true
  },
  showAnswerArea: {
    type: Boolean,
    default: true
  }
})

// Render question content with math expressions
const renderedContent = computed(() => {
  if (!props.question?.content?.prompt) return ''
  
  // Simple math expression rendering using KaTeX
  let content = props.question.content.prompt
  
  // Replace LaTeX math expressions with rendered math
  content = content.replace(/\$\$([^$]+)\$\$/g, (match, math) => {
    try {
      return renderToString(math, { throwOnError: false })
    } catch (e) {
      return match // Return original if rendering fails
    }
  })
  
  // Replace inline math expressions
  content = content.replace(/\$([^$]+)\$/g, (match, math) => {
    try {
      return renderToString(math, { throwOnError: false })
    } catch (e) {
      return match // Return original if rendering fails
    }
  })
  
  return content
})

// Calculate answer lines based on question type and marks
const answerLines = computed(() => {
  if (!props.question) return 3
  
  // More lines for higher mark questions
  const baseLines = 3
  const extraLines = Math.floor(props.question.marks / 2)
  return baseLines + extraLines
})
</script>

<style scoped>
.question-display {
  margin-bottom: 24pt;
  page-break-inside: avoid;
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8pt;
  font-weight: bold;
}

.question-number {
  color: #333;
}

.question-marks {
  color: #666;
  font-size: 0.9em;
}

.question-content {
  margin-bottom: 12pt;
  line-height: 1.5;
}

.question-content :deep(.katex) {
  font-size: 1em;
}

.question-content :deep(.katex-display) {
  margin: 8pt 0;
}

.answer-area {
  border-top: 1px solid #ddd;
  padding-top: 8pt;
  margin-top: 12pt;
}

.answer-lines {
  display: flex;
  flex-direction: column;
  gap: 8pt;
}

.answer-line {
  height: 20pt;
  border-bottom: 1px solid #eee;
}
</style>
