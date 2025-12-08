<!-- labelled-diagram/versions/DragDrop.vue -->
<template>
  <div class="labelled-diagram-dragdrop max-w-lg mx-auto bg-white p-4 rounded-lg border">
    <p class="text-sm text-gray-600 mb-3">Drag labels from the list to the correct positions on the diagram.</p>

    <div class="relative dropzone mb-3" ref="diagramContainer" @drop.prevent="onDrop" @dragover.prevent>
      <img ref="diagramImg" :src="question.diagramUrl" alt="Diagram" class="w-full h-auto object-contain" @load="updateHeight" />
      <div
        v-for="(placed, index) in placedLabels"
        :key="placed.label.id"
        :style="{ left: placed.x + 'px', top: placed.y + 'px' }"
        class="absolute bg-blue-500 text-white px-2 py-1 text-xs rounded shadow-sm pointer-events-none"
      >
        {{ placed.label.text }}
      </div>
    </div>

    <div class="available-labels border-t pt-3">
      <p class="text-xs text-gray-500 mb-2">Available labels (drag to diagram):</p>
      <div class="flex flex-wrap gap-2">
        <div
          v-for="(label, index) in availableLabels"
          :key="label.id"
          class="bg-gray-200 border border-gray-300 px-2 py-1 text-xs rounded cursor-move select-none"
          draggable="true"
          @dragstart="handleDragStart($event, index)"
        >
          {{ label.text }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, ref, onMounted, onUnmounted } from 'vue';
import type { LabelledDiagramQuestion } from '../../../types';

interface Props {
  question: LabelledDiagramQuestion;
}

const props = defineProps<Props>();

const diagramImg = ref<HTMLImageElement>();
const diagramContainer = ref<HTMLElement>();
const draggedIndex = ref<number | null>(null);
const availableLabels = ref([...props.question.labels]);
const placedLabels = ref<Array<{ label: typeof props.question.labels[0], x: number, y: number }>>([]);

const handleDragStart = (event: DragEvent, index: number) => {
  draggedIndex.value = index;
};

const onDrop = (event: DragEvent) => {
  if (draggedIndex.value === null || !diagramContainer.value) return;

  const rect = diagramContainer.value.getBoundingClientRect();
  const imgRect = diagramImg.value?.getBoundingClientRect();
  if (!imgRect) return;

  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;

  const relativeX = x - (imgRect.left - rect.left);
  const relativeY = y - (imgRect.top - rect.top);

  if (relativeX >= 0 && relativeX <= imgRect.width && relativeY >= 0 && relativeY <= imgRect.height) {
    const label = availableLabels.value.splice(draggedIndex.value, 1)[0];
    placedLabels.value.push({ label, x, y });
  }
  draggedIndex.value = null;
};

const updateHeight = () => {
  // Handle resize if needed
};

onMounted(() => {
  window.addEventListener('resize', updateHeight);
});

onUnmounted(() => {
  window.removeEventListener('resize', updateHeight);
});
</script>

<style scoped>
.labelled-diagram-dragdrop {
  min-height: 300px;
}
</style>
