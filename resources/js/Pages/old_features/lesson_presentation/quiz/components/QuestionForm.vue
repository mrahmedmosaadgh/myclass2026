<template>
  <div class="question-form">
    <h2 class="question-form__title">
      {{ isEditing ? 'Edit Question' : 'Create Question' }}
    </h2>

    <form @submit.prevent="handleSubmit" class="question-form__form">
      <!-- Question Type -->
      <div class="form-group">
        <label for="question-type" class="form-label">
          Question Type <span class="required">*</span>
        </label>
        <select
          id="question-type"
          v-model="formData.question_type_id"
          class="form-input"
          :class="{ 'form-input--error': hasFieldError('question_type_id') }"
          @change="clearFieldError('question_type_id')"
        >
          <option :value="null">Select a question type</option>
          <option
            v-for="type in questionTypes"
            :key="type.id"
            :value="type.id"
          >
            {{ type.name }}
          </option>
        </select>
        <ValidationError :error="getFieldError('question_type_id')" />
      </div>

      <!-- Question Text -->
      <div class="form-group">
        <label for="question-text" class="form-label">
          Question Text <span class="required">*</span>
        </label>
        <textarea
          id="question-text"
          v-model="formData.question_text"
          class="form-input form-textarea"
          :class="{ 'form-input--error': hasFieldError('question_text') }"
          rows="4"
          placeholder="Enter your question here..."
          @input="clearFieldError('question_text')"
        />
        <div class="form-hint">
          {{ formData.question_text.length }} / 5000 characters
        </div>
        <ValidationError :error="getFieldError('question_text')" />
      </div>

      <!-- Grade Level -->
      <div class="form-group">
        <label for="grade-level" class="form-label">
          Grade Level <span class="required">*</span>
        </label>
        <select
          id="grade-level"
          v-model="formData.grade_level_id"
          class="form-input"
          :class="{ 'form-input--error': hasFieldError('grade_level_id') }"
          @change="clearFieldError('grade_level_id')"
        >
          <option :value="null">Select a grade level</option>
          <option
            v-for="grade in gradeLevels"
            :key="grade.id"
            :value="grade.id"
          >
            {{ grade.name }}
          </option>
        </select>
        <ValidationError :error="getFieldError('grade_level_id')" />
      </div>

      <!-- Subject -->
      <div class="form-group">
        <label for="subject" class="form-label">
          Subject <span class="required">*</span>
        </label>
        <select
          id="subject"
          v-model="formData.subject_id"
          class="form-input"
          :class="{ 'form-input--error': hasFieldError('subject_id') }"
          @change="clearFieldError('subject_id')"
        >
          <option :value="null">Select a subject</option>
          <option
            v-for="subject in subjects"
            :key="subject.id"
            :value="subject.id"
          >
            {{ subject.name }}
          </option>
        </select>
        <ValidationError :error="getFieldError('subject_id')" />
      </div>

      <!-- Topic (Optional) -->
      <div class="form-group">
        <label for="topic" class="form-label">Topic</label>
        <select
          id="topic"
          v-model="formData.topic_id"
          class="form-input"
          :class="{ 'form-input--error': hasFieldError('topic_id') }"
          @change="clearFieldError('topic_id')"
        >
          <option :value="null">Select a topic (optional)</option>
          <option
            v-for="topic in topics"
            :key="topic.id"
            :value="topic.id"
          >
            {{ topic.name }}
          </option>
        </select>
        <ValidationError :error="getFieldError('topic_id')" />
      </div>

      <!-- Bloom Level -->
      <div class="form-group">
        <label for="bloom-level" class="form-label">Bloom Level</label>
        <select
          id="bloom-level"
          v-model="formData.bloom_level"
          class="form-input"
          :class="{ 'form-input--error': hasFieldError('bloom_level') }"
          @change="clearFieldError('bloom_level')"
        >
          <option :value="null">Select Bloom level (optional)</option>
          <option :value="1">1 - Remember</option>
          <option :value="2">2 - Understand</option>
          <option :value="3">3 - Apply</option>
          <option :value="4">4 - Analyze</option>
          <option :value="5">5 - Evaluate</option>
          <option :value="6">6 - Create</option>
        </select>
        <ValidationError :error="getFieldError('bloom_level')" />
      </div>

      <!-- Difficulty Level -->
      <div class="form-group">
        <label for="difficulty-level" class="form-label">
          Difficulty Level
        </label>
        <select
          id="difficulty-level"
          v-model="formData.difficulty_level"
          class="form-input"
          :class="{ 'form-input--error': hasFieldError('difficulty_level') }"
          @change="clearFieldError('difficulty_level')"
        >
          <option :value="null">Select difficulty (optional)</option>
          <option :value="1">1 - Very Easy</option>
          <option :value="2">2 - Easy</option>
          <option :value="3">3 - Medium</option>
          <option :value="4">4 - Hard</option>
          <option :value="5">5 - Very Hard</option>
        </select>
        <ValidationError :error="getFieldError('difficulty_level')" />
      </div>

      <!-- Estimated Time -->
      <div class="form-group">
        <label for="estimated-time" class="form-label">
          Estimated Time (seconds)
        </label>
        <input
          id="estimated-time"
          v-model.number="formData.estimated_time_sec"
          type="number"
          class="form-input"
          :class="{ 'form-input--error': hasFieldError('estimated_time_sec') }"
          min="1"
          max="3600"
          placeholder="e.g., 60"
          @input="clearFieldError('estimated_time_sec')"
        />
        <ValidationError :error="getFieldError('estimated_time_sec')" />
      </div>

      <!-- Status -->
      <div class="form-group">
        <label for="status" class="form-label">
          Status <span class="required">*</span>
        </label>
        <select
          id="status"
          v-model="formData.status"
          class="form-input"
          :class="{ 'form-input--error': hasFieldError('status') }"
          @change="clearFieldError('status')"
        >
          <option value="draft">Draft</option>
          <option value="active">Active</option>
          <option value="review">Under Review</option>
          <option value="archived">Archived</option>
        </select>
        <ValidationError :error="getFieldError('status')" />
      </div>

      <!-- Options Section (if question type supports options) -->
      <div v-if="selectedQuestionType?.has_options" class="form-section">
        <h3 class="form-section__title">Answer Options</h3>
        
        <div
          v-for="(option, index) in formData.options"
          :key="index"
          class="option-item"
        >
          <div class="option-item__header">
            <span class="option-item__label">Option {{ option.option_key }}</span>
            <button
              type="button"
              class="option-item__remove"
              @click="removeOption(index)"
              :disabled="formData.options.length <= 2"
            >
              Remove
            </button>
          </div>
          
          <input
            v-model="option.option_text"
            type="text"
            class="form-input"
            placeholder="Enter option text..."
            @input="clearFieldError('options')"
          />
          
          <label class="checkbox-label">
            <input
              v-model="option.is_correct"
              type="checkbox"
              @change="clearFieldError('options')"
            />
            <span>Correct answer</span>
          </label>
        </div>

        <button
          type="button"
          class="btn btn--secondary"
          @click="addOption"
          :disabled="formData.options.length >= 10"
        >
          Add Option
        </button>

        <ValidationError :error="getFieldError('options')" />
      </div>

      <!-- Hints Section (if question type supports hints) -->
      <div v-if="selectedQuestionType?.supports_hints" class="form-section">
        <h3 class="form-section__title">Hints (Optional)</h3>
        
        <div
          v-for="(hint, index) in formData.hints"
          :key="index"
          class="hint-item"
        >
          <input
            v-model="formData.hints[index]"
            type="text"
            class="form-input"
            :placeholder="`Hint ${index + 1}`"
            @input="clearFieldError('hints')"
          />
          <button
            type="button"
            class="btn btn--icon"
            @click="removeHint(index)"
          >
            ×
          </button>
        </div>

        <button
          type="button"
          class="btn btn--secondary"
          @click="addHint"
          :disabled="formData.hints.length >= 5"
        >
          Add Hint
        </button>

        <ValidationError :error="getFieldError('hints')" />
      </div>

      <!-- Explanation Section (if question type supports explanation) -->
      <div v-if="selectedQuestionType?.supports_explanation" class="form-section">
        <h3 class="form-section__title">Explanation (Optional)</h3>
        
        <textarea
          v-model="formData.explanation.text"
          class="form-input form-textarea"
          rows="3"
          placeholder="Provide an explanation for this question..."
          @input="clearFieldError('explanation')"
        />

        <label class="checkbox-label">
          <input
            v-model="formData.explanation.revealed_after_attempt"
            type="checkbox"
          />
          <span>Reveal explanation only after attempt</span>
        </label>

        <ValidationError :error="getFieldError('explanation')" />
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button
          type="button"
          class="btn btn--secondary"
          @click="handleCancel"
        >
          Cancel
        </button>
        <button
          type="submit"
          class="btn btn--primary"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? 'Saving...' : (isEditing ? 'Update Question' : 'Create Question') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useQuestionValidation, type QuestionFormData, type QuestionType } from '@/composables/useQuestionValidation'
import ValidationError from '@/components/ValidationError.vue'

interface Props {
  questionTypes: QuestionType[]
  gradeLevels: Array<{ id: number; name: string }>
  subjects: Array<{ id: number; name: string }>
  topics: Array<{ id: number; name: string }>
  initialData?: Partial<QuestionFormData>
  isEditing?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  initialData: () => ({}),
  isEditing: false,
})

const emit = defineEmits<{
  submit: [data: QuestionFormData]
  cancel: []
}>()

// Form data
const formData = ref<QuestionFormData>({
  question_type_id: null,
  question_text: '',
  grade_level_id: null,
  subject_id: null,
  topic_id: null,
  bloom_level: null,
  difficulty_level: null,
  estimated_time_sec: null,
  status: 'draft',
  hints: [],
  explanation: {
    text: '',
    revealed_after_attempt: true,
  },
  options: [],
  ...props.initialData,
})

const isSubmitting = ref(false)

// Get selected question type
const selectedQuestionType = computed(() => {
  if (!formData.value.question_type_id) return null
  return props.questionTypes.find(t => t.id === formData.value.question_type_id) || null
})

// Validation
const {
  errors,
  validateForm,
  getFieldError,
  hasFieldError,
  clearFieldError,
  clearErrors,
  setServerErrors,
} = useQuestionValidation(selectedQuestionType)

// Watch question type changes to initialize options
watch(() => formData.value.question_type_id, (newTypeId) => {
  const questionType = props.questionTypes.find(t => t.id === newTypeId)
  
  if (questionType?.has_options && formData.value.options.length === 0) {
    // Initialize with 4 default options
    formData.value.options = [
      { option_key: 'A', option_text: '', is_correct: false, distractor_strength: null, order_index: 0 },
      { option_key: 'B', option_text: '', is_correct: false, distractor_strength: null, order_index: 1 },
      { option_key: 'C', option_text: '', is_correct: false, distractor_strength: null, order_index: 2 },
      { option_key: 'D', option_text: '', is_correct: false, distractor_strength: null, order_index: 3 },
    ]
  } else if (!questionType?.has_options) {
    formData.value.options = []
  }
})

// Option management
const addOption = () => {
  const nextKey = String.fromCharCode(65 + formData.value.options.length) // A, B, C, ...
  formData.value.options.push({
    option_key: nextKey,
    option_text: '',
    is_correct: false,
    distractor_strength: null,
    order_index: formData.value.options.length,
  })
}

const removeOption = (index: number) => {
  formData.value.options.splice(index, 1)
  // Reorder remaining options
  formData.value.options.forEach((opt, i) => {
    opt.option_key = String.fromCharCode(65 + i)
    opt.order_index = i
  })
}

// Hint management
const addHint = () => {
  formData.value.hints.push('')
}

const removeHint = (index: number) => {
  formData.value.hints.splice(index, 1)
}

// Form submission
const handleSubmit = () => {
  clearErrors()
  
  if (!validateForm(formData.value)) {
    return
  }

  isSubmitting.value = true
  emit('submit', formData.value)
}

const handleCancel = () => {
  emit('cancel')
}

// Expose method to set server errors
defineExpose({
  setServerErrors,
  clearErrors,
})
</script>

<style scoped>
.question-form {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

.question-form__title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 2rem;
  color: #1f2937;
}

.question-form__form {
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

.form-input {
  padding: 0.625rem 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  transition: border-color 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input--error {
  border-color: #dc2626;
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.form-hint {
  font-size: 0.75rem;
  color: #6b7280;
}

.form-section {
  padding: 1.5rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-section__title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.option-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem;
  background-color: white;
  border-radius: 0.375rem;
  border: 1px solid #e5e7eb;
}

.option-item__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.option-item__label {
  font-weight: 500;
  color: #374151;
}

.option-item__remove {
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  color: #dc2626;
  background: none;
  border: 1px solid #dc2626;
  border-radius: 0.25rem;
  cursor: pointer;
  transition: all 0.2s;
}

.option-item__remove:hover:not(:disabled) {
  background-color: #dc2626;
  color: white;
}

.option-item__remove:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 1rem;
  height: 1rem;
  cursor: pointer;
}

.hint-item {
  display: flex;
  gap: 0.5rem;
  align-items: center;
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

.btn--icon {
  width: 2rem;
  height: 2rem;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fee2e2;
  color: #dc2626;
  font-size: 1.5rem;
  line-height: 1;
}

.btn--icon:hover {
  background-color: #fecaca;
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
  .question-form {
    padding: 1rem;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>
