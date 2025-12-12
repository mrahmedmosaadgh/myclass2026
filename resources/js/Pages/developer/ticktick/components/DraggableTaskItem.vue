<template>
  <div
    class="draggable-task-item"
    :class="{ 'drag-over': isDragOver }"
    draggable="true"
    @dragstart="onDragStart"
    @dragover.prevent="onDragOver"
    @dragleave="onDragLeave"
    @drop.prevent="onDrop"
    @dragend="onDragEnd"
  >
    <slot></slot>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  task: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    required: true
  },
  parentId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['reorder']);

const isDragOver = ref(false);

// Store drag data
const onDragStart = (event) => {
  // Set the data to be transferred
  event.dataTransfer.effectAllowed = 'move';
  event.dataTransfer.setData('text/plain', JSON.stringify({
    id: props.task.id,
    index: props.index,
    parentId: props.parentId
  }));

  // Add a class to the element being dragged
  event.target.classList.add('dragging');
};

const onDragOver = (event) => {
  // Indicate that the element can be dropped here
  event.dataTransfer.dropEffect = 'move';
  isDragOver.value = true;
};

const onDragLeave = () => {
  isDragOver.value = false;
};

const onDrop = (event) => {
  isDragOver.value = false;

  // Get the dragged task data
  const dragData = JSON.parse(event.dataTransfer.getData('text/plain'));
  const fromIndex = dragData.index;
  const toIndex = props.index;
  const fromParentId = dragData.parentId;
  const toParentId = props.parentId;

  // Only reorder if indices are different or if moving between different parents
  if (fromIndex !== toIndex || fromParentId !== toParentId) {
    emit('reorder', {
      fromIndex,
      toIndex,
      fromParentId,
      toParentId
    });
  }
};

const onDragEnd = (event) => {
  // Remove the dragging class
  event.target.classList.remove('dragging');
  isDragOver.value = false;
};
</script>

<style scoped>
.draggable-task-item {
  cursor: move;
  position: relative;
}

.draggable-task-item.dragging {
  opacity: 0.5;
}

.draggable-task-item.drag-over {
  border-top: 2px solid var(--q-primary);
}
</style>
