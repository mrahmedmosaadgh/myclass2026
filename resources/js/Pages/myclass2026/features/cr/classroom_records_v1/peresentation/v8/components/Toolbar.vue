<script setup>
import { computed, ref } from 'vue'
import { useQuasar } from 'quasar'

import { usePresentationStore } from '../stores/presentationStore.js'
import { useUIStore } from '../stores/uiStore.js'

import QuizCreationDialog from './quiz-v1/QuizCreationDialog.vue'
import QuizGeneratorDialog from './quiz-v2/QuizGeneratorDialog.vue'
import GroupSetupDialog from './group-quiz/GroupSetupDialog.vue'
import GroupQuizGenerator from './group-quiz/GroupQuizGenerator.vue'
import QuestionsExportImportDialog from './QuestionsExportImportDialog.vue'

const $q = useQuasar()

const presentation = usePresentationStore()
const ui = useUIStore()

/* -------------------------------- */
/* refs */
/* -------------------------------- */

const imageInputRef = ref(null)
const importInputRef = ref(null)

const showQuizCreationDialog = ref(false)
const showQuizGeneratorDialog = ref(false)
const showGroupSetupDialog = ref(false)
const showGroupQuizGenerator = ref(false)
const showQuestionsExportImportDialog = ref(false)

const presentationQuestions = computed(() => {
  const questions = []

  presentation.slides.forEach(slide => {
    if (!Array.isArray(slide.elements)) return

    slide.elements.forEach(element => {
      if (element.type === 'quiz-v2' && Array.isArray(element.questions)) {
        questions.push(...element.questions)
      }

      if (element.type === 'group-mcq' && element.questionData) {
        questions.push(element.questionData)
      }
    })
  })

  return questions
})

/* -------------------------------- */
/* ELEMENTS */
/* -------------------------------- */

function addText() {
  presentation.addElement({
    type: 'text',
    content: 'New Text',
    fontSize: 24,
    color: '#111827',
    width: 220,
    height: 50
  })
}

function addHeading() {
  presentation.addElement({
    type: 'text',
    content: 'Heading',
    fontSize: 48,
    color: '#111827',
    fontWeight: 'bold',
    width: 420,
    height: 80
  })
}

function addSubheading() {
  presentation.addElement({
    type: 'text',
    content: 'Subheading',
    fontSize: 30,
    color: '#4b5563',
    fontWeight: '600',
    width: 360,
    height: 60
  })
}

function addImage() {
  imageInputRef.value?.click()
}

function handleImageUpload(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()

  reader.onload = (event) => {
    presentation.addElement({
      type: 'image',
      src: event.target.result,
      width: 420,
      height: 300
    })
  }

  reader.readAsDataURL(file)

  e.target.value = ''
}

function addRectangle() {
  presentation.addElement({
    type: 'rectangle',
    backgroundColor: '#6366f1',
    width: 240,
    height: 160,
    borderRadius: '4px'
  })
}

/* -------------------------------- */
/* QUIZ */
/* -------------------------------- */

function addQuiz() {
  showQuizCreationDialog.value = true
}

function createQuiz(quizData) {
  presentation.addQuiz(quizData)
  showQuizCreationDialog.value = false
}

function cancelQuizCreation() {
  showQuizCreationDialog.value = false
}

function openQuizGenerator() {
  showQuizGeneratorDialog.value = true
}

function closeQuizGenerator() {
  showQuizGeneratorDialog.value = false
}

function openGroupSetup() {
  showGroupSetupDialog.value = true
}

function closeGroupSetup() {
  showGroupSetupDialog.value = false
}

function openGroupQuizGenerator() {
  showGroupQuizGenerator.value = true
}

function closeGroupQuizGenerator() {
  showGroupQuizGenerator.value = false
}

/* -------------------------------- */
/* SLIDES */
/* -------------------------------- */

function addNewSlide() {
  presentation.addSlide()

  $q.notify({
    type: 'positive',
    message: 'New slide added',
    position: 'top-right'
  })
}

function deleteCurrentSlide() {
  if (presentation.totalSlides <= 1) return

  presentation.deleteSlide(presentation.currentSlideIndex)

  $q.notify({
    type: 'warning',
    message: 'Slide deleted',
    position: 'top-right'
  })
}

/* -------------------------------- */
/* FILE */
/* -------------------------------- */

function exportPresentation() {
  presentation.exportPresentation()
}

function importPresentation() {
  importInputRef.value?.click()
}

function handleImportUpload(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()

  reader.onload = (event) => {
    const success = presentation.importPresentation(event.target.result)

    if (!success) {
      $q.notify({
        type: 'negative',
        message: 'Failed to import presentation',
        position: 'top',
        timeout: 4000
      })
    } else {
      $q.notify({
        type: 'positive',
        message: 'Presentation imported',
        position: 'top-right'
      })
    }
  }

  reader.readAsText(file)

  e.target.value = ''
}

function openQuestionsExportImport() {
  showQuestionsExportImportDialog.value = true
}

function closeQuestionsExportImport() {
  showQuestionsExportImportDialog.value = false
}

function handleQuestionsImported(importedQuestions) {
  if (!Array.isArray(importedQuestions) || importedQuestions.length === 0) return

  presentation.addElement({
    id: 'quiz-v2-' + Date.now(),
    type: 'quiz-v2',
    title: 'Imported Questions',
    questions: importedQuestions,
    settings: {
      pointsPerCorrect: 10,
      penaltyPerWrong: 0,
      showExplanation: true,
      timerEnabled: false,
      timerSeconds: 30,
      autoAdvance: true,
      autoAdvanceDelay: 1200
    },
    currentQuestionIndex: 0,
    userAnswers: {},
    showResults: false,
    x: 60,
    y: 40,
    width: 680,
    height: 520,
    zIndex: 1,
    visibilityOption: 'always-visible',
    isVisible: true
  })

  showQuestionsExportImportDialog.value = false
}
</script>

<template>
  <div class="toolbar-wrapper">

    <q-toolbar class="modern-toolbar">

      <!-- BRAND -->
      <div class="toolbar-brand">
        <div class="brand-icon">
          <q-icon
            name="dashboard_customize"
            size="20px"
          />
        </div>

        <div class="brand-text">
          Slides Studio
        </div>
      </div>

      <q-space />

      <!-- SEARCH -->
      <q-input
        dense
        rounded
        standout
        dark
        placeholder="Quick actions..."
        class="toolbar-search"
      >
        <template #prepend>
          <q-icon name="search" />
        </template>

        <template #append>
          <span class="shortcut-key">⌘K</span>
        </template>
      </q-input>

      <!-- INSERT -->
      <q-btn-dropdown
        flat
        rounded
        no-caps
        class="toolbar-dropdown"
        icon="add_circle"
        label="Insert"
        dropdown-icon="expand_more"
      >
        <q-list style="min-width: 240px">

          <q-item
            clickable
            v-close-popup
            @click="addText"
          >
            <q-item-section avatar>
              <q-icon name="text_fields" />
            </q-item-section>

            <q-item-section>
              Text
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="addHeading"
          >
            <q-item-section avatar>
              <q-icon name="title" />
            </q-item-section>

            <q-item-section>
              Heading
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="addSubheading"
          >
            <q-item-section avatar>
              <q-icon name="short_text" />
            </q-item-section>

            <q-item-section>
              Subheading
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item
            clickable
            v-close-popup
            @click="addImage"
          >
            <q-item-section avatar>
              <q-icon name="image" />
            </q-item-section>

            <q-item-section>
              Image
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="addRectangle"
          >
            <q-item-section avatar>
              <q-icon name="crop_square" />
            </q-item-section>

            <q-item-section>
              Shape
            </q-item-section>
          </q-item>

        </q-list>
      </q-btn-dropdown>

      <!-- QUIZ -->
      <q-btn-dropdown
        flat
        rounded
        no-caps
        class="toolbar-dropdown"
        icon="quiz"
        label="Quiz"
      >
        <q-list style="min-width: 240px">

          <q-item
            clickable
            v-close-popup
            @click="addQuiz"
          >
            <q-item-section avatar>
              <q-icon name="fact_check" />
            </q-item-section>

            <q-item-section>
              Create Quiz
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="openQuizGenerator"
          >
            <q-item-section avatar>
              <q-icon name="auto_awesome" />
            </q-item-section>

            <q-item-section>
              AI Quiz Generator
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item
            clickable
            v-close-popup
            @click="openGroupSetup"
          >
            <q-item-section avatar>
              <q-icon name="groups" />
            </q-item-section>

            <q-item-section>
              Group Setup
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="openGroupQuizGenerator"
          >
            <q-item-section avatar>
              <q-icon name="hub" />
            </q-item-section>

            <q-item-section>
              Group Quiz Generator
            </q-item-section>
          </q-item>

        </q-list>
      </q-btn-dropdown>

      <!-- SLIDES -->
      <q-btn-group
        flat
        rounded
        class="toolbar-group"
      >
        <q-btn
          flat
          rounded
          icon="add"
          label="Slide"
          no-caps
          @click="addNewSlide"
        >
          <q-tooltip>Add Slide</q-tooltip>
        </q-btn>

        <q-btn
          flat
          rounded
          color="negative"
          icon="delete"
          :disable="presentation.totalSlides <= 1"
          @click="deleteCurrentSlide"
        >
          <q-tooltip>Delete Slide</q-tooltip>
        </q-btn>
      </q-btn-group>

      <!-- CURRENT SLIDE -->
      <q-chip
        color="primary"
        text-color="white"
        icon="slideshow"
        class="slide-chip"
      >
        Slide {{ presentation.currentSlideIndex + 1 }}
      </q-chip>

      <!-- MODE -->
      <q-btn
        unelevated
        rounded
        no-caps
        class="mode-btn"
        :color="ui.isEditMode ? 'dark' : 'primary'"
        :icon="ui.isEditMode ? 'slideshow' : 'edit'"
        :label="ui.isEditMode ? 'Present' : 'Edit'"
        @click="ui.toggleEditMode"
      />

      <!-- FILE -->
      <q-btn-dropdown
        flat
        rounded
        no-caps
        class="toolbar-dropdown"
        icon="folder"
        label="File"
      >
        <q-list style="min-width: 220px">

          <q-item
            clickable
            v-close-popup
            @click="$emit('save-to-cloud')"
          >
            <q-item-section avatar>
              <q-icon name="cloud_upload" />
            </q-item-section>

            <q-item-section>
              Cloud Save
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="$emit('open-presentation-manager')"
          >
            <q-item-section avatar>
              <q-icon name="library_books" />
            </q-item-section>

            <q-item-section>
              My Presentations
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item
            clickable
            v-close-popup
            @click="exportPresentation"
          >
            <q-item-section avatar>
              <q-icon name="download" />
            </q-item-section>

            <q-item-section>
              Export
            </q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="importPresentation"
          >
            <q-item-section avatar>
              <q-icon name="upload" />
            </q-item-section>

            <q-item-section>
              Import
            </q-item-section>
          </q-item>

          <q-separator />

          <q-item
            clickable
            v-close-popup
            @click="openQuestionsExportImport"
          >
            <q-item-section avatar>
              <q-icon name="quiz" />
            </q-item-section>

            <q-item-section>
              Questions JSON
            </q-item-section>
          </q-item>

        </q-list>
      </q-btn-dropdown>

    </q-toolbar>

    <!-- IMAGE INPUT -->
    <input
      ref="imageInputRef"
      type="file"
      accept="image/*"
      class="hidden-input"
      @change="handleImageUpload"
    />

    <!-- IMPORT INPUT -->
    <input
      ref="importInputRef"
      type="file"
      accept=".json"
      class="hidden-input"
      @change="handleImportUpload"
    />

    <!-- DIALOGS -->

    <QuizCreationDialog
      v-if="showQuizCreationDialog"
      @close="cancelQuizCreation"
      @create="createQuiz"
    />

    <QuizGeneratorDialog
      v-if="showQuizGeneratorDialog"
      @close="closeQuizGenerator"
    />

    <GroupSetupDialog
      v-if="showGroupSetupDialog"
      @close="closeGroupSetup"
    />

    <GroupQuizGenerator
      v-if="showGroupQuizGenerator"
      @close="closeGroupQuizGenerator"
    />

    <QuestionsExportImportDialog
      v-if="showQuestionsExportImportDialog"
      :questions="presentationQuestions"
      @close="closeQuestionsExportImport"
      @imported="handleQuestionsImported"
    />

  </div>
</template>

<style scoped>
.toolbar-wrapper {
  position: sticky;
  top: 12px;
  z-index: 1000;
  padding-bottom: 12px;
}

.modern-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;

  padding: 14px 18px;

  border-radius: 4px;

  background: rgba(255, 255, 255, 0.75);

  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);

  border: 1px solid rgba(255,255,255,0.4);

  box-shadow:
    0 10px 40px rgba(15, 23, 42, 0.08),
    inset 0 1px 0 rgba(255,255,255,0.6);
}

/* BRAND */

.toolbar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-icon {
  width: 38px;
  height: 38px;

  display: flex;
  align-items: center;
  justify-content: center;

  border-radius: 4px;

  background: linear-gradient(
    135deg,
    #6366f1,
    #8b5cf6
  );

  color: white;

  box-shadow:
    0 8px 20px rgba(99,102,241,0.35);
}

.brand-text {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

/* SEARCH */

.toolbar-search {
  width: 220px;
}

.shortcut-key {
  font-size: 11px;
  opacity: 0.7;
}

/* BUTTONS */

.toolbar-dropdown {
  border-radius: 4px;
}

.toolbar-group {
  padding: 3px;
  border-radius: 4px;

  background: rgba(0,0,0,0.04);
}

.mode-btn {
  padding-inline: 18px;

  box-shadow:
    0 8px 20px rgba(0,0,0,0.08);
}

.slide-chip {
  font-weight: 600;
}

/* GLOBAL BUTTON STYLE */

:deep(.q-btn) {
  transition:
    transform 0.2s ease,
    background 0.2s ease,
    box-shadow 0.2s ease;
}

:deep(.q-btn:hover) {
  transform: translateY(-1px);
}

/* DROPDOWN MENU */

:deep(.q-menu .q-list) {
  padding: 8px;
  border-radius: 4px;
}

:deep(.q-item) {
  border-radius: 4px;
}

:deep(.q-item:hover) {
  background: rgba(99,102,241,0.08);
}

/* HIDDEN INPUT */

.hidden-input {
  display: none;
}

/* MOBILE */

@media (max-width: 768px) {

  .toolbar-wrapper {
    top: 0;
    padding-bottom: 0;
  }

  .modern-toolbar {
    overflow-x: auto;

    white-space: nowrap;

    border-radius: 0;

    padding: 10px;
  }

  .modern-toolbar::-webkit-scrollbar {
    display: none;
  }

  .brand-text {
    display: none;
  }

  .toolbar-search {
    display: none;
  }

  :deep(.q-btn .block) {
    display: none;
  }

  .slide-chip {
    display: none;
  }
}
</style>