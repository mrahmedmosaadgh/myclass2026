<template>
  <div class="line-plot-container">
    <!-- Title -->
    <div v-if="title" class="line-plot-title">
      {{ title }}
    </div>
    
    <!-- Description -->
    <div v-if="description" class="line-plot-description">
      {{ description }}
    </div>
    
    <!-- Line plot visualization -->
    <div class="line-plot-visualization">
      <!-- X marks container -->
      <div class="x-marks-container" :style="{ height: plotHeight + 'px' }">
        <XMark
          v-for="(count, position) in xMarks"
          :key="position"
          :position="parseInt(position)"
          :count="count"
          :color="visual.xMarkColor"
          :size="visual.xMarkSize"
          :animation="visual.animation"
        />
      </div>
      
      <!-- Number line -->
      <NumberLine
        :min="data.min"
        :max="data.max"
        :step="data.step"
        :show-ticks="visual.showTicks"
        :show-labels="visual.showLabels"
        :axis-label="axis.label"
        :tick-color="visual.tickColor"
        :tick-height="visual.tickHeight"
        :tick-width="visual.tickWidth"
        :axis-height="visual.axisHeight"
        :label-margin-top="visual.labelMarginTop"
        :axis-label-margin-top="visual.axisLabelMarginTop"
      />
    </div>
    
    <!-- Grid overlay (optional) -->
    <div 
      v-if="visual.gridVisible"
      class="grid-overlay"
      :style="{
        height: plotHeight + 'px',
        width: '100%'
      }"
    ></div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import XMark from './XMark.vue';
import NumberLine from './NumberLine.vue';

const props = defineProps({
  data: {
    type: Object,
    required: true,
    default: () => ({
      counts: {},
      min: 0,
      max: 10,
      step: 1
    })
  },
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  axis: {
    type: Object,
    default: () => ({ label: '' })
  },
  visual: {
    type: Object,
    default: () => ({
      xMarkColor: '#FF8C00', // Orange like IXL
      xMarkSize: 'medium',
      animation: true,
      showTicks: true,
      showLabels: true,
      gridVisible: true,
      tickColor: '#666',
      tickHeight: 12,
      tickWidth: 2,
      axisHeight: 24,
      labelMarginTop: 8,
      axisLabelMarginTop: 16
    })
  }
});

// Calculate X marks positions and counts
const xMarks = computed(() => {
  const result = {};
  
  // If counts are provided directly
  if (props.data.counts) {
    Object.entries(props.data.counts).forEach(([position, count]) => {
      result[position] = count;
    });
  } else if (props.data.raw) {
    // Convert raw data to counts
    const counts = {};
    props.data.raw.forEach(value => {
      counts[value] = (counts[value] || 0) + 1;
    });
    Object.entries(counts).forEach(([position, count]) => {
      result[position] = count;
    });
  }
  
  return result;
});

// Calculate plot height based on maximum count
const plotHeight = computed(() => {
  const maxCount = Math.max(...Object.values(xMarks.value), 0);
  return Math.max(100, maxCount * 20);
});
</script>

<style scoped>
.line-plot-container {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
}

.line-plot-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
  text-align: center;
}

.line-plot-description {
  font-size: 14px;
  color: #666;
  margin-bottom: 16px;
  text-align: center;
}

.line-plot-visualization {
  position: relative;
  width: 100%;
}

.x-marks-container {
  position: relative;
  width: 100%;
}

.grid-overlay {
  position: absolute;
  top: 0;
  left: 0;
  background-image: 
    linear-gradient(rgba(0,0,0,0.1) 1px, transparent 1px),
    linear-gradient(90deg, rgba(0,0,0,0.1) 1px, transparent 1px);
  background-size: 24px 24px;
  pointer-events: none;
}
</style>