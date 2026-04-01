<template>
  <BaseQuestionType
    type="text"
    :title="title"
    :instructions="instructions"
    :time-limit="timeLimit"
    :min-score="minScore"
    :max-score="maxScore"
    :show-answer-key="showAnswerKey"
  >
    <template #question-body>
      <div class="text-question">
        <div v-if="isEditMode" class="edit-mode">
          <div class="form-group">
            <label>Min Characters</label>
            <input 
              v-model.number="questionData.minLength" 
              type="number" 
              min="1"
              class="form-input"
              placeholder="1"
            />
          </div>
          
          <div class="form-group">
            <label>Max Characters</label>
            <input 
              v-model.number="questionData.maxLength" 
              type="number" 
              min="1"
              class="form-input"
              placeholder="1000"
            />
          </div>
          
          <div class="form-group">
            <label>Placeholder Text</label>
            <input 
              v-model="questionData.placeholder" 
              type="text"
              class="form-input"
              placeholder="Type your answer here..."
            />
          </div>
          
          <div class="form-group">
            <label>Sample Answer (for grading reference)</label>
            <textarea 
              v-model="questionData.sampleAnswer" 
              class="form-textarea"
              placeholder="Enter a sample answer..."
              rows="3"
            ></textarea>
          </div>
        </div>
        
        <div v-else class="answer-mode">
          <textarea
            v-model="answer"
            :placeholder="questionData.placeholder || 'Type your answer here...'"
            :maxlength="questionData.maxLength"
            :minlength="questionData.minLength"
            class="answer-textarea"
            rows="4"
            @input="onAnswerChange"
          ></textarea>
          
          <div class="character-count">
            {{ answer.length }} / {{ questionData.maxLength || '∞' }}
          </div>
        </div>
      </div>
    </template>
    
    <template #answer-key v-if="showAnswerKey && questionData.sampleAnswer">
      <div class="sample-answer">
        <p>{{ questionData.sampleAnswer }}</p>
      </div>
    </template>
  </BaseQuestionType>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import BaseQuestionType from './BaseQuestionType.vue'

const props = defineProps({
  title: String,
  instructions: String,
  timeLimit: Number,
  minScore: Number,
  maxScore: Number,
  isEditMode: Boolean,
  showAnswerKey: Boolean,
  initialData: Object
})

const emit = defineEmits(['answer-change', 'data-change'])

const answer = ref('')

const questionData = ref({
  minLength: 1,
  maxLength: 1000,
  placeholder: 'Type your answer here...',
  sampleAnswer: '',
  ...props.initialData
})

// Watch for data changes and emit
watch(questionData, (newData) => {
  if (props.isEditMode) {
    emit('data-change', newData)
  }
}, { deep: true })

const onAnswerChange = () => {
  if (!props.isEditMode) {
    emit('answer-change', {
      type: 'text',
      answer: answer.value,
      isValid: answer.value.length >= (questionData.value.minLength || 1)
    })
  }
}
</script>

<style scoped>
.text-question {
  width: 100%;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 4px;
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.form-input, .form-textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.form-input:focus, .form-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.answer-textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 16px;
  resize: vertical;
  transition: border-color 0.2s;
}

.answer-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.character-count {
  text-align: right;
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
}

.sample-answer {
  padding: 8px;
  background: #f8fafc;
  border-radius: 4px;
  font-style: italic;
  color: #475569;
}
</style>
