<template>
  <div class="weekly-plan-grid">
    <!-- Header with days of the week -->
    <div class="grid-header">
      <div class="time-column">Period/Day</div>
      <div 
        v-for="day in days" 
        :key="day" 
        class="day-column"
      >
        {{ day }}
      </div>
    </div>
    
    <!-- Grid body with draggable cells -->
    <div class="grid-body">
      <div 
        v-for="(period, periodIndex) in periods" 
        :key="`period-${periodIndex}`" 
        class="grid-row"
      >
        <div class="time-column">{{ getPeriodLabel(periodIndex) }}</div>
        
        <div 
          v-for="(day, dayIndex) in days" 
          :key="`${periodIndex}-${dayIndex}`" 
          class="grid-cell"
          @click="$emit('cell-click', { periodIndex, dayIndex })"
        >
          <draggable
            v-model="planData[periodIndex][dayIndex]"
            group="lessons"
            item-key="id"
            :animation="200"
            @change="handleDragChange($event, periodIndex, dayIndex)"
          >
            <template #item="{ element }">
              <div 
                class="lesson-item"
                :class="[
                  `status-${element.status}`,
                  { 'has-notes': element.notes }
                ]"
                :style="{ backgroundColor: getSubjectColor(element.subject_id) }"
              >
                <div class="lesson-title">{{ element.title }}</div>
                <div class="lesson-meta">
                  <span class="completion" v-if="element.status !== 'planned'">
                    {{ element.completion_percentage }}%
                  </span>
                </div>
              </div>
            </template>
          </draggable>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
  days: {
    type: Array,
    default: () => ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday']
  },
  periods: {
    type: Number,
    default: 8
  },
  initialPlanData: {
    type: Array,
    default: () => []
  },
  subjectColors: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:planData', 'cell-click', 'lesson-moved']);

// Initialize the grid with empty arrays for each cell
const planData = ref(Array.from({ length: props.periods }, () => 
  Array.from({ length: props.days.length }, () => [])
));

// Watch for changes in initialPlanData
watch(() => props.initialPlanData, (newData) => {
  if (newData && newData.length) {
    populateGrid(newData);
  }
}, { immediate: true });

// Populate the grid with initial data
const populateGrid = (data) => {
  // Reset the grid
  planData.value = Array.from({ length: props.periods }, () => 
    Array.from({ length: props.days.length }, () => [])
  );
  
  // Place each lesson in its corresponding cell
  data.forEach(lesson => {
    const periodIndex = lesson.period_number - 1;
    const dayIndex = props.days.findIndex(day => day === lesson.day);
    
    if (periodIndex >= 0 && dayIndex >= 0) {
      planData.value[periodIndex][dayIndex].push(lesson);
    }
  });
};

// Handle drag and drop changes
const handleDragChange = (event, periodIndex, dayIndex) => {
  // Emit the updated plan data
  emit('update:planData', flattenPlanData());
  
  // If a lesson was moved, emit the specific change
  if (event.added || event.moved) {
    const lesson = event.added ? event.added.element : event.moved.element;
    emit('lesson-moved', {
      lesson,
      newPosition: {
        periodIndex,
        dayIndex
      }
    });
  }
};

// Convert the 2D grid to a flat array of lessons with position data
const flattenPlanData = () => {
  const flattened = [];
  
  planData.value.forEach((row, periodIndex) => {
    row.forEach((cell, dayIndex) => {
      cell.forEach(lesson => {
        flattened.push({
          ...lesson,
          period_number: periodIndex + 1,
          day: props.days[dayIndex]
        });
      });
    });
  });
  
  return flattened;
};

// Helper functions
const getPeriodLabel = (index) => `Period ${index + 1}`;

const getSubjectColor = (subjectId) => {
  return props.subjectColors[subjectId] || '#e2e8f0'; // Default color
};
</script>

<style scoped>
.weekly-plan-grid {
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  overflow: hidden;
}

.grid-header {
  display: grid;
  grid-template-columns: 100px repeat(5, 1fr);
  background-color: #f8fafc;
  font-weight: 600;
}

.day-column, .time-column {
  padding: 0.75rem;
  text-align: center;
  border-bottom: 1px solid #e2e8f0;
  border-right: 1px solid #e2e8f0;
}

.grid-body {
  display: flex;
  flex-direction: column;
}

.grid-row {
  display: grid;
  grid-template-columns: 100px repeat(5, 1fr);
  min-height: 100px;
}

.grid-cell {
  border-bottom: 1px solid #e2e8f0;
  border-right: 1px solid #e2e8f0;
  min-height: 100px;
  padding: 0.25rem;
}

.lesson-item {
  padding: 0.5rem;
  border-radius: 0.25rem;
  margin-bottom: 0.25rem;
  cursor: move;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.lesson-title {
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.lesson-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
}

/* Status styling */
.status-planned {
  border-left: 3px solid #94a3b8;
}

.status-in_progress {
  border-left: 3px solid #3b82f6;
}

.status-completed {
  border-left: 3px solid #22c55e;
}

.status-partially_completed {
  border-left: 3px solid #f59e0b;
}

.status-not_completed {
  border-left: 3px solid #ef4444;
}

.has-notes::after {
  content: "📝";
  position: absolute;
  top: 2px;
  right: 2px;
  font-size: 0.75rem;
}
</style>