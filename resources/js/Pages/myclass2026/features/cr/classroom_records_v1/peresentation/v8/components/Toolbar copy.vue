<script setup>
import { ref } from 'vue'
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

const fileInputRef = ref(null)
const showQuizCreationDialog = ref(false)
const showQuizGeneratorDialog = ref(false)
const showGroupSetupDialog = ref(false)
const showGroupQuizGenerator = ref(false)

// Element creation methods
function addText() {
  presentation.addElement({
    type: 'text',
    content: 'New Text',
    fontSize: 24,
    color: '#000000',
    width: 200,
    height: 40
  })
}

function addHeading() {
  presentation.addElement({
    type: 'text',
    content: 'Heading',
    fontSize: 48,
    color: '#111827',
    fontWeight: 'bold',
    width: 400,
    height: 80
  })
}

function addSubheading() {
  presentation.addElement({
    type: 'text',
    content: 'Subheading',
    fontSize: 32,
    color: '#374151',
    fontWeight: '600',
    width: 350,
    height: 60
  })
}

function addImage() {
  fileInputRef.value?.click()
}

function handleImageUpload(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (event) => {
    presentation.addElement({
      type: 'image',
      src: event.target.result,
      width: 400,
      height: 300
    })
  }
  reader.readAsDataURL(file)
  
  // Reset input
  e.target.value = ''
}

function addRectangle() {
  presentation.addElement({
    type: 'rectangle',
    backgroundColor: '#6366f1',
    width: 200,
    height: 150,
    borderRadius: '8px'
  })
}

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

function openQuizGenerator() {
  showQuizGeneratorDialog.value = true
}

function closeQuizGenerator() {
  showQuizGeneratorDialog.value = false
}

// Slide management
function addNewSlide() {
  presentation.addSlide()
}

function deleteCurrentSlide() {
  if (presentation.totalSlides > 1) {
    presentation.deleteSlide(presentation.currentSlideIndex)
  }
}

// Export/Import
function exportPresentation() {
  presentation.exportPresentation()
}

function importPresentation() {
  fileInputRef.value?.click()
}

function handleImportUpload(e) {
  const file = e.target.files[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (event) => {
    const success = presentation.importPresentation(event.target.result)
    if (!success) {
      $q.notify({ type: 'negative', message: 'Failed to import presentation. Please check the file format.', position: 'top', timeout: 4000 })
    }
  }
  reader.readAsText(file)
  
  // Reset input
  e.target.value = ''
}
</script>

<template>
  <div class="toolbar">
    <!-- Element Creation Section -->
    <div class="toolbar-section">
      <div class="section-title">Add Elements</div>
      <div class="button-group">
        <button @click="addText" class="toolbar-btn" title="Add Text">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="4 7 4 4 7 4"></polyline>
            <line x1="9" y1="20" x2="15" y2="20"></line>
            <line x1="12" y1="4" x2="12" y2="20"></line>
          </svg>
          <span>Text</span>
        </button>
        
        <button @click="addHeading" class="toolbar-btn" title="Add Heading">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 12h16"></path>
            <path d="M4 6h16"></path>
            <path d="M4 18h7"></path>
          </svg>
          <span>Heading</span>
        </button>
        
        <button @click="addSubheading" class="toolbar-btn" title="Add Subheading">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 12h16"></path>
            <path d="M4 6h10"></path>
            <path d="M4 18h14"></path>
          </svg>
          <span>Subheading</span>
        </button>
        
        <button @click="addImage" class="toolbar-btn" title="Add Image">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <circle cx="8.5" cy="8.5" r="1.5"></circle>
            <polyline points="21 15 16 10 5 21"></polyline>
          </svg>
          <span>Image</span>
        </button>
        
        <button @click="addRectangle" class="toolbar-btn" title="Add Rectangle">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          </svg>
          <span>Rectangle</span>
        </button>
        
        <button @click="addQuiz" class="toolbar-btn" title="Add Quiz">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 11H3v2h6v-2zm0-4H3v2h6V7zm0 8H3v2h6v-2zm12-8h-6v2h6V7zm0 4h-6v2h6v-2zm0 4h-6v2h6v-2z"/>
          </svg>
          <span>Quiz</span>
        </button>

        <button @click="openQuizGenerator" class="toolbar-btn" title="AI Quiz Generator">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M8 12h8"/>
          </svg>
          <span>AI Quiz</span>
        </button>

        <button @click="openGroupSetup" class="toolbar-btn" title="Group Setup">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          <span>Setup</span>
        </button>

        <button @click="openGroupQuizGenerator" class="toolbar-btn" title="Group Quiz Generator">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <span>G-Quiz</span>
        </button>
      </div>
    </div>

    <!-- Slide Management Section -->
    <div class="toolbar-section">
      <div class="section-title">Slides</div>
      <div class="button-group">
        <button @click="addNewSlide" class="toolbar-btn" title="Add Slide">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          <span>Add Slide</span>
        </button>
        
        <button 
          @click="deleteCurrentSlide" 
          class="toolbar-btn danger" 
          :disabled="presentation.totalSlides <= 1"
          title="Delete Current Slide"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
          <span>Delete</span>
        </button>
      </div>
    </div>

    <!-- File Operations Section -->
    <div class="toolbar-section">
      <div class="section-title">File</div>
      <div class="button-group">
        <button
          @click="ui.toggleEditMode"
          class="toolbar-btn"
          :class="{ primary: !ui.isEditMode }"
          title="Toggle Present Mode (E)"
        >
          <svg v-if="ui.isEditMode" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
          </svg>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>{{ ui.isEditMode ? 'Present' : 'Edit' }}</span>
        </button>

        <button @click="exportPresentation" class="toolbar-btn" title="Export Presentation">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
          </svg>
          <span>Export</span>
        </button>
        
        <button @click="importPresentation" class="toolbar-btn" title="Import Presentation">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
          <span>Import</span>
        </button>
      </div>
    </div>

    <!-- Hidden file inputs -->
    <input
      ref="fileInputRef"
      type="file"
      accept="image/*"
      style="display: none"
      @change="handleImageUpload"
    />
    <input
      ref="fileInputRef"
      type="file"
      accept=".json"
      style="display: none"
      @change="handleImportUpload"
    />
    
    <!-- Quiz Creation Dialog -->
    <QuizCreationDialog
      v-if="showQuizCreationDialog"
      @close="cancelQuizCreation"
      @create="createQuiz"
    />

    <!-- AI Quiz Generator Dialog -->
    <QuizGeneratorDialog
      v-if="showQuizGeneratorDialog"
      @close="closeQuizGenerator"
    />

    <!-- Group Setup Dialog -->
    <GroupSetupDialog
      v-if="showGroupSetupDialog"
      @close="closeGroupSetup"
    />

    <!-- Group Quiz Generator -->
    <GroupQuizGenerator
      v-if="showGroupQuizGenerator"
      @close="closeGroupQuizGenerator"
    />
  </div>
</template>

<style scoped>
.toolbar {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px;
  margin-bottom: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.toolbar-section {
  margin-bottom: 16px;
}

.toolbar-section:last-child {
  margin-bottom: 0;
}

.section-title {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.button-group {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.toolbar-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  touch-action: manipulation; /* Improve touch responsiveness */
  min-height: 44px; /* iOS touch target minimum */
  position: relative;
}

.toolbar-btn:hover:not(:disabled) {
  background: #f9fafb;
  border-color: #d1d5db;
  color: #111827;
}

.toolbar-btn:active:not(:disabled) {
  background: #f3f4f6;
}

.toolbar-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toolbar-btn.danger {
  border-color: #fecaca;
  color: #dc2626;
}

.toolbar-btn.danger:hover:not(:disabled) {
  background: #fef2f2;
  border-color: #fca5a5;
}

.toolbar-btn.primary {
  background: #6366f1;
  color: white;
  border-color: #6366f1;
}

.toolbar-btn.primary:hover:not(:disabled) {
  background: #4f46e5;
  border-color: #4f46e5;
}

.toolbar-btn svg {
  flex-shrink: 0;
}

.toolbar-btn span {
  white-space: nowrap;
}

/* Mobile-first responsive design */
@media (max-width: 767px) {
  .toolbar {
    padding: 8px;
    margin-bottom: 8px;
    border-radius: 0;
    border-left: none;
    border-right: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
  }
  
  .toolbar-section {
    margin-bottom: 12px;
  }
  
  .toolbar-section:last-child {
    margin-bottom: 0;
  }
  
  .button-group {
    gap: 6px;
    justify-content: space-between;
  }
  
  .toolbar-btn {
    flex: 1;
    min-width: 0;
    padding: 8px 6px;
    font-size: 12px;
    flex-direction: column;
    align-items: center;
    gap: 4px;
  }
  
  .toolbar-btn span {
    font-size: 10px;
    line-height: 1;
  }
  
  .toolbar-btn svg {
    width: 20px;
    height: 20px;
  }
}

@media (min-width: 768px) {
  .toolbar {
    position: static;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    padding: 16px;
    margin-bottom: 16px;
  }
  
  .toolbar-btn {
    flex-direction: row;
    padding: 8px 12px;
    font-size: 14px;
  }
  
  .toolbar-btn span {
    font-size: 14px;
  }
  
  .toolbar-btn svg {
    width: 16px;
    height: 16px;
  }
}
</style>
