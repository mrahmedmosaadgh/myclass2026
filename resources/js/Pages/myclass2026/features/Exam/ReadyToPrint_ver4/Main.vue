<template>
  <div class="rtp4-page">
    <q-toolbar class="bg-primary text-white">
      <q-icon name="quiz" size="md" class="q-mr-sm" />
      <q-toolbar-title>Ready To Print v4</q-toolbar-title>

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
      />

      <div v-else class="text-grey-7">
        Open an exam to preview it.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ThreeDotMenu from './components/ThreeDotMenu.vue'
import OpenExamDialog from './components/OpenExamDialog.vue'
import OpenMyExamsDialog from './components/OpenMyExamsDialog.vue'
import SettingsDialog from './components/SettingsDialog.vue'
import LivePrintPreview from './components/LivePrintPreview.vue'

const openExamOpen = ref(false)
const openMyExamsOpen = ref(false)
const currentExam = ref(null)

const settingsOpen = ref(false)
const settingsInitialTab = ref('mcq')

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
