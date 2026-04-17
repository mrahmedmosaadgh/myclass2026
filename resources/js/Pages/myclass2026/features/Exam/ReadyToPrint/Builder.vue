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
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
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
