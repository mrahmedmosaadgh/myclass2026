<template>
  <div class="exam-test-page">
    <!-- Simple header with print button -->
    <div class="test-header">
      <h1>Math Questions Test</h1>
      <q-btn
        label="Fullscreen Print"
        color="primary"
        icon="fullscreen"
        @click="openFullscreenPrint"
      />
    </div>

    <!-- Questions display -->
    <div class="questions-container">
      <QuestionDisplay 
        v-for="(question, index) in sampleQuestions"
        :key="question.id"
        :question="question"
        :question-number="index + 1"
        :show-answer-area="true"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import QuestionDisplay from './components/QuestionDisplay.vue'

// Sample questions with math expressions
const sampleQuestions = ref([
  {
    id: 1,
    type: 'short_answer',
    marks: 2,
    content: {
      prompt: 'What is the sum of $2 \\frac{1}{5}$ and $1 \\frac{2}{5}$?'
    }
  },
  {
    id: 2,
    type: 'short_answer', 
    marks: 3,
    content: {
      prompt: 'Calculate: $\\frac{3}{4} + \\frac{2}{3} = ?$'
    }
  },
  {
    id: 3,
    type: 'short_answer',
    marks: 1,
    content: {
      prompt: 'Simplify: $\\sqrt{16} + \\sqrt{9}$'
    }
  }
])

function openFullscreenPrint() {
  // Open fullscreen print preview in new window
  const printWindow = window.open('', '_blank', 'width=800,height=1100,scrollbars=yes,resizable=yes')
  
  if (printWindow) {
    const html = generatePrintHTML()
    printWindow.document.write(html)
    printWindow.document.close()
    printWindow.focus()
  }
}

function generatePrintHTML() {
  let html = '<!DOCTYPE html><html><head><title>Math Questions Print</title>'
  html += '<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"><\/script>'
  html += '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">'
  html += '<style>@page { size: A4; margin: 12mm; } body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: 20px; } .question { margin-bottom: 24pt; page-break-inside: avoid; } .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8pt; font-weight: bold; } .question-content { margin-bottom: 12pt; } .answer-area { border-top: 1px solid #ddd; padding-top: 8pt; margin-top: 12pt; } .answer-line { height: 20pt; border-bottom: 1px solid #eee; margin-bottom: 8pt; } @media print { body { padding: 0; } <\/style>'
  html += '<\/head><body>'
  html += '<h1>Math Questions Test<\/h1>'
  
  // Add questions
  sampleQuestions.value.forEach((question, index) => {
    html += '<div class="question">'
      html += '<div class="question-header">'
      html += '<span>Question ' + (index + 1) + '<\/span>'
      html += '<span>' + question.marks + ' marks<\/span>'
      html += '<\/div>'
      html += '<div class="question-content">' + renderMathContent(question.content.prompt) + '<\/div>'
      html += '<div class="answer-area">'
      html += '<div class="answer-line"><\/div>'
      html += '<div class="answer-line"><\/div>'
      html += '<div class="answer-line"><\/div>'
      html += '<\/div>'
      html += '<\/div>'
  })
  
  html += '<\/body><\/html>'
  return html
}

function renderMathContent(content) {
  if (!content) return ''
  
  // Replace LaTeX math expressions with rendered math
  content = content.replace(/\$\$([^$]+)\$\$/g, (match, math) => {
    try {
      return katex.renderToString(math, { throwOnError: false })
    } catch (e) {
      return match
    }
  })
  
  // Replace inline math expressions
  content = content.replace(/\$([^$]+)\$/g, (match, math) => {
    try {
      return katex.renderToString(math, { throwOnError: false })
    } catch (e) {
      return match
    }
  })
  
  return content
}
</script>

<style scoped>
.exam-test-page {
  min-height: 100vh;
  background: #fafafa;
  padding: 20px;
}

.test-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  background: white;
  padding: 16px 24px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.test-header h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
  color: #333;
}

.questions-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
