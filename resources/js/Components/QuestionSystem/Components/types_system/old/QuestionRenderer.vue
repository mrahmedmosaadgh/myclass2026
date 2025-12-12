<!-- QuestionRenderer.vue -->
<template>
  <div class="question-renderer w-full max-w-4xl mx-auto p-4 bg-gray-50 rounded-lg shadow-md">
    <component :is="dynamicComponent" :question="question" />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, defineProps } from 'vue';
import type { Question } from './types';

interface Props {
  question: Question;
}

const props = defineProps<Props>();

const dynamicComponent = computed(() => {
  const type = props.question.type;
  return defineAsyncComponent(() => import(`./types/${type}/Main.vue`));
});
</script>

<style scoped>
.question-renderer {
  /* Additional styles if needed */
}
</style>
