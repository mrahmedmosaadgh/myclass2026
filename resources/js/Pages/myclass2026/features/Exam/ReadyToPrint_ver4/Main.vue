<template>
  <div class="rtp4-page">
    <q-toolbar class="bg-primary text-white">
      <q-icon name="quiz" size="md" class="q-mr-sm" />
      <q-toolbar-title>Ready To Print v4</q-toolbar-title>

      <q-space />

      <ThreeDotMenu
        @open-exam="openExamDialog"
        @open-my-exams="openMyExamsDialog"
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

    <div class="q-pa-md">
      <LivePrintPreview
        v-if="currentExam"
        :exam="currentExam"
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
import LivePrintPreview from './components/LivePrintPreview.vue'

const openExamOpen = ref(false)
const openMyExamsOpen = ref(false)
const currentExam = ref(null)

function openExamDialog() {
  openExamOpen.value = true
}

function openMyExamsDialog() {
  openMyExamsOpen.value = true
}

function handleExamLoaded(exam) {
  currentExam.value = exam
}
</script>

<style scoped>
.rtp4-page {
  min-height: 100vh;
}
</style>
