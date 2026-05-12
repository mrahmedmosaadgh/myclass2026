<script setup>
import { computed } from 'vue';
import { useGameStore } from '../../../stores/gameStore';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent, TitleComponent } from 'echarts/components';
import VChart from 'vue-echarts';
import PodiumDisplay from './PodiumDisplay.vue';

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent, TitleComponent]);

const props = defineProps({
  element: { type: Object, required: true },
  isEditMode: { type: Boolean, default: false }
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
    return { ...g, computedRank: currentRank };
  });
});

const topThree = computed(() => rankedGroups.value.slice(0, 3));

const chartOption = computed(() => {
  const names = rankedGroups.value.map((g) => g.name);
  const scores = rankedGroups.value.map((g) => ({
    value: g.score,
    itemStyle: {
      color: g.computedRank === 1 ? '#f59e0b' : g.color,
      borderRadius: [4, 4, 0, 0]
    }
  }));

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

function handleWrapperClick(e) {
  e.stopPropagation();
  emit('select');
}
</script>

<template>
  <q-card
    flat
    bordered
    class="leaderboard-v2"
    @mousedown="handleWrapperClick"
    @touchstart="handleWrapperClick"
  >
    <q-card-section class="text-center q-pb-none">
      <div class="text-h5 text-weight-bold text-grey-9">
        <q-icon name="emoji_events" color="amber" size="28px" class="q-mr-sm" />
        Final Standings
      </div>
      <div class="text-caption text-grey-6">Classroom Group Results</div>
    </q-card-section>

    <q-card-section v-if="gameStore.groups.length === 0" class="text-center q-pa-lg">
      <q-icon name="groups" size="48px" color="grey-4" />
      <div class="text-grey-6 q-mt-sm">No active groups to display.</div>
    </q-card-section>

    <template v-else>
      <!-- Podium -->
      <q-card-section class="q-pt-sm">
        <PodiumDisplay :groups="topThree" />
      </q-card-section>

      <q-separator />

      <!-- Full standings with progress bars -->
      <q-card-section class="q-pa-md">
        <div v-for="g in rankedGroups" :key="g.id" class="q-mb-sm">
          <div class="row items-center q-mb-xs">
            <q-avatar size="24px" :style="{ backgroundColor: g.color }" class="q-mr-sm">
              <span class="text-white text-weight-bold" style="font-size: 10px">{{ g.computedRank }}</span>
            </q-avatar>
            <div class="col text-subtitle2 text-weight-medium">{{ g.name }}</div>
            <div class="text-subtitle2 text-weight-bold text-grey-8">{{ g.score }} pts</div>
          </div>
          <q-linear-progress
            :value="Math.max(0, rankedGroups[0]?.score > 0 ? g.score / rankedGroups[0].score : 0)"
            size="10px"
            rounded
            :color="g.color"
            track-color="grey-3"
          />
        </div>
      </q-card-section>

      <q-separator />

      <!-- ECharts bar chart -->
      <q-card-section class="q-pa-md">
        <v-chart class="score-chart" :option="chartOption" autoresize />
      </q-card-section>
    </template>
  </q-card>
</template>

<style scoped>
.leaderboard-v2 {
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, #ffffff, #f8fafc);
  overflow: auto;
}
.score-chart {
  width: 100%;
  height: 300px;
}
</style>
