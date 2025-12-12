<template>
  <q-card class="stats-card" :class="`stats-card--${variant}`">
    <q-card-section class="stats-card__content">
      <div class="stats-card__icon-wrapper">
        <div class="stats-card__icon" :style="{ background: iconGradient }">
          <q-icon :name="icon" size="32px" color="white" />
        </div>
      </div>
      
      <div class="stats-card__info">
        <div class="stats-card__value" ref="valueRef">
          {{ displayValue }}
        </div>
        <div class="stats-card__label">{{ label }}</div>
        
        <div v-if="trend !== undefined" class="stats-card__trend" :class="trendClass">
          <q-icon :name="trendIcon" size="16px" />
          <span>{{ Math.abs(trend) }}%</span>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  trend: {
    type: Number,
    default: undefined
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'success', 'warning', 'info'].includes(value)
  },
  animate: {
    type: Boolean,
    default: true
  }
});

const displayValue = ref(props.animate ? 0 : props.value);
const valueRef = ref(null);

const iconGradient = computed(() => {
  const gradients = {
    primary: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    success: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    warning: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    info: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
  };
  return gradients[props.variant];
});

const trendClass = computed(() => {
  if (props.trend === undefined) return '';
  return props.trend >= 0 ? 'stats-card__trend--positive' : 'stats-card__trend--negative';
});

const trendIcon = computed(() => {
  if (props.trend === undefined) return '';
  return props.trend >= 0 ? 'trending_up' : 'trending_down';
});

// Animated counter
const animateValue = (start, end, duration) => {
  if (!props.animate || typeof end !== 'number') {
    displayValue.value = end;
    return;
  }
  
  const range = end - start;
  const increment = range / (duration / 16); // 60fps
  let current = start;
  
  const timer = setInterval(() => {
    current += increment;
    if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
      current = end;
      clearInterval(timer);
    }
    displayValue.value = Math.round(current);
  }, 16);
};

watch(() => props.value, (newValue, oldValue) => {
  if (typeof newValue === 'number' && typeof oldValue === 'number') {
    animateValue(oldValue, newValue, 1000);
  } else {
    displayValue.value = newValue;
  }
});

onMounted(() => {
  if (props.animate && typeof props.value === 'number') {
    animateValue(0, props.value, 1000);
  } else {
    displayValue.value = props.value;
  }
});
</script>

<style scoped lang="scss">
.stats-card {
  border-radius: 24px;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid rgba(0,0,0,0.05);
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
  }
  
  &__content {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px;
  }
  
  &__icon-wrapper {
    flex-shrink: 0;
  }
  
  &__icon {
    width: 64px;
    height: 64px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  }
  
  &__info {
    flex: 1;
    min-width: 0;
  }
  
  &__value {
    font-size: 2rem;
    font-weight: 800;
    color: #1a202c;
    line-height: 1.2;
    margin-bottom: 4px;
  }
  
  &__label {
    font-size: 0.9rem;
    color: #718096;
    font-weight: 600;
    margin-bottom: 8px;
  }
  
  &__trend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 12px;
    
    &--positive {
      color: #22c55e;
      background: rgba(34, 197, 94, 0.1);
    }
    
    &--negative {
      color: #ef4444;
      background: rgba(239, 68, 68, 0.1);
    }
  }
  
  // Variant styles
  &--primary {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
  }
  
  &--success {
    background: linear-gradient(135deg, rgba(17, 153, 142, 0.05) 0%, rgba(56, 239, 125, 0.05) 100%);
  }
  
  &--warning {
    background: linear-gradient(135deg, rgba(240, 147, 251, 0.05) 0%, rgba(245, 87, 108, 0.05) 100%);
  }
  
  &--info {
    background: linear-gradient(135deg, rgba(79, 172, 254, 0.05) 0%, rgba(0, 242, 254, 0.05) 100%);
  }
}

// Responsive
@media (max-width: 600px) {
  .stats-card {
    &__content {
      padding: 16px;
      gap: 16px;
    }
    
    &__icon {
      width: 56px;
      height: 56px;
      
      .q-icon {
        font-size: 28px;
      }
    }
    
    &__value {
      font-size: 1.5rem;
    }
    
    &__label {
      font-size: 0.8125rem;
    }
  }
}
</style>
