<template>
  <div class="puzzle-container relative">
    <div class="controls absolute top-4 right-4 space-x-2">
      <!-- Mode Toggle -->
      <q-btn
        :color="isCreatorMode ? 'primary' : 'secondary'"
        @click="toggleMode"
      >
        {{ isCreatorMode ? 'Create Puzzle' : 'Solve Puzzle' }}
      </q-btn>

      <!-- Save/Submit Buttons -->
      <q-btn
        v-if="isCreatorMode"
        color="positive"
        @click="savePuzzle"
      >
        Save Puzzle
      </q-btn>
      <q-btn
        v-else
        color="primary"
        @click="checkAnswer"
      >
        Submit Answer
      </q-btn>

      <q-btn
        color="grey"
        @click="resetPuzzle"
      >
        Reset
      </q-btn>
    </div>

    <!-- Results Dialog -->
    <q-dialog v-model="showResults">
      <q-card class="p-4">
        <q-card-section>
          <div class="text-h6">Results</div>
          <div class="text-body1 mt-2">
            Correct Dots: {{ correctDots }} / {{ totalDots }}
          </div>
          <div class="text-body2 mt-2">
            Score: {{ Math.round((correctDots / totalDots) * 100) }}%
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Main SVG Content -->
    <svg class="dots-overlay" :width="width" :height="height">
      <!-- Center Line -->
      <line
        :x1="width/2"
        :y1="0"
        :x2="width/2"
        :y2="height"
        stroke="#2563eb"
        stroke-width="2"
        stroke-dasharray="4"
      />

      <!-- Background Grid (only visible in creator mode) -->
      <template v-for="point in gridPoints" :key="'grid-'+point.x+'-'+point.y">
        <circle
          v-if="isCreatorMode || point.side === 'left'"
          :cx="point.x"
          :cy="point.y"
          r="5"
          :fill="getPointColor(point)"
          :opacity="getPointOpacity(point)"
          :class="[
            'grid-point',
            { 'interactive': isCreatorMode || point.side === 'left' }
          ]"
          @click="handlePointClick(point)"
          @dblclick="handleDoubleClick(point)"
        />
      </template>

      <!-- User Lines -->
      <template v-for="(line, index) in userLines" :key="'line-'+index">
        <!-- Original Line -->
        <line
          :x1="line.start.x"
          :y1="line.start.y"
          :x2="line.end.x"
          :y2="line.end.y"
          :stroke="line.isValid ? '#22c55e' : '#ef4444'"
          stroke-width="2"
        />
        <!-- Mirrored Line -->
        <line
          :x1="getMirroredX(line.start.x)"
          :y1="line.start.y"
          :x2="getMirroredX(line.end.x)"
          :y2="line.end.y"
          stroke="#666"
          stroke-width="2"
          stroke-dasharray="4"
        />
      </template>

      <!-- User Dots -->
      <template v-for="(dot, index) in userDots" :key="'dot-'+index">
        <!-- Original Dot -->
        <circle
          :cx="dot.x"
          :cy="dot.y"
          r="6"
          :fill="dot.isValid ? '#22c55e' : '#ef4444'"
          class="user-dot"
        />
        <!-- Mirrored Dot -->
        <circle
          :cx="getMirroredX(dot.x)"
          :cy="dot.y"
          r="6"
          fill="#666"
          class="mirrored-dot"
          opacity="0.6"
        />
      </template>
    </svg>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  imageUrl: {
    type: String,
    required: false
  },
  width: {
    type: Number,
    default: 800
  },
  height: {
    type: Number,
    default: 800
  },
  solution: {
    type: Array,
    required: false
  },
  gridSize: {
    type: Number,
    default: 50 // Space between grid points
  }
});

const userDots = ref([]);
const isValidated = ref(false);

// Add new refs
const isCreatorMode = ref(true);
const savedPuzzle = ref(null);
const showResults = ref(false);
const correctDots = ref(0);
const totalDots = ref(0);

// Add function to get mirrored X position
const getMirroredX = (x) => {
  const centerX = props.width / 2;
  const distanceFromCenter = x - centerX;
  return centerX - distanceFromCenter;
};

// Generate grid points
const gridPoints = computed(() => {
  const points = [];
  // Left side points (interactive)
  for (let x = props.gridSize; x <= props.width/2; x += props.gridSize) {
    for (let y = props.gridSize; y < props.height; y += props.gridSize) {
      points.push({
        x,
        y,
        side: 'left',
        isInteractive: true
      });
    }
  }
  // Right side points (non-interactive, gray)
  for (let x = props.width/2 + props.gridSize; x < props.width; x += props.gridSize) {
    for (let y = props.gridSize; y < props.height; y += props.gridSize) {
      points.push({
        x,
        y,
        side: 'right',
        isInteractive: false
      });
    }
  }
  return points;
});

// Generate lines between consecutive dots
const userLines = computed(() => {
  const lines = [];
  for (let i = 0; i < userDots.value.length - 1; i++) {
    lines.push({
      start: userDots.value[i],
      end: userDots.value[i + 1],
      isValid: isValidated.value && validateLine(userDots.value[i], userDots.value[i + 1])
    });
  }
  return lines;
});

// Handle point click
const handlePointClick = (point) => {
  // Always allow adding new connections to existing dots
  userDots.value.push({
    x: point.x,
    y: point.y,
    isValid: false
  });
  isValidated.value = false;
};

// Handle double click
const handleDoubleClick = (point) => {
  // Find all instances of this dot
  const indices = userDots.value.reduce((acc, dot, index) => {
    if (dot.x === point.x && dot.y === point.y) {
      acc.push(index);
    }
    return acc;
  }, []);

  // Remove all instances of this dot
  // Remove from highest index to lowest to avoid shifting issues
  indices.reverse().forEach(index => {
    userDots.value.splice(index, 1);
  });

  isValidated.value = false;
};

// Validate individual dot
const validateDot = (dot) => {
  return props.solution.some(
    solutionDot =>
      Math.abs(solutionDot.x - dot.x) < 5 &&
      Math.abs(solutionDot.y - dot.y) < 5
  );
};

// Validate line between two dots
const validateLine = (start, end) => {
  return props.solution.some((solutionDot, index) => {
    if (index === props.solution.length - 1) return false;
    const nextDot = props.solution[index + 1];
    return (
      (Math.abs(solutionDot.x - start.x) < 5 &&
       Math.abs(solutionDot.y - start.y) < 5 &&
       Math.abs(nextDot.x - end.x) < 5 &&
       Math.abs(nextDot.y - end.y) < 5) ||
      (Math.abs(solutionDot.x - end.x) < 5 &&
       Math.abs(solutionDot.y - end.y) < 5 &&
       Math.abs(nextDot.x - start.x) < 5 &&
       Math.abs(nextDot.y - start.y) < 5)
    );
  });
};

// Check solution
const checkSolution = () => {
  isValidated.value = true;
  userDots.value = userDots.value.map(dot => ({
    ...dot,
    isValid: validateDot(dot)
  }));
};

// Reset puzzle
const resetPuzzle = () => {
  userDots.value = [];
  isValidated.value = false;
  showResults.value = false;
};

// New methods
const toggleMode = () => {
  isCreatorMode.value = !isCreatorMode.value;
  resetPuzzle();
};

const savePuzzle = () => {
  savedPuzzle.value = {
    dots: [...userDots.value],
    lines: [...userLines.value]
  };
  // You could also save to localStorage or emit to parent
  localStorage.setItem('savedPuzzle', JSON.stringify(savedPuzzle.value));
  isCreatorMode.value = false;
  resetPuzzle();
};

const loadPuzzle = () => {
  const saved = localStorage.getItem('savedPuzzle');
  if (saved) {
    savedPuzzle.value = JSON.parse(saved);
  }
};

const getPointColor = (point) => {
  if (isCreatorMode.value) {
    return point.side === 'right' ? '#d1d5db' : '#94a3b8';
  }
  return '#ef4444';
};

const getPointOpacity = (point) => {
  if (isCreatorMode.value) {
    return point.side === 'right' ? '0.3' : '0.5';
  }
  return '0.7';
};

const checkAnswer = () => {
  if (!savedPuzzle.value) return;

  correctDots.value = userDots.value.filter(dot =>
    savedPuzzle.value.dots.some(savedDot =>
      Math.abs(savedDot.x - dot.x) < 5 &&
      Math.abs(savedDot.y - dot.y) < 5
    )
  ).length;

  totalDots.value = savedPuzzle.value.dots.length;
  showResults.value = true;
};

// Load saved puzzle on mount
onMounted(() => {
  loadPuzzle();
});
</script>

<style scoped>
.puzzle-container {
  position: relative;
  display: inline-block;
}

.dots-overlay {
  position: absolute;
  top: 0;
  left: 0;
}

.grid-point {
  transition: all 0.2s ease;
  user-select: none;
}

.grid-point.interactive {
  cursor: pointer;
}

.grid-point.interactive:hover {
  opacity: 1;
  r: 7;
}

.user-dot {
  transition: all 0.3s ease;
  cursor: pointer;
}

.user-dot:hover {
  r: 8;
}

.mirrored-dot {
  pointer-events: none;
}

.controls {
  background: rgba(255, 255, 255, 0.9);
  padding: 0.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>

