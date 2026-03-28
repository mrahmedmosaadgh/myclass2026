<script setup>
import { computed, ref, watch } from 'vue';
import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import { useDrag } from '../composables/useDrag';
import { useResize } from '../composables/useResize';
import { getToolActions } from '../composables/tools/index';
import ElementContextMenu from './ElementContextMenu.vue';
import EditableText from './EditableText.vue';
import EditableMath from './EditableMath.vue';
import InteractiveMCQ from './InteractiveMCQ.vue';
import InteractiveGroupMCQ from './InteractiveGroupMCQ.vue';
import LeaderboardSlide from './LeaderboardSlide.vue';
import LiveQuestionSlideElement from './LiveQuestionSlideElement.vue';

const props = defineProps({
  element: Object
});

const presentation = usePresentationStore();
const ui = useUIStore();

const isSelected = computed(() => {
  return ui.selectedElementId === props.element.id;
});

const toolActions = computed(() => {
  return getToolActions(props.element, presentation);
});

const showContextMenu = ref(false);

watch(() => isSelected.value, (val) => {
  if (!val) {
    showContextMenu.value = false;
  }
});

function update(changes) {
  presentation.updateElement({
    id: props.element.id,
    changes
  });
}

const { startDrag } = useDrag(props.element, update);
const { startResize } = useResize(props.element, update);

const isHovered = ref(false);
const hasBeenRevealed = ref(false);
const showZoomControl = ref(false);

const isLocked = computed(() => props.element.locked || false);
const elementZoom = computed(() => props.element.zoom || 100);

function toggleLock() {
  update({ locked: !isLocked.value });
}

function adjustZoom(delta) {
  const newZoom = Math.max(25, Math.min(300, elementZoom.value + delta));
  update({ zoom: newZoom });
}

function resetZoom() {
  update({ zoom: 100 });
}

watch(() => ui.isEditMode, (newVal) => {
  if (newVal) {
    hasBeenRevealed.value = false;
  }
});

const opacity = computed(() => {
  const el = props.element;

  if (el.visibilityOption === 'hidden-clickable') {
    return el.isVisible ? 1 : el.hiddenOpacity || 0.05;
  }

  if (el.visibilityOption === 'shown-clickable') {
    return el.isVisible ? 1 : 0.1;
  }

  return 1;
});

function handleClick() {
  const el = props.element;

  if (!ui.isEditMode) {
    if (el.isConcealed) {
      hasBeenRevealed.value = !hasBeenRevealed.value;
      return;
    }

    if (el.visibilityOption === 'hidden-clickable') {
      update({ isVisible: !el.isVisible });
    }
    if (el.visibilityOption === 'shown-clickable') {
      update({ isVisible: !el.isVisible });
    }
  }
}
</script>

<template>
  <div
    class="element-node"
    :class="{ 
      selected: isSelected, 
      'edit-mode': ui.isEditMode,
      'locked': isLocked,
      'concealed-element': element.isConcealed && !hasBeenRevealed && !ui.isEditMode 
    }"
    :data-locked="isLocked"
    :style="{
      transform: `translate(${element.x}px, ${element.y}px) scale(${elementZoom / 100})`,
      transformOrigin: 'top left',
      width: element.width + 'px',
      height: element.height + 'px',
      zIndex: element.zIndex,
      opacity,
      pointerEvents: element.visibilityOption === 'no-interaction' && !ui.isEditMode ? 'none' : 'auto'
    }"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
    @mousedown.stop="if(ui.isEditMode && !isLocked) { ui.selectElement(element.id); startDrag($event); showContextMenu = false; } else if(ui.isEditMode) { ui.selectElement(element.id); }"
    @touchstart.stop="if(ui.isEditMode && !isLocked) { ui.selectElement(element.id); startDrag($event); showContextMenu = false; } else if(ui.isEditMode) { ui.selectElement(element.id); }"
    @click.stop="handleClick"
    @contextmenu.prevent="if(ui.isEditMode) { ui.selectElement(element.id); showContextMenu = true; }"
  >
    <!-- TEXT -->
    <div 
      v-if="element.type === 'text'" 
      class="text-container"
      :class="{ 'text-selected': isSelected }"
      :style="{ color: element.color || '#000' }"
    >
      <EditableText 
        :content="element.content" 
        :isEditMode="ui.isEditMode" 
        @update="(content) => update({ content })" 
        @select="ui.selectElement(element.id)"
      />
    </div>

    <!-- MATH -->
    <div 
      v-else-if="element.type === 'math'" 
      class="text-container"
      :class="{ 'text-selected': isSelected }"
      :style="{ color: element.color || '#000' }"
    >
      <EditableMath 
        :content="element.content" 
        :isEditMode="ui.isEditMode"
        @update="(newContent) => update({ content: newContent })"
        @select="if(ui.isEditMode) { ui.selectElement(element.id); showContextMenu = false; }"
      />
    </div>

    <!-- INTERACTIVE MCQ (VER 2) -->
    <InteractiveMCQ
      v-else-if="element.type === 'mcq'"
      :element="element"
      :isEditMode="ui.isEditMode"
      @update="(changes) => update(changes)"
      @select="if(ui.isEditMode) { ui.selectElement(element.id); showContextMenu = false; }"
    />

    <!-- INTERACTIVE GROUP MCQ (VER 3) -->
    <InteractiveGroupMCQ
      v-else-if="element.type === 'group-mcq'"
      :element="element"
      :isEditMode="ui.isEditMode"
      @update="(changes) => update(changes)"
      @select="if(ui.isEditMode) { ui.selectElement(element.id); showContextMenu = false; }"
    />

    <!-- LEADERBOARD NATIVE COMPONENT -->
    <LeaderboardSlide
      v-else-if="element.type === 'leaderboard'"
      :element="element"
      :isEditMode="ui.isEditMode"
      @update="(changes) => update(changes)"
      @select="if(ui.isEditMode) { ui.selectElement(element.id); showContextMenu = false; }"
    />

    <!-- LIVE QUESTION ELEMENT -->
    <LiveQuestionSlideElement
      v-else-if="element.type === 'live-question'"
      :element="element"
      :isEditMode="ui.isEditMode"
      @update="(changes) => update(changes)"
      @select="if(ui.isEditMode) { ui.selectElement(element.id); showContextMenu = false; }"
    />

    <!-- IMAGE -->
    <img
      v-else-if="element.type === 'image'"
      :src="element.src"
      style="width: 100%; height: 100%; object-fit: cover;"
    />

    <!-- RECTANGLE -->
    <div
      v-else-if="element.type === 'rectangle'"
      style="width: 100%; height: 100%; border: 1px solid #3b82f6; border-radius: 4px;"
      :style="{ backgroundColor: element.bgColor || '#93c5fd' }"
    />

    <!-- Mini Hover Toolbar -->
    <div
      v-if="ui.isEditMode && (isHovered || showContextMenu)"
      class="mini-toolbar"
      @mousedown.stop
      @click.stop
    >
      <button 
        v-for="action in toolActions" 
        :key="action.id"
        @click.prevent="action.action()" 
        :title="action.title"
        v-html="action.icon"
      ></button>

      <div v-if="toolActions.length > 0" class="mini-divider"></div>

      <!-- Interaction Modes -->
      <div class="toolbar-dropdown">
        <button 
          @click.prevent="update({ visibilityOption: 'shown-clickable' })" 
          :class="{ active: element.visibilityOption === 'shown-clickable' }"
          title="Clickable + Visible First"
        >
          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        </button>
        <div class="toolbar-dropdown-content">
          <small class="dropdown-title">Visible Setting</small>
          <p>Animations / sound properties coming soon.</p>
        </div>
      </div>

      <div class="toolbar-dropdown">
        <button 
          @click.prevent="update({ visibilityOption: 'hidden-clickable' })" 
          :class="{ active: element.visibilityOption === 'hidden-clickable' }"
          title="Clickable + Hidden First"
        >
          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
        </button>
        <div class="toolbar-dropdown-content">
          <small class="dropdown-title">Hidden Setting</small>
          <p>Animations / sound properties coming soon.</p>
        </div>
      </div>

      <div class="toolbar-dropdown">
        <button 
          @click.prevent="update({ visibilityOption: 'no-interaction' })" 
          :class="{ active: element.visibilityOption === 'no-interaction' || !element.visibilityOption }"
          title="Not Clickable + Visible"
        >
          <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </button>
        <div class="toolbar-dropdown-content">
          <small class="dropdown-title">Static Setting</small>
          <p>Animations / sound properties coming soon.</p>
        </div>
      </div>

      <div class="mini-divider"></div>

      <!-- Layer Control -->
      <div class="layer-control" title="Adjust Layer (Z-Index)">
        <button @click.prevent="update({ zIndex: Math.max(0, (element.zIndex || 1) - 1) })" title="Send Backward">-</button>
        <span class="z-index-display">{{ element.zIndex || 1 }}</span>
        <button @click.prevent="update({ zIndex: (element.zIndex || 1) + 1 })" title="Bring Forward">+</button>
      </div>

      <div class="mini-divider"></div>

      <!-- Lock/Unlock Button -->
      <button 
        @click.prevent="toggleLock" 
        :class="{ 'active': isLocked }"
        :title="isLocked ? 'Unlock Element' : 'Lock Element'"
      >
        <svg v-if="isLocked" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
          <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
        </svg>
      </button>

      <!-- Zoom Control -->
      <div class="toolbar-dropdown zoom-dropdown">
        <button 
          @click.prevent="showZoomControl = !showZoomControl"
          :class="{ 'active': elementZoom !== 100 }"
          title="Element Zoom"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            <line x1="11" y1="8" x2="11" y2="14"></line>
            <line x1="8" y1="11" x2="14" y2="11"></line>
          </svg>
        </button>
        <div v-if="showZoomControl" class="toolbar-dropdown-content zoom-control" @mousedown.stop @click.stop>
          <small class="dropdown-title">Element Zoom</small>
          <div class="zoom-buttons">
            <button @click.prevent="adjustZoom(-10)" class="zoom-btn">-</button>
            <span class="zoom-display">{{ elementZoom }}%</span>
            <button @click.prevent="adjustZoom(10)" class="zoom-btn">+</button>
          </div>
          <input 
            type="range" 
            :value="elementZoom" 
            @input="update({ zoom: parseInt($event.target.value) })"
            min="25" 
            max="300" 
            step="5"
            class="zoom-slider"
          />
          <button @click.prevent="resetZoom" class="reset-zoom-btn">Reset to 100%</button>
        </div>
      </div>

      <div class="mini-divider"></div>

      <button @click.prevent="presentation.duplicateElement(element.id)" title="Duplicate">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
      </button>
      <button @click.prevent="presentation.deleteElement(element.id)" title="Delete" class="text-red-500">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
      </button>
      <button @click.prevent="showContextMenu = !showContextMenu" title="Menu" :class="{ 'active': showContextMenu }">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
      </button>
    </div>

    <!-- Context Menu Component -->
    <ElementContextMenu
      v-if="showContextMenu"
      :element="element"
      @duplicate="() => presentation.duplicateElement(element.id)"
      @delete="() => presentation.deleteElement(element.id)"
    />

    <!-- Explicit Touch Move Action (Mandatory for mobile text overlap) -->
    <div 
      v-if="isSelected && ui.isEditMode"
      class="move-handle"
      @mousedown.stop.prevent="startDrag($event)"
      @touchstart.stop.prevent="startDrag($event)"
      title="Drag to Move"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 9l-3 3 3 3M9 5l3-3 3 3M9 19l3 3 3-3M19 9l3 3-3 3M2 12h20M12 2v20"/></svg>
    </div>

    <!-- Resize Handles (disabled when locked) -->
    <div v-if="isSelected && !isLocked">
      <div class="handle nw" @mousedown="(e) => startResize(e, 'nw')" @touchstart="(e) => startResize(e, 'nw')" />
      <div class="handle n"  @mousedown="(e) => startResize(e, 'n')"  @touchstart="(e) => startResize(e, 'n')" />
      <div class="handle ne" @mousedown="(e) => startResize(e, 'ne')" @touchstart="(e) => startResize(e, 'ne')" />
      <div class="handle e"  @mousedown="(e) => startResize(e, 'e')"  @touchstart="(e) => startResize(e, 'e')" />
      <div class="handle se" @mousedown="(e) => startResize(e, 'se')" @touchstart="(e) => startResize(e, 'se')" />
      <div class="handle s"  @mousedown="(e) => startResize(e, 's')"  @touchstart="(e) => startResize(e, 's')" />
      <div class="handle sw" @mousedown="(e) => startResize(e, 'sw')" @touchstart="(e) => startResize(e, 'sw')" />
      <div class="handle w"  @mousedown="(e) => startResize(e, 'w')"  @touchstart="(e) => startResize(e, 'w')" />
    </div>
  </div>
</template>

<style scoped>
.element-node {
  position: absolute;
  user-select: none;
  background-color: transparent;
  transition: outline 0.1s ease-in-out;
}

.element-node.edit-mode {
  cursor: move;
}

.element-node.edit-mode.locked {
  cursor: not-allowed;
}

.element-node.edit-mode:not(.selected) {
  outline: 1px dashed #cbd5e1;
}

.element-node.edit-mode:not(.selected):hover {
  outline: 1px solid #94a3b8;
}

.element-node.selected {
  outline: 2px solid #6366f1;
}

.element-node.selected.locked {
  outline: 2px dashed #f59e0b;
  outline-offset: 2px;
}

.text-container {
  width: 100%; 
  height: 100%; 
  padding: 4px; 
  box-sizing: border-box; 
  font-size: 1.5rem;
  border: 2px dotted transparent;
  transition: border-color 0.2s;
}

/* For text, we can override the node outline to be completely hidden, and let the text-container dashed border do the job. Or keep both. Let's just keep the container logic visible. */
.element-node.selected .text-selected {
  border-color: #6366f1;
}

.element-node:has(.text-selected).selected {
  outline: none; /* remove parent solid ring so only the dots remain inside */
}

.handle {
  position: absolute;
  width: 14px;
  height: 14px;
  background: #6366f1;
  border-radius: 50%;
  pointer-events: auto;
  box-shadow: 0 1px 3px rgba(0,0,0,0.3);
  transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

/* Corners */
.nw { top: -7px; left: -7px; cursor: nwse-resize; }
.ne { top: -7px; right: -7px; cursor: nesw-resize; }
.sw { bottom: -7px; left: -7px; cursor: nesw-resize; }
.se { bottom: -7px; right: -7px; cursor: nwse-resize; }
.nw:hover, .ne:hover, .sw:hover, .se:hover,
.nw:active, .ne:active, .sw:active, .se:active {
  transform: scale(1.5);
  background-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.3);
}

/* Edges */
.n { top: -7px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
.s { bottom: -7px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
.n:hover, .s:hover,
.n:active, .s:active {
  transform: translateX(-50%) scale(1.5);
  background-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.3);
}

.e { right: -7px; top: 50%; transform: translateY(-50%); cursor: ew-resize; }
.w { left: -7px; top: 50%; transform: translateY(-50%); cursor: ew-resize; }
.e:hover, .w:hover,
.e:active, .w:active {
  transform: translateY(-50%) scale(1.5);
  background-color: #10b981;
  box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.3);
}

.mini-toolbar {
  position: absolute;
  top: -35px;
  right: 0;
  display: flex;
  gap: 4px;
  background: white;
  padding: 4px;
  border-radius: 6px;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  border: 1px solid #e5e7eb;
  z-index: 1000;
}

/* Invisible bridge so mouse doesn't trigger mouseleave in the gap */
.mini-toolbar::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  height: 15px;
}

.mini-toolbar button {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  color: #4b5563;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.mini-toolbar button:hover {
  background: #f3f4f6;
  color: #111827;
}

.mini-toolbar button.active {
  background: #6366f1;
  color: white;
}

.mini-divider {
  width: 1px;
  height: 14px;
  background: #e5e7eb;
  margin: 0 4px;
}

.toolbar-dropdown {
  position: relative;
  display: flex;
}

.toolbar-dropdown-content {
  display: none;
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  margin-top: 6px;
  background: white;
  min-width: 130px;
  box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 8px;
  z-index: 1005;
  flex-direction: column;
}

.toolbar-dropdown:hover .toolbar-dropdown-content {
  display: flex;
}

.move-handle {
  position: absolute;
  top: -30px;
  left: 0;
  width: 26px;
  height: 26px;
  background: #3b82f6;
  color: white;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: grab;
  z-index: 1005;
  pointer-events: auto;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.move-handle:active {
  cursor: grabbing;
  background: #2563eb;
  transform: scale(1.05);
}

.toolbar-dropdown-content::before {
  content: "";
  position: absolute;
  top: -8px;
  left: 0;
  width: 100%;
  height: 8px;
}

/* Zoom Control Styles */
.zoom-control {
  min-width: 180px;
  padding: 12px;
}

.zoom-buttons {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin: 8px 0;
}

.zoom-btn {
  width: 32px;
  height: 32px;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.zoom-btn:hover {
  background: #e5e7eb;
  border-color: #9ca3af;
}

.zoom-display {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
  min-width: 50px;
  text-align: center;
}

.zoom-slider {
  width: 100%;
  margin: 8px 0;
  cursor: pointer;
}

.zoom-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 16px;
  height: 16px;
  background: #6366f1;
  border-radius: 50%;
  cursor: pointer;
}

.zoom-slider::-moz-range-thumb {
  width: 16px;
  height: 16px;
  background: #6366f1;
  border-radius: 50%;
  cursor: pointer;
  border: none;
}

.reset-zoom-btn {
  width: 100%;
  padding: 6px 12px;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 8px;
}

.reset-zoom-btn:hover {
  background: #e5e7eb;
  border-color: #9ca3af;
}

/* Locked Element Indicator */
.element-node.edit-mode.selected {
  outline: 2px solid #6366f1;
}

.element-node.edit-mode.selected:has([data-locked="true"]) {
  outline: 2px dashed #f59e0b;
  outline-offset: 2px;
}

/* Hide move handle when locked */
.move-handle {
  display: flex;
}

.element-node:has([data-locked="true"]) .move-handle {
  display: none;
}

.dropdown-title {
  font-size: 10px;
  font-weight: 700;
  color: #4b5563;
  margin-bottom: 2px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.toolbar-dropdown-content p {
  font-size: 10px;
  color: #9ca3af;
  margin: 0;
  line-height: 1.3;
}

.layer-control {
  display: flex;
  align-items: center;
  background: #f8fafc;
  border-radius: 4px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.layer-control button {
  padding: 2px 6px;
  font-size: 12px;
  font-weight: bold;
  border-radius: 0;
}

.layer-control button:hover {
  background: #e2e8f0;
}

.z-index-display {
  font-size: 10px;
  font-weight: 700;
  min-width: 14px;
  text-align: center;
  color: #475569;
  pointer-events: none;
}

.text-red-500:hover {
  color: #ef4444 !important;
  background-color: #fee2e2 !important;
}

.concealed-element {
  border: 3px dashed #9ca3af;
  background-color: rgba(243, 244, 246, 0.9);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: inset 0 0 10px rgba(0,0,0,0.05);
}

.concealed-element > * {
  opacity: 0 !important;
  pointer-events: none !important;
}

.concealed-element:hover {
  background-color: rgba(229, 231, 235, 1);
  border-color: #6b7280;
}
</style>
