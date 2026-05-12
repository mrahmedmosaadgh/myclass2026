<script setup>
import { computed } from 'vue';

const props = defineProps({
  correctCount: { type: Number, default: 0 },
  wrongCount: { type: Number, default: 0 },
  totalGroups: { type: Number, default: 0 }
});

const accuracy = computed(() => {
  const total = props.correctCount + props.wrongCount;
  if (total === 0) return 0;
  return Math.round((props.correctCount / total) * 100);
});
</script>

<template>
  <q-slide-transition>
    <q-banner
      v-if="totalGroups > 0"
      rounded
      class="grading-banner q-mb-md"
      :class="wrongCount === 0 ? 'bg-positive-1' : (correctCount === 0 ? 'bg-negative-1' : 'bg-warning-1')"
    >
      <template #avatar>
        <q-icon
          :name="wrongCount === 0 ? 'emoji_events' : (correctCount === 0 ? 'sentiment_dissatisfied' : 'assessment')"
          :color="wrongCount === 0 ? 'positive' : (correctCount === 0 ? 'negative' : 'warning')"
          size="32px"
        />
      </template>

      <div class="text-subtitle1 text-weight-bold">
        Results: {{ correctCount }} correct, {{ wrongCount }} wrong out of {{ totalGroups }}
      </div>
      <div class="text-body2 text-grey-7">
        Accuracy: {{ accuracy }}%
      </div>

      <template #action>
        <q-linear-progress
          :value="totalGroups > 0 ? correctCount / totalGroups : 0"
          size="8px"
          rounded
          color="positive"
          track-color="negative"
          class="full-width q-mt-sm"
        />
      </template>
    </q-banner>
  </q-slide-transition>
</template>

<style scoped>
.grading-banner {
  border-left: 4px solid;
}
.bg-positive-1 {
  background: #f0fdf4;
  border-left-color: #22c55e;
}
.bg-negative-1 {
  background: #fef2f2;
  border-left-color: #ef4444;
}
.bg-warning-1 {
  background: #fffbeb;
  border-left-color: #f59e0b;
}
</style>
