<template>
  <div class="draggable-wrapper">
    <div
      ref="container"
      class="content-container"
      :style="elementStyle"
      :class="{ 'dragging': isDragging }"
    >
      <div
        ref="dragHandle"
        class="drag-handle"
        @mousedown.stop="startDrag"
        @touchstart.stop="startDrag"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          class="drag-icon"
        >
          <circle cx="12" cy="4" r="2" fill="currentColor"/>
          <circle cx="12" cy="12" r="2" fill="currentColor"/>
          <circle cx="12" cy="20" r="2" fill="currentColor"/>
          <circle cx="4" cy="4" r="2" fill="currentColor"/>
          <circle cx="4" cy="12" r="2" fill="currentColor"/>
          <circle cx="4" cy="20" r="2" fill="currentColor"/>
        </svg>
      </div>
      <div class="slot-wrapper">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DraggableWrapper',
  props: {
    disabled: {
      type: Boolean,
      default: false
    },
    initialX: {
      type: Number,
      default: 0
    },
    initialY: {
      type: Number,
      default: 0
    },
    // Add storage key prop for unique identification
    storageKey: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      isDragging: false,
      currentX: 0,
      currentY: 0,
      initialMouseX: 0,
      initialMouseY: 0
    }
  },
  created() {
    // Load saved position on component creation
    this.loadSavedPosition()
  },
  computed: {
    elementStyle() {
      return {
        transform: `translate(${this.currentX}px, ${this.currentY}px)`,
        transition: this.isDragging ? 'none' : 'transform 0.3s ease'
      }
    }
  },
  methods: {
    startDrag(event) {
      if (this.disabled) return

      event.preventDefault()
      this.isDragging = true

      const touch = event.type === 'touchstart'
      const startEvent = touch ? event.touches[0] : event

      this.initialMouseX = startEvent.clientX - this.currentX
      this.initialMouseY = startEvent.clientY - this.currentY

      const moveEvent = touch ? 'touchmove' : 'mousemove'
      const endEvent = touch ? 'touchend' : 'mouseup'

      document.addEventListener(moveEvent, this.onDrag, { passive: false })
      document.addEventListener(endEvent, this.stopDrag)
    },
    onDrag(event) {
      if (!this.isDragging) return

      event.preventDefault()
      const touch = event.type === 'touchmove'
      const moveEvent = touch ? event.touches[0] : event

      this.currentX = moveEvent.clientX - this.initialMouseX
      this.currentY = moveEvent.clientY - this.initialMouseY

      this.$emit('drag', { x: this.currentX, y: this.currentY })
    },
    // Add method to save position
    savePosition() {
      const position = {
        x: this.currentX,
        y: this.currentY
      }
      localStorage.setItem(`draggable_${this.storageKey}`, JSON.stringify(position))
    },
    // Add method to load position
    loadSavedPosition() {
      const savedPosition = localStorage.getItem(`draggable_${this.storageKey}`)
      if (savedPosition) {
        const { x, y } = JSON.parse(savedPosition)
        this.currentX = x
        this.currentY = y
      } else {
        // Use initial props if no saved position
        this.currentX = this.initialX
        this.currentY = this.initialY
      }
    },
    stopDrag() {
      this.isDragging = false
      document.removeEventListener('mousemove', this.onDrag)
      document.removeEventListener('mouseup', this.stopDrag)
      document.removeEventListener('touchmove', this.onDrag)
      document.removeEventListener('touchend', this.stopDrag)

      // Save position when drag ends
      this.savePosition()

      this.$emit('dragend', { x: this.currentX, y: this.currentY })
    }
  }
}
</script>

<style scoped>
.draggable-wrapper {
  position: relative;
}

.content-container {
  position: relative;
  display: flex;
  gap: 8px;
}

.drag-handle {
  opacity: 0;
  cursor: move;
  padding: 4px;
  color: #666;
  transition: all 0.2s ease;
  align-self: center;
  position: absolute;
  left: -24px;
  top: 50%;
  transform: translateY(-50%);
  user-select: none;
}

.content-container:hover .drag-handle {
  opacity: 1;
}

.drag-handle:hover {
  color: #333;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}

.dragging {
  opacity: 0.95;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.dragging .drag-handle {
  opacity: 1;
}

.slot-wrapper {
  flex: 1;
  /* Allow text selection and interactions inside content */
  user-select: text;
  pointer-events: auto;
}

/* Only disable user interactions when dragging */
.dragging .slot-wrapper {
  user-select: none;
  pointer-events: none;
}
</style>
