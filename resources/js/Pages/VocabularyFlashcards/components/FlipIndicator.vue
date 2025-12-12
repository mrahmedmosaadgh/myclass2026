<template>
  <div class="flip-indicator">
    <span class="flip-text">{{ text }}</span>
    <div class="flip-arrow">↻</div>
  </div>
</template>

<script setup>
defineProps({
  text: {
    type: String,
    required: true
  }
})
</script>

<style scoped>
.flip-indicator {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.75rem;
}

.flip-text {
  font-weight: 500;
}

.flip-arrow {
  font-size: 1rem;
  animation: rotate 2s ease-in-out infinite;
}

@keyframes rotate {
  0%, 100% { transform: rotate(0deg); }
  50% { transform: rotate(180deg); }
}
</style>