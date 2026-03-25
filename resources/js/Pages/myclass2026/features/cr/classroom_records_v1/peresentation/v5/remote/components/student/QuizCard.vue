<script setup>
import { ref, onMounted } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import axios from 'axios';

const props = defineProps({
  quizId: { type: [Number, String], required: true }
});

const gameStore = useGameStore();
const question = ref(null);
const selectedIndex = ref(null);
const isSubmitting = ref(false);
const isAnswered = ref(false);

const loadQuestion = async () => {
  try {
    const response = await axios.get(`/api/questions/${props.quizId}`);
    question.value = response.data;
  } catch (err) {
    console.error('Failed to load question:', err);
  }
};

const submitAnswer = async (optionId) => {
  if (isAnswered.value || isSubmitting.value) return;
  
  selectedIndex.value = question.value.options.findIndex(o => o.id === optionId);
  isSubmitting.value = true;
  
  try {
    await axios.post(`/api/cr/sessions/${gameStore.sessionId}/submit-answer`, {
      question_id: props.quizId,
      answer: optionId
    });
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
        <span class="q-badge">LIVE QUIZ</span>
        <h2 class="question-text">{{ question.text }}</h2>
      </div>

      <div class="options-list">
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
          <span class="opt-text">{{ opt.text }}</span>
          <span v-if="selectedIndex === idx && isAnswered" class="check-icon">✓</span>
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

.loading-q {
  padding: 3rem;
  text-align: center;
  color: #94a3b8;
  font-weight: 600;
}
</style>
