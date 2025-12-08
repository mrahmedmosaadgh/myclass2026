<template>
    <!-- Target Drop Area -->
    <!-- <div ref="dropZone" class="drop-zone">
      Drop Here
    </div> -->
<DropZone_use />
    <!-- Box at starting position (before pick-up) -->
    <div
      v-if="!isPicked && !dropped"
      class="box"
      :style="{ top: `${initialY}px`, left: `${initialX}px` }"
      @click.stop="pickUp"
    >
      {{ text }}
    </div>

    <!-- Box after successful drop -->
    <div
      v-if="dropped"
      class="box"
      :style="{ top: `${dropY}px`, left: `${dropX}px` }"
      @click.stop="pickUp"
    >
      {{ text }}
    </div>

    <!-- Box following cursor -->
    <div v-if="isPicked" :style="cursorStyle" class="box">
      {{ text }}
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue'
  import DropZone_use from './DropZone_use.vue'
  const props = defineProps({
    text: { type: String, default: 'Box' },
    initialX: { type: Number, default: 100 },
    initialY: { type: Number, default: 100 },
  })

  const dropZone = ref(null)
  const isPicked = ref(false)
  const dropped = ref(false)
  const dropX = ref(0)
  const dropY = ref(0)
  const mouseX = ref(0)
  const mouseY = ref(0)

  const updateMouse = (e) => {
    mouseX.value = e.clientX
    mouseY.value = e.clientY
  }

  onMounted(() => {
    window.addEventListener('mousemove', updateMouse)
    window.addEventListener('click', handleGlobalClick)
  })

  onUnmounted(() => {
    window.removeEventListener('mousemove', updateMouse)
    window.removeEventListener('click', handleGlobalClick)
  })

  const pickUp = () => {
    isPicked.value = true
    dropped.value = false
    document.body.style.cursor = 'none'
  }

  const handleGlobalClick = () => {
    if (!isPicked.value) return

    const zone = dropZone.value?.getBoundingClientRect()
    const insideDropZone =
      zone &&
      mouseX.value >= zone.left &&
      mouseX.value <= zone.right &&
      mouseY.value >= zone.top &&
      mouseY.value <= zone.bottom

    if (insideDropZone) {
      dropX.value = mouseX.value - 25
      dropY.value = mouseY.value - 25
      dropped.value = true
      isPicked.value = false
      document.body.style.cursor = ''
    }
  }

  const cursorStyle = computed(() => ({
    position: 'fixed',
    top: `${mouseY.value - 25}px`,
    left: `${mouseX.value - 25}px`,
    width: '50px',
    height: '50px',
    backgroundColor: '#4caf50',
    color: 'white',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontWeight: 'bold',
    borderRadius: '4px',
    zIndex: 9999,
    pointerEvents: 'none',
  }))
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
    font-weight: bold;
    border-radius: 4px;
    cursor: pointer;
  }
  .drop-zone {
    position: absolute;
    top: 200px;
    left: 200px;
    width: 200px;
    height: 150px;
    background-color: #2196f3;
    color: white;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
  }
  </style>
