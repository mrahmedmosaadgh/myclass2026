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
    store.setRenderedSnapshot(result.snapshot)
  } else {
    // Show error to user
    console.error('Render failed:', result.error)
    // You might want to show this in the UI
  }
}

function openPrintPreview() {
  printPreviewOpen.value = true
}

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
  let html = '<!DOCTYPE html><html><head><title>Exam Print Preview</title>'
  html += '<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"><\/script>'
  html += '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">'
  html += '<style>@page { size: A4; margin: 12mm; } body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: 20px; } .question { margin-bottom: 24pt; page-break-inside: avoid; } .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8pt; font-weight: bold; } .question-content { margin-bottom: 12pt; } .answer-area { border-top: 1px solid #ddd; padding-top: 8pt; margin-top: 12pt; } .answer-line { height: 20pt; border-bottom: 1px solid #eee; margin-bottom: 8pt; } @media print { body { padding: 0; } <\/style>'
  html += '<\/head><body>'
  html += '<h1>' + (store.exam.title || 'Exam') + '<\/h1>'
  html += generatePrintContent()
  html += '<\/body><\/html>'
  return html
}

function generatePrintContent() {
  if (!store.renderSnapshot?.pages) return '<p>No content to print</p>'
  
  let questionNumber = 1
  let html = ''
  
  for (const page of store.renderSnapshot.pages) {
    for (const block of page.blocks) {
      if (block.type === 'question') {
        html += '<div class="question">' +
          '<div class="question-header">' +
            '<span>Question ' + questionNumber + '</span>' +
            '<span>' + (block.data.marks || 1) + ' marks</span>' +
          '</div>' +
          '<div class="question-content">' + renderMathContent(block.data.content?.prompt || '') + '</div>' +
          '<div class="answer-area">' +
            '<div class="answer-line"></div>' +
            '<div class="answer-line"></div>' +
            '<div class="answer-line"></div>' +
          '</div>' +
        '</div>'
        questionNumber++
      } else if (block.type === 'section') {
        html += '<h2>' + (block.data.title || 'Section') + '</h2>'
        if (block.data.instructions) {
          html += '<p>' + block.data.instructions + '</p>'
        }
      }
    }
  }
  
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
.exam-builder-page {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: #fafafa;
}

.builder-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 24px;
  background: white;
  border-bottom: 1px solid #e0e0e0;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-left h1 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.lifecycle-badge {
  font-weight: 500;
}

.header-actions {
  display: flex;
  gap: 8px;
}

.builder-main {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.builder-left {
  width: 280px;
  background: white;
  border-right: 1px solid #e0e0e0;
  overflow-y: auto;
}

.builder-center {
  flex: 1;
  background: white;
  overflow-y: auto;
}

.builder-right {
  width: 320px;
  background: white;
  border-left: 1px solid #e0e0e0;
  overflow-y: auto;
}

.builder-bottom {
  background: white;
  border-top: 1px solid #e0e0e0;
  max-height: 200px;
  overflow-y: auto;
}

.print-preview-container {
  height: calc(100vh - 120px);
  overflow: auto;
  background: #f5f5f5;
}

@media print {
  .exam-builder-page {
    height: auto;
  }

  .builder-header,
  .builder-left,
  .builder-right,
  .builder-bottom {
    display: none;
  }

  .builder-center {
    width: 100%;
    height: auto;
    overflow: visible;
  }
}
</style>
