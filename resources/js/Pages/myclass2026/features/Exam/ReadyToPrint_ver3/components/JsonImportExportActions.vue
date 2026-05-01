<template>
  <q-item clickable v-close-popup @click="exportToJson">
    <q-item-section avatar><q-icon name="download" /></q-item-section>
    <q-item-section>
      <q-item-label>Download (Export JSON)</q-item-label>
    </q-item-section>
  </q-item>

  <q-item clickable v-close-popup @click="exportToWord">
    <q-item-section avatar><q-icon name="description" /></q-item-section>
    <q-item-section>
      <q-item-label>Export to Word (.docx)</q-item-label>
    </q-item-section>
  </q-item>

  <q-item clickable v-close-popup @click="exportToQTI">
    <q-item-section avatar><q-icon name="code" /></q-item-section>
    <q-item-section>
      <q-item-label>Export to QTI (XML)</q-item-label>
      <q-item-label caption>IMS Question & Test Interoperability</q-item-label>
    </q-item-section>
  </q-item>

  <q-separator />

  <q-item clickable v-close-popup @click="triggerImportFile">
    <q-item-section avatar><q-icon name="upload_file" /></q-item-section>
    <q-item-section>
      <q-item-label>Import JSON</q-item-label>
    </q-item-section>
  </q-item>

  <q-separator />

  <q-item clickable v-close-popup @click="triggerImportFile">
    <q-item-section avatar><q-icon name="folder_open" /></q-item-section>
    <q-item-section>
      <q-item-label>Open JSON File</q-item-label>
      <q-item-label caption>Import from file</q-item-label>
    </q-item-section>
  </q-item>

  <q-separator />

  <q-item clickable v-close-popup @click="copyFullExamFormat">
    <q-item-section avatar><q-icon name="content_copy" /></q-item-section>
    <q-item-section>
      <q-item-label>Copy Full Exam Format</q-item-label>
      <q-item-label caption>Example JSON for AI generation</q-item-label>
    </q-item-section>
  </q-item>

  <q-item clickable v-close-popup @click="copyQuestionsFormat">
    <q-item-section avatar><q-icon name="content_copy" /></q-item-section>
    <q-item-section>
      <q-item-label>Copy Questions Format</q-item-label>
      <q-item-label caption>Example questions JSON for AI</q-item-label>
    </q-item-section>
  </q-item>

  <q-item clickable v-close-popup @click="copySettingsFormat">
    <q-item-section avatar><q-icon name="content_copy" /></q-item-section>
    <q-item-section>
      <q-item-label>Copy Settings Format</q-item-label>
      <q-item-label caption>Example pageOptions JSON for AI</q-item-label>
    </q-item-section>
  </q-item>

  <input
    ref="importFileInput"
    type="file"
    accept="application/json,.json"
    style="display: none"
    @change="handleImportFile"
  />
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { exportToWord as exportToWordUtil, exportToQTI as exportToQTIUtil, generateExportFileName } from '../utils/exportFormats'

const props = defineProps({
  sampleQuestions: {
    type: Array,
    required: true
  },
  pageOptions: {
    type: Object,
    required: true
  },
  sections: {
    type: Array,
    required: true
  },
  questionSectionMap: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'update:sampleQuestions',
  'update:pageOptions',
  'update:sections',
  'update:questionSectionMap'
])

const $q = useQuasar()
const importFileInput = ref(null)

function exportToJson() {
  try {
    const normalizedQuestions = (props.sampleQuestions || []).map(q => ({
      ...q,
      ver: q.ver ?? 3
    }))

    const payload = {
      version: 1,
      exportedAt: new Date().toISOString(),
      questions: normalizedQuestions,
      settings: props.pageOptions,
      sections: props.sections,
      questionSectionMap: props.questionSectionMap
    }

    const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = generateExportFileName('Full Export', props.pageOptions) + '.json'
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
  } catch (e) {
    console.error('Export failed', e)
    $q.notify({ type: 'negative', message: 'Export failed: ' + (e?.message || e), position: 'top' })
  }
}

async function exportToWord() {
  try {
    const data = {
      questions: props.sampleQuestions || [],
      settings: props.pageOptions,
      sections: props.sections,
      questionSectionMap: props.questionSectionMap
    }

    const blob = await exportToWordUtil(data)
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = generateExportFileName('Exam', props.pageOptions) + '.docx'
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)

    $q.notify({
      type: 'positive',
      message: 'Word export completed successfully',
      position: 'top'
    })
  } catch (e) {
    console.error('Word export failed', e)
    $q.notify({
      type: 'negative',
      message: 'Word export failed: ' + (e?.message || e),
      position: 'top'
    })
  }
}

async function exportToQTI() {
  try {
    const data = {
      questions: props.sampleQuestions || [],
      settings: props.pageOptions,
      sections: props.sections,
      questionSectionMap: props.questionSectionMap
    }

    const blob = exportToQTIUtil(data)
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = generateExportFileName('Exam QTI', props.pageOptions) + '.xml'
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)

    $q.notify({
      type: 'positive',
      message: 'QTI export completed successfully',
      position: 'top'
    })
  } catch (e) {
    console.error('QTI export failed', e)
    $q.notify({
      type: 'negative',
      message: 'QTI export failed: ' + (e?.message || e),
      position: 'top'
    })
  }
}

function triggerImportFile() {
  if (importFileInput.value) {
    importFileInput.value.value = ''
    importFileInput.value.click()
  }
}

function importEscapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

function normalizeImportedExamData(parsed) {
  const warnings = []

  if (Array.isArray(parsed)) {
    const normalizedQuestions = parsed.map(q => ({
      ...q,
      ver: q.ver ?? 3
    }))
    return {
      questions: normalizedQuestions,
      pageOptions: null,
      sections: null,
      questionSectionMap: null,
      format: 'Questions Array',
      version: null,
      exportedAt: null,
      warnings
    }
  }

  if (!parsed || typeof parsed !== 'object') {
    return null
  }

  let questions = null
  if (Array.isArray(parsed.questions)) {
    questions = parsed.questions.map(q => ({
      ...q,
      ver: q.ver ?? 3
    }))
  } else if (Array.isArray(parsed.sampleQuestions)) {
    questions = parsed.sampleQuestions.map(q => ({
      ...q,
      ver: q.ver ?? 3
    }))
    warnings.push('Detected legacy/full export key `sampleQuestions`; imported it as questions.')
  }

  return {
    questions,
    pageOptions: parsed.pageOptions && typeof parsed.pageOptions === 'object' && !Array.isArray(parsed.pageOptions) ? parsed.pageOptions : null,
    sections: Array.isArray(parsed.sections) ? parsed.sections : null,
    questionSectionMap: parsed.questionSectionMap && typeof parsed.questionSectionMap === 'object' && !Array.isArray(parsed.questionSectionMap) ? parsed.questionSectionMap : null,
    format: 'Exam Object',
    version: parsed.version ?? null,
    exportedAt: parsed.exportedAt ?? null,
    warnings
  }
}

function buildNormalizedSectionMap(questions, sectionList, rawMap) {
  const validSectionIds = new Set((sectionList || []).map(s => s?.id).filter(Boolean).map(String))
  const normalizedMap = {}
  let droppedInvalidMappings = 0

  if (rawMap && typeof rawMap === 'object') {
    Object.entries(rawMap).forEach(([key, sectionId]) => {
      const sectionKey = String(sectionId)
      if (validSectionIds.has(sectionKey)) {
        normalizedMap[String(key)] = sectionKey
      } else {
        droppedInvalidMappings++
      }
    })
  }

  const defaultSectionId = sectionList?.[0]?.id ? String(sectionList[0].id) : null
  let autoAssignedMappings = 0

  ;(questions || []).forEach(q => {
    const qid = String(q?.id ?? '')
    if (!qid || qid === 'undefined' || qid === 'null') return

    const direct = normalizedMap[qid]
    const prefixed = normalizedMap['q' + qid]
    const questionSection = q?.section ? String(q.section) : null
    const best = direct || prefixed || (questionSection && validSectionIds.has(questionSection) ? questionSection : null)

    if (best) {
      normalizedMap[qid] = best
      return
    }

    if (defaultSectionId) {
      normalizedMap[qid] = defaultSectionId
      autoAssignedMappings++
    }
  })

  return {
    normalizedMap,
    droppedInvalidMappings,
    autoAssignedMappings,
    validSectionCount: validSectionIds.size
  }
}

function showImportSummaryDialog(summary) {
  const importedItems = (summary.imported || []).map(item => `<li>${importEscapeHtml(item)}</li>`).join('')
  const missingItems = (summary.missing || []).map(item => `<li>${importEscapeHtml(item)}</li>`).join('')
  const warningItems = (summary.warnings || []).map(item => `<li>${importEscapeHtml(item)}</li>`).join('')

  const message = [
    `<div><strong>Source:</strong> ${importEscapeHtml(summary.fileName || 'Unknown file')}</div>`,
    `<div><strong>Detected format:</strong> ${importEscapeHtml(summary.format || 'Unknown')}</div>`,
    `<div><strong>File size:</strong> ${importEscapeHtml(summary.fileSizeText || '-')}</div>`,
    summary.version !== null && summary.version !== undefined ? `<div><strong>Export version:</strong> ${importEscapeHtml(summary.version)}</div>` : '',
    summary.exportedAt ? `<div><strong>Exported at:</strong> ${importEscapeHtml(summary.exportedAt)}</div>` : '',
    importedItems ? `<hr style="margin:8px 0;"><div><strong>Imported successfully</strong></div><ul style="margin:4px 0 0 16px;">${importedItems}</ul>` : '',
    missingItems ? `<div style="margin-top:8px;"><strong>Missing / not applied</strong></div><ul style="margin:4px 0 0 16px;">${missingItems}</ul>` : '',
    warningItems ? `<div style="margin-top:8px;"><strong>Warnings</strong></div><ul style="margin:4px 0 0 16px;">${warningItems}</ul>` : ''
  ].filter(Boolean).join('')

  $q.dialog({
    title: 'Import Summary',
    message,
    html: true,
    ok: 'Done'
  })
}

async function handleImportFile(event) {
  try {
    const file = event?.target?.files?.[0]
    if (!file) return

    const text = await file.text()
    let parsed
    try {
      parsed = JSON.parse(text)
    } catch (parseError) {
      $q.notify({
        type: 'negative',
        message: 'Invalid JSON file. Please upload a valid exported JSON from ReadyToPrint.',
        caption: parseError?.message || 'JSON parse error',
        position: 'top',
        timeout: 7000
      })
      return
    }

    const normalized = normalizeImportedExamData(parsed)
    if (!normalized) {
      $q.notify({
        type: 'negative',
        message: 'Unsupported JSON format. Expected an exam object or an array of questions.',
        position: 'top',
        timeout: 7000
      })
      return
    }

    const imported = []
    const missing = []
    const warnings = [...normalized.warnings]

    const nextQuestions = Array.isArray(normalized.questions) ? normalized.questions : null
    if (!nextQuestions) {
      $q.notify({
        type: 'negative',
        message: 'No questions found in imported JSON.',
        caption: 'Accepted keys: questions or sampleQuestions, or root JSON array.',
        position: 'top',
        timeout: 7000
      })
      return
    }

    let nextSections = Array.isArray(normalized.sections) && normalized.sections.length > 0
      ? normalized.sections
      : props.sections

    let nextPageOptions = props.pageOptions
    if (normalized.pageOptions) {
      nextPageOptions = { ...props.pageOptions, ...normalized.pageOptions }
      imported.push('Page settings')
    } else {
      missing.push('pageOptions')
    }

    if (Array.isArray(normalized.sections) && normalized.sections.length > 0) {
      imported.push(`Sections: ${normalized.sections.length}`)
    } else {
      missing.push('Sections (kept current sections)')
    }

    const mapResult = buildNormalizedSectionMap(nextQuestions, nextSections, normalized.questionSectionMap)

    if (!normalized.questionSectionMap) {
      missing.push('questionSectionMap (auto-mapped questions to default section where needed)')
    }
    if (mapResult.validSectionCount === 0) {
      warnings.push('No valid sections found after import; question-section mapping may be incomplete.')
    }
    if (mapResult.droppedInvalidMappings > 0) {
      warnings.push(`Dropped ${mapResult.droppedInvalidMappings} invalid question-section mappings (unknown section IDs).`)
    }
    if (mapResult.autoAssignedMappings > 0) {
      warnings.push(`Auto-assigned default section to ${mapResult.autoAssignedMappings} question(s).`)
    }

    emit('update:sampleQuestions', nextQuestions)
    emit('update:sections', nextSections)
    emit('update:questionSectionMap', mapResult.normalizedMap)
    emit('update:pageOptions', nextPageOptions)

    imported.push(`Questions: ${nextQuestions.length}`)
    imported.push(`Question-section mappings: ${Object.keys(mapResult.normalizedMap).length}`)

    showImportSummaryDialog({
      fileName: file.name,
      fileSizeText: `${Math.max(1, Math.round((file.size || 0) / 1024))} KB`,
      format: normalized.format,
      version: normalized.version,
      exportedAt: normalized.exportedAt,
      imported,
      missing,
      warnings
    })

    $q.notify({
      type: 'positive',
      message: 'Import completed successfully.',
      position: 'top'
    })
  } catch (e) {
    console.error('Import failed', e)
    $q.notify({
      type: 'negative',
      message: 'Import failed: ' + (e?.message || e),
      caption: 'Please verify the JSON file and try again.',
      position: 'top',
      timeout: 7000
    })
  }
}

function copyFullExamFormat() {
  const example = {
    version: 1,
    exportedAt: new Date().toISOString(),
    questions: [
      {
        id: 1,
        type: 'short_answer',
        marks: 2,
        ver: 3,
        content: {
          prompt: 'What is the sum of $2 + 3$?'
        }
      },
      {
        id: 2,
        type: 'multiple_choice',
        marks: 3,
        ver: 3,
        content: {
          prompt: 'Solve for x: $x^2 = 16$',
          options: ['x = 4', 'x = -4', 'x = 4 or x = -4', 'x = 8'],
          correct_option_index: 2
        }
      },
      {
        id: 3,
        type: 'true_false',
        marks: 1,
        ver: 3,
        content: {
          prompt: 'The sum of angles in a triangle is 180 degrees.',
          options: ['True', 'False'],
          correct_option_index: 0
        }
      }
    ],
    settings: {
      examTitle: {
        enabled: true,
        text: 'Midterm Exam'
      },
      printHeader: {
        mode: 'template1',
        template1: {
          schoolName: 'Example School',
          subject: 'Mathematics',
          grade: 'Grade 10',
          date: '2026-05-01'
        }
      },
      printFooter: {
        enabled: true,
        pageNumberFormat: 'Page {page}',
        pageNumberFontSize: 10,
        pageNumberColor: '#000000'
      },
      paginationMode: 'strict'
    },
    sections: [
      {
        id: 'section_1',
        title: 'Section A',
        description: 'Multiple Choice and Short Answer',
        totalMarks: 10
      }
    ],
    questionSectionMap: {
      '1': 'section_1',
      '2': 'section_1',
      '3': 'section_1'
    }
  }

  const json = JSON.stringify(example, null, 2)
  navigator.clipboard.writeText(json).then(() => {
    $q.notify({
      type: 'positive',
      message: 'Full exam format copied to clipboard',
      position: 'top'
    })
  }).catch(err => {
    console.error('Copy failed', err)
    $q.notify({
      type: 'negative',
      message: 'Failed to copy to clipboard',
      position: 'top'
    })
  })
}

function copyQuestionsFormat() {
  const example = {
    version: 1,
    exportedAt: new Date().toISOString(),
    questions: [
      {
        id: 1,
        type: 'short_answer',
        marks: 2,
        ver: 3,
        content: {
          prompt: 'What is the sum of $2 + 3$?'
        }
      },
      {
        id: 2,
        type: 'multiple_choice',
        marks: 3,
        ver: 3,
        content: {
          prompt: 'Solve for x: $x^2 = 16$',
          options: ['x = 4', 'x = -4', 'x = 4 or x = -4', 'x = 8'],
          correct_option_index: 2
        }
      },
      {
        id: 3,
        type: 'true_false',
        marks: 1,
        ver: 3,
        content: {
          prompt: 'The sum of angles in a triangle is 180 degrees.',
          options: ['True', 'False'],
          correct_option_index: 0
        }
      }
    ],
    sections: [
      {
        id: 'section_1',
        title: 'Section A',
        description: 'Multiple Choice and Short Answer',
        totalMarks: 10
      }
    ],
    questionSectionMap: {
      '1': 'section_1',
      '2': 'section_1',
      '3': 'section_1'
    }
  }

  const json = JSON.stringify(example, null, 2)
  navigator.clipboard.writeText(json).then(() => {
    $q.notify({
      type: 'positive',
      message: 'Questions format copied to clipboard',
      position: 'top'
    })
  }).catch(err => {
    console.error('Copy failed', err)
    $q.notify({
      type: 'negative',
      message: 'Failed to copy to clipboard',
      position: 'top'
    })
  })
}

function copySettingsFormat() {
  const example = {
    version: 1,
    exportedAt: new Date().toISOString(),
    pageOptions: {
      examTitle: {
        enabled: true,
        text: 'Midterm Exam'
      },
      printHeader: {
        mode: 'template1',
        template1: {
          schoolName: 'Example School',
          subject: 'Mathematics',
          grade: 'Grade 10',
          date: '2026-05-01'
        }
      },
      printFooter: {
        enabled: true,
        pageNumberFormat: 'Page {page}',
        pageNumberFontSize: 10,
        pageNumberColor: '#000000'
      },
      paginationMode: 'strict'
    }
  }

  const json = JSON.stringify(example, null, 2)
  navigator.clipboard.writeText(json).then(() => {
    $q.notify({
      type: 'positive',
      message: 'Settings format copied to clipboard',
      position: 'top'
    })
  }).catch(err => {
    console.error('Copy failed', err)
    $q.notify({
      type: 'negative',
      message: 'Failed to copy to clipboard',
      position: 'top'
    })
  })
}
</script>
