<script setup>
import { computed } from 'vue';
import { useGameStore } from '../stores/gameStore';

import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, TitleComponent } from 'echarts/components';
import VChart from 'vue-echarts';

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent, TitleComponent]);

const props = defineProps({
  element: Object,
  isEditMode: Boolean
});

const emit = defineEmits(['select']);
const gameStore = useGameStore();

const rankedGroups = computed(() => {
  const sorted = [...gameStore.groups].sort((a, b) => b.score - a.score);
  if (sorted.length === 0) return [];
  
  let currentRank = 1;
  let previousScore = sorted[0].score;

  return sorted.map((g) => {
    if (g.score < previousScore) {
      currentRank++;
      previousScore = g.score;
    }
    return {
      ...g,
      computedRank: currentRank
    };
  });
});

const topThree = computed(() => {
  return rankedGroups.value.slice(0, 3);
});

const chartOption = computed(() => {
  const names = rankedGroups.value.map(g => g.name);
  const scores = rankedGroups.value.map(g => {
    return {
      value: g.score,
      itemStyle: {
        color: g.computedRank === 1 ? '#f59e0b' : g.color,
        borderRadius: [4, 4, 0, 0]
      }
    };
  });

  return {
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
    xAxis: { 
      type: 'category', 
      data: names, 
      axisLabel: { interval: 0, rotate: names.length > 5 ? 30 : 0, fontWeight: 'bold' },
      axisTick: { alignWithLabel: true }
    },
    yAxis: { type: 'value' },
    series: [{
      type: 'bar',
      data: scores,
      barMaxWidth: 60,
      label: { show: true, position: 'top', fontWeight: 'bold', fontSize: 14 }
    }]
  };
});

function getMedalColor(rank) {
  if (rank === 1) return '#f59e0b'; // Gold
  if (rank === 2) return '#94a3b8'; // Silver
  if (rank === 3) return '#d97706'; // Bronze
  return 'transparent';
}

function getPodiumHeight(rank) {
  if (rank === 1) return '160px'; // 1st
  if (rank === 2) return '120px'; // 2nd
  if (rank === 3) return '90px';  // 3rd
  return '0px';
}

function handleWrapperClick(e) {
  e.stopPropagation();
  emit('select');
}
</script>

<template>
  <div class="lb-wrapper" @mousedown="handleWrapperClick" @touchstart="handleWrapperClick">
    <div class="lb-header">
      <h2>🏆 Final Standings</h2>
      <p class="subtitle" v-if="gameStore.groups.length > 0">Classroom Group Results</p>
    </div>

    <div v-if="gameStore.groups.length === 0" class="empty-state">
      <p>No active groups to display.</p>
    </div>

    <div v-else class="lb-content">
      <!-- Podium Area (Top 3) -->
      <div class="podium-section">
        <!-- 2nd Place -->
        <div v-if="topThree[1]" class="podium-spot spot-2nd">
          <div class="podium-avatar" :style="{ borderColor: topThree[1].color }">
              <span class="avatar-initial">{{ topThree[1].name.charAt(0).toUpperCase() }}</span>
          </div>
          <div class="podium-score">{{ topThree[1].score }} pts</div>
          <div class="podium-block" :style="{ height: getPodiumHeight(topThree[1].computedRank), backgroundColor: getMedalColor(topThree[1].computedRank) }">
            <span class="rank-num">{{ topThree[1].computedRank }}</span>
          </div>
          <div class="podium-name" :style="{ color: topThree[1].color }">{{ topThree[1].name }}</div>
        </div>

        <!-- 1st Place (Center) -->
        <div v-if="topThree[0]" class="podium-spot spot-1st">
          <div class="podium-avatar" :style="{ borderColor: topThree[0].color }">
              <span class="avatar-initial">{{ topThree[0].name.charAt(0).toUpperCase() }}</span>
          </div>
          <div class="podium-score">{{ topThree[0].score }} pts</div>
          <div class="podium-block" :style="{ height: getPodiumHeight(topThree[0].computedRank), backgroundColor: getMedalColor(topThree[0].computedRank) }">
            <span class="rank-num">{{ topThree[0].computedRank }}</span>
          </div>
          <div class="podium-name" :style="{ color: topThree[0].color }">{{ topThree[0].name }}</div>
        </div>

        <!-- 3rd Place -->
        <div v-if="topThree[2]" class="podium-spot spot-3rd">
          <div class="podium-avatar" :style="{ borderColor: topThree[2].color }">
              <span class="avatar-initial">{{ topThree[2].name.charAt(0).toUpperCase() }}</span>
          </div>
          <div class="podium-score">{{ topThree[2].score }} pts</div>
          <div class="podium-block" :style="{ height: getPodiumHeight(topThree[2].computedRank), backgroundColor: getMedalColor(topThree[2].computedRank) }">
            <span class="rank-num">{{ topThree[2].computedRank }}</span>
          </div>
          <div class="podium-name" :style="{ color: topThree[2].color }">{{ topThree[2].name }}</div>
        </div>
      </div>

      <!-- Analytics Chart Below Podium -->
      <div class="standings-box" v-if="rankedGroups.length > 0">
         <v-chart class="score-chart" :option="chartOption" autoresize />
      </div>
    </div>
  </div>
</template>

<style scoped>
.lb-wrapper {
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, #ffffff, #f1f5f9);
  border-radius: 12px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  padding: 24px;
  overflow: hidden;
}

.lb-header {
  text-align: center;
  margin-bottom: 20px;
}
.lb-header h2 {
  font-size: 2rem;
  color: #0f172a;
  margin: 0;
  text-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.subtitle {
  color: #64748b;
  font-size: 1.1rem;
  margin: 4px 0 0 0;
}

.empty-state {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-style: italic;
  font-size: 1.2rem;
}

.lb-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

/* Podium Design */
.podium-section {
  display: flex;
  justify-content: center;
  align-items: flex-end;
  gap: 20px;
  padding: 50px 0 20px 0;
}

.podium-spot {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 140px;
  position: relative;
}

.spot-1st { z-index: 3; margin: 0 -10px; transform: scale(1.05); }
.spot-2nd { z-index: 2; }
.spot-3rd { z-index: 1; }

.podium-avatar {
  width: 60px;
  height: 60px;
  background: white;
  border: 5px solid #e2e8f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  background: #f8fafc;
}
.avatar-initial {
  font-size: 2rem;
  font-weight: bold;
  color: #334155;
}

.podium-score {
  font-weight: 800;
  font-size: 1.2rem;
  color: #0f172a;
  margin-bottom: 8px;
}

.podium-block {
  width: 100%;
  border-radius: 12px 12px 0 0;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 16px;
  box-shadow: inset 0 2px 15px rgba(255,255,255,0.4), 0 -2px 10px rgba(0,0,0,0.1);
}

.rank-num {
  font-size: 2.5rem;
  font-weight: 900;
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.podium-name {
  margin-top: 16px;
  font-weight: 800;
  font-size: 1.3rem;
  text-align: center;
  width: 150%;
  text-shadow: 0 1px 1px rgba(255,255,255,0.8);
}

/* Mini Standings / Chart */
.standings-box {
  margin-top: 30px;
  width: 100%;
  max-width: 600px;
  height: 250px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  padding: 10px;
}
.score-chart {
  width: 100%;
  height: 100%;
}
</style>
