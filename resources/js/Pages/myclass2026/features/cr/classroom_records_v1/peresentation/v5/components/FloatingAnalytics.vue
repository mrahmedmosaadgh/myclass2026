<script setup>
import { ref, computed } from 'vue';
import { useGameStore } from '../stores/gameStore';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent } from 'echarts/components';
import VChart from 'vue-echarts';

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent]);

const gameStore = useGameStore();
const isCollapsed = ref(false);

const chartOption = computed(() => {
  const sorted = [...gameStore.groups].sort((a, b) => b.score - a.score);
  const names = sorted.map(g => g.name);
  const scores = sorted.map((g, idx) => ({
    value: g.score,
    itemStyle: {
      color: idx === 0 && g.score > 0 ? '#f59e0b' : g.color,
      borderRadius: [3, 3, 0, 0]
    }
  }));

  return {
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    grid: { left: '5%', right: '5%', bottom: '5%', top: '15%', containLabel: true },
    xAxis: { 
      type: 'category', 
      data: names, 
      axisLabel: { show: names.length <= 5, fontSize: 10, fontWeight: 'bold' },
      axisTick: { show: false }
    },
    yAxis: { type: 'value', axisLabel: { fontSize: 9 } },
    series: [{
      type: 'bar',
      data: scores,
      barMaxWidth: 30,
      label: { show: true, position: 'top', fontSize: 10, fontWeight: 'bold' }
    }]
  };
});
</script>

<template>
  <div class="floating-analytics" :class="{ 'is-collapsed': isCollapsed }">
    <div class="widget-header" @click="isCollapsed = !isCollapsed">
      <div class="header-left">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
        <span v-if="!isCollapsed">Live Points</span>
      </div>
      <button class="toggle-btn">
        <svg v-if="isCollapsed" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
      </button>
    </div>

    <div v-show="!isCollapsed" class="widget-body">
      <div v-if="gameStore.groups.length === 0" class="empty-hint">
        No groups active
      </div>
      <v-chart v-else class="mini-chart" :option="chartOption" autoresize />
    </div>
  </div>
</template>

<style scoped>
.floating-analytics {
  width: 240px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.floating-analytics.is-collapsed {
  width: 140px;
  background: #1e293b;
  color: white;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.widget-header {
  padding: 10px 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  user-select: none;
}
.is-collapsed .widget-header {
  background: transparent;
  border-bottom: none;
  padding: 8px 12px;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
  font-size: 0.85rem;
  color: #334155;
}
.is-collapsed .header-left { color: white; }

.toggle-btn {
  background: transparent;
  border: none;
  padding: 0;
  color: #64748b;
  cursor: pointer;
}
.is-collapsed .toggle-btn { color: #94a3b8; }

.widget-body {
  height: 180px;
  padding: 8px;
  display: flex;
  flex-direction: column;
}

.mini-chart {
  width: 100%;
  height: 100%;
}

.empty-hint {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  color: #94a3b8;
  font-style: italic;
}
</style>
