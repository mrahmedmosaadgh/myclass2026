<template>
  <svg 
    :width="size" 
    :height="size" 
    viewBox="0 0 24 24"
    class="x-mark"
    :class="{
      'x-mark-animated': animation,
      'x-mark-large': size === 'large',
      'x-mark-medium': size === 'medium',
      'x-mark-small': size === 'small'
    }"
  >
    <g :transform="`translate(${positionX}, ${positionY})`">
      <!-- X mark using two lines -->
      <line 
        x1="4" y1="4" 
        x2="20" y2="20" 
        :stroke="color" 
        stroke-width="3" 
        stroke-linecap="round"
      />
      <line 
        x1="4" y1="20" 
        x2="20" y2="4" 
        :stroke="color" 
        stroke-width="3" 
        stroke-linecap="round"
      />
    </g>
  </svg>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  position: {
    type: Number,
    required: true
  },
  count: {
    type: Number,
    default: 1
  },
  color: {
    type: String,
    default: '#FF8C00' // Orange like IXL
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  },
  animation: {
    type: Boolean,
    default: true
  }
});

// Calculate position based on count and spacing
const positionX = computed(() => props.position * 24 + 12);
const positionY = computed(() => 12 - (props.count - 1) * 4);
</script>

<style scoped>
.x-mark {
  display: inline-block;
  vertical-align: middle;
}

.x-mark-animated {
  transition: transform 0.3s ease-in-out;
}

.x-mark-small {
  width: 16px;
  height: 16px;
}

.x-mark-medium {
  width: 24px;
  height: 24px;
}

.x-mark-large {
  width: 32px;
  height: 32px;
}
</style>