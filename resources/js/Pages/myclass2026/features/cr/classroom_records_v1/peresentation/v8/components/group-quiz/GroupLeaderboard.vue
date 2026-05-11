<script setup>
import { computed } from 'vue'
import { useGameStore } from '../../stores/gameStore.js'

const gameStore = useGameStore()

const props = defineProps({
  element: { type: Object, required: true },
  isPresentMode: { type: Boolean, default: false }
})

// ── Computed ───────────────────────────────────────────
const chartData = computed(() => {
  const ranked = gameStore.rankedGroups
  return {
    labels: ranked.map(g => g.name),
    values: ranked.map(g => g.score),
    colors: ranked.map(g => g.color || '#6366f1')
  }
})

const maxScore = computed(() => {
  const scores = chartData.value.values
  return scores.length > 0 ? Math.max(...scores, 0) : 1
})

const hasData = computed(() => gameStore.groups.length > 0)

// ── Bar dimensions ─────────────────────────────────────
const barHeight = 28
const barGap = 12
const labelWidth = 120
const chartWidth = 500
const chartHeight = computed(() =>
  Math.max(200, gameStore.groups.length * (barHeight + barGap) + 40)
)
</script>

<template>
  <div class="glb-container">
    <h3 class="glb-title">🏆 Group Leaderboard</h3>

    <!-- Empty State -->
    <div v-if="!hasData" class="glb-empty">
      <div class="glb-empty-icon">📊</div>
      <p>No groups configured</p>
      <p class="glb-empty-hint">Open Group Setup to add teams</p>
    </div>

    <!-- Chart -->
    <div v-else class="glb-chart-wrapper">
      <svg
        class="glb-chart"
        :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
        :style="{ height: chartHeight + 'px' }"
      >
        <!-- Background grid lines -->
        <line
          v-for="n in 5"
          :key="'grid-' + n"
          :x1="labelWidth"
          :y1="20"
          :x2="labelWidth"
          :y2="chartHeight - 20"
          stroke="#f3f4f6"
          stroke-width="1"
        />

        <!-- Bars -->
        <g
          v-for="(group, i) in gameStore.rankedGroups"
          :key="group.id"
          class="glb-bar-group"
        >
          <!-- Label -->
          <text
            :x="labelWidth - 10"
            :y="30 + i * (barHeight + barGap) + barHeight / 2"
            text-anchor="end"
            dominant-baseline="middle"
            class="glb-label"
          >
            {{ group.name }}
          </text>

          <!-- Bar background -->
          <rect
            :x="labelWidth"
            :y="30 + i * (barHeight + barGap)"
            :width="chartWidth - labelWidth - 60"
            :height="barHeight"
            rx="4"
            fill="#f3f4f6"
          />

          <!-- Bar fill -->
          <rect
            :x="labelWidth"
            :y="30 + i * (barHeight + barGap)"
            :width="Math.max(4, (group.score / maxScore) * (chartWidth - labelWidth - 60))"
            :height="barHeight"
            rx="4"
            :fill="group.color || '#6366f1'"
            opacity="0.85"
          />

          <!-- Rank badge -->
          <circle
            :cx="labelWidth - 30"
            :cy="30 + i * (barHeight + barGap) + barHeight / 2"
            r="12"
            :fill="group.computedRank === 1 ? '#fbbf24' : '#e5e7eb'"
          />
          <text
            :x="labelWidth - 30"
            :y="30 + i * (barHeight + barGap) + barHeight / 2"
            text-anchor="middle"
            dominant-baseline="middle"
            class="glb-rank-text"
          >
            {{ group.computedRank }}
          </text>

          <!-- Score label -->
          <text
            :x="labelWidth + Math.max(4, (group.score / maxScore) * (chartWidth - labelWidth - 60)) + 8"
            :y="30 + i * (barHeight + barGap) + barHeight / 2"
            dominant-baseline="middle"
            class="glb-score"
          >
            {{ group.score }} pts
          </text>
        </g>
      </svg>
    </div>

    <!-- Score Summary -->
    <div v-if="hasData" class="glb-footer">
      <div class="glb-stat">
        <span class="glb-stat-label">Groups</span>
        <span class="glb-stat-value">{{ gameStore.groups.length }}</span>
      </div>
      <div class="glb-stat">
        <span class="glb-stat-label">Highest</span>
        <span class="glb-stat-value">{{ maxScore }} pts</span>
      </div>
      <div class="glb-stat">
        <span class="glb-stat-label">Online</span>
        <span class="glb-stat-value">{{ gameStore.onlineCount }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.glb-container {
  width: 100%;
  height: 100%;
  background: #ffffff;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
  font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
}

.glb-title {
  margin: 0 0 12px 0;
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  text-align: center;
}

.glb-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #9ca3af;
}

.glb-empty-icon {
  font-size: 40px;
  opacity: 0.5;
}

.glb-empty-hint {
  font-size: 13px;
  margin: 0;
}

.glb-chart-wrapper {
  flex: 1;
  overflow-y: auto;
  display: flex;
  justify-content: center;
}

.glb-chart {
  width: 100%;
  max-width: 600px;
}

.glb-label {
  font-size: 13px;
  font-weight: 600;
  fill: #374151;
}

.glb-rank-text {
  font-size: 11px;
  font-weight: 700;
  fill: #374151;
}

.glb-score {
  font-size: 12px;
  font-weight: 600;
  fill: #6b7280;
}

.glb-footer {
  display: flex;
  justify-content: center;
  gap: 24px;
  padding-top: 12px;
  margin-top: 8px;
  border-top: 1px solid #f3f4f6;
}

.glb-stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

.glb-stat-label {
  font-size: 11px;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.glb-stat-value {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}
</style>
