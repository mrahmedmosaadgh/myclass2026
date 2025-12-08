<template>
    <!-- Original Zone where all boxes start -->
    <div ref="originalZone" class="zone original" @click="handleReturnAll">
    <DraggableBox
      v-for="(b, i) in boxes"
      :key="i"
      :text="b.text"
      :initial-x="b.x"
      :initial-y="b.y"
      :original-zone="originalZone"
      ref="boxRefs"
    />
  </div>

    <!-- Drop Zone -->
    <DropZone
      :activeItem="currentActive"
      :acceptsDrop="acceptsDrop"
      @drop="handleDrop"
      @rejected="handleRejected"
      style="position: absolute; top: 250px; left: 300px"
    >
      Drop Zone
    </DropZone>
  </template>

  <script setup>
  import { ref, computed } from 'vue'
  import DraggableBox from './components/DraggableBox.vue'
  import DropZone from './components/DropZone.vue'

  const boxes = [
    { text: 'Correct', initialX: 100, initialY: 100 },
    { text: 'Wrong', initialX: 200, initialY: 100 },
  ]

  const boxRefs = ref([]) // Box references
  const originalZone = ref(null)

  const currentActive = computed(() =>
    boxRefs.value.find((box) => box?.isPicked)
  )

  const acceptsDrop = (item) => item.text === 'Correct'

  const handleDrop = ({ x, y, item }) => {
    item.drop(x, y)
  }

  const handleRejected = (item) => {
    item.reset()
  }

  const handleOriginalZoneClick = (e) => {
    const bounds = originalZone.value.getBoundingClientRect()
    // Iterate over all boxes and check if they were clicked inside the original zone
    boxRefs.value.forEach((box) => {
      if (!box.isPicked) {
        const inZone =
          e.clientX >= bounds.left &&
          e.clientX <= bounds.right &&
          e.clientY >= bounds.top &&
          e.clientY <= bounds.bottom

        if (inZone && box.returnToOriginalZone) {
          box.returnToOriginalZone() // Now calling the exposed method correctly
        }
      }
    })
  }
  </script>

  <style scoped>
  .original-zone {
    position: absolute;
    top: 50px;
    left: 50px;
    width: 400px;
    height: 200px;
    background-color: #f0f0f0;
    border: 2px dashed #999;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .original-zone p {
    text-align: center;
    font-size: 18px;
  }
  </style>
