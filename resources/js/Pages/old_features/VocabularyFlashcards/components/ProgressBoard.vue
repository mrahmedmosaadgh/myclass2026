<template>
  <div class="progress-board mb-8">
    <div class="score-container">
      <ScoreCard
        icon="🎯"
        title="Total Score"
        :value="`${totalScore.toFixed(1)}/${vocabularyCount}`"
        :subtitle="`${scorePercentage}%`"
        type="total"
      />
      
      <ScoreCard
        icon="📊"
        title="Progress"
        :value="`${answeredCards}/${vocabularyCount}`"
        subtitle="answered"
        type="progress"
      >
        <template #extra>
          <div class="progress-bar">
            <div 
              class="progress-fill" 
              :style="{ width: scorePercentage + '%' }"
            ></div>
          </div>
        </template>
      </ScoreCard>

      <ScoreCard
        icon="📈"
        title="Breakdown"
        :value="breakdownText"
        type="breakdown"
      >
        <template #extra>
          <div class="breakdown-stats">
            <span class="stat-item yes">✓ {{ yesCount }}</span>
            <span class="stat-item maybe">~ {{ notYetCount }}</span>
            <span class="stat-item no">✗ {{ noCount }}</span>
          </div>
        </template>
      </ScoreCard>
    </div>

    <!-- Reward Section -->
    <RewardSection 
      v-if="scorePercentage >= 80 && answeredCards === vocabularyCount"
      :score-percentage="scorePercentage"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import ScoreCard from './ScoreCard.vue'
import RewardSection from './RewardSection.vue'

const props = defineProps({
  totalScore: {
    type: Number,
    required: true
  },
  vocabularyCount: {
    type: Number,
    required: true
  },
  answeredCards: {
    type: Number,
    required: true
  },
  scorePercentage: {
    type: Number,
    required: true
  },
  yesCount: {
    type: Number,
    required: true
  },
  noCount: {
    type: Number,
    required: true
  },
  notYetCount: {
    type: Number,
    required: true
  }
})

const breakdownText = computed(() => {
  return `${props.yesCount + props.notYetCount + props.noCount} rated`
})
</script>

<style scoped>
.progress-board {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.score-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
  margin: 0.5rem 0;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #059669, #10b981);
  border-radius: 4px;
  transition: width 0.5s ease;
}

.breakdown-stats {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.stat-item {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 600;
}

.stat-item.yes {
  background: #dcfce7;
  color: #166534;
}

.stat-item.maybe {
  background: #fef3c7;
  color: #92400e;
}

.stat-item.no {
  background: #fee2e2;
  color: #991b1b;
}

@media (max-width: 768px) {
  .progress-board {
    padding: 1.5rem;
  }
  
  .score-container {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>