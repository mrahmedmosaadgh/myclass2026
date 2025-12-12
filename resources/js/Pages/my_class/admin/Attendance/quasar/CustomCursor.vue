<template>
    <div v-if="visible" :style="cursorStyle" class="custom-cursor">
      {{ text }}
    </div>
  </template>

  <script setup>
  import { ref, onMounted, onUnmounted, computed } from 'vue'

  const props = defineProps({
    text: {
      type: String,
      default: 'Click',
    },
    size: {
      type: Number,
      default: 50,
    },
  })

  const x = ref(0)
  const y = ref(0)
  const visible = ref(true)

  const updatePosition = (e) => {
    x.value = e.clientX
    y.value = e.clientY
  }

  onMounted(() => {
    document.body.style.cursor = 'none'
    window.addEventListener('mousemove', updatePosition)
  })

  onUnmounted(() => {
    document.body.style.cursor = ''
    window.removeEventListener('mousemove', updatePosition)
  })

  const cursorStyle = computed(() => ({
    position: 'fixed',
    top: `${y.value - props.size / 2}px`,
    left: `${x.value - props.size / 2}px`,
    width: `${props.size}px`,
    height: `${props.size}px`,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    pointerEvents: 'none',
    backgroundColor: '#333',
    color: 'white',
    fontSize: '14px',
    fontWeight: 'bold',
    borderRadius: '4px',
    zIndex: 9999,
  }))
  </script>

  <style scoped>
  .custom-cursor {
    transition: transform 0.05s ease;
    user-select: none;
  }
  </style>
