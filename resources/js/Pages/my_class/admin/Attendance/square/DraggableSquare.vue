<template>
    <!-- The original square to pick up -->
    <div v-if="!isPicked" class="draggable" @click="pickUp" :style="{ top: startY + 'px', left: startX + 'px' }">
      {{ text }}
    </div>

    <!-- Follows the cursor -->
    <div v-if="isPicked" class="custom-cursor" :style="cursorStyle">
      {{ text }}
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue'

  const props = defineProps({
    text: String,
    startX: { type: Number, default: 100 },
    startY: { type: Number, default: 100 },
  })

  const emit = defineEmits(['dropped'])

  const x = ref(0)
  const y = ref(0)
  const isPicked = ref(false)

  const updateMouse = (e) => {
    x.value = e.clientX
    y.value = e.clientY
  }

  onMounted(() => {
    window.addEventListener('mousemove', updateMouse)
  })

  onUnmounted(() => {
    window.removeEventListener('mousemove', updateMouse)
  })

  const pickUp = () => {
    isPicked.value = true
    document.body.style.cursor = 'none'
  }

  const drop = () => {
    if (isPicked.value) {
      isPicked.value = false
      emit('dropped', { x: x.value, y: y.value })
      document.body.style.cursor = ''
    }
  }

  const cursorStyle = computed(() => ({
    position: 'fixed',
    top: `${y.value - 25}px`,
    left: `${x.value - 25}px`,
    width: '50px',
    height: '50px',
    backgroundColor: '#2196f3',
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
  .draggable {
    position: absolute;
    width: 50px;
    height: 50px;
    background-color: #2196f3;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    border-radius: 4px;
    cursor: pointer;
  }
  </style>
