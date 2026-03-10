<template>
  <div class="mode-toggle mb-6">
    <div class="toggle-container">
      <button
        @click="$emit('mode-change', 'practice')"
        class="mode-btn"
        :class="{ 'active': currentMode === 'practice' }"
      >
        <span class="mode-icon">📚</span>
        <span class="mode-text">Practice Mode</span>
        <span class="mode-description">Learn without pressure</span>
      </button>
      
      <button
        @click="$emit('mode-change', 'quiz')"
        class="mode-btn"
        :class="{ 'active': currentMode === 'quiz' }"
      >
        <span class="mode-icon">🎯</span>
        <span class="mode-text">Quiz Mode</span>
        <span class="mode-description">Test your knowledge</span>
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  currentMode: {
    type: String,
    required: true
  }
})

defineEmits(['mode-change'])
</script>

<style scoped>
.mode-toggle {
  display: flex;
  justify-content: center;
}

.toggle-container {
  display: flex;
  background: #f8fafc;
  border-radius: 1rem;
  padding: 0.5rem;
  gap: 0.5rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.mode-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 1rem 2rem;
  border: none;
  border-radius: 0.75rem;
  background: transparent;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 150px;
}

.mode-btn:hover {
  background: rgba(255, 255, 255, 0.7);
  transform: translateY(-2px);
}

.mode-btn.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.mode-icon {
  font-size: 1.5rem;
}

.mode-text {
  font-weight: 600;
  font-size: 0.875rem;
}

.mode-description {
  font-size: 0.75rem;
  opacity: 0.8;
}

@media (max-width: 640px) {
  .toggle-container {
    flex-direction: column;
    width: 100%;
  }
  
  .mode-btn {
    min-width: auto;
    padding: 0.75rem 1rem;
  }
}
</style>