<!-- labelled-diagram/versions/Default.vue -->
<template>
  <div class="labelled-diagram-default relative max-w-lg mx-auto bg-white p-4 rounded-lg border">
    <img :src="question.diagramUrl" alt="Diagram" class="w-full h-auto object-contain mb-4" />
    <div class="absolute inset-0" v-if="question.labels?.length">
      <span
        v-for="label in question.labels"
        :key="label.id"
        :style="{ left: label.x + '%', top: label.y + '%' }"
        class="absolute bg-white border border-gray-400 px-2 py-1 text-sm font-medium rounded shadow-md"
      >
        {{ label.text }}
      </span>
    </div>
    <div v-for="match in question.correctMatches || []" :key="match.labelId" class="text-xs text-green-600 mt-1">
      Match: {{ match.labelId }} → {{ match.targetId }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';
import type { LabelledDiagramQuestion } from '../../../types';

interface Props {
  question: LabelledDiagramQuestion;
}

defineProps<Props>();
</script>

<style scoped>
.labelled-diagram-default img {
  max-height: 60vh;
}
</style>
