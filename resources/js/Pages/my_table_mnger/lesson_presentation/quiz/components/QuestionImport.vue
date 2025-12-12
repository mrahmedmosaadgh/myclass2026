<template>
  <div class="question-import">
    <h2 class="question-import__title">{{ importTitle() }}</h2>

    <div class="question-import__info">
      <p>
        {{ t('quiz.import.uploadInstructions') }}
      </p>
      <ul class="column-list">
        <li><strong>question_type</strong> - Question type slug (e.g., multiple_choice, true_false)</li>
        <li><strong>grade_level</strong> - Grade level name</li>
        <li><strong>subject</strong> - Subject name</li>
        <li><strong>topic</strong> - Topic name (optional)</li>
        <li><strong>question_text</strong> - The question text</li>
        <li><strong>option_a, option_b, option_c, option_d</strong> - Answer options</li>
        <li><strong>correct_answer</strong> - Correct answer(s) (e.g., A or A,C for multi-select)</li>
        <li><strong>bloom_level</strong> - Bloom taxonomy level (1-6, optional)</li>
        <li><strong>difficulty_level</strong> - Difficulty level (1-5, optional)</li>
        <li><strong>estimated_time_sec</strong> - Estimated time in seconds (optional)</li>
        <li><strong>hints</strong> - Semicolon-separated hints (optional)</li>
        <li><strong>explanation</strong> - Explanation text (optional)</li>
        <li><strong>status</strong> - Status (draft, active, review, archived)</li>
      </ul>
    </div>

    <form @submit.prevent="handleSubmit" class="question-import__form">
      <!-- File Upload -->
      <div class="form-group">
        <label for="import-file" class="form-label">
          {{ importSelectFile() }} <span class="required">*</span>
        </label>
        
        <div class="file-input-wrapper">
          <input
            id="import-file"
            ref="fileInput"
            type="file"
            accept=".csv,.txt,.xlsx,.xls"
            class="file-input"
            :class="{ 'file-input--error': hasFieldError('file') }"
            @change="handleFileChange"
          />
          
          <div v-if="!selectedFile" class="file-input-placeholder">
            <svg
              class="file-input-icon"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
              />
            </svg>
            <p class="file-input-text">
              {{ t('quiz.import.clickToSelect') }}
            </p>
            <p class="file-input-hint">
              {{ t('quiz.import.fileHint') }} (max {{ formatFileSize(MAX_FILE_SIZE) }})
            </p>
          </div>

          <div v-else class="file-info">
            <div class="file-info__icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                />
              </svg>
            </div>
            <div class="file-info__details">
              <p class="file-info__name">{{ fileInfo.name }}</p>
              <p class="file-info__meta">
                {{ fileInfo.size }} • {{ fileInfo.extension.toUpperCase() }}
              </p>
            </div>
            <button
              type="button"
              class="file-info__remove"
              @click="clearFile"
              :aria-label="t('quiz.import.removeFile')"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>

        <ValidationError :error="getFieldError('file')" />
      </div>

      <!-- Import Progress -->
      <div v-if="isImporting" class="import-progress">
        <div class="import-progress__spinner"></div>
        <p class="import-progress__text">{{ importImporting() }}</p>
      </div>

      <!-- Import Results -->
      <div v-if="importResults" class="import-results">
        <div
          class="import-results__summary"
          :class="{
            'import-results__summary--success': importResults.failed === 0,
            'import-results__summary--partial': importResults.failed > 0 && importResults.successful > 0,
            'import-results__summary--error': importResults.successful === 0,
          }"
        >
          <h3 class="import-results__title">Import Complete</h3>
          <div class="import-results__stats">
            <div class="stat">
              <span class="stat__label">Total Rows:</span>
              <span class="stat__value">{{ importResults.total_rows }}</span>
            </div>
            <div class="stat stat--success">
              <span class="stat__label">Successful:</span>
              <span class="stat__value">{{ importResults.successful }}</span>
            </div>
            <div class="stat stat--error">
              <span class="stat__label">Failed:</span>
              <span class="stat__value">{{ importResults.failed }}</span>
            </div>
          </div>
        </div>

        <!-- Error Details -->
        <div v-if="importResults.errors && importResults.errors.length > 0" class="import-errors">
          <h4 class="import-errors__title">Error Details</h4>
          <div class="import-errors__list">
            <div
              v-for="(error, index) in importResults.errors"
              :key="index"
              class="import-error"
            >
              <span class="import-error__row">Row {{ error.row }}:</span>
              <span class="import-error__message">{{ error.message }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button
          type="button"
          class="btn btn--secondary"
          @click="handleCancel"
          :disabled="isImporting"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="btn btn--primary"
          :disabled="!selectedFile || isImporting"
        >
          {{ isImporting ? importImporting() : t('quiz.import.importQuestions') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useImportValidation } from '@/composables/useImportValidation'
import ValidationError from '@/components/ValidationError.vue'
import { useQuizI18n } from '../composables/useQuizI18n'
import { useI18n } from 'vue-i18n'

// Initialize i18n
const { t } = useI18n()
const { 
  importTitle,
  importSelectFile,
  importUploadFile,
  importImporting,
  importSuccess,
  importPartialSuccess,
  importFailed,
  importValidationErrors,
  importRowError
} = useQuizI18n()

interface ImportResults {
  total_rows: number
  successful: number
  failed: number
  errors: Array<{
    row: number
    message: string
    data?: any
  }>
}

const emit = defineEmits<{
  submit: [file: File]
  cancel: []
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const selectedFile = ref<File | null>(null)
const isImporting = ref(false)
const importResults = ref<ImportResults | null>(null)

const {
  errors,
  validateFile,
  getFieldError,
  hasFieldError,
  clearErrors,
  formatFileSize,
  getFileInfo,
  MAX_FILE_SIZE,
} = useImportValidation()

const fileInfo = computed(() => {
  return selectedFile.value ? getFileInfo(selectedFile.value) : null
})

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0] || null
  
  clearErrors()
  importResults.value = null
  
  if (file && validateFile(file)) {
    selectedFile.value = file
  } else {
    selectedFile.value = null
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
}

const clearFile = () => {
  selectedFile.value = null
  importResults.value = null
  clearErrors()
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const handleSubmit = async () => {
  clearErrors()
  
  if (!validateFile(selectedFile.value)) {
    return
  }

  isImporting.value = true
  emit('submit', selectedFile.value!)
}

const handleCancel = () => {
  emit('cancel')
}

// Expose method to set import results
const setImportResults = (results: ImportResults) => {
  importResults.value = results
  isImporting.value = false
}

// Expose method to set errors
const setErrors = (serverErrors: Record<string, string[]>) => {
  isImporting.value = false
  // Handle server errors if needed
}

defineExpose({
  setImportResults,
  setErrors,
  clearFile,
})
</script>

<style scoped>
.question-import {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

.question-import__title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
  color: #1f2937;
}

.question-import__info {
  padding: 1.5rem;
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 0.5rem;
  margin-bottom: 2rem;
}

.question-import__info p {
  margin-bottom: 1rem;
  color: #1e40af;
}

.column-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.column-list li {
  padding: 0.5rem 0;
  color: #1e40af;
  font-size: 0.875rem;
}

.column-list strong {
  font-family: monospace;
  background-color: #dbeafe;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
}

.question-import__form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.required {
  color: #dc2626;
}

.file-input-wrapper {
  position: relative;
  border: 2px dashed #d1d5db;
  border-radius: 0.5rem;
  transition: border-color 0.2s;
}

.file-input-wrapper:hover {
  border-color: #9ca3af;
}

.file-input {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.file-input--error + .file-input-placeholder {
  border-color: #dc2626;
}

.file-input-placeholder {
  padding: 3rem 2rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.file-input-icon {
  width: 3rem;
  height: 3rem;
  color: #9ca3af;
  margin-bottom: 1rem;
}

.file-input-text {
  font-size: 0.875rem;
  color: #374151;
  margin-bottom: 0.25rem;
}

.file-input-hint {
  font-size: 0.75rem;
  color: #6b7280;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: #f9fafb;
}

.file-info__icon {
  flex-shrink: 0;
  width: 2.5rem;
  height: 2.5rem;
  color: #3b82f6;
}

.file-info__details {
  flex: 1;
}

.file-info__name {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.file-info__meta {
  font-size: 0.75rem;
  color: #6b7280;
}

.file-info__remove {
  flex-shrink: 0;
  width: 2rem;
  height: 2rem;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  border-radius: 0.25rem;
  transition: all 0.2s;
}

.file-info__remove:hover {
  background-color: #fee2e2;
  color: #dc2626;
}

.import-progress {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background-color: #eff6ff;
  border-radius: 0.5rem;
}

.import-progress__spinner {
  width: 1.5rem;
  height: 1.5rem;
  border: 2px solid #bfdbfe;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.import-progress__text {
  color: #1e40af;
  font-weight: 500;
}

.import-results {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.import-results__summary {
  padding: 1.5rem;
  border-radius: 0.5rem;
  border: 1px solid;
}

.import-results__summary--success {
  background-color: #f0fdf4;
  border-color: #bbf7d0;
}

.import-results__summary--partial {
  background-color: #fffbeb;
  border-color: #fde68a;
}

.import-results__summary--error {
  background-color: #fef2f2;
  border-color: #fecaca;
}

.import-results__title {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.import-results__stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.stat {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat__label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat__value {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
}

.stat--success .stat__value {
  color: #16a34a;
}

.stat--error .stat__value {
  color: #dc2626;
}

.import-errors {
  padding: 1.5rem;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
}

.import-errors__title {
  font-size: 1rem;
  font-weight: 600;
  color: #991b1b;
  margin-bottom: 1rem;
}

.import-errors__list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 300px;
  overflow-y: auto;
}

.import-error {
  display: flex;
  gap: 0.5rem;
  padding: 0.75rem;
  background-color: white;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.import-error__row {
  font-weight: 600;
  color: #991b1b;
  flex-shrink: 0;
}

.import-error__message {
  color: #7f1d1d;
}

.btn {
  padding: 0.625rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
}

.btn--primary {
  background-color: #3b82f6;
  color: white;
}

.btn--primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn--secondary {
  background-color: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn--secondary:hover:not(:disabled) {
  background-color: #e5e7eb;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

@media (max-width: 640px) {
  .question-import {
    padding: 1rem;
  }

  .import-results__stats {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>
