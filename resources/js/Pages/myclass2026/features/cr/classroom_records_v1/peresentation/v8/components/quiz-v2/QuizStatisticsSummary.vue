<script setup>
import { computed } from 'vue'
import { usePresentationStore } from '../../stores/presentationStore.js'

const props = defineProps({
  quizId: {
    type: String,
    required: true
  }
})

const presentation = usePresentationStore()

const stats = computed(() => presentation.getQuizStatistics(props.quizId))

function getScoreColor(score) {
  if (score >= 80) return '#48bb78'
  if (score >= 60) return '#fbbf24'
  return '#f56565'
}

function getScoreLabel(score) {
  if (score >= 80) return 'Excellent'
  if (score >= 60) return 'Good'
  if (score >= 40) return 'Fair'
  return 'Needs Improvement'
}

function getTrendClass() {
  const scores = stats.value.scores
  if (scores.length < 2) return ''
  
  const recent = scores.slice(-3)
  const avgRecent = recent.reduce((a, b) => a + b, 0) / recent.length
  const avgEarlier = scores.slice(0, -3).reduce((a, b) => a + b, 0) / (scores.length - 3) || avgRecent
  
  if (avgRecent > avgEarlier) return 'trend-up'
  if (avgRecent < avgEarlier) return 'trend-down'
  return 'trend-stable'
}
</script>

<template>
  <div class="stats-summary">
    <!-- Empty State -->
    <div v-if="stats.totalAttempts === 0" class="stats-empty">
      <div class="empty-icon">📊</div>
      <div class="empty-text">No statistics yet</div>
      <div class="empty-hint">Complete the quiz to see your performance statistics</div>
    </div>

    <!-- Statistics Display -->
    <div v-else class="stats-content">
      <!-- Main Stats Grid -->
      <div class="stats-grid">
        <!-- Average Score -->
        <div class="stat-card stat-card-primary">
          <div class="stat-icon">📈</div>
          <div class="stat-info">
            <div class="stat-value" :style="{ color: getScoreColor(stats.averageScore) }">
              {{ stats.averageScore }}%
            </div>
            <div class="stat-label">Average Score</div>
            <div class="stat-badge" :class="getTrendClass()">
              {{ stats.scores.length >= 3 ? '📊 Trend' : '' }}
            </div>
          </div>
        </div>

        <!-- Highest Score -->
        <div class="stat-card stat-card-success">
          <div class="stat-icon">🏆</div>
          <div class="stat-info">
            <div class="stat-value" style="color: #48bb78">
              {{ stats.highestScore }}%
            </div>
            <div class="stat-label">Highest Score</div>
            <div class="stat-badge">{{ getScoreLabel(stats.highestScore) }}</div>
          </div>
        </div>

        <!-- Lowest Score -->
        <div class="stat-card stat-card-warning">
          <div class="stat-icon">📉</div>
          <div class="stat-info">
            <div class="stat-value" style="color: #f56565">
              {{ stats.lowestScore }}%
            </div>
            <div class="stat-label">Lowest Score</div>
            <div class="stat-badge">{{ getScoreLabel(stats.lowestScore) }}</div>
          </div>
        </div>

        <!-- Total Attempts -->
        <div class="stat-card stat-card-info">
          <div class="stat-icon">📝</div>
          <div class="stat-info">
            <div class="stat-value" style="color: #63b3ed">
              {{ stats.totalAttempts }}
            </div>
            <div class="stat-label">Total Attempts</div>
            <div class="stat-badge">Quiz Attempts</div>
          </div>
        </div>
      </div>

      <!-- Score Distribution -->
      <div class="score-distribution">
        <div class="distribution-title">Score Distribution</div>
        <div class="distribution-bars">
          <div class="distribution-item">
            <div class="dist-label">Excellent (80%+)</div>
            <div class="dist-bar">
              <div 
                class="dist-fill"
                :style="{ 
                  width: (stats.scores.filter(s => s >= 80).length / stats.totalAttempts * 100) + '%',
                  backgroundColor: '#48bb78'
                }"
              />
            </div>
            <div class="dist-count">{{ stats.scores.filter(s => s >= 80).length }}</div>
          </div>
          <div class="distribution-item">
            <div class="dist-label">Good (60-79%)</div>
            <div class="dist-bar">
              <div 
                class="dist-fill"
                :style="{ 
                  width: (stats.scores.filter(s => s >= 60 && s < 80).length / stats.totalAttempts * 100) + '%',
                  backgroundColor: '#fbbf24'
                }"
              />
            </div>
            <div class="dist-count">{{ stats.scores.filter(s => s >= 60 && s < 80).length }}</div>
          </div>
          <div class="distribution-item">
            <div class="dist-label">Fair (40-59%)</div>
            <div class="dist-bar">
              <div 
                class="dist-fill"
                :style="{ 
                  width: (stats.scores.filter(s => s >= 40 && s < 60).length / stats.totalAttempts * 100) + '%',
                  backgroundColor: '#f97316'
                }"
              />
            </div>
            <div class="dist-count">{{ stats.scores.filter(s => s >= 40 && s < 60).length }}</div>
          </div>
          <div class="distribution-item">
            <div class="dist-label">Needs Improvement (&lt;40%)</div>
            <div class="dist-bar">
              <div 
                class="dist-fill"
                :style="{ 
                  width: (stats.scores.filter(s => s < 40).length / stats.totalAttempts * 100) + '%',
                  backgroundColor: '#f56565'
                }"
              />
            </div>
            <div class="dist-count">{{ stats.scores.filter(s => s < 40).length }}</div>
          </div>
        </div>
      </div>

      <!-- Recent Performance -->
      <div v-if="stats.scores.length > 1" class="recent-performance">
        <div class="performance-title">Recent Performance</div>
        <div class="performance-chart">
          <div 
            v-for="(score, index) in stats.scores.slice(-10)" 
            :key="index"
            class="chart-bar"
            :title="`Attempt ${stats.totalAttempts - stats.scores.length + index + 1}: ${score}%`"
          >
            <div 
              class="chart-fill"
              :style="{ 
                height: score + '%',
                backgroundColor: getScoreColor(score)
              }"
            />
          </div>
        </div>
        <div class="performance-legend">
          <span class="legend-item">Recent 10 attempts</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.stats-summary {
  background: #1e1e1e;
  border: 1px solid #333;
  border-radius: 12px;
  padding: 20px;
}

.stats-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  gap: 12px;
}

.empty-icon {
  font-size: 48px;
  opacity: 0.5;
}

.empty-text {
  font-size: 16px;
  color: #888;
  font-weight: 600;
}

.empty-hint {
  font-size: 14px;
  color: #666;
}

.stats-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.stat-card {
  background: #252525;
  border: 1px solid #383838;
  border-radius: 10px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: all 0.2s;
}

.stat-card:hover {
  border-color: #505050;
  background: #2a2a2a;
}

.stat-card-primary {
  border-color: #63b3ed;
  background: rgba(99, 179, 237, 0.05);
}

.stat-card-success {
  border-color: #48bb78;
  background: rgba(72, 187, 120, 0.05);
}

.stat-card-warning {
  border-color: #f56565;
  background: rgba(245, 101, 101, 0.05);
}

.stat-card-info {
  border-color: #63b3ed;
  background: rgba(99, 179, 237, 0.05);
}

.stat-icon {
  font-size: 28px;
  flex-shrink: 0;
}

.stat-info {
  flex: 1;
  min-width: 0;
}

.stat-value {
  font-size: 24px;
  font-weight: 800;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 12px;
  color: #888;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  margin-bottom: 4px;
}

.stat-badge {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 4px;
  background: #333;
  color: #aaa;
  display: inline-block;
}

.trend-up {
  color: #48bb78;
  background: rgba(72, 187, 120, 0.15);
}

.trend-down {
  color: #f56565;
  background: rgba(245, 101, 101, 0.15);
}

.trend-stable {
  color: #fbbf24;
  background: rgba(251, 191, 36, 0.15);
}

.score-distribution {
  background: #252525;
  border: 1px solid #383838;
  border-radius: 10px;
  padding: 16px;
}

.distribution-title {
  font-size: 14px;
  font-weight: 600;
  color: #f0f0f0;
  margin-bottom: 12px;
}

.distribution-bars {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.distribution-item {
  display: grid;
  grid-template-columns: 140px 1fr 40px;
  gap: 12px;
  align-items: center;
}

.dist-label {
  font-size: 12px;
  color: #888;
}

.dist-bar {
  height: 8px;
  background: #333;
  border-radius: 4px;
  overflow: hidden;
}

.dist-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.dist-count {
  font-size: 12px;
  color: #e0e0e0;
  font-weight: 600;
  text-align: right;
}

.recent-performance {
  background: #252525;
  border: 1px solid #383838;
  border-radius: 10px;
  padding: 16px;
}

.performance-title {
  font-size: 14px;
  font-weight: 600;
  color: #f0f0f0;
  margin-bottom: 12px;
}

.performance-chart {
  display: flex;
  align-items: flex-end;
  gap: 8px;
  height: 80px;
  padding: 8px 0;
}

.chart-bar {
  flex: 1;
  height: 100%;
  display: flex;
  align-items: flex-end;
  cursor: pointer;
}

.chart-fill {
  width: 100%;
  border-radius: 4px 4px 0 0;
  transition: height 0.3s ease;
  min-height: 4px;
}

.performance-legend {
  margin-top: 8px;
  font-size: 12px;
  color: #666;
}

.legend-item {
  display: inline-block;
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .distribution-item {
    grid-template-columns: 1fr;
    gap: 4px;
  }
  
  .dist-bar {
    order: 2;
  }
  
  .dist-count {
    order: 3;
    text-align: left;
  }
}
</style>
