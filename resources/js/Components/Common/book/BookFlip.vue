<template>
  <div class="book">
    <div
      v-for="(page, index) in pages"
      :key="index"
      class="page"
      :class="{ flipped: flippedPages.includes(index) }"
      :style="{ zIndex: pages.length - index }"
      @mousedown="startDrag($event, index)"
      @touchstart="startDrag($event, index)"
      ref="pageRefs"
    >
      <div class="front">
        <slot :name="`front-${index}`">
          <div class="p-4">Default Front Content {{index + 1}}</div>
        </slot>
      </div>
      <div class="back">
        <slot :name="`back-${index}`">
          <div class="p-4">Default Back Content {{index + 1}}</div>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

const props = defineProps({
  totalPages: {
    type: Number,
    required: true,
    default: 1
  },
})

const pages = Array.from({ length: props.totalPages }, (_, i) => i)
const flippedPages = ref([])
const pageRefs = ref([])
let dragging = false
let dragStartX = 0
let currentDragIndex = null

function startDrag(event, index) {
  dragging = true
  dragStartX = event.type.includes('mouse') ? event.clientX : event.touches[0].clientX
  currentDragIndex = index

  const moveHandler = (e) => {
    if (!dragging) return
    const x = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX
    const deltaX = x - dragStartX

    const page = pageRefs.value[index]
    if (page) {
      const direction = flippedPages.value.includes(index) ? 1 : -1
      const rotation = Math.min(Math.max(direction * deltaX / 2, -180), 0)
      page.style.transform = `rotateY(${rotation * direction}deg)`
    }
  }

  const endHandler = (e) => {
    dragging = false
    const x = e.type.includes('mouse') ? e.clientX : e.changedTouches[0].clientX
    const deltaX = x - dragStartX
    const threshold = 100
    const isFlipped = flippedPages.value.includes(index)
    const page = pageRefs.value[index]

    if (!isFlipped && deltaX < -threshold) {
      flippedPages.value.push(index)
    } else if (isFlipped && deltaX > threshold) {
      flippedPages.value = flippedPages.value.filter((i) => i !== index)
    } else {
      if (page) page.style.transform = ''
    }

    window.removeEventListener('mousemove', moveHandler)
    window.removeEventListener('mouseup', endHandler)
    window.removeEventListener('touchmove', moveHandler)
    window.removeEventListener('touchend', endHandler)
  }

  window.addEventListener('mousemove', moveHandler)
  window.addEventListener('mouseup', endHandler)
  window.addEventListener('touchmove', moveHandler)
  window.addEventListener('touchend', endHandler)
}

watch(flippedPages, () => {
  nextTick(() => {
    pageRefs.value.forEach((page, i) => {
      if (flippedPages.value.includes(i)) {
        page.classList.add('flipped')
        page.style.transform = ''
      } else {
        page.classList.remove('flipped')
        page.style.transform = ''
      }
    })
  })
})
</script>

<style scoped>
.book {
  position: relative;
  width: 600px;
  height: 400px;
  perspective: 2000px;
  margin: 0 auto;
}

.page {
  position: absolute;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  transition: transform 0.6s ease;
  transform-origin: left;
  cursor: grab;
}

.page.flipped {
  transform: rotateY(-180deg);
  z-index: 0 !important;
}

.page .front,
.page .back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  background: white;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.page .back {
  transform: rotateY(180deg);
}
</style>


