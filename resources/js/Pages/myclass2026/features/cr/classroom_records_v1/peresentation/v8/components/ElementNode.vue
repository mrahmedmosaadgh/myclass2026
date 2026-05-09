<script setup>
import { computed } from 'vue'
import { usePresentationStore } from '../stores/presentationStore.js'
import { useUIStore } from '../stores/uiStore.js'
import { useDrag } from '../composables/useDrag.js'
import { useResize } from '../composables/useResize.js'

const props = defineProps({
  element: Object,
  isPresentMode: {
    type: Boolean,
    default: false
  }
})

const presentation = usePresentationStore()
const ui = useUIStore()

// Computed properties
const isSelected = computed(() => {
  return ui.selectedElementId === props.element.id && !props.isPresentMode
})

// Visibility logic for present mode
const opacity = computed(() => {
  if (!props.isPresentMode) return 1
  
  const el = props.element
  
  if (el.visibilityOption === 'hidden-clickable') {
    return el.isVisible !== false ? 1 : (el.hiddenOpacity || 0.05)
  }
  
  if (el.visibilityOption === 'shown-clickable') {
    return el.isVisible !== false ? 1 : 0.1
  }
  
  return 1
})

const pointerEvents = computed(() => {
  if (props.isPresentMode) {
    return props.element.visibilityOption === 'no-interaction' ? 'none' : 'auto'
  }
  return 'auto'
})

const cursor = computed(() => {
  if (props.isPresentMode) {
    if (props.element.visibilityOption === 'moveable') return 'move'
    if (props.element.visibilityOption === 'hidden-clickable' || 
        props.element.visibilityOption === 'shown-clickable') return 'pointer'
    return 'default'
  }
  return 'move'
})

// Methods
function update(changes) {
  presentation.updateElement({
    id: props.element.id,
    changes
  })
}

function handleClick(e) {
  if (props.isPresentMode) {
    const el = props.element
    
    if (el.visibilityOption === 'hidden-clickable') {
      update({ isVisible: !el.isVisible })
    }
    
    if (el.visibilityOption === 'shown-clickable') {
      update({ isVisible: !el.isVisible })
    }
    
    return
  }
  
  // Edit mode - select element
  ui.selectElement(props.element.id)
}

function handleContextMenu(e) {
  if (props.isPresentMode) return
  
  e.preventDefault()
  e.stopPropagation()
  
  ui.showContextMenu(e.clientX, e.clientY, props.element.id)
}

// Drag and resize
const { startDrag } = useDrag(props.element, update)
const { startResize, getCursorForDirection } = useResize(props.element, update)

// Visibility badge for present mode
const visibilityBadge = computed(() => {
  if (!props.isPresentMode) return null
  
  const el = props.element
  
  switch (el.visibilityOption) {
    case 'hidden-clickable':
      return el.isVisible === false ? 'HIDDEN' : null
    case 'shown-clickable':
      return el.isVisible === false ? 'HIDDEN' : null
    case 'moveable':
      return 'MOVEABLE'
    default:
      return null
  }
})

const badgeColor = computed(() => {
  if (!visibilityBadge.value) return null
  
  const el = props.element
  
  switch (el.visibilityOption) {
    case 'hidden-clickable':
      return '#f59e0b' // Amber
    case 'shown-clickable':
      return '#10b981' // Emerald
    case 'moveable':
      return '#6366f1' // Indigo
    default:
      return null
  }
})
</script>

<template>
  <div
    class="element-node"
    :class="{ 
      selected: isSelected,
      'present-mode': isPresentMode,
      'moveable': isPresentMode && element.visibilityOption === 'moveable'
    }"
    :style="{
      transform: `translate(${element.x}px, ${element.y}px)`,
      width: element.width + 'px',
      height: element.height + 'px',
      zIndex: element.zIndex,
      opacity,
      pointerEvents,
      cursor
    }"
    @mousedown.stop="!isPresentMode && (ui.selectElement(element.id), startDrag($event))"
    @click.stop="handleClick"
    @contextmenu="handleContextMenu"
  >
    <!-- TEXT Element -->
    <div 
      v-if="element.type === 'text'" 
      class="element-content text-element"
      :style="{
        fontSize: element.fontSize + 'px',
        color: element.color,
        fontFamily: element.fontFamily || 'ui-sans-serif, system-ui, sans-serif',
        fontWeight: element.fontWeight || 'normal',
        textAlign: element.textAlign || 'left',
        lineHeight: element.lineHeight || '1.4'
      }"
      v-html="element.content"
    />
    
    <!-- IMAGE Element -->
    <img
      v-else-if="element.type === 'image'"
      :src="element.src"
      class="element-content image-element"
      :style="{
        objectFit: element.objectFit || 'cover',
        borderRadius: element.borderRadius || '0px'
      }"
      @error="$event.target.style.display = 'none'"
    />
    
    <!-- RECTANGLE Element -->
    <div
      v-else-if="element.type === 'rectangle'"
      class="element-content rectangle-element"
      :style="{
        backgroundColor: element.backgroundColor || '#ddd',
        border: element.border || 'none',
        borderRadius: element.borderRadius || '0px'
      }"
    />
    
    <!-- HTML Element -->
    <div
      v-else-if="element.type === 'html'"
      class="element-content html-element"
      v-html="element.content"
    />
    
    <!-- Resize handles (only in edit mode when selected) -->
    <div v-if="isSelected && !isPresentMode" class="resize-handles">
      <div 
        class="resize-handle nw" 
        @mousedown="(e) => startResize(e, 'nw')"
        :style="{ cursor: getCursorForDirection('nw') }"
      />
      <div 
        class="resize-handle n" 
        @mousedown="(e) => startResize(e, 'n')"
        :style="{ cursor: getCursorForDirection('n') }"
      />
      <div 
        class="resize-handle ne" 
        @mousedown="(e) => startResize(e, 'ne')"
        :style="{ cursor: getCursorForDirection('ne') }"
      />
      <div 
        class="resize-handle e" 
        @mousedown="(e) => startResize(e, 'e')"
        :style="{ cursor: getCursorForDirection('e') }"
      />
      <div 
        class="resize-handle se" 
        @mousedown="(e) => startResize(e, 'se')"
        :style="{ cursor: getCursorForDirection('se') }"
      />
      <div 
        class="resize-handle s" 
        @mousedown="(e) => startResize(e, 's')"
        :style="{ cursor: getCursorForDirection('s') }"
      />
      <div 
        class="resize-handle sw" 
        @mousedown="(e) => startResize(e, 'sw')"
        :style="{ cursor: getCursorForDirection('sw') }"
      />
      <div 
        class="resize-handle w" 
        @mousedown="(e) => startResize(e, 'w')"
        :style="{ cursor: getCursorForDirection('w') }"
      />
    </div>
    
    <!-- Visibility badge (present mode) -->
    <div 
      v-if="isPresentMode && visibilityBadge && badgeColor" 
      class="visibility-badge"
      :style="{ backgroundColor: badgeColor }"
    >
      {{ visibilityBadge }}
    </div>
  </div>
</template>

<style scoped>
.element-node {
  position: absolute;
  user-select: none;
  box-sizing: border-box;
}

.element-node.selected {
  outline: 2px solid #6366f1;
  outline-offset: 2px;
}

.element-node.present-mode.moveable {
  transition: none;
}

.element-content {
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.text-element {
  white-space: pre-wrap;
  word-wrap: break-word;
}

.image-element {
  width: 100%;
  height: 100%;
  display: block;
}

.rectangle-element {
  width: 100%;
  height: 100%;
}

.html-element {
  width: 100%;
  height: 100%;
  overflow: auto;
}

.resize-handles {
  position: absolute;
  top: -5px;
  left: -5px;
  right: -5px;
  bottom: -5px;
  pointer-events: none;
}

.resize-handle {
  position: absolute;
  width: 10px;
  height: 10px;
  background: #6366f1;
  border: 2px solid white;
  border-radius: 50%;
  pointer-events: auto;
  z-index: 10;
}

.resize-handle:hover {
  background: #4f46e5;
  transform: scale(1.2);
}

/* Corner handles */
.nw { top: -5px; left: -5px; }
.ne { top: -5px; right: -5px; }
.sw { bottom: -5px; left: -5px; }
.se { bottom: -5px; right: -5px; }

/* Edge handles */
.n { top: -5px; left: 50%; transform: translateX(-50%); }
.s { bottom: -5px; left: 50%; transform: translateX(-50%); }
.e { right: -5px; top: 50%; transform: translateY(-50%); }
.w { left: -5px; top: 50%; transform: translateY(-50%); }

.visibility-badge {
  position: absolute;
  top: -25px;
  left: 0;
  font-size: 10px;
  font-weight: 600;
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  white-space: nowrap;
  z-index: 5;
}
</style>
