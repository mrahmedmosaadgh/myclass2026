<template>
  <div class="smart-score-display">
    <!-- Score bar -->
    <div class="score-bar-container">
      <div 
        class="score-bar"
        :style="{
          backgroundColor: scoreColor,
          width: `${scorePercentage}%`
        }"
      ></div>
      <div class="score-bar-background"></div>
    </div>
    
    <!-- Score info -->
    <div class="score-info">
      <div class="score-value">
        {{ formattedScore }}
      </div>
      
      <!-- Mastery badge -->
      <div 
        v-if="visual.showMasteryBadge"
        class="mastery-badge"
        :class="badgeClass"
      >
        {{ masteryLevel }}
      </div>
      
      <!-- Improvement indicator -->
      <div 
        v-if="visual.showImprovement && improvement"
        class="improvement-indicator"
        :class="{
          'improvement-positive': improvement.startsWith('+'),
          'improvement-negative': !improvement.startsWith('+') && improvement !== ''
        }"
      >
        {{ improvement }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { getScoreColor, getMasteryLevel, formatScore, calculateImprovement, getBadgeColor, normalizeSmartScoreData } from '../../utils/smartScoreUtils';

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});

// Normalize data
const normalizedData = computed(() => normalizeSmartScoreData(props.data));

// Computed properties
const currentScore = computed(() => normalizedData.value.currentScore);
const maxScore = computed(() => normalizedData.value.maxScore);
const masteryLevel = computed(() => normalizedData.value.masteryLevel);
const previousScore = computed(() => normalizedData.value.previousScore);
const improvement = computed(() => normalizedData.value.improvement);
const visual = computed(() => normalizedData.value.visual);

const scorePercentage = computed(() => {
  return Math.min(100, (currentScore.value / maxScore.value) * 100);
});

const scoreColor = computed(() => getScoreColor(currentScore.value));
const formattedScore = computed(() => formatScore(currentScore.value, visual.value.showPercentage));
const badgeClass = computed(() => getBadgeColor(masteryLevel.value));
</script>

<style scoped>
.smart-score-display {
  width: 100%;
  max-width: 300px;
  margin: 0 auto;
}

.score-bar-container {
  position: relative;
  height: 24px;
  border-radius: 12px;
  overflow: hidden;
  background-color: #f3f4f6;
  margin-bottom: 8px;
}

.score-bar {
  height: 100%;
  border-radius: 12px;
  transition: width 0.3s ease-in-out;
}

.score-bar-background {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.score-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.score-value {
  font-size: 20px;
  font-weight: 600;
  color: #333;
}

.mastery-badge {
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
}

.improvement-indicator {
  font-size: 14px;
  font-weight: 500;
  padding: 2px 6px;
  border-radius: 4px;
}

.improvement-positive {
  background-color: rgba(76, 175, 80, 0.1);
  color: #4CAF50;
}

.improvement-negative {
  background-color: rgba(244, 67, 54, 0.1);
  color: #F44336;
}
</style>