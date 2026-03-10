<template>
  <div class="scoring-buttons" @click.stop>
    <button 
      @click="handleScore(1)"
      class="score-btn yes-btn"
      :class="{ 'active': score === 1 }"
      title="I know this word well"
    >
      <span class="score-icon">✓</span>
      <span class="score-text">Yes</span>
    </button>
    
    <button 
      @click="handleScore(0.5)"
      class="score-btn maybe-btn"
      :class="{ 'active': score === 0.5 }"
      title="I'm still learning this word"
    >
      <span class="score-icon">~</span>
      <span class="score-text">Not Yet</span>
    </button>
    
    <button 
      @click="handleScore(0)"
      class="score-btn no-btn"
      :class="{ 'active': score === 0 }"
      title="I don't know this word"
    >
      <span class="score-icon">✗</span>
      <span class="score-text">No</span>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  score: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['set-score'])

const handleScore = (scoreValue) => {
  emit('set-score', scoreValue)
}
</script>

<style scoped>
.scoring-buttons {
  display: flex;
  gap: 0.5rem;
  /* margin-top: 1rem; */
  justify-content: center;
}

.score-btn {
  scale: 0.8;
  display: flex;
  flex-direction: column;
  align-items: center;
  /* gap: 0.25rem; */
  padding: 0.5rem;
  /* border: 2px solid rgba(255, 255, 255, 0.3); */
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 60px;
  backdrop-filter: blur(5px);
}

.score-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
}

.score-btn.active {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.8);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.yes-btn.active {
  background: rgba(34, 197, 94, 0.3);
  border-color: rgba(34, 197, 94, 0.8);
}

.maybe-btn.active {
  background: rgba(251, 191, 36, 0.3);
  border-color: rgba(251, 191, 36, 0.8);
}

.no-btn.active {
  background: rgba(239, 68, 68, 0.3);
  border-color: rgba(239, 68, 68, 0.8);
}

.score-icon {
  font-size: 1.25rem;
  font-weight: bold;
}

.score-text {
  font-size: 0.75rem;
  font-weight: 600;
}

@media (max-width: 768px) {
  .scoring-buttons {
    gap: 0.25rem;
  }
  
  .score-btn {
    min-width: 50px;
    padding: 0.375rem;
  }
}
</style>