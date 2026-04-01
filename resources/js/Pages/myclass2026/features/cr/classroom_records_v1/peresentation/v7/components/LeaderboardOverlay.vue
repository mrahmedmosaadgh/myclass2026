<script setup>
import { computed } from 'vue';
import { useGameStore } from '../stores/gameStore';

import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, TitleComponent } from 'echarts/components';
import VChart from 'vue-echarts';

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent, TitleComponent]);

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

function close() {
  gameStore.isLeaderboardOpen = false;
}

// Visual Helpers
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
</script>

<template>
  <div v-if="gameStore.isLeaderboardOpen" class="modal-backdrop" @click.self="close">
    <div class="leaderboard-modal">
      
      <div class="modal-header">
        <h2>🏆 Live Classroom Leaderboard</h2>
        <button class="close-btn" @click="close">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
      </div>

      <div class="modal-body">
        
        <div v-if="gameStore.groups.length === 0" class="empty-state">
           <p>No active groups. Add groups via the Settings Menu first!</p>
        </div>

        <template v-else>
          <!-- Podium Area (Top 3) -->
          <div class="podium-section" v-if="topThree.length > 0">
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

          <div class="divider"></div>

          <!-- Remaining Groups List -->
          <div class="list-section">
             <h3>Analytics</h3>
             <div class="chart-container">
               <v-chart class="score-chart" :option="chartOption" autoresize />
             </div>
          </div>

        </template>
      </div>

    </div>
  </div>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(15, 23, 42, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  backdrop-filter: blur(8px);
}

.leaderboard-modal {
  background: white;
  width: 700px;
  max-width: 95vw;
  max-height: 90vh;
  border-radius: 16px;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
  0% { transform: translateY(40px) scale(0.95); opacity: 0; }
  100% { transform: translateY(0) scale(1); opacity: 1; }
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(to right, #f8fafc, #ffffff);
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #0f172a;
  display: flex;
  align-items: center;
  gap: 8px;
}

.close-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  color: #64748b;
  padding: 6px;
  border-radius: 8px;
  transition: all 0.2s;
}
.close-btn:hover { background: #f1f5f9; color: #0f172a; }

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  flex: 1;
  overflow-y: auto;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #64748b;
  font-style: italic;
}

/* Podium Design */
.podium-section {
  display: flex;
  justify-content: center;
  align-items: flex-end;
  gap: 16px;
  padding: 50px 0 30px 0;
  min-height: 280px;
}

.podium-spot {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 120px;
  position: relative;
}

.spot-1st { z-index: 3; margin: 0 -10px; }
.spot-2nd { z-index: 2; }
.spot-3rd { z-index: 1; }

.podium-avatar {
  width: 50px;
  height: 50px;
  background: white;
  border: 4px solid #e2e8f0;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  background: #f8fafc;
}
.avatar-initial {
  font-size: 1.5rem;
  font-weight: bold;
  color: #334155;
}

.podium-score {
  font-weight: 800;
  font-size: 1.1rem;
  color: #0f172a;
  margin-bottom: 8px;
}

.podium-block {
  width: 100%;
  border-radius: 8px 8px 0 0;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 12px;
  box-shadow: inset 0 2px 10px rgba(255,255,255,0.4), 0 -2px 10px rgba(0,0,0,0.1);
}

.rank-num {
  font-size: 2.2rem;
  font-weight: 900;
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.podium-name {
  margin-top: 12px;
  font-weight: 700;
  font-size: 1.1rem;
  text-align: center;
  width: 140%;
  text-shadow: 0 1px 1px rgba(255,255,255,0.8);
}

.divider {
  height: 1px;
  background: #e2e8f0;
  margin: 10px 0 24px 0;
}

/* Standings Table / Chart */
.list-section {
  display: flex;
  flex-direction: column;
}

.list-section h3 {
  margin: 0 0 16px 0;
  color: #334155;
  font-size: 1.1rem;
}

.chart-container {
  width: 100%;
  height: 280px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px;
}

.score-chart {
  width: 100%;
  height: 100%;
}
</style>
