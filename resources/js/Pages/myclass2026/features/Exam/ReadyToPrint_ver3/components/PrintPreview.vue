<template>
  <div class="print-preview">
    <div
      v-for="(page, pageIndex) in pages"
      :key="pageIndex"
      class="preview-document"
      :style="documentStyle"
    >
      <!-- Header -->
      <div v-if="shouldShowHeader(pageIndex)" class="document-header" :style="headerStyle">
        <div class="header-content">
          <h2>{{ examMeta.title }}</h2>
          <div class="header-meta">
            <span>{{ examMeta.subject }}</span>
            <span>{{ examMeta.grade }}</span>
            <span>{{ examMeta.term }}</span>
          </div>
        </div>
      </div>

      <!-- Content blocks for this page -->
      <div class="document-content">
        <div
          v-for="block in page.blocks"
          :key="getBlockKey(block)"
          class="content-block"
          :class="`block-${block.type}`"
        >
          <!-- Section title -->
          <h3 v-if="block.type === 'sectionTitle'" class="section-title">
            {{ block.text }}
          </h3>

          <!-- Section instructions -->
          <div v-else-if="block.type === 'sectionInstructions'" class="section-instructions">
            {{ block.text }}
          </div>

          <!-- Question -->
          <div v-else-if="block.type === 'question'" class="content-question">
            <QuestionDisplay 
              :question="block.question"
              :question-number="getQuestionNumber(block)"
              :show-answer-area="true"
              :force-essay="isSectionForcingEssay(block.sectionId)"
            />
          </div>

          <!-- Spacer -->
          <div v-else-if="block.type === 'spacer'" class="block-spacer" :style="{ height: `${block.height}mm` }"></div>

          <!-- Page break (visual indicator only) -->
          <div v-else-if="block.type === 'pageBreak'" class="block-pagebreak">
            --- Page Break ---
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="document-footer" :style="footerStyle">
        <div class="footer-content">
          <div class="footer-page-number">
            {{ getPageNumberText(pageIndex) }}
          </div>
          <div v-if="shouldShowContinuation(pageIndex)" class="footer-continuation">
            {{ footerConfig.continuationMessage }}
          </div>
          <div v-if="shouldShowEnd(pageIndex)" class="footer-end">
            {{ footerConfig.endMessage }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useExamReadyToPrintStore } from '@/Stores/examReadyToPrintStore'
import QuestionDisplay from './QuestionDisplay.vue'

const { t } = useI18n()
const store = useExamReadyToPrintStore()

const renderSnapshot = computed(() => store.renderSnapshot)
const examMeta = computed(() => renderSnapshot.value.examMeta)
const pageSetup = computed(() => renderSnapshot.value.pageSetup)
const headerConfig = computed(() => renderSnapshot.value.headerConfig)
const footerConfig = computed(() => renderSnapshot.value.footerConfig)
const pages = computed(() => renderSnapshot.value.pages || [])

function isSectionForcingEssay(sectionId) {
  const s = store.exam.sections.find(x => x.id === sectionId)
  return !!s?.rules?.forceQuestionsToEssay
}

const documentStyle = computed(() => ({
  width: pageSetup.value.paper === 'A4' ? '210mm' : '216mm',
  minHeight: pageSetup.value.paper === 'A4' ? '297mm' : '279mm',
  paddingTop: `${pageSetup.value.marginsMm.top}mm`,
  paddingRight: `${pageSetup.value.marginsMm.right}mm`,
  paddingBottom: `${pageSetup.value.marginsMm.bottom}mm`,
  paddingLeft: `${pageSetup.value.marginsMm.left}mm`,
  boxSizing: 'border-box',
  background: 'white',
  position: 'relative',
}))

const headerStyle = computed(() => ({
  height: `${pageSetup.value.headerHeightMm}mm`,
  borderBottom: '1px solid #ccc',
  marginBottom: '16px',
}))

const footerStyle = computed(() => ({
  height: `${pageSetup.value.footerHeightMm}mm`,
  borderTop: '1px solid #ccc',
  marginTop: '16px',
}))

const pageNumberText = computed(() => {
  switch (footerConfig.value.pageNumbering) {
    case 'x_of_y': return 'Page 1 of 1'
    case 'x_slash_y': return '1 / 1'
    case 'page_x': return 'Page 1'
    default: return '1'
  }
})

// Helper methods for pagination-aware rendering
function shouldShowHeader(pageIndex) {
  if (headerConfig.value.mode === 'none') return false
  if (headerConfig.value.mode === 'first_page_only') return pageIndex === 0
  if (headerConfig.value.mode === 'all_pages') return true
  if (headerConfig.value.mode === 'custom_per_page') return true // TODO: implement custom logic
  return true
}

function getPageNumberText(pageIndex) {
  const currentPage = pageIndex + 1
  const totalPages = pages.value.length
  switch (footerConfig.value.pageNumbering) {
    case 'x_of_y': return `Page ${currentPage} of ${totalPages}`
    case 'x_slash_y': return `${currentPage} / ${totalPages}`
    case 'page_x': return `Page ${currentPage}`
    default: return `${currentPage}`
  }
}

function shouldShowContinuation(pageIndex) {
  return pageIndex < pages.value.length - 1
}

function shouldShowEnd(pageIndex) {
  return pageIndex === pages.value.length - 1
}

function getBlockKey(block) {
  if (block.type === 'sectionTitle' || block.type === 'sectionInstructions') {
    return `${block.type}-${block.sectionId}`
  }
  if (block.type === 'question') {
    return `${block.type}-${block.sectionId}-${block.questionId}`
  }
  return `${block.type}-${Math.random()}`
}

function getQuestionNumber(block) {
  // Build question numbers based on position in rendered pages
  let questionNumber = 1
  for (let i = 0; i < pages.value.length; i++) {
    for (const pageBlock of pages.value[i].blocks) {
      if (pageBlock.type === 'question') {
        if (pageBlock.questionId === block.questionId) {
          return questionNumber
        }
        questionNumber++
      }
    }
  }
  return questionNumber
}
</script>

<style scoped>
.print-preview {
  padding: 20px;
  display: flex;
  justify-content: center;
  background: #f5f5f5;
}

.preview-document {
  background: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  font-family: 'Times New Roman', serif;
  font-size: 12pt;
  line-height: 1.4;
  color: #000;
}

.document-header {
  text-align: center;
}

.header-content h2 {
  margin: 0 0 8px 0;
  font-size: 18pt;
  font-weight: bold;
}

.header-meta {
  display: flex;
  justify-content: center;
  gap: 20px;
  font-size: 11pt;
  color: #555;
}

.document-content {
  min-height: calc(100% - 40mm); /* Account for header/footer */
}

.content-section {
  margin-bottom: 24pt;
}

.section-title {
  margin: 0 0 8pt 0;
  font-size: 14pt;
  font-weight: bold;
  color: #333;
}

.section-instructions {
  margin-bottom: 12pt;
  font-style: italic;
  color: #555;
}

.content-question {
  margin-bottom: 16pt;
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4pt;
}

.question-number {
  font-weight: bold;
}

.question-marks {
  font-weight: bold;
  color: #666;
}

.question-prompt {
  margin-bottom: 8pt;
}

.answer-area {
  margin-left: 16pt;
}

.answer-lines .answer-line {
  height: 1pt;
  border-bottom: 1px solid #ccc;
  margin-bottom: 6pt;
}

.answer-essay-box {
  height: 60pt;
  border: 1px solid #ccc;
  background: #fafafa;
}

.answer-mcq .mcq-option {
  display: flex;
  align-items: center;
  margin-bottom: 4pt;
}

.option-label {
  width: 20pt;
  font-weight: bold;
}

.option-space {
  flex: 1;
  height: 1pt;
  border-bottom: 1px solid #ccc;
  margin-left: 8pt;
}

.answer-truefalse {
  display: flex;
  gap: 40pt;
}

.tf-option {
  display: flex;
  align-items: center;
  gap: 8pt;
}

.tf-option::before {
  content: '';
  width: 12pt;
  height: 12pt;
  border: 1px solid #ccc;
  border-radius: 50%;
}

.document-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 10pt;
  color: #666;
}

.footer-page-number {
  font-weight: bold;
}

.footer-continuation,
.footer-end {
  font-style: italic;
}

@media print {
  .print-preview {
    padding: 0;
    background: none;
  }

  .preview-document {
    box-shadow: none;
    margin: 0;
  }

  @page {
    size: A4;
    margin: 0;
  }
}
</style>
