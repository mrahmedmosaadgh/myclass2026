<template>
    <div
      ref="zone"
      class="drop-zone"
      :class="{ 'accepted': dropState === 'accepted', 'rejected': dropState === 'rejected' }"
    >
      <slot />
      <div v-if="dropState === 'accepted'" class="status">✔</div>
      <div v-if="dropState === 'rejected'" class="status">✖</div>
    </div>
  </template>

  <script setup>
  import { ref, watch, onMounted, onUnmounted } from 'vue'

  const props = defineProps({
    acceptsDrop: {
      type: Function,
      default: () => true, // Accepts all by default
    },
    activeItem: Object, // Info about the item being dragged (if needed)
  })

  const emit = defineEmits(['drop', 'rejected'])

  const dropState = ref(null) // null, 'accepted', 'rejected'
  const zone = ref(null)

  const handleClick = (e) => {
    if (!props.activeItem?.isPicked) return

    const bounds = zone.value.getBoundingClientRect()
    const inZone =
      e.clientX >= bounds.left &&
      e.clientX <= bounds.right &&
      e.clientY >= bounds.top &&
      e.clientY <= bounds.bottom

    if (!inZone) return

    const accepted = props.acceptsDrop(props.activeItem)

    if (accepted) {
      emit('drop', { x: e.clientX, y: e.clientY, item: props.activeItem })
      dropState.value = 'accepted'
    } else {
      emit('rejected', props.activeItem)
      dropState.value = 'rejected'
    }

    setTimeout(() => {
      dropState.value = null
    }, 1000)
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
    background-color: #e0e0e0;
    border: 2px dashed #999;
    border-radius: 8px;
    min-height: 100px;
    min-width: 150px;
    transition: border-color 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .accepted {
    border-color: #4caf50;
  }

  .rejected {
    border-color: #f44336;
  }

  .status {
    position: absolute;
    top: 4px;
    right: 6px;
    font-size: 20px;
    font-weight: bold;
  }
  </style>
