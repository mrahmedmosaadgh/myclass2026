<template>
  <div class="exam-builder-page">
    <!-- Header with lifecycle badge and actions -->
    <div class="builder-header">
      <div class="header-left">
        <h1>{{ $t('exam.readyToPrint.title') }}</h1>
        <q-badge
          :color="lifecycleBadgeColor"
          :label="$t(`exam.readyToPrint.lifecycle.${lifecycle.status}`)"
          class="lifecycle-badge"
        />
        <q-badge
          v-if="lifecycle.dirty"
          color="orange"
          :label="$t('exam.readyToPrint.dirty')"
        />
      </div>
      <div class="header-actions">
        <q-btn
          :label="$t('exam.readyToPrint.validate')"
          color="primary"
          @click="validateExam"
          :loading="validating"
        />
        <q-btn
          v-if="canApprove"
          :label="$t('exam.readyToPrint.approve')"
          color="positive"
          @click="approveExam"
        />
        <q-btn
          v-if="canRender"
          :label="$t('exam.readyToPrint.render')"
          color="secondary"
          @click="generateSnapshot"
        />
        <q-btn
          v-if="renderSnapshot.id"
          :label="$t('exam.readyToPrint.print')"
          color="dark"
          @click="openPrintPreview"
        />

             <q-btn
          
          :label="$t('exam.readyToPrint.print')"
          color="dark"
          @click="openPrintPreview"
        />
        <q-btn
          v-if="renderSnapshot.id"
          label="Fullscreen Print"
          color="primary"
          icon="fullscreen"
          @click="openFullscreenPrint"
        />
      </div>
    </div>

    <!-- Main layout: left tree, center editor, right settings -->
    <div class="builder-main">
      <!-- Left: Structure tree -->
      <div class="builder-left">
        <StructureTree />
      </div>

      <!-- Center: Context editor -->
      <div class="builder-center">
        <ContextEditor />
      </div>

      <!-- Right: Print & layout settings -->
      <div class="builder-right">
        <PrintSettings />
      </div>
    </div>

    <!-- Bottom: Validation panel -->
    <div v-if="validation.items.length" class="builder-bottom">
      <ValidationPanel />
    </div>

    <!-- Print preview dialog -->
    <q-dialog v-model="printPreviewOpen" maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ $t('exam.readyToPrint.printPreview') }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-separator />
        <q-card-section class="print-preview-container">
          <PrintPreview v-if="renderSnapshot.id" />
          <PrintPreview   />
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import { useExamReadyToPrintStore } from '@/Stores/examReadyToPrintStore'
import { usePaginationEngine } from './composables/usePaginationEngine.js'
import StructureTree from './components/StructureTree.vue'
import ContextEditor from './components/ContextEditor.vue'
import PrintSettings from './components/PrintSettings.vue'
import ValidationPanel from './components/ValidationPanel.vue'
import PrintPreview from './components/PrintPreview.vue'

const { t } = useI18n()
const store = useExamReadyToPrintStore()

const printPreviewOpen = ref(false)
const validating = ref(false)

const lifecycle = computed(() => store.lifecycle)
const validation = computed(() => store.validation)
const renderSnapshot = computed(() => store.renderSnapshot)
const canApprove = computed(() => store.canApprove)
const canRender = computed(() => store.canRender)

const lifecycleBadgeColor = computed(() => {
  switch (lifecycle.value.status) {
    case 'draft': return 'grey'
    case 'validated': return 'blue'
    case 'approved': return 'green'
    case 'rendered': return 'purple'
    default: return 'grey'
  }
})

async function validateExam() {
  validating.value = true
  try {
    // Run pagination engine to detect layout issues
    const { generateRenderSnapshot } = usePaginationEngine(computed(() => store.exam))
    const result = generateRenderSnapshot()
    
    const validationItems = []
    
    if (!result.success) {
      validationItems.push({
        severity: 'error',
        scope: 'exam',
        message: result.error,
      })
    } else {
      validationItems.push({
        severity: 'info',
        scope: 'exam',
        message: `Layout validated: ${result.snapshot.pages.length} pages, ${result.snapshot.totalBlocks} blocks`,
      })
    }
    
    // Basic schema checks
    if (!store.exam.examMeta.title) {
      validationItems.push({
        severity: 'warn',
        scope: 'exam',
        message: 'Exam title is missing',
      })
    }
    
    if (store.exam.sections.length === 0) {
      validationItems.push({
        severity: 'warn',
        scope: 'exam',
        message: 'No sections defined',
      })
    }
    
    store.setValidationReport({
      items: validationItems,
      ranAt: new Date().toISOString()
    })
    
    if (validationItems.every(x => x.severity !== 'error')) {
      store.markValidated()
    }
  } finally {
    validating.value = false
  }
}

function approveExam() {
  store.approve()
}

function generateSnapshot() {
  const { generateRenderSnapshot } = usePaginationEngine(computed(() => store.exam))
  const result = generateRenderSnapshot()
  
  if (result.success) {
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
    try {
      const m = String(html).match(/<title[^>]*>([\s\S]*?)<\/title>/i)
      printWindow.document.title = (m && m[1] ? String(m[1]).trim() : '') || 'Exam'
    } catch {}
    printWindow.focus()
  }
}

// Load user-specific data from server
async function loadUserData() {
  try {
    const response = await axios.get('/exam/ready-to-print/api/load-data-v3')
    const data = response.data
    
    if (data.questions && data.settings) {
      store.loadUserData(data.questions, data.settings)
    }
  } catch (error) {
    console.error('Failed to load user data:', error)
  }
}

// Save user-specific data to server
async function saveUserData() {
  try {
    const data = {
      questions: store.exam.sections || [],
      settings: store.exam
    }
    
    await axios.post('/exam/ready-to-print/api/save-data-v3', data)
    store.markClean()
  } catch (error) {
    console.error('Failed to save user data:', error)
  }
}

// Auto-save functionality
let autoSaveTimer = null

function triggerAutoSave() {
  if (autoSaveTimer) {
    clearTimeout(autoSaveTimer)
  }
  
  autoSaveTimer = setTimeout(() => {
    saveUserData()
  }, 2000) // Save after 2 seconds of inactivity
}

// Load data on component mount
onMounted(() => {
  loadUserData()
})

// Watch for changes and trigger auto-save
const { markDirty } = store

// Override store methods to trigger auto-save
const originalMarkDirty = markDirty.bind(store)
store.markDirty = function() {
  originalMarkDirty()
  triggerAutoSave()
}

function generatePrintHTML() {
  const printFooter = store.exam.printFooter || {}
  const bottomOffsetMm = Number(printFooter.bottomOffsetMm) || 0
  const applyOffsetToPageNumbers = !!printFooter.applyOffsetToPageNumbers
  
  const baseTitle = String(store.exam?.title || '').trim() || 'Exam'
  const now = new Date()
  const dd = String(now.getDate()).padStart(2, '0')
  const mm = String(now.getMonth() + 1).padStart(2, '0')
  const yyyy = String(now.getFullYear())
  const HH = String(now.getHours()).padStart(2, '0')
  const MM = String(now.getMinutes()).padStart(2, '0')
  const timestamp = dd + '-' + mm + '-' + yyyy + '_' + HH + '-' + MM
  const safeTitle = String(baseTitle).replace(/[<>:"/\\|?*]/g, '').trim() || 'Exam'
  const docTitle = safeTitle + '_' + timestamp

  let html = '<!DOCTYPE html><html><head><title>' + docTitle + '</title>'
  html += '<script src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"><\/script>'
  html += '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">'
  
  // Add footer styles with offset support
  let footerStyles = '.print-footer { position: fixed; bottom: 0; left: 0; right: 0; z-index: 999; background: transparent; overflow: hidden; box-sizing: border-box; padding: 0 0; }'
  if (bottomOffsetMm !== 0) {
    footerStyles = '.print-footer { position: fixed; bottom: ' + bottomOffsetMm + 'mm; left: 0; right: 0; z-index: 999; background: transparent; overflow: hidden; box-sizing: border-box; padding: 0 0; }'
  }
  
  html += '<style>@page { size: A4; margin: 12mm; } body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: 20px; } .question { margin-bottom: 24pt; page-break-inside: avoid; } .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8pt; font-weight: bold; } .question-content { margin-bottom: 12pt; } .answer-area { border-top: 1px solid #ddd; padding-top: 8pt; margin-top: 12pt; } .answer-line { height: 20pt; border-bottom: 1px solid #eee; margin-bottom: 8pt; } ' + footerStyles + ' @media print { body { padding: 0; } <\/style>'
  html += '<\/head><body>'
  html += '<h1>' + (store.exam.title || 'Exam') + '<\/h1>'
  html += generatePrintContent()
  
  // Add footer if enabled
  if (printFooter.enabled) {
    const pageNumberPosition = printFooter.pageNumberPosition || 'bottom-center'
    const position = pageNumberPosition.includes('top') ? 'top' : 'bottom'
    const positionStyles = {
      'bottom-left': 'text-align:left; left:12mm; right:auto;',
      'bottom-center': 'text-align:center; left:0; right:0;',
      'bottom-right': 'text-align:right; right:12mm; left:auto;',
      'top-left': 'text-align:left; left:12mm; right:auto; top:0;',
      'top-center': 'text-align:center; left:0; right:0; top:0;',
      'top-right': 'text-align:right; right:12mm; left:auto; top:0;'
    }
    const style = positionStyles[pageNumberPosition] || positionStyles['bottom-center']
    
    // Apply offset to page numbers if enabled
    const pageNumberOffsetStyle = (applyOffsetToPageNumbers && bottomOffsetMm !== 0) ? 
      (position + ':' + bottomOffsetMm + 'mm;') : ''
    
    html += '<div class="print-footer">'
    if (printFooter.showPageNumbers) {
      html += '<div class="page-number" style="position:absolute; ' + position + ':0; ' + style + ' font-size:10pt; color:#000000; padding:8mm 12mm; ' + pageNumberOffsetStyle + '">'
      html += '<span class="page-number-content">Page <span class="current-page"></span></span>'
      html += '</div>'
    }
    html += '</div>'
  }
  
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

  let text = String(content)

  // Strip citations
  text = text.replace(/\[\s*cite\s*:\s*\d+\s*\]/gi, '')

  // Normalize whitespace
  text = text.replace(/\s{2,}/g, ' ').trim()

  const katexConfig = {
    throwOnError: false
  }

  // Replace LaTeX math expressions with rendered math
  text = text.replace(/\$\$([^$]+)\$\$/g, (match, math) => {
    try {
      return katex.renderToString(math, { ...katexConfig, displayMode: true })
    } catch (e) {
      return match
    }
  })

  // Replace inline math expressions
  text = text.replace(/\$([^$]+)\$/g, (match, math) => {
    try {
      return katex.renderToString(math, katexConfig)
    } catch (e) {
      return match
    }
  })

  return text
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
