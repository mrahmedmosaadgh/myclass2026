<template>
  <aside class="scoreboard">
    <!-- Correct -->
    <div class="score-row">
      <div class="badge badge--correct">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12" />
        </svg>
        <span>{{ correctCount }}</span>
      </div>
      <p class="score-label">{{ correctCount }} / {{ totalQuestions }}</p>
    </div>

    <!-- Incorrect -->
    <div class="score-row">
      <div class="badge badge--incorrect">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
        <span>{{ incorrectCount }}</span>
      </div>
      <p class="score-label">{{ incorrectCount }} / {{ totalQuestions }}</p>
    </div>

    <!-- Progress % -->
    <div class="score-row">
      <div class="badge badge--progress">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10" />
          <polyline points="12 6 12 12 16 14" />
        </svg>
        <span>{{ percentage }}%</span>
      </div>
      <p class="score-label">{{ answeredCount }} / {{ totalQuestions }}</p>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  correctCount:    { type: Number, default: 0 },
  incorrectCount:  { type: Number, default: 0 },
  answeredCount:   { type: Number, default: 0 },
  totalQuestions:  { type: Number, required: true },
});

const percentage = computed(() => {
  if (props.answeredCount === 0) return 0;
  return Math.round((props.correctCount / props.answeredCount) * 100);
});
</script>

<style scoped>
.scoreboard {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 20px 16px;
  border-right: 2px solid #e5e7eb;
  min-width: 120px;
}

.score-row {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.badge {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 18px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 1.1rem;
  color: #fff;
  min-width: 80px;
  justify-content: center;
}

.badge--correct  { background: #22c55e; }
.badge--incorrect { background: #ef4444; }
.badge--progress { background: #3b82f6; }

.score-label {
  margin: 0;
  font-size: 0.85rem;
  color: #3b82f6;
  font-weight: 600;
}
</style>
