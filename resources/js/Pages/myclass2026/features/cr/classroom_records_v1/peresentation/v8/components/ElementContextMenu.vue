<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { usePresentationStore } from '../stores/presentationStore.js'
import { useUIStore } from '../stores/uiStore.js'

const presentation = usePresentationStore()
const ui = useUIStore()

const props = defineProps({
  show: Boolean,
  x: Number,
  y: Number,
  elementId: String
})

const element = computed(() => {
  if (!props.elementId) return null
  return presentation.currentSlide.elements.find(el => el.id === props.elementId)
})

function update(changes) {
  if (!props.elementId) return
  presentation.updateElement({
    id: props.elementId,
    changes
  })
}

function duplicate() {
  if (!props.elementId) return
  presentation.duplicateElement(props.elementId)
  ui.hideContextMenu()
}

function deleteElement() {
  if (!props.elementId) return
  presentation.deleteElement(props.elementId)
  ui.hideContextMenu()
}

function bringToFront() {
  if (!props.elementId) return
  presentation.bringToFront(props.elementId)
}

function sendToBack() {
  if (!props.elementId) return
  presentation.sendToBack(props.elementId)
}

function setVisibility(option) {
  update({
    visibilityOption: option,
    isVisible: option === 'hidden-clickable' ? false : true
  })
}

function resetVisibility() {
  update({
    visibilityOption: 'shown-clickable',
    isVisible: true,
    hiddenOpacity: 0.05
  })
}

function setHiddenOpacity(opacity) {
  update({ hiddenOpacity: opacity })
}

// Close on click outside
function handleClickOutside(e) {
  if (!e.target.closest('.context-menu')) {
    ui.hideContextMenu()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <teleport to="body">
    <div
      v-if="show && element"
      class="context-menu"
      :style="{
        left: x + 'px',
        top: y + 'px'
      }"
    >
      <!-- Visibility Section -->
      <div class="menu-section">
        <div class="section-title">Visibility</div>
        
        <button 
          @click="setVisibility('hidden-clickable')"
          :class="{ active: element.visibilityOption === 'hidden-clickable' }"
          class="menu-item"
        >
          <div class="item-icon">👁️‍🗨️</div>
          <div class="item-content">
            <div class="item-title">Start Hidden</div>
            <div class="item-description">Click to show during presentation</div>
          </div>
        </button>
        
        <button 
          @click="setVisibility('shown-clickable')"
          :class="{ active: element.visibilityOption === 'shown-clickable' }"
          class="menu-item"
        >
          <div class="item-icon">👁️</div>
          <div class="item-content">
            <div class="item-title">Start Visible</div>
            <div class="item-description">Click to hide during presentation</div>
          </div>
        </button>
        
        <button 
          @click="setVisibility('moveable')"
          :class="{ active: element.visibilityOption === 'moveable' }"
          class="menu-item"
        >
          <div class="item-icon">✋</div>
          <div class="item-content">
            <div class="item-title">Moveable</div>
            <div class="item-description">Drag to reposition during presentation</div>
          </div>
        </button>
        
        <button 
          @click="setVisibility('no-interaction')"
          :class="{ active: element.visibilityOption === 'no-interaction' }"
          class="menu-item"
        >
          <div class="item-icon">🔒</div>
          <div class="item-content">
            <div class="item-title">No Interaction</div>
            <div class="item-description">Always visible, no click behavior</div>
          </div>
        </button>
        
        <!-- Hidden Opacity Slider (for hidden elements) -->
        <div v-if="element.visibilityOption === 'hidden-clickable'" class="menu-item slider">
          <div class="item-icon">👁️‍🗨️</div>
          <div class="item-content">
            <div class="item-title">Hidden Opacity</div>
            <div class="slider-container">
              <input
                type="range"
                min="0"
                max="0.5"
                step="0.05"
                :value="element.hiddenOpacity || 0.05"
                @input="setHiddenOpacity(parseFloat($event.target.value))"
                class="opacity-slider"
              />
              <span class="slider-value">{{ Math.round((element.hiddenOpacity || 0.05) * 100) }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Layers Section -->
      <div class="menu-section">
        <div class="section-title">Layers</div>
        
        <button @click="bringToFront" class="menu-item">
          <div class="item-icon">⬆️</div>
          <div class="item-content">
            <div class="item-title">Bring to Front</div>
            <div class="item-description">Move element to top layer</div>
          </div>
        </button>
        
        <button @click="sendToBack" class="menu-item">
          <div class="item-icon">⬇️</div>
          <div class="item-content">
            <div class="item-title">Send to Back</div>
            <div class="item-description">Move element to bottom layer</div>
          </div>
        </button>
      </div>

      <!-- Element Section -->
      <div class="menu-section">
        <div class="section-title">Element</div>
        
        <button @click="duplicate" class="menu-item">
          <div class="item-icon">📋</div>
          <div class="item-content">
            <div class="item-title">Duplicate</div>
            <div class="item-description">Create a copy of this element</div>
          </div>
        </button>
        
        <button @click="deleteElement" class="menu-item danger">
          <div class="item-icon">🗑️</div>
          <div class="item-content">
            <div class="item-title">Delete</div>
            <div class="item-description">Remove this element</div>
          </div>
        </button>
        
        <button @click="resetVisibility" class="menu-item">
          <div class="item-icon">🔄</div>
          <div class="item-content">
            <div class="item-title">Reset Visibility</div>
            <div class="item-description">Reset to default visibility settings</div>
          </div>
        </button>
      </div>
    </div>
  </teleport>
</template>

<style scoped>
.context-menu {
  position: fixed;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  padding: 8px 0;
  min-width: 280px;
  max-width: 320px;
  z-index: 10000;
  max-height: 80vh;
  overflow-y: auto;
}

.menu-section {
  border-bottom: 1px solid #f3f4f6;
  padding: 0 8px;
}

.menu-section:last-child {
  border-bottom: none;
}

.section-title {
  font-size: 11px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 8px 0 4px 0;
  margin: 0;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 8px 12px;
  border: none;
  background: transparent;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
  border-radius: 6px;
  margin: 2px 0;
  text-align: left;
}

.menu-item:hover {
  background: #f9fafb;
  color: #111827;
}

.menu-item.active {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}

.menu-item.danger {
  color: #dc2626;
}

.menu-item.danger:hover {
  background: #fef2f2;
  color: #b91c1c;
}

.menu-item.slider {
  cursor: default;
}

.item-icon {
  font-size: 16px;
  width: 20px;
  text-align: center;
  flex-shrink: 0;
}

.item-content {
  flex: 1;
  min-width: 0;
}

.item-title {
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 2px;
}

.item-description {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.3;
}

.slider-container {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}

.opacity-slider {
  flex: 1;
  height: 4px;
  border-radius: 2px;
  background: #e5e7eb;
  outline: none;
  cursor: pointer;
}

.opacity-slider::-webkit-slider-thumb {
  appearance: none;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #6366f1;
  cursor: pointer;
  border: 2px solid white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.opacity-slider::-moz-range-thumb {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #6366f1;
  cursor: pointer;
  border: 2px solid white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.slider-value {
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  min-width: 40px;
  text-align: right;
}
</style>
