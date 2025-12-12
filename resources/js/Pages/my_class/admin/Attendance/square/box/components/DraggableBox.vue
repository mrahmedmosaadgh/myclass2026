<template>
    <!-- Static box -->
    <div
      v-if="!isPicked"
      class="box"
      :style="{ top: `${positionY}px`, left: `${positionX}px` }"
      @mousedown="startDrag"
      @dblclick.stop="returnToOriginalZone"
    >
      {{ text }}
    </div>

    <!-- Floating box (while picked) -->
    <div
      v-else
      class="box dragging"
      :style="draggingStyle"
    >
      {{ text }}
    </div>
  </template>

  <script setup>
  import { ref, computed, onUnmounted, defineExpose } from 'vue'

  const props = defineProps({
    text: String,
    initialX: Number,
    initialY: Number,
    originalZone: Object,
  })

  const isPicked = ref(false)
  const positionX = ref(props.initialX)
  const positionY = ref(props.initialY)
  const clickOffset = ref({ x: 0, y: 0 })
  const mouse = ref({ x: 0, y: 0 })
  const lastPositions = ref([])

  const startDrag = (e) => {
    const rect = e.target.getBoundingClientRect()
    clickOffset.value = {
      x: e.clientX - rect.left,
      y: e.clientY - rect.top,
    }

    lastPositions.value = []
    isPicked.value = true

    document.addEventListener('mousemove', updateMouse, { passive: true })
    document.addEventListener('mouseup', stopDrag, { passive: true })
  }

  const updateMouse = (e) => {
    mouse.value = { x: e.clientX, y: e.clientY }
    lastPositions.value.push({ x: e.clientX, y: e.clientY, time: Date.now() })
    if (lastPositions.value.length > 5) lastPositions.value.shift()
  }

  const stopDrag = (e) => {
    document.removeEventListener('mousemove', updateMouse)
    document.removeEventListener('mouseup', stopDrag)

    // Apply drag result
    isPicked.value = false
    positionX.value = mouse.value.x - clickOffset.value.x
    positionY.value = mouse.value.y - clickOffset.value.y

    // Optional: slight momentum
    if (lastPositions.value.length > 1) {
      const first = lastPositions.value[0]
      const last = lastPositions.value.at(-1)
      const dt = last.time - first.time || 1
      const velocityX = (last.x - first.x) / dt
      const velocityY = (last.y - first.y) / dt
      positionX.value += velocityX * 10
      positionY.value += velocityY * 10
    }

    // Snap back if inside original zone
    if (props.originalZone) {
      const zone = props.originalZone.getBoundingClientRect()
      if (
        e.clientX >= zone.left && e.clientX <= zone.right &&
        e.clientY >= zone.top && e.clientY <= zone.bottom
      ) {
        returnToOriginalZone()
      }
    }
  }

  const returnToOriginalZone = () => {
    positionX.value = props.initialX
    positionY.value = props.initialY
  }

  const draggingStyle = computed(() => ({
    position: 'fixed',
    top: `${mouse.value.y - clickOffset.value.y}px`,
    left: `${mouse.value.x - clickOffset.value.x}px`,
    zIndex: 9999,
    pointerEvents: 'none',
  }))

  onUnmounted(() => {
    document.removeEventListener('mousemove', updateMouse)
    document.removeEventListener('mouseup', stopDrag)
  })

  defineExpose({
    isPicked,
    text: props.text,
    drop: stopDrag,
    reset: returnToOriginalZone,
    returnToOriginalZone,
  })
  </script>

  <style scoped>
  .box {
    position: absolute;
    width: 50px;
    height: 50px;
    background-color: #4caf50;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    cursor: grab;
    user-select: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }

  .box:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  }

  .box.dragging {
    cursor: grabbing;
    opacity: 0.9;
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    transform: scale(1.05);
  }
  </style>
