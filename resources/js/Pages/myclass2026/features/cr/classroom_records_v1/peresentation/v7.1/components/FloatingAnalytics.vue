<script setup>
import { ref, computed, watch } from 'vue';
import { useGameStore } from '../stores/gameStore';
import { use } from 'echarts/core';
import { CanvasRenderer } from 'echarts/renderers';
import { BarChart } from 'echarts/charts';
import { GridComponent, TooltipComponent } from 'echarts/components';
import VChart from 'vue-echarts';
import FullscreenOverlay from './FullscreenOverlay.vue';

use([CanvasRenderer, BarChart, GridComponent, TooltipComponent]);

const gameStore = useGameStore();
const isCollapsed = ref(false);
const isExpanded = ref(false);

const groupImages = ref({});
const GROUP_IMAGES_KEY = 'builder-v7-live-points-group-images';

function loadGroupImages() {
  try {
    const raw = localStorage.getItem(GROUP_IMAGES_KEY);
    const parsed = raw ? JSON.parse(raw) : {};
    groupImages.value = parsed && typeof parsed === 'object' ? parsed : {};
  } catch {
    groupImages.value = {};
  }
}

watch(isExpanded, (val) => {
  if (val) loadGroupImages();
});

watch(groupImages, (val) => {
  try {
    localStorage.setItem(GROUP_IMAGES_KEY, JSON.stringify(val || {}));
  } catch {
    // ignore
  }
}, { deep: true });

function openExpanded(e) {
  if (e) e.stopPropagation();
  isExpanded.value = true;
}

function closeExpanded() {
  isExpanded.value = false;
}

function clearGroupImage(groupId) {
  if (!groupId) return;
  const next = { ...(groupImages.value || {}) };
  delete next[groupId];
  groupImages.value = next;
}

function handleGroupImageFile(groupId, file) {
  if (!groupId || !file) return;
  if (!String(file.type || '').startsWith('image/')) return;
  const reader = new FileReader();
  reader.onload = () => {
    const next = { ...(groupImages.value || {}) };
    next[groupId] = String(reader.result || '');
    groupImages.value = next;
  };
  reader.readAsDataURL(file);
}

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

const sortedGroups = computed(() => [...gameStore.groups].sort((a, b) => b.score - a.score));
const maxScore = computed(() => {
  if (sortedGroups.value.length === 0) return 1;
  const max = Math.max(...sortedGroups.value.map(g => Number(g.score) || 0));
  return max > 0 ? max : 1;
});

function getBarHeightPct(score) {
  const n = Number(score) || 0;
  return Math.max(0, Math.min(100, (n / maxScore.value) * 100));
}
</script>

<template>
  <div class="floating-analytics" :class="{ 'is-collapsed': isCollapsed }">
    <div class="widget-header" @click="isCollapsed = !isCollapsed">
      <div class="header-left">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
        <span v-if="!isCollapsed">Live Points</span>
      </div>
      <div class="header-actions" @click.stop>
        <button v-if="!isCollapsed" class="expand-btn" @click="openExpanded" title="Expand">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>
        </button>
        <button class="toggle-btn" aria-label="Collapse">
          <svg v-if="isCollapsed" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
      </div>
    </div>

    <div v-show="!isCollapsed" class="widget-body">
      <div v-if="gameStore.groups.length === 0" class="empty-hint">
        No groups active
      </div>
      <v-chart v-else class="mini-chart" :option="chartOption" autoresize />
      
      <div class="widget-footer">
        <a href="/classroom-records/presentation/remote/teacher" class="remote-btn">
          <span>Go Remote</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
        </a>
      </div>
    </div>
  </div>

  <FullscreenOverlay :show="isExpanded" title="Live Points (Expanded)" @close="closeExpanded">
    <div v-if="sortedGroups.length === 0" class="expanded-empty">No groups active</div>
    <div v-else class="expanded-chart">
      <div v-for="g in sortedGroups" :key="g.id" class="expanded-col">
        <div class="col-title">{{ g.name }}</div>
        <div class="col-stage">
          <div class="col-bar" :style="{ height: getBarHeightPct(g.score) + '%', background: g.color }">
            <img v-if="groupImages[g.id]" :src="groupImages[g.id]" class="col-img" alt="" />
          </div>
        </div>
        <div class="col-score">{{ g.score }}</div>
        <div class="col-tools">
          <label class="img-btn">
            <input type="file" accept="image/*" class="img-input" @change="e => handleGroupImageFile(g.id, e.target.files && e.target.files[0])" />
            Add Image
          </label>
          <button v-if="groupImages[g.id]" class="img-clear" @click="() => clearGroupImage(g.id)">Remove</button>
        </div>
      </div>
    </div>
  </FullscreenOverlay>
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

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.expand-btn {
  background: transparent;
  border: none;
  padding: 0;
  color: #64748b;
  cursor: pointer;
}

.expand-btn:hover {
  color: #334155;
}

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

.widget-footer {
  margin-top: auto;
  padding-top: 8px;
  border-top: 1px solid #f1f5f9;
}

.remote-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 8px;
  background: #6366f1;
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 700;
  transition: background 0.2s;
}

.remote-btn:hover {
  background: #4f46e5;
}

.expanded-empty {
  color: #64748b;
  font-weight: 800;
  font-size: 1rem;
  padding: 16px;
}

.expanded-chart {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
}

.expanded-col {
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 14px;
  background: #ffffff;
  box-shadow: 0 8px 18px rgba(0,0,0,0.06);
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.col-title {
  font-weight: 900;
  color: #0f172a;
  text-align: center;
}

.col-stage {
  height: 360px;
  border: 1px dashed #cbd5e1;
  border-radius: 14px;
  background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 12px;
  overflow: hidden;
}

.col-bar {
  width: 64px;
  border-radius: 14px 14px 8px 8px;
  position: relative;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  transition: height 0.25s ease;
}

.col-img {
  width: 56px;
  height: 56px;
  border-radius: 999px;
  object-fit: cover;
  margin-top: -28px;
  border: 3px solid #ffffff;
  box-shadow: 0 8px 16px rgba(0,0,0,0.18);
}

.col-score {
  text-align: center;
  font-weight: 900;
  color: #0f172a;
  font-size: 1.25rem;
}

.col-tools {
  display: flex;
  justify-content: center;
  gap: 10px;
}

.img-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 12px;
  border-radius: 10px;
  background: #0f172a;
  color: white;
  font-weight: 800;
  font-size: 0.85rem;
  cursor: pointer;
}

.img-btn:hover {
  background: #111827;
}

.img-input {
  display: none;
}

.img-clear {
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #991b1b;
  border-radius: 10px;
  padding: 8px 12px;
  font-weight: 900;
  font-size: 0.85rem;
  cursor: pointer;
}

.img-clear:hover {
  background: #fee2e2;
}
</style>
