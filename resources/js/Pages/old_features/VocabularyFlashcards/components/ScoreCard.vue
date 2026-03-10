<template>
  <div class="score-card" :class="cardClass">
    <div class="score-icon" :class="iconClass">
      {{ icon }}
    </div>
    <div class="score-info">
      <h3 class="score-title">{{ title }}</h3>
      <p class="score-value">{{ value }}</p>
      <p v-if="subtitle" class="score-subtitle">{{ subtitle }}</p>
      <slot name="extra"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  value: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'default'
  }
})

const cardClass = computed(() => {
  return `score-card-${props.type}`
})

const iconClass = computed(() => {
  return `score-icon-${props.type}`
})
</script>

<style scoped>
.score-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.score-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.score-icon {
  font-size: 2rem;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.score-icon-total {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.score-icon-progress {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.score-icon-breakdown {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.score-info {
  flex: 1;
}

.score-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 0.25rem;
}

.score-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.score-subtitle {
  font-size: 1.125rem;
  font-weight: 600;
  color: #059669;
}

@media (max-width: 768px) {
  .score-card {
    padding: 1rem;
  }
  
  .score-icon {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
  }
  
  .score-value {
    font-size: 1.25rem;
  }
}
</style>