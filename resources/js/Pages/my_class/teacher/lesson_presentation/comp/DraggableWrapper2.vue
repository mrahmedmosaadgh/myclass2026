<template>
  <div
    class="draggable-wrapper group relative"
    :style="{
      left: `${modelValue.x}%`,
      top: `${modelValue.y}%`,
      position: 'absolute',
      opacity: modelValue.visible ? 1 : 0.3,
      transform: `scale(${modelValue.visible ? 1 : 0.95})`,
      transition: 'opacity 0.3s ease, transform 0.3s ease',
      zIndex: isDragging ? 50 : 10,
    }"
  >
    <!-- Drag Handle -->
    <div
      v-if="!hideDragHandle"
      @dblclick="$emit('toggle-visibility')"
      :class="[
        'absolute -top-2 -left-2 w-8 h-8 rounded-full flex items-center justify-center cursor-move z-50 transition-all duration-200',
        isDragging ? 'bg-indigo-600' : 'bg-gray-500 hover:bg-gray-600',
        showHandle ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'
      ]"
      @mousedown.stop="startDrag"
      :title="dragTitle"
    >
      <LucideIcon
        :name="dragIconName"
        class="w-4 h-4 text-white"
        :class="{ 'animate-spin': isDragging }"
      />
    </div>

    <!-- Content -->
    <div
      :class="[
        'draggable-content',
        { 'pointer-events-none': isDragging }
      ]"
    >
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
    default: () => ({
      x: 0,
      y: 0,
      visible: true
    })
  },
  hideDragHandle: {
    type: Boolean,
    default: false
  },
  showHandle: {
    type: Boolean,
    default: false
  },
  dragIconName: {
    type: String,
    default: 'move'
  },
  dragTitle: {
    type: String,
    default: 'Drag to move'
  },
  constraints: {
    type: Object,
    default: () => ({
      minX: 0,
      maxX: 90,
      minY: 0,
      maxY: 90
    })
  }
});

const emit = defineEmits(['update:modelValue', 'drag-start', 'drag-end', 'toggle-visibility']);

// Drag state
const isDragging = ref(false);
const dragStartPos = ref({ x: 0, y: 0 });
const elementStartPos = ref({ x: 0, y: 0 });
const canvasRect = ref(null);

const startDrag = (event) => {
  event.preventDefault();
  isDragging.value = true;

  // Get canvas dimensions
  const canvas = event.target.closest('.canvas-area') || document.querySelector('.canvas-area');
  canvasRect.value = canvas.getBoundingClientRect();

  // Store initial positions
  dragStartPos.value = {
    x: event.clientX,
    y: event.clientY
  };
  elementStartPos.value = {
    x: props.modelValue.x,
    y: props.modelValue.y
  };

  // Add event listeners
  document.addEventListener('mousemove', handleDrag);
  document.addEventListener('mouseup', stopDrag);

  emit('drag-start');
};

const handleDrag = (event) => {
  if (!isDragging.value || !canvasRect.value) return;

  // Calculate delta movement in pixels
  const deltaX = event.clientX - dragStartPos.value.x;
  const deltaY = event.clientY - dragStartPos.value.y;

  // Convert delta to percentage of canvas size
  const percentX = (deltaX / canvasRect.value.width) * 100;
  const percentY = (deltaY / canvasRect.value.height) * 100;

  // Calculate new position
  let newX = elementStartPos.value.x + percentX;
  let newY = elementStartPos.value.y + percentY;

  // Apply constraints
  newX = Math.max(props.constraints.minX, Math.min(props.constraints.maxX, newX));
  newY = Math.max(props.constraints.minY, Math.min(props.constraints.maxY, newY));

  // Emit update
  emit('update:modelValue', {
    ...props.modelValue,
    x: newX,
    y: newY
  });
};

const stopDrag = () => {
  isDragging.value = false;
  document.removeEventListener('mousemove', handleDrag);
  document.removeEventListener('mouseup', stopDrag);
  emit('drag-end');
};
</script>

<style scoped>
.draggable-wrapper {
  will-change: transform;
  touch-action: none;
}

.draggable-content {
  min-width: 20px;
  min-height: 20px;
}
</style>
