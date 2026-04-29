<template>
  <div class="rtp4-page">
    <q-toolbar class="bg-primary text-white">
      <q-icon name="quiz" size="md" class="q-mr-sm" />
      <q-toolbar-title>Ready To Print v4</q-toolbar-title>

      <q-btn
        flat
        dense
        icon="open_in_new"
        label="Server Print"
        class="q-mr-sm"
        :loading="openingPrintHtml"
        :disable="!currentExam"
        @click="openServerPrintHtml"
      />

      <q-btn
        flat
        dense
        icon="picture_as_pdf"
        label="Server PDF"
        class="q-mr-sm"
        :loading="pdfGenerating"
        :disable="!currentExam"
        @click="downloadServerPdf"
      />

      <q-space />

      <ThreeDotMenu
        @open-exam="openExamDialog"
        @open-my-exams="openMyExamsDialog"
        @open-settings="openSettingsDialog"
      />
    </q-toolbar>

    <OpenExamDialog
      v-model="openExamOpen"
      @loaded="handleExamLoaded"
    />

    <OpenMyExamsDialog
      v-model="openMyExamsOpen"
      @loaded="handleExamLoaded"
    />

    <SettingsDialog
      v-model="settingsOpen"
      :initial-tab="settingsInitialTab"
      v-model:settings="previewSettings"
    />

    <div class="q-pa-md">
      <LivePrintPreview
        v-if="currentExam"
        :exam="currentExam"
        :preview-settings="previewSettings"
        :pdf-generating="pdfGenerating"
        @download-pdf="downloadServerPdf"
        @debug-print="openServerPrintDebug"
      />

      <div v-else class="text-grey-7">
        Open an exam to preview it.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import ThreeDotMenu from './components/ThreeDotMenu.vue'
import OpenExamDialog from './components/OpenExamDialog.vue'
import OpenMyExamsDialog from './components/OpenMyExamsDialog.vue'
import SettingsDialog from './components/SettingsDialog.vue'
import LivePrintPreview from './components/LivePrintPreview.vue'

const $q = useQuasar()

const openExamOpen = ref(false)
const openMyExamsOpen = ref(false)
const currentExam = ref(null)

const settingsOpen = ref(false)
const settingsInitialTab = ref('mcq')
const pdfGenerating = ref(false)
const openingPrintHtml = ref(false)

const previewSettings = ref({
  showMarksPerQuestion: false,
  mcqOptions: {
    columns: 1,
    optionGapPt: 6,
    labelGapPt: 8,
    labelStyle: 'letter',
    customLabelTemplate: '{letter})',
    checkboxStyle: 'box',
    checkboxShowLabel: false,
    checkboxLabelType: 'letter',
    labelFontSizePt: 0,
    optionFontSizePt: 0,
    labelBold: false,
    optionBold: false
  },
  answerKey: {
    enabled: true,
    template: 'compact_choice_table'
  }
})

function openExamDialog() {
  openExamOpen.value = true
}

function openMyExamsDialog() {
  openMyExamsOpen.value = true
}

function openSettingsDialog(mode) {
  // mode: 'generate' | 'questions'
  settingsInitialTab.value = mode === 'questions' ? 'questions' : 'mcq'
  settingsOpen.value = true
}

function handleExamLoaded(exam) {
  // Normalize questions to current format (ver 3: correct_option_index, ver field)
  const normalizedExam = { ...exam }
  if (Array.isArray(normalizedExam.questions)) {
    normalizedExam.questions = normalizedExam.questions.map(normalizeQuestion)
  } else if (Array.isArray(normalizedExam.sampleQuestions)) {
    normalizedExam.sampleQuestions = normalizedExam.sampleQuestions.map(normalizeQuestion)
  }

  currentExam.value = normalizedExam

  // If the loaded exam has MCQ settings, use them as a base for preview.
  const incoming = exam?.settings || exam?.pageOptions
  if (incoming?.mcqOptions) {
    previewSettings.value = {
      ...previewSettings.value,
      mcqOptions: { ...previewSettings.value.mcqOptions, ...incoming.mcqOptions }
    }
  }
}

function generatePrintFileName() {
  const exam = currentExam.value || {}
  const title = String(exam?.name || exam?.settings?.examTitle?.text || 'Exam').trim()
  const date = new Date().toISOString().slice(0, 10)
  return `${title} - ${date}`.replace(/[<>:"/\\|?*]/g, '').slice(0, 200)
}

function getCurrentExamId() {
  const id = currentExam.value?.id
  return id ? String(id) : ''
}

function ensureExamIdOrNotify() {
  const examId = getCurrentExamId()
  if (examId) return examId

  $q.notify({
    type: 'warning',
    message: 'This exam has no saved ID yet. Open a saved exam from "Open My Exams" to use server print/PDF.',
    position: 'top'
  })
  return ''
}

async function openServerPrintHtml() {
  if (openingPrintHtml.value) return
  const examId = ensureExamIdOrNotify()
  if (!examId) return

  openingPrintHtml.value = true
  try {
    const response = await fetch(`/api/exam/ready-to-print/print-html/${encodeURIComponent(examId)}`, {
      headers: {
        Accept: 'text/html, application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const contentType = response.headers.get('content-type') || ''
    if (!response.ok) {
      if (contentType.includes('application/json')) {
        const errorData = await response.json()
        throw new Error(errorData?.message || 'Failed to generate print HTML')
      }
      const text = await response.text()
      throw new Error(text.substring(0, 200) || 'Failed to generate print HTML')
    }

    if (contentType.includes('application/json')) {
      const payload = await response.json()
      throw new Error(payload?.message || 'Unexpected JSON response for print HTML')
    }

    const html = await response.text()
    const w = window.open('', '_blank')
    if (!w) {
      throw new Error('Popup blocked. Please allow popups to open print preview.')
    }
    w.document.write(html)
    w.document.close()
  } catch (error) {
    $q.notify({ type: 'negative', message: String(error?.message || error), position: 'top' })
  } finally {
    openingPrintHtml.value = false
  }
}

async function downloadServerPdf() {
  if (pdfGenerating.value) return
  const examId = ensureExamIdOrNotify()
  if (!examId) return

  pdfGenerating.value = true
  try {
    const response = await fetch(`/api/exam/ready-to-print/generate-pdf/${encodeURIComponent(examId)}`, {
      headers: {
        Accept: 'application/pdf, text/html, application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const contentType = response.headers.get('content-type') || ''

    if (!response.ok) {
      if (contentType.includes('application/json')) {
        const errorData = await response.json()
        throw new Error(errorData?.message || 'Failed to generate PDF')
      }
      const text = await response.text()
      throw new Error(text.substring(0, 200) || 'Failed to generate PDF')
    }

    if (contentType.includes('application/json')) {
      const payload = await response.json()
      throw new Error(payload?.message || 'Unexpected JSON response for PDF')
    }

    if (contentType.includes('text/html')) {
      const html = await response.text()
      const w = window.open('', '_blank')
      if (!w) {
        throw new Error('Popup blocked. Please allow popups to use HTML print fallback.')
      }
      w.document.write(html)
      w.document.close()
      w.onload = () => w.print()
      return
    }

    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${generatePrintFileName()}.pdf`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    $q.notify({ type: 'negative', message: String(error?.message || error), position: 'top' })
  } finally {
    pdfGenerating.value = false
  }
}

async function openServerPrintDebug() {
  if (openingPrintHtml.value) return
  const examId = ensureExamIdOrNotify()
  if (!examId) return

  openingPrintHtml.value = true
  try {
    const response = await fetch(`/api/exam/ready-to-print/print-html/${encodeURIComponent(examId)}?debug=1`, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    const payload = await response.json().catch(() => null)
    if (!response.ok) {
      throw new Error(payload?.message || 'Failed to load debug print payload')
    }

    const report = payload?.paginationReport || {}
    const html = payload?.html || ''

    const w = window.open('', '_blank')
    if (!w) {
      throw new Error('Popup blocked. Please allow popups to open debug print view.')
    }

    const escaped = (v) => String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
    const reportPretty = escaped(JSON.stringify(report, null, 2))
    const scriptCloseTag = '</scr' + 'ipt>'
    const htmlJson = JSON.stringify(String(html)).replaceAll(scriptCloseTag, '<\\/script>')

    w.document.write(`<!doctype html>
      <html>
        <head>
          <meta charset="utf-8" />
          <title>Print Debug - ${escaped(examId)}</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 0; }
            .wrap { display: grid; grid-template-columns: 38% 62%; min-height: 100vh; }
            .left { padding: 12px; border-right: 1px solid #ddd; overflow: auto; background: #fafafa; }
            .right { padding: 0; }
            h3 { margin: 0 0 10px; }
            pre { white-space: pre-wrap; word-break: break-word; font-size: 12px; line-height: 1.45; }
            iframe { border: 0; width: 100%; height: 100vh; }
          </style>
        </head>
        <body>
          <div class="wrap">
            <div class="left">
              <h3>Pagination Report</h3>
              <pre>${reportPretty}</pre>
            </div>
            <div class="right">
              <iframe id="previewFrame"></iframe>
            </div>
          </div>
          <script>
            const html = ${htmlJson}
            const frame = document.getElementById('previewFrame')
            if (frame) frame.srcdoc = html
          <\/script>
        </body>
      </html>`)
    w.document.close()
  } catch (error) {
    $q.notify({ type: 'negative', message: String(error?.message || error), position: 'top' })
  } finally {
    openingPrintHtml.value = false
  }
}

/**
 * Migrate a question object to the current JSON format (ver 3):
 * - Stamps ver: 3 if missing
 * - Renames content.correct_answer → content.correct_option_index for MCQ
 */
function normalizeQuestion(q) {
  const out = { ...q }

  if (!out.ver) out.ver = 3

  if (out.type === 'multiple_choice' && out.content) {
    const c = { ...out.content }

    if (c.correct_option_index === undefined || c.correct_option_index === null) {
      const legacy = c.correct_answer

      if (legacy !== undefined && legacy !== null && legacy !== '') {
        const asNum = Number(legacy)
        if (!Number.isNaN(asNum) && Number.isFinite(asNum)) {
          c.correct_option_index = asNum
        } else if (typeof legacy === 'string' && Array.isArray(c.options)) {
          const idx = c.options.findIndex(o => String(o).trim() === String(legacy).trim())
          if (idx >= 0) c.correct_option_index = idx
        }
        delete c.correct_answer
      }
    }

    out.content = c
  }

  return out
}
</script>

<style scoped>
.rtp4-page {
  min-height: 100vh;
}
</style>
