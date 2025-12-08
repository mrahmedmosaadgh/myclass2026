<!-- labelled-diagram/Main.vue -->
<template>
  <div class="labelled-diagram-main p-4 bg-white rounded-lg shadow-sm border">
    <h3 class="text-lg font-semibold mb-3 text-gray-800">{{ question.title || 'Labelled Diagram' }}</h3>
    <p v-if="question.description" class="text-gray-600 mb-4">{{ question.description }}</p>

    <div class="version-selector mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Select Version:</label>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
        <button
          v-for="(version, key) in versions"
          :key="key"
          @click="selectedVersion = key"
          class="p-3 text-sm border rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
          :class="{
            'bg-blue-500 text-white border-blue-500': selectedVersion === key,
            'bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100': selectedVersion !== key
          }"
        >
          {{ version.label }}
        </button>
      </div>
    </div>

    <component :is="versions[selectedVersion].component" :question="question" />
  </div>
</template>

<script setup lang="ts">
import { ref, defineProps } from 'vue';
import type { LabelledDiagramQuestion } from '../../types';
import { Default, DragDrop, Interactive } from './versions/index';

interface Props {
  question: LabelledDiagramQuestion;
}

const props = defineProps<Props>();

const versions = {
  default: { component: Default, label: 'Default' },
  dragdrop: { component: DragDrop, label: 'Drag & Drop' },
  interactive: { component: Interactive, label: 'Interactive' },
};

const selectedVersion = ref(props.question.version || 'default');
</script>

<style scoped>
.labelled-diagram-main {
  /* Additional mobile-first styles */
}
</style>
