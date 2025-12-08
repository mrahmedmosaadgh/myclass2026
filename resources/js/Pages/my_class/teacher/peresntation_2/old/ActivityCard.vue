<template>
  <div class="activity-card q-pa-sm q-mb-sm">
    <div class="card-header" :style="{borderLeft: '6px solid ' + color}">
      <div class="title">{{activity.title || activity.type}}</div>
      <div class="meta">{{timeLabel}}</div>
    </div>
    <div class="card-body q-mt-sm">
      <div><strong>Type:</strong> <span :style="{color}">{{activity.type}}</span></div>
      <div v-if="activity.teacherInstructions"><strong>Teacher:</strong> {{activity.teacherInstructions}}</div>
      <div v-if="activity.studentInstructions"><strong>Student:</strong> {{activity.studentInstructions}}</div>
      <div v-if="activity.materials && activity.materials.length">
        <strong>Materials:</strong>
        <ul>
          <li v-for="(m, idx) in activity.materials" :key="idx"><a :href="m" target="_blank">{{m}}</a></li>
        </ul>
      </div>
      <div v-if="activity.learningOutcome"><strong>Outcome:</strong> {{activity.learningOutcome}}</div>
      <div v-if="activity.dependencies && activity.dependencies.length">
        <strong>Depends on:</strong>
        <span class="deps">
          <span v-for="d in activity.dependencies" :key="d" class="dep">{{d}}</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({ activity: Object });

const colorMap = {
  lecture: '#4caf50',
  quiz: '#ff9800',
  game: '#00bcd4',
  discussion: '#9c27b0',
  exercise: '#607d8b'
};

const color = computed(() => colorMap[props.activity.type] || '#777');
const timeLabel = computed(() => {
  const s = props.activity.startTime ?? 0;
  const d = props.activity.duration ?? props.activity.durationMinutes ?? 0;
  const e = s + d;
  return `Start: ${s}s · Duration: ${d}s · End: ${e}s`;
});
</script>

<style scoped>
.activity-card { border:1px solid #eee; border-radius:6px; background:#fff }
.card-header { display:flex; justify-content:space-between; padding:8px 12px; align-items:center; }
.title { font-weight:600 }
.meta { font-size:12px; color:#666 }
.card-body { padding:0 12px 12px 12px }
.deps .dep { display:inline-block; background:#f0f0f0; padding:2px 6px; margin-right:6px; border-radius:4px; font-size:12px }
</style>
