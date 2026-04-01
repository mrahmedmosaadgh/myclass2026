<script setup>
import { ref, onMounted } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import axios from 'axios';

const props = defineProps({
  quizId: { type: [Number, String], required: true },
  type: { type: String, default: 'multiple_choice' },
  studentName: { type: String, default: '' }
});

const gameStore = useGameStore();
const question = ref(null);
const selectedIndex = ref(null);
const shortAnswerText = ref('');
const isSubmitting = ref(false);
const isAnswered = ref(false);

const loadQuestion = async () => {
  try {
    const response = await axios.get(`/api/cr/questions/${props.quizId}`);
    question.value = response.data;
  } catch (err) {
    console.error('Failed to load question:', err);
  }
};

const submitAnswer = async (answerValue) => {
  if (isAnswered.value || isSubmitting.value) return;
  if (props.type === 'short_answer' && !shortAnswerText.value.trim()) return;
  
  isSubmitting.value = true;
  
  try {
    const payload = {
      question_id: props.quizId,
      answer: props.type === 'multiple_choice' ? answerValue : shortAnswerText.value,
      nickname: props.studentName
    };

    await axios.post(`/api/cr/sessions/${gameStore.sessionId}/submit-answer`, payload);
    
    if (props.type === 'multiple_choice') {
      selectedIndex.value = question.value.options.findIndex(o => o.id === answerValue);
    }
    
    isAnswered.value = true;
  } catch (err) {
    console.error('Answer submission failed:', err);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(loadQuestion);
</script>

<template>
  <div class="quiz-card card shadow-lg" :class="{ answered: isAnswered }">
    <div v-if="!question" class="loading-q">
      Loading question...
    </div>
    
    <template v-else>
      <div class="question-header">
        <span class="q-badge">LIVE {{ type === 'short_answer' ? 'SHORT ANSWER' : 'QUIZ' }}</span>
        <h2 class="question-text">{{ question.question_text }}</h2>
      </div>

      <!-- Multiple Choice UI -->
      <div v-if="type === 'multiple_choice'" class="options-list">
        <button 
          v-for="(opt, idx) in question.options" 
          :key="idx"
          class="option-btn"
          :class="{ 
            selected: selectedIndex === idx,
            disabled: isAnswered 
          }"
          @click="submitAnswer(opt.id)"
          :disabled="isAnswered || isSubmitting"
        >
          <span class="opt-letter">{{ String.fromCharCode(65 + idx) }}</span>
          <span class="opt-text">{{ opt.option_text }}</span>
          <span v-if="selectedIndex === idx && isAnswered" class="check-icon">✓</span>
        </button>
      </div>

      <!-- Short Answer UI -->
      <div v-else-if="type === 'short_answer'" class="short-answer-form">
        <textarea 
          v-model="shortAnswerText" 
          placeholder="Type your answer here..."
          :disabled="isAnswered || isSubmitting"
          rows="3"
        ></textarea>
        <button 
          class="submit-btn" 
          @click="submitAnswer" 
          :disabled="isAnswered || isSubmitting || !shortAnswerText.trim()"
        >
          {{ isSubmitting ? 'Sending...' : 'Submit Answer' }}
        </button>
      </div>

      <div v-if="isAnswered" class="locked-msg">
        <div class="icon">🔒</div>
        <p>Answer locked! Waiting for results...</p>
      </div>
    </template>
  </div>
</template>

<style scoped>
.quiz-card {
  background: white;
  border-radius: 1.25rem;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  transition: all 0.3s;
}

.quiz-card.answered {
  background: #f8fafc;
}

.question-header {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.q-badge {
  background: #fee2e2;
  color: #ef4444;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 4px;
  width: fit-content;
}

.question-text {
  font-size: 1.25rem;
  font-weight: 800;
  color: #1e293b;
  line-height: 1.4;
  margin: 0;
}

.options-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.option-btn {
  display: flex;
  align-items: center;
  padding: 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 1rem;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.option-btn:hover:not(:disabled) {
  border-color: #6366f1;
  background: #f8faff;
  transform: translateX(4px);
}

.option-btn.selected {
  border-color: #6366f1;
  background: #f8faff;
}

.option-btn.disabled {
  cursor: default;
  opacity: 0.7;
}

.opt-letter {
  width: 32px;
  height: 32px;
  background: #f1f5f9;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  color: #64748b;
  margin-right: 1rem;
}

.selected .opt-letter {
  background: #6366f1;
  color: white;
}

.opt-text {
  font-weight: 600;
  color: #334155;
  font-size: 1rem;
  flex: 1;
}

.check-icon {
  color: #10b981;
  font-weight: 900;
  font-size: 1.2rem;
}

.locked-msg {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #f1f5f9;
  border-radius: 0.75rem;
  color: #475569;
}

.locked-msg .icon { font-size: 1.2rem; }
.locked-msg p { margin: 0; font-size: 0.9rem; font-weight: 600; }

.short-answer-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.short-answer-form textarea {
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 1rem;
  resize: none;
  font-family: inherit;
  font-size: 1rem;
  transition: all 0.2s;
}

.short-answer-form textarea:focus {
  outline: none;
  border-color: #6366f1;
  background: #f8faff;
}

.submit-btn {
  padding: 1rem;
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #4f46e5;
  transform: translateY(-2px);
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading-q {
  padding: 3rem;
  text-align: center;
  color: #94a3b8;
  font-weight: 600;
}
</style>
