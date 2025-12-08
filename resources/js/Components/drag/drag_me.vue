<template>
  <div
    ref="dragElement"
    :class="['draggable-wrapper', { 'dragging': isDragging }]"
    :style="elementStyle"
    @mousedown="startDrag"
    @touchstart="startDrag"
  >
    <slot></slot>
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
    bounds: {
      type: Object,
      default: () => ({
        left: null,
        right: null,
        top: null,
        bottom: null
      })
    }
  },
  data() {
    return {
      isDragging: false,
      currentX: this.initialX,
      currentY: this.initialY,
      initialMouseX: 0,
      initialMouseY: 0
    }
  },
  computed: {
    elementStyle() {
      return {
        transform: `translate(${this.currentX}px, ${this.currentY}px)`,
        cursor: this.disabled ? 'default' : 'move',
        transition: this.isDragging ? 'none' : 'transform 0.3s ease'
      }
    }
  },
  methods: {
    startDrag(event) {
      if (this.disabled) return

      this.isDragging = true
      const touch = event.type === 'touchstart'

      this.initialMouseX = touch ? event.touches[0].clientX : event.clientX
      this.initialMouseY = touch ? event.touches[0].clientY : event.clientY

      document.addEventListener(touch ? 'touchmove' : 'mousemove', this.onDrag)
      document.addEventListener(touch ? 'touchend' : 'mouseup', this.stopDrag)
    },
    onDrag(event) {
      if (!this.isDragging) return

      event.preventDefault()
      const touch = event.type === 'touchmove'

      const currentMouseX = touch ? event.touches[0].clientX : event.clientX
      const currentMouseY = touch ? event.touches[0].clientY : event.clientY

      const deltaX = currentMouseX - this.initialMouseX
      const deltaY = currentMouseY - this.initialMouseY

      let newX = this.currentX + deltaX
      let newY = this.currentY + deltaY

      // Apply bounds if specified
      if (this.bounds.left !== null) newX = Math.max(this.bounds.left, newX)
      if (this.bounds.right !== null) newX = Math.min(this.bounds.right, newX)
      if (this.bounds.top !== null) newY = Math.max(this.bounds.top, newY)
      if (this.bounds.bottom !== null) newY = Math.min(this.bounds.bottom, newY)

      this.currentX = newX
      this.currentY = newY
      this.initialMouseX = currentMouseX
      this.initialMouseY = currentMouseY

      this.$emit('drag', { x: this.currentX, y: this.currentY })
    },
    stopDrag() {
      this.isDragging = false
      document.removeEventListener('mousemove', this.onDrag)
      document.removeEventListener('mouseup', this.stopDrag)
      document.removeEventListener('touchmove', this.onDrag)
      document.removeEventListener('touchend', this.stopDrag)

      this.$emit('dragend', { x: this.currentX, y: this.currentY })
    }
  }
}
</script>

<style scoped>
.draggable-wrapper {
  position: relative;
  user-select: none;
  touch-action: none;
}

.dragging {
  opacity: 0.8;
  z-index: 1000;
}
</style>
