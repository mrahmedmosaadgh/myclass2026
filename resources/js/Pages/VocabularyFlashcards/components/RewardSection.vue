<template>
  <div class="reward-section">
    <div class="reward-animation">
      <div class="reward-icon">🏆</div>
      <h3 class="reward-title">Excellent Work!</h3>
      <p class="reward-message">
        You scored {{ scorePercentage }}%! Keep up the great learning!
      </p>
      <div class="reward-badges">
        <span v-if="scorePercentage >= 90" class="badge gold">
          🥇 Gold Star
        </span>
        <span v-else class="badge silver">
          🥈 Silver Star
        </span>
        <span class="badge achievement">
          🎓 Vocabulary Master
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  scorePercentage: {
    type: Number,
    required: true
  }
})
</script>

<style scoped>
.reward-section {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
  border-radius: 1rem;
  padding: 2rem;
  text-align: center;
  animation: celebration 0.8s ease-out;
}

.reward-animation {
  animation: bounce 1s ease-in-out;
}

.reward-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  animation: rotate 2s ease-in-out infinite;
}

.reward-title {
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.reward-message {
  font-size: 1.125rem;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 1.5rem;
}

.reward-badges {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.badge {
  padding: 0.5rem 1rem;
  border-radius: 2rem;
  font-weight: 600;
  font-size: 0.875rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
}

.badge.gold {
  background: rgba(255, 215, 0, 0.3);
  color: #92400e;
  border-color: rgba(255, 215, 0, 0.5);
}

.badge.silver {
  background: rgba(192, 192, 192, 0.3);
  color: #374151;
  border-color: rgba(192, 192, 192, 0.5);
}

.badge.achievement {
  background: rgba(139, 69, 19, 0.3);
  color: #92400e;
  border-color: rgba(139, 69, 19, 0.5);
}

@keyframes celebration {
  0% { transform: scale(0.8); opacity: 0; }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-10px); }
  60% { transform: translateY(-5px); }
}

@keyframes rotate {
  0%, 100% { transform: rotate(0deg); }
  50% { transform: rotate(180deg); }
}

@media (max-width: 768px) {
  .reward-title {
    font-size: 1.5rem;
  }
  
  .reward-badges {
    flex-direction: column;
    align-items: center;
  }
}
</style>