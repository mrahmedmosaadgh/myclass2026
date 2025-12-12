<template>
  <div class="lesson-timeline">
    <div class="timeline-list">
      <template v-for="act in sortedActivities" :key="act.id || act.activityId">
        <div class="timeline-row">
          <div class="timeline-dot" :style="{background: colorFor(act.type)}"></div>
          <div class="timeline-content">
            <div class="timeline-title">{{act.title || act.type}}</div>
            <div class="timeline-meta">Start: {{act.startTime ?? 0}}s — Duration: {{act.duration ?? 0}}s</div>
            <div class="timeline-actions">
              <button @click="$emit('seekTo', act.startTime ?? 0)">Seek</button>
              <button v-if="act.type==='quiz' || act.type==='question'" @click="$emit('selectQuestion', act.questionId || act.question?.id)">Open</button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({ timeline: { type: Array, default: () => [] }, activities: { type: Array, default: () => [] } });

function colorFor(type) {
  const map = { lecture: '#4caf50', quiz: '#ff9800', game: '#00bcd4', discussion: '#9c27b0', exercise: '#607d8b', videoSegment: '#2196f3' };
  return map[type] || '#888';
}

const sortedActivities = computed(() => {
  const arr = (props.activities && props.activities.length) ? props.activities : (props.timeline || []);
  // ensure each has startTime and duration for sorting
  const normalized = arr.map((a, idx) => ({ ...a, id: a.id || a.activityId || ('a' + idx), startTime: a.startTime ?? a.start ?? a.time ?? 0, duration: a.duration ?? ((a.end != null && a.start != null) ? (a.end - a.start) : (a.duration ?? 0)) }));
  normalized.sort((x,y) => (x.startTime || 0) - (y.startTime || 0));
  return normalized;
});
</script>

<style scoped>
.timeline-list { max-height: 380px; overflow:auto; padding:8px }
.timeline-row { display:flex; align-items:flex-start; gap:12px; padding:8px 0; border-bottom:1px dashed #f0f0f0 }
.timeline-dot { width:14px; height:14px; border-radius:50%; margin-top:6px }
.timeline-content { flex:1 }
.timeline-title { font-weight:600 }
.timeline-meta { font-size:12px; color:#666 }
.timeline-actions button { margin-right:6px }
</style>
