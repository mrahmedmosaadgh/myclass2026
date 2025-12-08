<template>
    <div class="drag-drop-container" @mousedown="handleMouseDown">
      <slot></slot>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue';

  const isDragging = ref(false);
  const draggedItem = ref(null);

  const handleMouseDown = (event) => {
    isDragging.value = true;
    draggedItem.value = event.target; // This is just a basic example, you may want to add more logic here
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
  };

  const handleMouseMove = (event) => {
    if (isDragging.value && draggedItem.value) {
      draggedItem.value.style.left = `${event.pageX}px`;
      draggedItem.value.style.top = `${event.pageY}px`;
    }
  };

  const handleMouseUp = () => {
    isDragging.value = false;
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', handleMouseUp);
  };
  </script>

  <style scoped>
  .drag-drop-container {
    position: relative;
    width: 100%;
    height: 100%;
  }
  </style>
