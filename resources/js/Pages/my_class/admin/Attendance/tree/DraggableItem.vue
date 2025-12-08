<template>
    <div
      :draggable="isDraggable"
      @dragstart="isDraggable ? onDragStart($event) : null"
      @dragover.prevent="isDraggable ? onDragOver($event) : null"
      @dragleave="isDraggable ? onDragLeave($event) : null"
      @drop="isDraggable ? onDrop($event) : null"
      :class="{ 'drop-hover': isHovering }"
      :data-id="id"
    >
      <slot />
    </div>
  </template>

  <script setup>
  import { ref, computed } from 'vue'

  const props = defineProps({
    id: {
      type: [String, Number],
      required: true
    },
    draggable: {
      type: Boolean,
      default: true
    }
  })

  const emit = defineEmits(['dragstart', 'drop', 'dragover', 'dragleave'])

  const isHovering = ref(false)

  // If you want to enable/disable dynamically based on prop
  const isDraggable = computed(() => props.draggable)

  function onDragStart(event) {
    event.dataTransfer.setData('text/plain', props.id)
    emit('dragstart', props.id)
  }

  function onDragOver(event) {
    isHovering.value = true
    emit('dragover', props.id)
  }

  function onDragLeave() {
    isHovering.value = false
    emit('dragleave', props.id)
  }

  function onDrop(event) {
    const draggedId = event.dataTransfer.getData('text/plain')
    isHovering.value = false
    emit('drop', { draggedId, targetId: props.id })
  }
  </script>

  <style scoped>
  .drop-hover {
    background-color: rgba(0, 150, 255, 0.1);
    border: 1px dashed #0096ff;
  }
  </style>
