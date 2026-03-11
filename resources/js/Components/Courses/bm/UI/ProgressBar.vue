<template>
  <div class="bm-progress-bar row items-center q-my-md">
    <div class="col-grow relative-position bg-grey-3" style="height: 12px; border-radius: 6px; overflow: hidden;">
      <div 
        class="bg-positive absolute-top-left" 
        :style="{ width: percent + '%', height: '100%', transition: 'width 0.4s ease-out' }"
      ></div>
    </div>
    <div class="col-auto q-ml-sm text-caption text-weight-bold text-grey-8">
      {{ current }} / {{ total }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  current: { type: Number, required: true },
  total: { type: Number, required: true }
});

const percent = computed(() => {
  return props.total === 0 ? 0 : Math.min(100, Math.round((props.current / props.total) * 100));
});
</script>
