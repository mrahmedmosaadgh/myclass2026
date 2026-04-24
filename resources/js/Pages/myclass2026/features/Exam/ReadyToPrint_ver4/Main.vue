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
  currentExam.value = exam
  // If the loaded exam has MCQ settings, use them as a base for preview.
  const incoming = exam?.settings || exam?.pageOptions
  if (incoming?.mcqOptions) {
    previewSettings.value = {
      ...previewSettings.value,
      mcqOptions: { ...previewSettings.value.mcqOptions, ...incoming.mcqOptions }
    }
  }
}
</script>

<style scoped>
.rtp4-page {
  min-height: 100vh;
}
</style>
