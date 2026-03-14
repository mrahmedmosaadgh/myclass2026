<template>
  <div
    class="draggable-element absolute"
    :class="{ 'selected': isSelected }"
    :style="{
      left: element.x + 'px',
      top: element.y + 'px',
      width: element.width + 'px',
      height: element.height + 'px',
      transform: `rotate(${element.rotation}deg)`,
      zIndex: isSelected ? 1000 : 1
    }"
    @mousedown="startDrag"
    @click.stop="$emit('select')"
  >
    <TextElement
      v-if="element.type === 'text'"
      :element="element"
      :is-selected="isSelected"
      @update="updates => $emit('update', updates)"
      @select="$emit('select')"
      @delete="$emit('delete')"
      @move-to-front="$emit('move-to-front')"
      @move-to-back="$emit('move-to-back')"
    />
    
    <ImageElement
      v-else-if="element.type === 'image'"
      :element="element"
      :is-selected="isSelected"
      @update="updates => $emit('update', updates)"
      @select="$emit('select')"
      @delete="$emit('delete')"
      @move-to-front="$emit('move-to-front')"
      @move-to-back="$emit('move-to-back')"
    />
    
    <div
      v-else-if="element.type === 'shape'"
      class="w-full h-full"
      :class="{
        'rounded': element.shapeType === 'rectangle',
        'rounded-full': element.shapeType === 'circle'
      }"
      :style="{
        backgroundColor: element.fillColor,
        border: `${element.strokeWidth}px solid ${element.strokeColor}`
      }"
    >
      <div v-if="element.shapeType === 'arrow'" class="w-full h-full flex items-center justify-center">
        <svg viewBox="0 0 24 24" class="w-8 h-8" :fill="element.fillColor">
          <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>
        </svg>
      </div>
    </div>
    
    <!-- Selection handles when selected -->
    <div v-if="isSelected" class="selection-handles">
      <!-- Corner handles -->
      <div 
        v-for="handle in cornerHandles" 
        :key="handle.position"
        class="handle-corner"
        :class="`handle-${handle.position}`"
        @mousedown.stop="startResize(handle.position)"
      ></div>
      
      <!-- Rotation handle -->
      <div 
        class="handle-rotation"
        @mousedown.stop="startRotate"
      ></div>
      
      <!-- Delete button -->
      <button
        class="delete-button"
        @click.stop="$emit('delete')"
        title="Delete element"
      >
        ×
      </button>
      
      <!-- Layer buttons -->
      <div class="layer-buttons">
        <button 
          @click.stop="$emit('move-to-front')"
          title="Move to front"
          class="layer-btn"
        >
          ↗
        </button>
        <button 
          @click.stop="$emit('move-to-back')"
          title="Move to back"
          class="layer-btn"
        >
          ↙
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ImageElement from './ImageElement.vue';
import TextElement from './TextElement.vue';

const props = defineProps({
  element: {
    type: Object,
    required: true
  },
  isSelected: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update', 'select', 'delete', 'move-to-front', 'move-to-back']);

const isEditing = ref(false);
const textElement = ref(null);
const isDragging = ref(false);
const isResizing = ref(false);
const isRotating = ref(false);

// Handle positions
const cornerHandles = computed(() => [
  { position: 'nw' }, { position: 'ne' },
  { position: 'sw' }, { position: 'se' }
]);

// Drag functionality
let dragStartX, dragStartY, elementStartX, elementStartY;

const startDrag = (event) => {
  if (isEditing.value) return;
  
  isDragging.value = true;
  dragStartX = event.clientX;
  dragStartY = event.clientY;
  elementStartX = props.element.x;
  elementStartY = props.element.y;
  
  document.addEventListener('mousemove', handleDrag);
  document.addEventListener('mouseup', stopDrag);
  
  event.preventDefault();
};

const handleDrag = (event) => {
  if (!isDragging.value) return;
  
  const deltaX = event.clientX - dragStartX;
  const deltaY = event.clientY - dragStartY;
  
  emit('update', {
    x: elementStartX + deltaX,
    y: elementStartY + deltaY
  });
};

const stopDrag = () => {
  isDragging.value = false;
  document.removeEventListener('mousemove', handleDrag);
  document.removeEventListener('mouseup', stopDrag);
};

// Resize functionality
let resizeStartX, resizeStartY, startWidth, startHeight;

const startResize = (handlePosition) => {
  isResizing.value = true;
  resizeStartX = event.clientX;
  resizeStartY = event.clientY;
  startWidth = props.element.width;
  startHeight = props.element.height;
  
  document.addEventListener('mousemove', (e) => handleResize(e, handlePosition));
  document.addEventListener('mouseup', stopResize);
  
  event.stopPropagation();
};

const handleResize = (event, handlePosition) => {
  if (!isResizing.value) return;
  
  const deltaX = event.clientX - resizeStartX;
  const deltaY = event.clientY - resizeStartY;
  
  let newWidth = startWidth;
  let newHeight = startHeight;
  
  // Adjust dimensions based on handle position
  switch (handlePosition) {
    case 'se': // South East
      newWidth = Math.max(20, startWidth + deltaX);
      newHeight = Math.max(20, startHeight + deltaY);
      break;
    case 'sw': // South West
      newWidth = Math.max(20, startWidth - deltaX);
      newHeight = Math.max(20, startHeight + deltaY);
      break;
    case 'ne': // North East
      newWidth = Math.max(20, startWidth + deltaX);
      newHeight = Math.max(20, startHeight - deltaY);
      break;
    case 'nw': // North West
      newWidth = Math.max(20, startWidth - deltaX);
      newHeight = Math.max(20, startHeight - deltaY);
      break;
  }
  
  emit('update', {
    width: newWidth,
    height: newHeight
  });
};

const stopResize = () => {
  isResizing.value = false;
  document.removeEventListener('mousemove', handleResize);
  document.removeEventListener('mouseup', stopResize);
};

// Rotate functionality
let rotateStartX, rotateStartY, startRotation;

const startRotate = (event) => {
  isRotating.value = true;
  const rect = event.currentTarget.parentElement.getBoundingClientRect();
  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 2;
  
  startRotation = props.element.rotation || 0;
  rotateStartX = event.clientX - centerX;
  rotateStartY = event.clientY - centerY;
  
  document.addEventListener('mousemove', handleRotate);
  document.addEventListener('mouseup', stopRotate);
  
  event.stopPropagation();
};

const handleRotate = (event) => {
  if (!isRotating.value) return;
  
  const rect = event.currentTarget.parentElement.getBoundingClientRect();
  const centerX = rect.left + rect.width / 2;
  const centerY = rect.top + rect.height / 2;
  
  const currentX = event.clientX - centerX;
  const currentY = event.clientY - centerY;
  
  const angle = Math.atan2(currentY, currentX) - Math.atan2(rotateStartY, rotateStartX);
  const degrees = (angle * 180) / Math.PI;
  
  emit('update', {
    rotation: (startRotation + degrees) % 360
  });
};

const stopRotate = () => {
  isRotating.value = false;
  document.removeEventListener('mousemove', handleRotate);
  document.removeEventListener('mouseup', stopRotate);
};

// Text editing
const enableTextEdit = () => {
  isEditing.value = true;
  setTimeout(() => {
    if (textElement.value) {
      textElement.value.focus();
      document.execCommand('selectAll', false, null);
    }
  }, 0);
};

const disableTextEdit = () => {
  isEditing.value = false;
};

const updateTextContent = (event) => {
  emit('update', {
    content: event.target.textContent
  });
};

// Image handling
const handleImageError = (event) => {
  event.target.src = 'https://placehold.co/200x100?text=Image+Error';
};
</script>

<style scoped>
.draggable-element {
  cursor: move;
  user-select: none;
}

.draggable-element.selected {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.selection-handles {
  position: absolute;
  top: -10px;
  left: -10px;
  right: -10px;
  bottom: -10px;
  pointer-events: none;
}

.handle-corner {
  position: absolute;
  width: 12px;
  height: 12px;
  background: #3b82f6;
  border: 2px solid white;
  border-radius: 50%;
  pointer-events: all;
  cursor: nwse-resize;
}

.handle-nw { top: -6px; left: -6px; }
.handle-ne { top: -6px; right: -6px; cursor: nesw-resize; }
.handle-sw { bottom: -6px; left: -6px; cursor: nesw-resize; }
.handle-se { bottom: -6px; right: -6px; }

.handle-rotation {
  position: absolute;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  width: 12px;
  height: 12px;
  background: #3b82f6;
  border: 2px solid white;
  border-radius: 50%;
  pointer-events: all;
  cursor: grab;
}

.handle-rotation:hover {
  cursor: grabbing;
}

.delete-button {
  position: absolute;
  top: -15px;
  right: -15px;
  width: 20px;
  height: 20px;
  background: #ef4444;
  color: white;
  border: 2px solid white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 14px;
  pointer-events: all;
  cursor: pointer;
}

.layer-buttons {
  position: absolute;
  bottom: -35px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 2px;
  pointer-events: all;
}

.layer-btn {
  width: 20px;
  height: 20px;
  background: #6b7280;
  color: white;
  border: 2px solid white;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  cursor: pointer;
}

[contenteditable]:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>