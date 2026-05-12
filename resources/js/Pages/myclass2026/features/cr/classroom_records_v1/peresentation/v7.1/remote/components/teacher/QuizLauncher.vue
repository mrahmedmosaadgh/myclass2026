<script setup>
import { ref, onMounted } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import axios from 'axios';

const gameStore = useGameStore();

const quizType = ref('multiple_choice'); // 'multiple_choice' or 'short_answer'
const questionText = ref('');
const options = ref(['', '', '', '']);
const correctAnswer = ref(0);
const timer = ref(60);
const isLaunching = ref(false);
const history = ref([]);

onMounted(() => {
  const savedHistory = localStorage.getItem('teacher_quiz_history');
  if (savedHistory) {
    try {
      history.value = JSON.parse(savedHistory);
    } catch (e) {
      console.error('Failed to parse history', e);
    }
  }
});

const saveHistory = () => {
  localStorage.setItem('teacher_quiz_history', JSON.stringify(history.value));
};

const reuseQuestion = (q) => {
  quizType.value = q.type;
  questionText.value = q.question;
  if (q.type === 'multiple_choice') {
    options.value = [...q.options];
    correctAnswer.value = q.correctAnswer;
  }
};

const deleteQuestion = (index) => {
  history.value.splice(index, 1);
  saveHistory();
};

const launchQuiz = async () => {
  if (!questionText.value) return;
  if (quizType.value === 'multiple_choice' && options.value.some(o => !o)) return;
  if (!gameStore.sessionId) return;
  
  isLaunching.value = true;
  try {
    const payload = {
      question: questionText.value,
      type: quizType.value,
      options: quizType.value === 'multiple_choice' ? options.value : null,
      correctAnswer: quizType.value === 'multiple_choice' ? correctAnswer.value : null,
      duration: timer.value
    };

    await axios.post(`/api/cr/sessions/${gameStore.sessionId}/launch-quiz`, payload);
    
    // Add to history (avoid duplicates of exact same question)
    const exists = history.value.findIndex(h => h.question === payload.question && h.type === payload.type);
    if (exists !== -1) history.value.splice(exists, 1);
    
    history.value.unshift(payload);
    if (history.value.length > 10) history.value.pop(); // Keep last 10
    saveHistory();

    // Clear form after launch
    questionText.value = '';
    options.value = ['', '', '', ''];
    console.log('Quiz launched successfully');
  } catch (err) {
    console.error('Failed to launch quiz:', err);
  } finally {
    isLaunching.value = false;
  }
};
</script>

<template>
  <div class="quiz-launcher">
    <div class="header-row">
      <h3 class="panel-title">Launch Live Quiz</h3>
      <div class="type-toggle">
        <button 
          :class="{ active: quizType === 'multiple_choice' }" 
          @click="quizType = 'multiple_choice'"
        >MCQ</button>
        <button 
          :class="{ active: quizType === 'short_answer' }" 
          @click="quizType = 'short_answer'"
        >Short Answer</button>
      </div>
    </div>
    
    <div class="form-group">
      <label>Question Text</label>
      <textarea 
        v-model="questionText" 
        placeholder="Enter your question here..."
        rows="3"
      ></textarea>
    </div>

    <div v-if="quizType === 'multiple_choice'" class="options-grid">
      <div v-for="(opt, idx) in options" :key="idx" class="option-item">
        <div class="option-header">
          <label>Option {{ String.fromCharCode(65 + idx) }}</label>
          <input 
            type="radio" 
            :value="idx" 
            v-model="correctAnswer" 
            title="Mark as correct"
          >
        </div>
        <input 
          v-model="options[idx]" 
          type="text" 
          placeholder="Enter option..."
        >
      </div>
    </div>

    <div class="footer-controls">
      <div class="timer-setup">
        <label>Timer (sec)</label>
        <input type="number" v-model="timer" min="5" max="300">
      </div>
      
      <button 
        class="launch-btn" 
        :disabled="isLaunching || !questionText"
        @click="launchQuiz"
      >
        {{ isLaunching ? 'Launching...' : '🚀 Launch to Students' }}
      </button>
    </div>

    <!-- Question History -->
    <div v-if="history.length > 0" class="quiz-history">
      <div class="history-header">
        <h4>Recent Questions</h4>
      </div>
      <div class="history-list">
        <div v-for="(q, idx) in history" :key="idx" class="history-item">
          <div class="h-info">
            <span class="h-type" :class="q.type">
              {{ q.type === 'short_answer' ? 'Short' : 'MCQ' }}
            </span>
            <p class="h-text">{{ q.question }}</p>
          </div>
          <div class="h-actions">
            <button class="btn-reuse" @click="reuseQuestion(q)" title="Reuse this question">🔄</button>
            <button class="btn-delete" @click="deleteQuestion(idx)" title="Remove from history">×</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.quiz-launcher {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.panel-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
  color: #1e293b;
}

.type-toggle {
  display: flex;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 8px;
  gap: 4px;
}

.type-toggle button {
  padding: 4px 12px;
  border: none;
  background: transparent;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.type-toggle button.active {
  background: white;
  color: #6366f1;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #64748b;
}

textarea {
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  resize: none;
  font-family: inherit;
  font-size: 0.95rem;
}

textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.option-item {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.option-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.option-header label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
}

.option-item input[type="text"] {
  padding: 0.6rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.4rem;
  font-size: 0.9rem;
}

.footer-controls {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1.5rem;
  margin-top: 0.5rem;
}

.timer-setup {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.timer-setup label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
}

.timer-setup input {
  width: 80px;
  padding: 0.6rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.4rem;
}

.launch-btn {
  flex: 1;
  padding: 0.75rem;
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.launch-btn:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-2px);
}

.launch-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* History Styles */
.quiz-history {
  margin-top: 1.5rem;
  border-top: 1px dashed #e2e8f0;
  padding-top: 1rem;
}

.history-header h4 {
  font-size: 0.85rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  margin-bottom: 0.75rem;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 200px;
  overflow-y: auto;
}

.history-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.6rem;
  background: #f8fafc;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
}

.h-info {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  min-width: 0;
}

.h-type {
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 4px;
  text-transform: uppercase;
  white-space: nowrap;
}

.h-type.multiple_choice { background: #e0e7ff; color: #6366f1; }
.h-type.short_answer { background: #fef2f2; color: #ef4444; }

.h-text {
  font-size: 0.85rem;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  color: #475569;
}

.h-actions {
  display: flex;
  gap: 4px;
}

.h-actions button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background 0.2s;
}

.btn-reuse:hover { background: #e0e7ff; }
.btn-delete:hover { background: #fee2e2; }

@media (max-width: 480px) {
  .options-grid {
    grid-template-columns: 1fr;
  }
}
</style>
