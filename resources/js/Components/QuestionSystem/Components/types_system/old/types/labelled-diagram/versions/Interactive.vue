<!-- labelled-diagram/versions/Interactive.vue -->
<template>
  <div class="labelled-diagram-interactive relative max-w-lg mx-auto bg-white p-4 rounded-lg border">
    <p class="text-sm text-gray-600 mb-3">Click on the diagram to reveal labels in their correct positions.</p>
    <img :src="question.diagramUrl" alt="Diagram" class="w-full h-auto object-contain mb-4" />

    <div
      v-for="label in question.labels"
      :key="label.id"
      :style="{ left: label.x + '%', top: label.y + '%' }"
      class="absolute bg-gray-300 border-2 border-dashed border-gray-400 px-2 py-1 text-xs rounded cursor-pointer hover:bg-yellow-200 transition-colors"
      @click="revealLabel(label.id)"
      v-if="!revealedLabels.has(label.id)"
    ></div>

    <div
      v-for="label in question.labels"
      :key="label.id + '-revealed'"
      :style="{ left: label.x + '%', top: label.y + '%' }"
      class="absolute bg-white border-2 border-green-500 px-2 py-1 text-xs font-medium rounded shadow-lg pointer-events-none"
      v-if="revealedLabels.has(label.id)"
    >
      {{ label.text }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, ref } from 'vue';
import type { LabelledDiagramQuestion } from '../../../types';

interface Props {
  question: LabelledDiagramQuestion;
}

const props = defineProps<Props>();

const revealedLabels = ref<Set<string>>(new Set());

const revealLabel = (id: string) => {
  revealedLabels.value.add(id);
};
</script>

<style scoped>
.labelled-diagram-interactive img {
  max-height: 60vh;
}
</style>
