<template>
  <div 
    class="smart-score-badge"
    :class="badgeClass"
    :style="{
      width: size === 'small' ? '32px' : '48px',
      height: size === 'small' ? '32px' : '48px'
    }"
  >
    <span class="score-text">
      {{ formattedScore }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { getScoreColor, formatScore, getBadgeColor, normalizeSmartScoreData } from '../../utils/smartScoreUtils';

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'medium',
    validator: (value) => ['small', 'medium', 'large'].includes(value)
  }
});

// Normalize data
const normalizedData = computed(() => normalizeSmartScoreData(props.data));

// Computed properties
const currentScore = computed(() => normalizedData.value.currentScore);
const masteryLevel = computed(() => normalizedData.value.masteryLevel);
const visual = computed(() => normalizedData.value.visual);

const formattedScore = computed(() => formatScore(currentScore.value, visual.value.showPercentage));
const badgeClass = computed(() => getBadgeColor(masteryLevel.value));
</script>

<style scoped>
.smart-score-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: 600;
  font-size: 14px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.score-text {
  color: inherit;
}
</style>