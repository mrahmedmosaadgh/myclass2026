<template>
  <BaseQuestionType
    type="multiple_choice"
    :title="title"
    :instructions="instructions"
    :time-limit="timeLimit"
    :min-score="minScore"
    :max-score="maxScore"
    :show-answer-key="showAnswerKey"
  >
    <template #question-body>
      <div class="multiple-choice-question">
        <div v-if="isEditMode" class="edit-mode">
          <div class="options-list">
            <div 
              v-for="(option, index) in questionData.options" 
              :key="index"
              class="option-item"
            >
              <div class="option-header">
                <span class="option-label">Option {{ String.fromCharCode(65 + index) }}</span>
                <div class="option-actions">
                  <button 
                    @click="setCorrectAnswer(index)"
                    class="correct-btn"
                    :class="{ active: questionData.correctAnswer === index }"
                  >
                    ✓ Correct
                  </button>
                  <button 
                    @click="removeOption(index)"
                    class="remove-btn"
                    v-if="questionData.options.length > 2"
                  >
                    ✕
                  </button>
                </div>
              </div>
              <input
                v-model="option.text"
                type="text"
                class="option-input"
                :placeholder="`Option ${String.fromCharCode(65 + index)}`"
              />
            </div>
          </div>
          
          <button @click="addOption" class="add-option-btn">
            + Add Option
          </button>
        </div>
        
        <div v-else class="answer-mode">
          <div class="options-grid">
            <div 
              v-for="(option, index) in questionData.options" 
              :key="index"
              class="option-card"
              :class="{ selected: selectedAnswer === index }"
              @click="selectOption(index)"
            >
              <div class="option-letter">{{ String.fromCharCode(65 + index) }}</div>
              <div class="option-text">{{ option.text }}</div>
            </div>
          </div>
        </div>
      </div>
    </template>
    
    <template #answer-key v-if="showAnswerKey && questionData.correctAnswer !== null">
      <div class="correct-answer">
        <strong>Correct Answer:</strong> {{ String.fromCharCode(65 + questionData.correctAnswer) }} - 
        {{ questionData.options[questionData.correctAnswer]?.text }}
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

const selectedAnswer = ref(null)

const questionData = ref({
  options: [
    { text: '' },
    { text: '' },
    { text: '' },
    { text: '' }
  ],
  correctAnswer: null,
  ...props.initialData
})

// Ensure we have at least 2 options
while (questionData.value.options.length < 2) {
  questionData.value.options.push({ text: '' })
}

// Watch for data changes and emit
watch(questionData, (newData) => {
  if (props.isEditMode) {
    emit('data-change', newData)
  }
}, { deep: true })

const addOption = () => {
  if (questionData.value.options.length < 6) {
    questionData.value.options.push({ text: '' })
  }
}

const removeOption = (index) => {
  if (questionData.value.options.length > 2) {
    questionData.value.options.splice(index, 1)
    // Adjust correct answer if needed
    if (questionData.value.correctAnswer === index) {
      questionData.value.correctAnswer = null
    } else if (questionData.value.correctAnswer > index) {
      questionData.value.correctAnswer--
    }
  }
}

const setCorrectAnswer = (index) => {
  questionData.value.correctAnswer = questionData.value.correctAnswer === index ? null : index
}

const selectOption = (index) => {
  selectedAnswer.value = index
  emit('answer-change', {
    type: 'multiple_choice',
    answer: index,
    answerText: questionData.value.options[index]?.text,
    isCorrect: index === questionData.value.correctAnswer
  })
}
</script>

<style scoped>
.multiple-choice-question {
  width: 100%;
}

.options-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 16px;
}

.option-item {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 12px;
  background: #f9fafb;
}

.option-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.option-label {
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.option-actions {
  display: flex;
  gap: 8px;
}

.correct-btn {
  padding: 4px 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 12px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
}

.correct-btn.active {
  background: #10b981;
  color: white;
  border-color: #10b981;
}

.remove-btn {
  padding: 4px 8px;
  border: 1px solid #ef4444;
  border-radius: 4px;
  font-size: 12px;
  background: white;
  color: #ef4444;
  cursor: pointer;
  transition: all 0.2s;
}

.option-input {
  width: 100%;
  padding: 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 14px;
}

.add-option-btn {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.2s;
}

.add-option-btn:hover {
  background: #2563eb;
}

.options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 12px;
}

.option-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
}

.option-card:hover {
  border-color: #d1d5db;
  transform: translateY(-1px);
}

.option-card.selected {
  border-color: #3b82f6;
  background: #eff6ff;
}

.option-letter {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f3f4f6;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #374151;
  flex-shrink: 0;
}

.option-card.selected .option-letter {
  background: #3b82f6;
  color: white;
}

.option-text {
  font-weight: 500;
  color: #111827;
}

.correct-answer {
  padding: 8px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 4px;
  color: #166534;
}
</style>
