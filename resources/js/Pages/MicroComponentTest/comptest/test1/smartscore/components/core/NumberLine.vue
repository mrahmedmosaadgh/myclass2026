<template>
  <div class="number-line-container">
    <div class="number-line-axis" :style="{ height: axisHeight + 'px' }">
      <!-- Number line -->
      <div 
        class="number-line"
        :style="{
          width: lineWidth + 'px',
          marginLeft: margin + 'px'
        }"
      >
        <!-- Ticks and labels -->
        <div 
          v-for="tick in ticks"
          :key="tick.value"
          class="tick-container"
          :style="{
            left: tick.position + '%',
            width: tickWidth + 'px'
          }"
        >
          <div 
            class="tick"
            :style="{
              height: tickHeight + 'px',
              backgroundColor: tickColor
            }"
          ></div>
          <div 
            v-if="showLabels"
            class="tick-label"
            :style="{
              marginTop: labelMarginTop + 'px'
            }"
          >
            {{ tick.label }}
          </div>
        </div>
      </div>
    </div>
    
    <!-- Axis label -->
    <div 
      v-if="axisLabel"
      class="axis-label"
      :style="{
        marginTop: axisLabelMarginTop + 'px'
      }"
    >
      {{ axisLabel }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: 10
  },
  step: {
    type: Number,
    default: 1
  },
  showTicks: {
    type: Boolean,
    default: true
  },
  showLabels: {
    type: Boolean,
    default: true
  },
  axisLabel: {
    type: String,
    default: ''
  },
  tickColor: {
    type: String,
    default: '#666'
  },
  tickHeight: {
    type: Number,
    default: 12
  },
  tickWidth: {
    type: Number,
    default: 2
  },
  axisHeight: {
    type: Number,
    default: 24
  },
  labelMarginTop: {
    type: Number,
    default: 8
  },
  axisLabelMarginTop: {
    type: Number,
    default: 16
  }
});

// Calculate ticks
const ticks = computed(() => {
  const result = [];
  const range = props.max - props.min;
  
  for (let i = props.min; i <= props.max; i += props.step) {
    const position = ((i - props.min) / range) * 100;
    result.push({
      value: i,
      position: position,
      label: i.toString()
    });
  }
  
  return result;
});

// Calculate line width and margin
const lineWidth = computed(() => {
  // Use 90% of container width for the line
  return 90;
});

const margin = computed(() => {
  // Center the line
  return 5;
});
</script>

<style scoped>
.number-line-container {
  width: 100%;
  position: relative;
}

.number-line-axis {
  position: relative;
  overflow: visible;
}

.number-line {
  position: relative;
  border-bottom: 1px solid #666;
}

.tick-container {
  position: absolute;
  top: 0;
  transform: translateX(-50%);
}

.tick {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
}

.tick-label {
  position: absolute;
  bottom: -24px;
  left: 50%;
  transform: translateX(-50%);
  font-size: 12px;
  color: #333;
  white-space: nowrap;
}

.axis-label {
  text-align: center;
  font-size: 14px;
  color: #333;
  font-weight: 500;
}
</style>