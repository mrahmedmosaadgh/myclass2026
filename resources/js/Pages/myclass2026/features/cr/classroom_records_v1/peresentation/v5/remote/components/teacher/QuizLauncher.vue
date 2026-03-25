<script setup>
import { ref } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import axios from 'axios';

const gameStore = useGameStore();

const questionText = ref('');
const options = ref(['', '', '', '']);
const correctAnswer = ref(0);
const timer = ref(60);
const isLaunching = ref(false);

const launchQuiz = async () => {
  if (!questionText.value || options.value.some(o => !o)) return;
  if (!gameStore.sessionId) return;
  
  isLaunching.value = true;
  try {
    await axios.post(`/api/cr/sessions/${gameStore.sessionId}/launch-quiz`, {
      question: questionText.value,
      options: options.value,
      correctAnswer: correctAnswer.value,
      duration: timer.value
    });
    
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
    <h3 class="panel-title">Launch Live Quiz</h3>
    
    <div class="form-group">
      <label>Question Text</label>
      <textarea 
        v-model="questionText" 
        placeholder="Enter your question here..."
        rows="3"
      ></textarea>
    </div>

    <div class="options-grid">
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
  </div>
</template>

<style scoped>
.quiz-launcher {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.panel-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
  color: #1e293b;
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

@media (max-width: 480px) {
  .options-grid {
    grid-template-columns: 1fr;
  }
}
</style>
