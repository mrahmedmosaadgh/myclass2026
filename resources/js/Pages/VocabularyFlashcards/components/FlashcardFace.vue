<template>
  <div class="flashcard-face" :class="faceClass">
    <div class="card-header">
      <div class="card-number">{{ index + 1 }}</div>
      <div class="language-badge" :class="badgeClass">
        {{ languageCode }}
      </div>
    </div>
    
    <div class="card-content">
      <div class="text-content">
        <slot name="content"></slot>
      </div>
      
      <div class="card-footer">
        <slot name="actions"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    required: true, // 'front' or 'back'
    validator: (value) => ['front', 'back'].includes(value)
  },
  vocabularyItem: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    required: true
  },
  isPlaying: {
    type: Boolean,
    required: true
  },
  mode: {
    type: String,
    required: true
  },
  score: {
    type: Number,
    default: null
  }
})

const faceClass = computed(() => {
  return `flashcard-${props.type}`
})

const badgeClass = computed(() => {
  return props.type === 'back' ? 'arabic-badge' : ''
})

const languageCode = computed(() => {
  return props.type === 'front' ? 'EN' : 'AR'
})
</script>

<style scoped>
.flashcard-face {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  width: 100%;
  height: 100%;
  border-radius: 1rem;
  padding: 0;
  display: flex;
  flex-direction: column;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  -moz-backface-visibility: hidden;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.flashcard-container:hover .flashcard-face {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1);
}

.flashcard-front {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.flashcard-back {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
  transform: rotateY(180deg);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.card-number {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.875rem;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.language-badge {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: bold;
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(5px);
}

.arabic-badge {
  background: rgba(255, 255, 255, 0.25);
}

.card-content {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  padding: 1rem 1.5rem 1.5rem;
}

.text-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.card-footer {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: center;
}

@media (max-width: 768px) {
  .card-content {
    padding: 0.75rem 1rem 1rem;
  }
  
  .card-header {
    padding: 0.75rem 1rem 0.25rem;
  }
}
</style>