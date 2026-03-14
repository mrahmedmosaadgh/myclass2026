<template>
  <div class="question-card">
    <!-- Question Banner -->
    <div class="question-banner">
      <span class="question-text">{{ question.question }}</span>
    </div>

    <!-- Answer Options 2×2 grid -->
    <div class="options-grid">
      <button
        v-for="option in question.options"
        :key="option"
        class="option-btn"
        :class="getOptionClass(option)"
        :disabled="disabled"
        @click="selectAnswer(option)"
      >
        {{ option }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  question: { type: Object, required: true },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['answer-selected']);

const chosen = ref(null);

const selectAnswer = (option) => {
  if (props.disabled || chosen.value !== null) return;
  chosen.value = option;
  emit('answer-selected', option);
};

const getOptionClass = (option) => {
  if (chosen.value === null) return '';
  if (option === props.question.correctAnswer) return 'option-btn--correct';
  if (option === chosen.value) return 'option-btn--incorrect';
  return 'option-btn--dimmed';
};

// Reset local state when the question prop changes (new question)
import { watch } from 'vue';
watch(() => props.question.id, () => {
  chosen.value = null;
});
</script>

<style scoped>
.question-card {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
  padding: 16px;
}

/* Question Banner */
.question-banner {
  background: #3b82f6;
  border-radius: 10px;
  padding: 22px;
  text-align: center;
}

.question-text {
  font-size: 2.5rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: 2px;
}

/* 2×2 Options Grid */
.options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.option-btn {
  padding: 22px 10px;
  font-size: 1.6rem;
  font-weight: 700;
  color: #fff;
  background: #f59e0b;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: transform 0.1s ease, filter 0.15s ease;
}

.option-btn:not(:disabled):hover {
  transform: translateY(-2px);
  filter: brightness(1.1);
}

.option-btn:disabled { cursor: default; }

.option-btn--correct  { background: #22c55e !important; }
.option-btn--incorrect { background: #ef4444 !important; }
.option-btn--dimmed   { background: #d1d5db !important; color: #9ca3af !important; }
</style>
