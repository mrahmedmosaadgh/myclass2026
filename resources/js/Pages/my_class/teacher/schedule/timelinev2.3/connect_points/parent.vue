<template>
  <div class="flex gap-4">
    <!-- Solution Display -->
    <!-- :image-url="'/path/to/puzzle-image.jpg'" -->
    <SolutionGrid
      :width="400"
      :height="400"
      :solution="solutionPoints"
    />

    <!-- User Puzzle -->
    <!-- :image-url="'/path/to/puzzle-image.jpg'" -->
    <UserPuzzle
      :width="400"
      :height="400"
      :solution="solutionPoints"
      :grid-size="150"
    />
  </div>
</template>

<script setup>
import SolutionGrid from './SolutionGrid.vue';
import UserPuzzle from './UserPuzzle.vue';

const solutionPoints = [
  { x: 100, y: 100 },
  { x: 100, y: 140 },
  { x: 100, y: 180 },
  { x: 100, y: 220 }
  // Add more points as needed
];
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
}

.grid-point {
  cursor: pointer;
  transition: opacity 0.2s;
}

.grid-point:hover {
  opacity: 1;
}

.user-dot {
  transition: fill 0.3s;
}
</style>
