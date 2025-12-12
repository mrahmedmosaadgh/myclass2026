<!-- DropZone.vue -->
<template>
    <div
      ref="zone"
      class="drop-zone"
      :class="{ accepted: dropState === 'accepted', rejected: dropState === 'rejected', pending: dropState === 'pending' }"
    >
      <slot />
      <div v-if="dropState === 'accepted'" class="status">✔</div>
      <div v-if="dropState === 'rejected'" class="status">✖</div>
      <div v-if="dropState === 'pending'" class="status">⏳</div>
    </div>
  </template>

  <script setup>
  import { ref, onMounted, onUnmounted } from 'vue'

  const props = defineProps({
    acceptsDrop: Function,
    activeItem: Object,
  })
  const emit = defineEmits(['drop', 'rejected'])

  const zone = ref(null)
  const dropState = ref(null)

  const handleClick = (e) => {
    if (!props.activeItem?.isPicked) return

    const bounds = zone.value.getBoundingClientRect()
    const inZone =
      e.clientX >= bounds.left &&
      e.clientX <= bounds.right &&
      e.clientY >= bounds.top &&
      e.clientY <= bounds.bottom

    if (!inZone) return

    // Calculate exact drop position with sub-pixel precision
    const zoneX = (e.clientX - bounds.left) / bounds.width * 100
    const zoneY = (e.clientY - bounds.top) / bounds.height * 100
    
    // Visual feedback before accepting
    dropState.value = 'pending'
    
    setTimeout(() => {
      const accepted = props.acceptsDrop({
        ...props.activeItem,
        zoneX,
        zoneY,
        clientX: e.clientX,
        clientY: e.clientY,
        bounds
      })

      if (accepted) {
        emit('drop', { 
          x: zoneX, 
          y: zoneY,
          clientX: e.clientX,
          clientY: e.clientY,
          item: props.activeItem,
          bounds
        })
        dropState.value = 'accepted'
      } else {
        emit('rejected', props.activeItem)
        dropState.value = 'rejected'
      }

      setTimeout(() => {
        dropState.value = null
      }, 1000)
    }, 50) // Small delay for better UX
  }

  onMounted(() => {
    window.addEventListener('click', handleClick)
  })

  onUnmounted(() => {
    window.removeEventListener('click', handleClick)
  })
  </script>

  <style scoped>
  .drop-zone {
    position: relative;
    min-width: 300px;
    min-height: 200px;
    background-color: #eee;
    border: 2px dashed #999;
    border-radius: 6px;
  }
  .accepted {
    border-color: #4caf50;
  }
  .rejected {
    border-color: #f44336;
  }
  .pending {
    border-color: #03a9f4;
  }
  .status {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 20px;
  }
  </style>
