<template>
  <div class="lesson-video-player">
    <video
      ref="videoEl"
      :src="slide?.video?.src"
      controls
      @timeupdate="onTimeUpdate"
      @ended="onEnded"
      style="width:100%; background:#000"
    ></video>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
const props = defineProps({ slide: Object });
const emit = defineEmits(['timelineEvent']);

const videoEl = ref(null);

let lastFired = new Set();

watch(() => props.slide, (s) => {
  lastFired = new Set();
  if (videoEl.value && s && s.video && typeof s.video.playFrom === 'number') {
    videoEl.value.currentTime = s.video.playFrom || 0;
    videoEl.value.play().catch(()=>{});
  }
});

function onTimeUpdate(e) {
  const t = videoEl.value.currentTime;
  const timeline = props.slide?.timeline || [];
  timeline.forEach(item => {
    if (item.trigger === 'time') {
      const time = item.time || item.start || 0;
      if (t >= time && !lastFired.has(item.id)) {
        lastFired.add(item.id);
        emit('timelineEvent', item);
        if (item.type === 'pause') videoEl.value.pause();
      }
    }
  });
}

function onEnded() {
  // emit auto continue events
  emit('timelineEvent', { type: 'ended' });
}

function seekTo(time) {
  if (videoEl.value) videoEl.value.currentTime = time;
}

onMounted(()=>{});

defineExpose({ seekTo });
</script>

<style scoped>
.lesson-video-player video { border-radius:6px; }
</style>
