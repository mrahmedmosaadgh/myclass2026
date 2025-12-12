<template>
  <div class="puzzle-container relative">
    <!-- <img :src="imageUrl" class="puzzle-image" alt="Puzzle" /> -->
    <svg class="dots-overlay" :width="width" :height="height">
      <!-- Solution Lines -->
      <template v-for="(line, index) in solutionLines" :key="'line-'+index">
        <line
          :x1="line.start.x"
          :y1="line.start.y"
          :x2="line.end.x"
          :y2="line.end.y"
          stroke="#2563eb"
          stroke-width="2"
        />
      </template>
      <!-- Solution Dots -->
      <template v-for="(dot, index) in solutionDots" :key="'dot-'+index">
        <circle
          :cx="dot.x"
          :cy="dot.y"
          r="4"
          fill="#2563eb"
        />
      </template>
    </svg>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  imageUrl: {
    type: String,
    required: false
  },
  width: {
    type: Number,
    default: 400
  },
  height: {
    type: Number,
    default: 400
  },
  solution: {
    type: Array,
    required: true,
    // Format: [{x: number, y: number}]
  }
});

const solutionDots = computed(() => props.solution);

const solutionLines = computed(() => {
  const lines = [];
  for (let i = 0; i < props.solution.length - 1; i++) {
    lines.push({
      start: props.solution[i],
      end: props.solution[i + 1]
    });
  }
  return lines;
});
</script>

<style scoped>
.puzzle-container {
  position: relative;
  display: inline-block;
}

.puzzle-image {
  max-width: 100%;
  height: auto;
}

.dots-overlay {
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: none;
}
</style>
