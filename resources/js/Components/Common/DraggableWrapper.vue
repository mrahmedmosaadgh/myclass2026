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
      v-if="!hideDragHandle && !readonly"
      @dblclick="$emit('toggle-visibility')"
      :class="[
        'absolute -top-2 -left-2 w-8 h-8 rounded-full flex items-center justify-center cursor-move z-50 transition-all duration-200',
        isDragging ? 'bg-indigo-600' : 'bg-gray-500 hover:bg-gray-600',
        modelValue.visible ? 'opacity-0 group-hover:opacity-100' : 'opacity-60'
      ]"
      @mousedown.stop="startDrag"
      :title="dragTitle"
    >
      <LucideIcon
        :name="dragIconName"
        :size="16"
        class="text-white"
      />
    </div>

    <!-- Element Controls -->
    <div
      v-if="!readonly"
      :class="[
        'absolute -top-2 -right-2 flex gap-1 z-50 transition-all duration-200',
        modelValue.visible ? 'opacity-0 group-hover:opacity-100' : 'opacity-60'
      ]"
    >
      <!-- Visibility Toggle -->
      <button
        @click="$emit('toggle-visibility')"
        class="control-button"
        :title="modelValue.visible ? 'Hide Element' : 'Show Element'"
      >
        <LucideIcon
          :name="modelValue.visible ? 'eye' : 'eye-off'"
          :size="16"
          class="text-gray-600"
        />
      </button>

      <!-- Settings Button -->
      <button
        v-if="!hideSettings"
        @click="$emit('open-settings')"
        class="control-button"
        title="Element Settings"
      >
        <LucideIcon
          name="settings"
          :size="16"
          class="text-gray-600"
        />
      </button>

      <!-- Delete Button -->
      <button
        v-if="!hideDelete"
        @click="$emit('delete-element')"
        class="control-button"
        title="Delete Element"
      >
        <LucideIcon
          name="trash-2"
          :size="16"
          class="text-gray-600"
        />
      </button>
    </div>

    <!-- Content Slot -->
    <slot></slot>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import LucideIcon from './LucideIcon.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  },
  readonly: {
    type: Boolean,
    default: false
  },
  hideDragHandle: {
    type: Boolean,
    default: false
  },
  hideSettings: {
    type: Boolean,
    default: false
  },
  hideDelete: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits([
  'update:modelValue',
  'toggle-visibility',
  'open-settings',
  'delete-element'
]);

const isDragging = ref(false);
const dragStartPos = ref({ x: 0, y: 0 });

const dragIconName = computed(() => isDragging.value ? 'move' : 'grip');
const dragTitle = computed(() => isDragging.value ? 'Release to drop' : 'Drag to move');

const startDrag = (event) => {
  if (props.readonly || !props.modelValue.visible) return;

  isDragging.value = true;
  dragStartPos.value = {
    x: event.clientX,
    y: event.clientY
  };

  document.addEventListener('mousemove', handleDrag);
  document.addEventListener('mouseup', stopDrag);
};

const handleDrag = (event) => {
  if (!isDragging.value) return;

  const dx = event.clientX - dragStartPos.value.x;
  const dy = event.clientY - dragStartPos.value.y;

  // Convert pixel movement to percentage of container
  const container = event.target.closest('.canvas-editor');
  if (!container) return;

  const containerRect = container.getBoundingClientRect();
  const xPercent = (dx / containerRect.width) * 100;
  const yPercent = (dy / containerRect.height) * 100;

  emit('update:modelValue', {
    ...props.modelValue,
    x: Math.max(0, Math.min(100, props.modelValue.x + xPercent)),
    y: Math.max(0, Math.min(100, props.modelValue.y + yPercent))
  });

  dragStartPos.value = {
    x: event.clientX,
    y: event.clientY
  };
};

const stopDrag = () => {
  isDragging.value = false;
  document.removeEventListener('mousemove', handleDrag);
  document.removeEventListener('mouseup', stopDrag);
};
</script>

<style scoped>
.draggable-wrapper {
  touch-action: none;
}

.control-button {
  @apply w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm
         hover:bg-gray-100 transition-colors duration-200;
}

.control-button:hover {
  @apply bg-gray-100;
}
</style>

