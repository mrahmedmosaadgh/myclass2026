<script setup>
import { ref, reactive } from 'vue'
import { usePresentationStore } from '../../stores/presentationStore.js'

const presentation = usePresentationStore()

const emit = defineEmits(['close'])

// Quiz data
const quizData = reactive({
  title: '',
  questions: [
    {
      id: 'q1',
      question: 'What is 2 + 2?',
      options: ['3', '4', '5', '6'],
      correctAnswer: 1,
      explanation: '2 + 2 = 4'
    }
  ]
})

// Add new question
function addQuestion() {
  const newQuestion = {
    id: 'q' + Date.now(),
    question: 'New Question',
    options: ['Option A', 'Option B', 'Option C', 'Option D'],
    correctAnswer: 0,
    explanation: ''
  }
  quizData.questions.push(newQuestion)
}

// Remove question
function removeQuestion(index) {
  if (quizData.questions.length > 1) {
    quizData.questions.splice(index, 1)
  }
}

// Set correct answer for a question
function setCorrectAnswer(questionIndex, optionIndex) {
  quizData.questions[questionIndex].correctAnswer = optionIndex
}

// Create quiz
function createQuiz() {
  if (!quizData.title.trim()) {
    quizData.title = 'Untitled Quiz'
  }
  
  // Validate that each question has a question text
  quizData.questions.forEach((question, index) => {
    if (!question.question.trim()) {
      question.question = `Question ${index + 1}`
    }
  })
  
  presentation.addQuiz(quizData)
  emit('close')
}

// Cancel
function cancel() {
  emit('close')
}
</script>

<template>
  <div class="quiz-creation-dialog-backdrop" @click.self="cancel">
    <div class="quiz-creation-dialog">
      <div class="dialog-header">
        <h3>Create Quiz</h3>
        <button @click="cancel" class="close-btn">×</button>
      </div>
      
      <div class="dialog-body">
        <!-- Quiz Settings -->
        <div class="quiz-settings">
          <label class="input-label">
            Quiz Title
            <input 
              v-model="quizData.title" 
              type="text" 
              placeholder="Enter quiz title" 
              class="text-input"
            />
          </label>
        </div>
        
        <!-- Questions Section -->
        <div class="questions-section">
          <div class="section-header">
            <h4>Questions</h4>
            <button @click="addQuestion" class="add-question-btn">
              <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              Add Question
            </button>
          </div>
          
          <div class="questions-list">
            <div
              v-for="(question, index) in quizData.questions"
              :key="question.id"
              class="question-editor"
            >
              <div class="question-header">
                <span class="question-number">Question {{ index + 1 }}</span>
                <button 
                  @click="removeQuestion(index)"
                  :disabled="quizData.questions.length === 1"
                  class="remove-btn"
                  title="Remove question"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  </svg>
                </button>
              </div>
              
              <!-- Question Text -->
              <div class="question-input-group">
                <label class="input-label">Question</label>
                <input
                  v-model="question.question"
                  type="text"
                  placeholder="Enter question"
                  class="text-input question-input"
                />
              </div>
              
              <!-- Options -->
              <div class="options-section">
                <label class="input-label">Options</label>
                <div class="options-grid">
                  <div
                    v-for="(option, optIndex) in question.options"
                    :key="optIndex"
                    class="option-editor"
                  >
                    <input
                      v-model="question.options[optIndex]"
                      type="text"
                      :placeholder="`Option ${String.fromCharCode(65 + optIndex)}`"
                      class="text-input option-input"
                    />
                    <button
                      @click="setCorrectAnswer(index, optIndex)"
                      :class="{ 'correct': question.correctAnswer === optIndex }"
                      class="correct-btn"
                      :title="`Mark as correct answer`"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Explanation -->
              <div class="explanation-section">
                <label class="input-label">Explanation (optional)</label>
                <input
                  v-model="question.explanation"
                  type="text"
                  placeholder="Explanation for the correct answer"
                  class="text-input explanation-input"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="dialog-footer">
        <button @click="createQuiz" class="create-btn">Create Quiz</button>
        <button @click="cancel" class="cancel-btn">Cancel</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.quiz-creation-dialog-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(4px);
  padding: 16px;
}

.quiz-creation-dialog {
  background: white;
  border-radius: 12px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.dialog-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #111827;
}

.close-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: #6b7280;
  font-size: 20px;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #111827;
}

.dialog-body {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

.quiz-settings {
  margin-bottom: 24px;
}

.input-label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.text-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.text-input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.questions-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.section-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #111827;
}

.add-question-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.add-question-btn:hover {
  background: #4f46e5;
}

.btn-icon {
  width: 16px;
  height: 16px;
}

.questions-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.question-editor {
  padding: 16px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.question-number {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.remove-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.remove-btn:hover:not(:disabled) {
  background: #fef2f2;
  color: #dc2626;
}

.remove-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.question-input-group {
  margin-bottom: 16px;
}

.options-section {
  margin-bottom: 16px;
}

.options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.option-editor {
  display: flex;
  gap: 8px;
  align-items: center;
}

.option-input {
  flex: 1;
}

.correct-btn {
  width: 32px;
  height: 32px;
  border: 1px solid #d1d5db;
  background: white;
  color: #6b7280;
  cursor: pointer;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.correct-btn:hover {
  border-color: #6366f1;
  color: #6366f1;
}

.correct-btn.correct {
  background: #10b981;
  border-color: #10b981;
  color: white;
}

.explanation-section {
  margin-bottom: 8px;
}

.dialog-footer {
  display: flex;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid #e5e7eb;
  justify-content: flex-end;
}

.create-btn {
  padding: 10px 20px;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.create-btn:hover {
  background: #059669;
}

.cancel-btn {
  padding: 10px 20px;
  background: white;
  color: #6b7280;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.cancel-btn:hover {
  background: #f9fafb;
  border-color: #9ca3af;
}

/* Mobile-first responsive design */
@media (max-width: 767px) {
  .quiz-creation-dialog-backdrop {
    padding: 8px;
  }
  
  .quiz-creation-dialog {
    max-height: 100vh;
  }
  
  .dialog-header,
  .dialog-body,
  .dialog-footer {
    padding: 16px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .add-question-btn {
    width: 100%;
    justify-content: center;
  }
  
  .options-grid {
    grid-template-columns: 1fr;
  }
  
  .dialog-footer {
    flex-direction: column;
  }
  
  .create-btn,
  .cancel-btn {
    width: 100%;
  }
}

/* Touch optimizations */
.text-input,
.add-question-btn,
.remove-btn,
.correct-btn,
.create-btn,
.cancel-btn {
  min-height: 44px; /* iOS touch target minimum */
  touch-action: manipulation;
}

.text-input {
  font-size: 16px; /* Prevent zoom on iOS */
}
</style>
