<template>
  <q-card class="stats-card" :class="`stats-card--${variant}`" role="figure" :aria-label="`${label}: ${displayValue}`">
    <q-card-section class="stats-card__content">
      <!-- The cool circle icon on the left -->
      <div class="stats-card__icon-wrapper">
        <div class="stats-card__icon" :style="{ background: iconGradient }">
          <q-icon :name="icon" size="32px" color="white" aria-hidden="true" />
        </div>
      </div>

      <!-- The number + label + trend (like "up 12%") -->
      <div class="stats-card__info">
        <div class="stats-card__value" ref="valueRef">
          {{ displayValue }}
        </div>
        <div class="stats-card__label">{{ label }}</div>

        <!-- Shows if the number went up or down -->
        <div v-if="trend !== undefined" class="stats-card__trend" :class="trendClass" role="text">
          <q-icon :name="trendIcon" size="16px" aria-hidden="true" />
          <span>{{ Math.abs(trend) }}%</span>
          <span class="q-sr-only">{{ trend >= 0 ? 'increased' : 'decreased' }}</span>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'

// These are the "inputs" this card needs from the parent
const props = defineProps({
  icon: { type: String, required: true },        // e.g. "emoji_events"
  label: { type: String, required: true },       // e.g. "Total Quizzes"
  value: { type: [Number, String], required: true }, // the big number
  trend: { type: Number, default: undefined },   // optional % change
  variant: {
    type: String,
    default: 'primary',
    validator: (v) => ['primary', 'success', 'warning', 'info'].includes(v)
  },
  animate: { type: Boolean, default: true }      // should the number count up?
})

// This is the number we actually show (starts at 0 if animating)
const displayValue = ref(props.animate && typeof props.value === 'number' ? 0 : props.value)
const valueRef = ref(null)

// Different colors for different card types
const iconGradient = computed(() => {
  const colors = {
    primary: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    success: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    warning: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    info:    'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
  }
  return colors[props.variant]
})

// Is the trend going up or down?
const trendClass = computed(() => 
  props.trend >= 0 ? 'stats-card__trend--positive' : 'stats-card__trend--negative'
)

const trendIcon = computed(() => 
  props.trend >= 0 ? 'trending_up' : 'trending_down'
)

// SMOOTH & BUTTERY counter animation (like in cool apps!)
const animateCounter = (from, to, duration = 1000) => {
  if (!props.animate || typeof to !== 'number') {
    displayValue.value = to
    return
  }

  const startTime = performance.now()

  const tick = (currentTime) => {
    const elapsed = currentTime - startTime
    const progress = Math.min(elapsed / duration, 1) // 0 → 1

    // Ease-out effect (slows down at the end - feels nice!)
    const easeOut = 1 - Math.pow(1 - progress, 3)
    const current = Math.round(from + (to - from) * easeOut)

    displayValue.value = current

    if (progress < 1) {
      requestAnimationFrame(tick)
    } else {
      displayValue.value = to // make sure we end exactly on target
    }
  }

  requestAnimationFrame(tick)
}

// When the value changes → animate from old to new
watch(() => props.value, (newVal, oldVal) => {
  const old = typeof oldVal === 'number' ? oldVal : 0
  const numNew = typeof newVal === 'number' ? newVal : newVal
  animateCounter(old, numNew)
}, { immediate: false })

// First time the card appears → animate from 0
onMounted(() => {
  if (props.animate && typeof props.value === 'number') {
    nextTick(() => animateCounter(0, props.value))
  }
})
</script>

<style scoped lang="scss">
.stats-card {
  border-radius: 24px;
  overflow: hidden;
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

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

  &__icon-wrapper { flex-shrink: 0; }

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
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 12px;

    &--positive {
      color: #22c55e;
      background: rgba(34, 197, 94, 0.15);
    }

    &--negative {
      color: #ef4444;
      background: rgba(239, 68, 68, 0.15);
    }
  }

  // Background tint per variant
  &--primary { background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%); }
  &--success { background: linear-gradient(135deg, rgba(17, 153, 142, 0.08) 0%, rgba(56, 239, 125, 0.08) 100%); }
  &--warning { background: linear-gradient(135deg, rgba(240, 147, 251, 0.08) 0%, rgba(245, 87, 108, 0.08) 100%); }
  &--info    { background: linear-gradient(135deg, rgba(79, 172, 254, 0.08) 0%, rgba(0, 242, 254, 0.08) 100%); }
}

// Make it look good on phones
@media (max-width: 600px) {
  .stats-card {
    &__content { padding: 16px; gap: 16px; }
    &__icon { 
      width: 56px; 
      height: 56px; 
      .q-icon { font-size: 28px !important; }
    }
    &__value { font-size: 1.75rem; }
  }
}
</style>
